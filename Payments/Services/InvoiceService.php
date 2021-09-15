<?php

namespace Payments\Services;

use Payments\Repository\InvoiceRepository;

class InvoiceService
{
    private $invoice_repository;

    public function __construct(InvoiceRepository $invoice_repository)
    {
        $this->invoice_repository = $invoice_repository;
    }

   public function cancelInvoice($transaction_id)
   {
       return $this->invoice_repository->cancelInvoice($transaction_id);
   }
}
