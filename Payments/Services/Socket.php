<?php


namespace Payments\Services;


use Illuminate\Support\Facades\Http;

class Socket
{

    private $socket_url = 'http://0.0.0.0:2121/socket';

    public function sendInvoiceMessage(Invoice $invoice_model, $status)
    {
        Http::post($this->socket_url, [
            "uid" => $invoice_model->getTransactionId(),
            "content" => [
                "name" => $status,
                "amount" => $invoice_model->getDueAmount(),
                "checkout_link" => $invoice_model->getCheckoutLink(),
                "payment_currency" => $invoice_model->getPaymentCurrency()
            ]]);
    }


}
