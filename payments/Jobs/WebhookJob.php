<?php

namespace Payments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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

            $invoice_db = Invoice::query()->firstOrCreate([
                'transaction_id'=> $event['invoiceId']
            ]);


            BtcpayserverInvoiceResolveJob::dispatch($invoice_db);


        } else {
            throw new \Exception('btc pay server issues');
        }

    }


}
