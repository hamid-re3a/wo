<?php


namespace Payments\Services\Processors;


use App\Jobs\Order\MLMOrderJob;
use Orders\Services\Grpc\Id;
use Orders\Services\Grpc\Order;
use Orders\Services\OrderService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;
use Payments\Models\Invoice;

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

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'partial_paid');

        // send email notification for due amount
        EmailJob::dispatch(new EmailInvoicePaidPartial($this->order_service->getUser(), $this->invoice_db,$this->order_service), $this->order_service->getUser()->getEmail());


    }

    public function processing()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'paid');

    }

    public function completed()
    {
        $this->invoice_db->update([
            'is_paid' => true
        ]);

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'confirmed');

        // send thank you email notification
        EmailJob::dispatch(new EmailInvoicePaidComplete($this->order_service->getUser(), $this->invoice_db), $this->order_service->getUser()->getEmail());


        //Update order
        $this->order_service->setIsPaidAt(now()->toDateTimeString());
        app(OrderService::class)->updateOrder($this->order_service);

        //Send order to MLM
        MLMOrderJob::dispatch(serialize($this->order_service))->onConnection('rabbit')->onQueue('mlm');
    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'expired');

        // send email to user to regenerate new invoice for due amount
        EmailJob::dispatch(new EmailInvoiceExpired($this->order_service->getUser(), $this->invoice_db), $this->order_service->getUser()->getEmail());

    }

    public function invalid()
    {

        // TODO: Implement invalid() method.

    }
}
