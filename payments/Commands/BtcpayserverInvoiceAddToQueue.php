<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Payments\Jobs\BtcpayserverInvoiceResolveJob;
use Payments\Models\Invoice;

class BtcpayserverInvoiceAddToQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invoice check';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1. if this invest plan has got its 24 profits and the plan date is expired return
        $invoices = Invoice::query()->whereNotIn('status',['Expired','Complete','Settled'])->get();
        $bar = $this->output->createProgressBar($invoices->count());
        $this->info(PHP_EOL . 'Start invoices');
        $bar->start();

        foreach ($invoices as $item) {
            BtcpayserverInvoiceResolveJob::dispatch($item);
            $bar->advance();
        }


        $bar->finish();
        $this->info(PHP_EOL . 'invoices completed successfully' . PHP_EOL);

    }


}
