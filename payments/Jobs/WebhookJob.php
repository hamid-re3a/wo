<?php

namespace Payments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        // to implement

        $event = json_decode($this->content);

        if (isset($event['type']) && isset($event['invoiceId']) &&
            isset($event['timestamp']) && isset($event['invoiceId'])) {

        }

        throw new \Exception();
    }
}
