<?php

namespace Wallets\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Payments\Services\Processors\PayoutProcessor;
use Wallets\Models\WithdrawProfit;

class ProcessBTCWithdrawalRequestsCommand extends Command
{

    private $payout_processor;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:process-btc-withdrawals';

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

        $chunk_allowed_size_setting = walletGetSetting('count_withdraw_requests_to_automatic_payout_process') ? walletGetSetting('count_withdraw_requests_to_automatic_payout_process') : 50;

        $withdrawal_requests = $this->fetchWithdrawalRequestsFromDB();

        if ($withdrawal_requests->count() >= $chunk_allowed_size_setting) {
            $withdrawal_requests->chunkById($chunk_allowed_size_setting, function ($chunked) {
                $this->payout_processor->pay('BTC',$chunked);
            });
        }
    }

    /**
     * Fetch withdrawal requests for auto-payout-amount
     * @return Builder
     * @throws \Exception
     */
    public function fetchWithdrawalRequestsFromDB(): Builder
    {
        $maximum_amount = walletGetSetting('maximum_auto_handle_withdrawals_payout');
        //Query withdrawal requests
        return WithdrawProfit::query()
            ->select(['wallet_hash','crypto_amount','id'])
            ->where(function (Builder $query) {
                $query->whereNull('postponed_to')
                    ->orWhere('postponed_to', '>=', now()->toDateTimeString());
            })->where('pf_amount', '<=', $maximum_amount)
            ->where('status', '=', 1)->where('currency', '=', 'BTC');
    }
}
