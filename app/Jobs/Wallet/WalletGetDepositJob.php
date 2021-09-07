<?php

namespace App\Jobs\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Wallets\Services\Deposit;
use Wallets\Services\WalletService;

class WalletGetDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var $deposit_data Deposit
         */
        $deposit_data = unserialize($this->data);
        $wallet_service = app(WalletService::class);
        $deposit_response = $wallet_service->deposit($deposit_data);

        WalletDepositDataJob::dispatch($deposit_response)->onConnection('rabbit')->onQueue($deposit_data->getServiceName());

    }
}
