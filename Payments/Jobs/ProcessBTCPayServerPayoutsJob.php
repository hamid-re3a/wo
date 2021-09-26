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
use Wallets\Services\WalletService;

class ProcessBTCPayServerPayoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */
    private $wallets_with_amounts;
    private $withdraw_profit_request_ids;

    /**
     * Create a new job instance.
     *
     * @param array $wallets_with_amounts
     * @param array $withdraw_profit_request_ids
     */
    public function __construct(array $wallets_with_amounts,array $withdraw_profit_request_ids)
    {
        $this->queue = 'default';
        $this->wallets_with_amounts = $wallets_with_amounts;
        $this->withdraw_profit_request_ids = $withdraw_profit_request_ids;
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
        $total_needed_balance = collect($this->wallets_with_amounts)->sum('amount');

        if($total_needed_balance > $wallet_balance) {
            Log::error('Automatic payouts failed-insufficient wallet balance');
            //TODO Notify admin for insufficient balance
            return;
        }


        $response =  Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
            ->post(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/transactions'
                , [
                'destinations' => $this->wallets_with_amounts,
                'proceedWithPayjoin' => true,
                'proceedWithBroadcast' => true,
                'noChange' => false,
                'rbf' => true
            ]);
        if($response->ok()) {
            try {
                DB::beginTransaction();
                $network_transaction = BPSNetworkTransactions::query()->create([
                    'transaction_hash' => $response->json()['transactionHash'],
                    'comment' => $response->json()['comment'],
                    'labels' => $response->json()['labels'],
                    'amount' => $response->json()['amount'],
                    'blockHash' => $response->json()['blockHash'],
                    'blockHeight' => $response->json()['blockHeight'],
                    'confirmations' => $response->json()['confirmations'],
                    'timestamp' => Carbon::parse($response->json()['timestamp'])->toDateTimeString(),
                    'status' => $response->json()['status'],
                ]);
                $wallet_service = app(WalletService::class);
                $wallet_service->updateWithdrawRequestsByIds($this->withdraw_profit_request_ids,[
                    'network_transaction_id' => $network_transaction->id,
                    'status' => 3
                ]);
                DB::commit();
            } catch (\Throwable $exception) {
                DB::rollBack();
                //TODO notify admin for successful payment and db error
            }
        } else {
            //TODO notify admin
        }
    }
}
