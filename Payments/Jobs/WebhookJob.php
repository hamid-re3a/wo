<?php

namespace Payments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Payments\Models\Invoice;

class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function handle()
    {

        $event = json_decode($this->content, true);
        if (isset($event['type']) && isset($event['invoiceId']) &&
            isset($event['timestamp']) && !empty($event['invoiceId'])) {


            $invoice_db = Invoice::query()->where('transaction_id', $event['invoiceId'])->first();


            if($invoice_db)
                InvoiceResolverBTCPayServerJob::dispatch($invoice_db);
            else{
                Log::error('Webhook called | Invalid TransactionID => ' . $event['invoiceId']);
            }
        } else {
            throw new \Exception('btc pay server issues');
        }

    }


}
