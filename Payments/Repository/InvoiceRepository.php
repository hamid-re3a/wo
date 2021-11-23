<?php

namespace Payments\Repository;


use Payments\Models\Invoice;

class InvoiceRepository
{
    protected $entity_name = Invoice::class;

    public function cancelInvoice($transaction_id) : Invoice
    {
        /**@var $invoice_entity Invoice*/
        $invoice_entity = new $this->entity_name;
        $invoice_find = $invoice_entity->query()->where("transaction_id", $transaction_id)->first();
        $invoice_find->status = "user_cancel";
        $invoice_find->save();
        return $invoice_find->refresh();
    }

}
