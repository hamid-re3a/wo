<?php


namespace Payments\Services\Processors;


use Payments\Models\Invoice;

interface ProcessorInterface
{
    public function __construct(Invoice $invoice_db);

    public function processing();

    public function partial();

    public function completed();

    public function expired();

    public function invalid();
}
