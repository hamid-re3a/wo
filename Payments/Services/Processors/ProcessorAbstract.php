<?php


namespace Payments\Services\Resolves;


use Payments\Models\Invoice;
use Payments\Services\Id;
use Payments\Services\PaymentService;
use Payments\Services\Socket;

abstract class ProcessorAbstract
{
    protected $socket_service;
    protected $invoice_db;
    protected $invoice_model;

    public function __construct(Invoice $invoice_db) {

        $this->socket_service = new Socket();
        $this->invoice_db = $invoice_db;

        $payment_Id = new Id();
        $payment_Id->setId((int)$this->invoice_db->id);
        $this->invoice_model = app(PaymentService::class)->getInvoiceById($payment_Id);
    }

    public function mlmStuff()
    {
        //TODO MLM dispatch job dispatch
    }

    abstract public function processing();

    abstract public function partial();

    abstract public function completed();

    abstract public function expired();

    abstract public function invalid();

}
