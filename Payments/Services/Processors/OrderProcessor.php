<?php


namespace Payments\Services\Processors;


use Illuminate\Support\Facades\Log;
use MLM\Services\Grpc\Acknowledge;
use Orders\Services\Grpc\Id;
use Orders\Services\Grpc\Order;
use Orders\Services\OrderService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;
use Payments\Models\Invoice;
use User\Models\User;
use function Symfony\Component\Translation\t;

class OrderProcessor extends ProcessorAbstract
{
    /**
     * @var $order_service Order
     * @var $invoice_db Invoice
     */

    private $order_service;

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
        if(!is_numeric($this->order_service->getId())){
            Log::error('OrderProcessor Error => Cant fetch order {invoice} => ' . $invoice_db->id);
        }

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'partial_paid');

        // send email notification for due amount
        $user = User::query()->whereId($this->order_service->getUserId())->first();
        EmailJob::dispatch(new EmailInvoicePaidPartial($user->getUserService(), $this->invoice_db, $this->order_service), $user->email);


    }

    public function processing()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'paid');

    }

    public function completed()
    {
        // TODO handle if mlm grpc is down condition

        //Send order to MLM
        /** @var $submit_response Acknowledge */
        $mlm_grpc = new \MLM\Services\Grpc\MLMServiceClient('staging-api-gateway.janex.org:9598', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
        list($submit_response, $flag) = $mlm_grpc->submitOrder($this->order_service)->wait();
        if ($flag->code != 0)
            throw new \Exception('MLM Grpc error', 406);



        $this->invoice_db->update([
            'is_paid' => true
        ]);
        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'confirmed');

        // send thank you email notification
        $user = User::query()->whereId($this->order_service->getUserId())->first();
        EmailJob::dispatch(new EmailInvoicePaidComplete($user->getUserService(), $this->invoice_db), $user->email);


        if ($submit_response->getStatus()) {
            //Update order
            $this->order_service->setIsPaidAt(now()->toDateTimeString());
            app(OrderService::class)->updateOrder($this->order_service);
        }

    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'expired');

        // send email to user to regenerate new invoice for due amount
        $user = User::query()->whereId($this->order_service->getUserId())->first();
        EmailJob::dispatch(new EmailInvoiceExpired($user->getUserService(), $this->invoice_db), $user->email);

    }

    public function invalid()
    {

        // TODO: Implement invalid() method.

    }
}
