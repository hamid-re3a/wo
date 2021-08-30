<?php


namespace Payments\Services\Processors;


use Orders\Services\Id;
use Orders\Services\OrderService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;

class OrderProcessor extends ProcessorAbstract
{
    private $order_model;

    public function __construct(\Payments\Models\Invoice $invoice_db)
    {

        parent::__construct($invoice_db);

        $order_service = app(OrderService::class);
        $order_id = new Id();
        $order_id->setId($this->invoice_model->getPayableId());
        $this->order_model = $order_service->OrderById($order_id);

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'partial_paid');

        // send email notification for due amount
        EmailJob::dispatch(new EmailInvoicePaidPartial($this->order_model->getUser(), $this->invoice_model,$this->order_model), $this->order_model->getUser()->getEmail());


    }

    public function processing()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'paid');

    }

    public function completed()
    {

        $this->invoice_db->update([
            'is_paid' => true
        ]);

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'confirmed');

        // send thank you email notification
        EmailJob::dispatch(new EmailInvoicePaidComplete($this->order_model->getUser(), $this->invoice_model), $this->order_model->getUser()->getEmail());

        //Update order
        $this->order_model->setIsPaidAt(now()->toDateTimeString());
        app(OrderService::class)->updateOrder($this->order_model);
    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'expired');

        // send email to user to regenerate new invoice for due amount
        EmailJob::dispatch(new EmailInvoiceExpired($this->order_model->getUser(), $this->invoice_model), $this->order_model->getUser()->getEmail());

    }

    public function invalid()
    {

        // TODO: Implement invalid() method.

    }
}
