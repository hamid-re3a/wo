<?php

namespace Payments\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Payments\Models\BPSNetworkTransactions;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\WalletService;

class ProcessBTCPayServerPayoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */
    private $payout_requests;

    /**
     * Create a new job instance.
     *
     * @param array $payout_requests
     */
    public function __construct($payout_requests)
    {
        $this->queue = 'default';
        $this->payout_requests = collect($payout_requests);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        //Check wallet balance
        $wallet_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
            ->get(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/');

        $wallet_balance = $wallet_response->json()['confirmedBalance'];
        $total_needed_balance = $this->payout_requests->sum('crypto_amount');

        if($total_needed_balance > $wallet_balance) {
            Log::error('Automatic payouts failed-insufficient wallet balance');
            //TODO Notify admin for insufficient balance
            throw new \Exception('Automatic payouts failed-insufficient wallet balance');
        }

        $destinations = [];
        foreach($this->payout_requests AS $req) {
            $destinations[] = [
                'destination' => $req['wallet_hash'],
                'amount' => $req['crypto_amount']
            ];
        }
        $response =  Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
            ->post(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/transactions'
                , [
                'destinations' => $destinations,
                'proceedWithPayjoin' => true,
                'proceedWithBroadcast' => true,
                'noChange' => false,
                'rbf' => true
            ]);
        if($response->ok()) {
            InsertBPSNetworkTransactionJob::dispatch($this->payout_requests->pluck('id'),$response->json());
        } else {
            Log::info($response->status());
            Log::info(serialize($response->json()));
            throw \Exception('Payout BPS payout failed');
            //TODO notify admin
        }
    }
}
