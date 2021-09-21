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
        $maximum_amount = walletGetSetting('maximum_auto_handle_withdrawals');
        $count_withdraw_requests = walletGetSetting('count_withdraw_requests_to_automatic_payout_process') ? walletGetSetting('count_withdraw_requests_to_automatic_payout_process') : 50;
        //Query withdrawal requests
        $withdrawal_requests = WithdrawProfit::query()->whereHas('withdrawTransaction', function (Builder $query) use ($maximum_amount) {
            $query->where(DB::raw("(SIGN(amount))"), '<=', $maximum_amount);
            $query->where('wallet_id', '!=', 1);
            $query->where('type', '=', 'withdraw');
            $query->whereHas('metaData', function (Builder $subQuery) {
                $subQuery->where('name', '=', 'Withdraw request');
            });
        })->where('status','=',1);
        $btc_price = Http::get('https://blockchain.info/ticker');

        if ($btc_price->ok() AND $withdrawal_requests->count() >= $count_withdraw_requests AND is_array($btc_price) AND isset($btc_price->json()['USD']['15m'])) {
            $btc_price = $btc_price->json()['USD']['15m'];
            $this->wallets = [];
            $this->ids = [];

            $withdrawal_requests->chunkById($count_withdraw_requests, function ($chunked) use($btc_price){
                foreach ($chunked AS $withdraw_request) {
                    $this->ids[] = $withdraw_request->id;
                    $this->wallets[] = [
                        'destination' => $withdraw_request->wallet_hash,
                        'amount' => abs($withdraw_request->withdrawTransaction->amountFloat) / $btc_price
                    ];
                }
                ProcessBTCTransactionsJob::dispatchSync($this->wallets, $this->ids);
                unset($this->chunked);
                unset($withdraw_request);
                unset($this->wallets);
                unset($this->ids);
            });
        }


    }
}
