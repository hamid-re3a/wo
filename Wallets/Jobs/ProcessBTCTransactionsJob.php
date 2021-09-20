<?php

namespace Wallets\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Wallets\Models\NetworkTransaction;
use Wallets\Models\WithdrawProfit;

class ProcessBTCTransactionsJob implements ShouldQueue
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
            $network_transaction = NetworkTransaction::query()->create([
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
            WithdrawProfit::query()->whereIn('id',$this->withdraw_profit_request_ids)->update([
                'network_transaction_id' => $network_transaction->id
            ]);
        }
    }
}
