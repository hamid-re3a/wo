<?php

namespace Wallets\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Wallets\Jobs\ProcessBTCTransactionsJob;
use Wallets\Models\WithdrawProfit;

class ProcessWithdrawalRequestsCommand extends Command
{

    private $wallets;
    private $ids;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:process-withdrawals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process small withdrawal requests';

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
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $maximum_amount = walletGetSetting('maximum_auto_handle_withdrawals');

        //Query withdrawal requests
        $withdrawal_requests = WithdrawProfit::query()->whereHas('withdrawTransaction', function (Builder $query) use ($maximum_amount) {
            $query->where('amount', '<=', $maximum_amount * 100);
            $query->where('wallet_id', '!=', 1);
            $query->where('type', '=', 'withdraw');
            $query->whereHas('metaData', function (Builder $subQuery) {
                $subQuery->where('name', '=', 'Withdraw request');
            });
        })->where('status','=',1);

        if ($withdrawal_requests->count() >= 50) {

            $this->wallets = [];
            $this->ids = [];

            $withdrawal_requests->chunkById(50, function ($chunked) {
                foreach ($chunked AS $withdraw_request) {
                    $this->ids[] = $withdraw_request->id;
                    $this->wallets[] = [
                        'destination' => $withdraw_request->wallet_address,
                        'amount' => $withdraw_request->withdrawTransaction->amount / 100
                    ];
                }
                ProcessBTCTransactionsJob::dispatch($this->wallets, $this->ids);
                unset($this->chunked);
                unset($withdraw_request);
                unset($this->wallets);
                unset($this->ids);
            });
        }


    }
}
