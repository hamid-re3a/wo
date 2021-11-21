<?php

namespace Payments\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orders\Services\Grpc\Id;
use Orders\Services\OrderService;
use Payments\Repository\InvoiceRepository;

class InvoiceService
{
    private $invoice_repository;

    public function __construct(InvoiceRepository $invoice_repository)
    {
        $this->invoice_repository = $invoice_repository;
    }

   public function cancelInvoice($transaction_id) : bool
   {
       try {
           DB::beginTransaction();
           $invoice = $this->invoice_repository->cancelInvoice($transaction_id);

           //Cancel Order
           $order_service = app(OrderService::class);
           $id = new Id();
           $id->setId($invoice->payable_id);
           $ack = $order_service->cancelOrder($id);
           if($ack->getStatus()) {
               DB::commit();
               return true;
           }

           throw new \Exception();


       } catch (\Throwable $exception) {
           DB::rollBack();
           Log::error('\Payments\Services\InvoiceService@cancelInvoice exception => ' . $exception->getMessage());
           throw $exception;
       }
   }
}
