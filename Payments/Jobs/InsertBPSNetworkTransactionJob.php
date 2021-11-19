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

class InsertBPSNetworkTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */
    private $payout_requests_ids;
    private $network_transaction;

    /**
     * Create a new job instance.
     *
     * @param $payout_requests_ids
     * @param $network_transaction
     */
    public function __construct($payout_requests_ids,$network_transaction)
    {
        $this->queue = env('DEFAULT_QUEUE_NAME','default');
        $this->payout_requests_ids = $payout_requests_ids;
        $this->network_transaction = $network_transaction;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $network_transaction = BPSNetworkTransactions::query()->create([
                'transaction_hash' => $this->network_transaction['transactionHash'],
                'comment' => $this->network_transaction['comment'],
                'labels' => $this->network_transaction['labels'],
                'amount' => $this->network_transaction['amount'],
                'blockHash' => $this->network_transaction['blockHash'],
                'blockHeight' => $this->network_transaction['blockHeight'],
                'confirmations' => $this->network_transaction['confirmations'],
                'timestamp' => Carbon::parse($this->network_transaction['timestamp'])->toDateTimeString(),
                'status' => $this->network_transaction['status'],
            ]);
            WithdrawProfit::query()->whereIn('id',$this->payout_requests_ids)->update([
                'network_transaction_id' => $network_transaction->id,
                'status' => 3
            ]);
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw new \Exception('Payout BPS payout failed',400);
            //TODO notify admin for unsuccessful payout and db error
        }
    }
}
