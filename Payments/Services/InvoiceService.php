<?php

namespace Payments\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceCreated;
use Payments\Mail\Payment\Wallet\EmailWalletInvoiceCreated;
use Payments\Repository\InvoiceRepository;
use Payments\Repository\PaymentCurrencyRepository;
use Payments\Repository\PaymentDriverRepository;
use Payments\Repository\PaymentTypesRepository;

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
