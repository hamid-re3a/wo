<?php

namespace Wallets\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Payments\Services\Processors\PayoutProcessor;
use Wallets\Jobs\ProcessBTCTransactionsJob;
use Wallets\Models\WithdrawProfit;

class ProcessWithdrawalRequestsCommand extends Command
{

    private $wallets;
    private $payout_requests;
    private $payout_processor;

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
     * @param PayoutProcessor $payout_processor
     */
    public function __construct(PayoutProcessor $payout_processor)
    {
        parent::__construct();
        $this->payout_processor = $payout_processor;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (!walletGetSetting('auto_payout_withdrawal_request_is_enable'))
            return null;

        $maximum_amount = walletGetSetting('maximum_auto_handle_withdrawals_payout');
        $count_withdraw_requests = walletGetSetting('count_withdraw_requests_to_automatic_payout_process') ? walletGetSetting('count_withdraw_requests_to_automatic_payout_process') : 50;

        //Query withdrawal requests
        $withdrawal_requests = WithdrawProfit::query()
            ->where(function(Builder $query){
                $query->whereNull('postponed_to')
                        ->orWhere('postponed_to', '>=' , now()->toDateTimeString());
            })
            ->whereHas('withdrawTransaction', function (Builder $query) use ($maximum_amount) {
            $query->where('type', '=', 'withdraw');
            $query->whereHas('metaData', function (Builder $subQuery) {
                $subQuery->where('name', '=', 'Withdraw request');
            });
        })->where('pf_amount', '<=', $maximum_amount)->where('status', '=', 1);

        if ($withdrawal_requests->count() >= $count_withdraw_requests) {
            $withdrawal_requests->chunkById($count_withdraw_requests, function ($chunked) {
                foreach ($chunked AS $withdraw_request) {
                    /** @var $withdraw_request WithdrawProfit */
                    $this->payout_requests[$withdraw_request->payout_service]['ids'][] = $withdraw_request->id;
                    $this->payout_requests[$withdraw_request->payout_service]['wallets'][] = [
                        'destination' => $withdraw_request->wallet_hash,
                        'amount' => $withdraw_request->crypto_amount
                    ];
                }
                foreach ($this->payout_requests AS $payout_service => $requests) {
                    if (count($requests['ids']) > 0 AND (count($requests['ids']) == count($requests['wallets']))) {
                        $this->payout_processor->pay($payout_service, $requests['wallets'], $requests['ids']);
                    }
                }

                unset($this->chunked);
                unset($withdraw_request);
                unset($payout_service);
                unset($requests);
                unset($this->payout_service);
                unset($this->payout_service);
            });
        }


    }
}
