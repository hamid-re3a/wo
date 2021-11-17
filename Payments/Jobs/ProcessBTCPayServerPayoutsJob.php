<?php

namespace Payments\Jobs;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Wallets\Jobs\HandleWithdrawUnsentEmails;

class ProcessBTCPayServerPayoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */
    private $payout_requests;
    private $dispatchType;

    /**
     * Create a new job instance.
     *
     * @param array $payout_requests
     * @param string $dispatchType
     */
    public function __construct($payout_requests, $dispatchType = 'dispatch')
    {
        $this->queue = env('DEFAULT_QUEUE_NAME','default');
        $this->payout_requests = $payout_requests instanceof Collection ? $payout_requests : collect($payout_requests);
        $this->dispatchType = $dispatchType;
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
        $total_needed_balance = $this->payout_requests->sum('crypto_amount') + 0.001; // Add 0.001 for checking BTCPayServer balance for fee and other stuff

        if($total_needed_balance > $wallet_balance) {
            Log::error('Automatic payouts failed-insufficient wallet balance');
            //TODO Notify admin for insufficient balance
            throw new \Exception(trans('wallet.withdraw-profit-request.insufficient-bpb-wallet-balance', [
                'amount' => $total_needed_balance . ' BTC'
            ]));
        }

        $destinations = [];
        foreach($this->payout_requests AS $req) {
            $destinations[] = [
                'destination' => $req['wallet_hash'],
                'amount' => $req['crypto_amount']
            ];
        }
        Log::info('Payments\Jobs\ProcessBTCPayServerPayoutsJob $destinations => ' . serialize($destinations));
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
            InsertBPSNetworkTransactionJob::{$this->dispatchType}($this->payout_requests->pluck('id'),$response->json());
            HandleWithdrawUnsentEmails::dispatch();
        } else {
            Log::info($response->status());
            Log::info(serialize($response->json()));
            throw new \Exception('Payout BPS failed');
            //TODO notify admin
        }
    }
}
