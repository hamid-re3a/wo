<?php


namespace Payments\Services\Processors;


use Payments\Models\Invoice;
use Payments\Services\Socket;

abstract class ProcessorAbstract
{
    protected $socket_service;
    protected $invoice_db;
    protected $invoice_model;

    public function __construct(Invoice $invoice_db) {

        $this->socket_service = new Socket();
        $this->invoice_db = $invoice_db;
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
