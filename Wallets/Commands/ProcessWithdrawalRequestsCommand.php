<?php

namespace Wallets\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        if(!walletGetSetting('auto_payout_withdrawal_request_is_enable'))
            return null;

        $maximum_amount = walletGetSetting('maximum_auto_handle_withdrawals_payout');
        $count_withdraw_requests = walletGetSetting('count_withdraw_requests_to_automatic_payout_process') ? walletGetSetting('count_withdraw_requests_to_automatic_payout_process') : 50;
        $wallet_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
            ->get(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/');

        //Query withdrawal requests
        $withdrawal_requests = WithdrawProfit::query()->whereHas('withdrawTransaction', function (Builder $query) use ($maximum_amount) {
            $query->where(DB::raw("(SIGN(amount))"), '<=', $maximum_amount);
            $query->where('wallet_id', '!=', 1);
            $query->where('type', '=', 'withdraw');
            $query->whereHas('metaData', function (Builder $subQuery) {
                $subQuery->where('name', '=', 'Withdraw request');
            });
        })->where('status','=',1);

        $wallet_balance = $wallet_response->json()['confirmedBalance'];

        $total_needed_balance = $withdrawal_requests->sum('crypto_amount');

        if($total_needed_balance > $wallet_balance) {
            //TODO Notify admin for insufficient balance
            return;
        }

        if ($withdrawal_requests->count() >= $count_withdraw_requests) {
            $this->wallets = [];
            $this->ids = [];

            $withdrawal_requests->chunkById($count_withdraw_requests, function ($chunked){
                foreach ($chunked AS $withdraw_request) {
                    /** @var $withdraw_request WithdrawProfit*/
                    $this->ids[] = $withdraw_request->id;
                    $this->wallets[] = [
                        'destination' => $withdraw_request->wallet_hash,
                        'amount' => $withdraw_request->crypto_amount
                    ];
                }
                if(count($this->ids) > 0 AND count($this->wallets) > 0 AND (count($this->wallets) == count($this->ids)) )
                    ProcessBTCTransactionsJob::dispatch(O$this->wallets, $this->ids);

                unset($this->chunked);
                unset($withdraw_request);
                unset($this->wallets);
                unset($this->ids);
            });
        }


    }
}
