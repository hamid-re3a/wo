<?php


namespace Payments\Services\Processors;


use MLM\Services\Grpc\Acknowledge;
use MLM\Services\MlmClientFacade;
use Orders\Services\Grpc\Id;
use Orders\Services\Grpc\Order;
use Orders\Services\OrderService;
use Packages\Services\PackageService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaid;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;
use Payments\Models\Invoice;
use User\Models\User;
use Wallets\Services\WalletService;

class OrderProcessor extends ProcessorAbstract
{
    /**
     * @var $order_service Order
     * @var $invoice_db Invoice
     * @var $user User
     */

    private $order_service;
    private $user;

    public function __construct(Invoice $invoice_db)
    {

        /**
         * @var $order_service Order
         * @var $invoice_db Invoice
         */
        parent::__construct($invoice_db);

        $order_service = app(OrderService::class);
        $order_id = new Id();
        $order_id->setId($this->invoice_db->payable_id);
        $this->order_service = $order_service->OrderById($order_id);

        $this->user = User::query()->whereId($this->order_service->getUserId())->first();

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'partial_paid');

        // send email notification for due amount
        EmailJob::dispatch(new EmailInvoicePaidPartial($this->user->getGrpcMessage(), $this->invoice_db, $this->order_service), $this->user->email);


    }

    public function processing()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'paid');


        EmailJob::dispatch(new EmailInvoicePaid($this->user->getGrpcMessage(), $this->invoice_db), $this->user->email);

    }

    public function completed()
    {
        // TODO handle if mlm grpc is down condition

        //Send order to MLM
        /**
         * @var $submit_response Acknowledge
         * @var $order_service Order
         */
        $order_service = $this->order_service;
        $order_service->setIsPaidAt(now()->toDateTimeString());
        $order_service->setIsResolvedAt(now()->toDateTimeString());
        $acknowledge = MlmClientFacade::submitOrder($order_service);
        if ($acknowledge)
            throw new \Exception();


        if ($submit_response->getStatus()) {
            $package_service = app(PackageService::class);
            $package_object = $package_service->packageFullById(app(\Packages\Services\Grpc\Id::class)->setId($this->order_service->getPackageId()));

            //Deposit to Charity
            $wallet_service = app(WalletService::class);
            $wallet_service->depositIntoCharityWallet($order_service->getPackagesCostInPf(),[
                'order_id' => $order_service->getId(),
                'member_id' => $this->user->member_id,
                'package_name' => $package_object->getName() . '(' . $package_object->getShortName() .')',
            ],'Package purchased');


            //Update order
            $order_service->setIsCommissionResolvedAt($submit_response->getCreatedAt());
            app(OrderService::class)->updateOrder($order_service);

            $this->invoice_db->update([
                'is_paid' => true
            ]);
            // send web socket notification
            $this->socket_service->sendInvoiceMessage($this->invoice_db, 'confirmed');

            // send thank you email notification
            EmailJob::dispatch(new EmailInvoicePaidComplete($this->user->getGrpcMessage(), $this->invoice_db), $this->user->email);
        }

    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'expired');

        // send email to user to regenerate new invoice for due amount
        EmailJob::dispatch(new EmailInvoiceExpired($this->user->getGrpcMessage(), $this->invoice_db), $this->user->email);

    }

    public function invalid()
    {

        // TODO: Implement invalid() method.

    }
}
