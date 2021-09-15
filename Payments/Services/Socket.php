<?php


namespace Payments\Services;


use Illuminate\Support\Facades\Http;

class Socket
{

    private $socket_url = 'http://0.0.0.0:2121/socket';

    public function sendInvoiceMessage(\Payments\Models\Invoice $invoice_model, $status)
    {
        Http::post($this->socket_url, [
            "uid" => $invoice_model->transaction_id,
            "content" => [
                "name" => $status,
                "amount" => $invoice_model->due_amount,
                "checkout_link" => $invoice_model->checkout_link,
                "payment_currency" => $invoice_model->payment_currency
            ]]);
    }


}
