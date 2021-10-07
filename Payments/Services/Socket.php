<?php


namespace Payments\Services;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Socket
{

    private $socket_url = 'http://0.0.0.0:2121/socket';

    public function __construct()
    {
        $this->socket_url = env('SUBSCRIPTION_WEBSOCKET_URL','http://0.0.0.0:2121/socket');
    }

    public function sendInvoiceMessage(\Payments\Models\Invoice $invoice_model, $status)
    {
        Log::info('Socket sent => ' . $invoice_model->transaction_id . ' name => ' . $status . ' checkout_link => ' . $invoice_model->checkout_link);
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
