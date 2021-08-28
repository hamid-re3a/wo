<?php

namespace Payments\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Payments\Models\Invoice;
use Payments\Services\OrderProcessor;
use Payments\Services\Resolves\ProcessorAbstract;
use Payments\Services\Socket;
use Payments\Services\WalletProcessor;

class InvoiceResolverBTCPayServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $invoice_db;
    private $invoice_model;
    private $order_model;

    public function __construct(Invoice $invoice_db)
    {
        $this->invoice_db = $invoice_db;
        $this->invoice_model = new \Payments\Services\Invoice();
    }

    public function handle()
    {
        try {
            DB::beginTransaction();
            if ($this->invoice_db->is_paid AND $this->invoice_db->status == 'Settled')
                return;

            $response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                    config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                    config('payment.btc-pay-server-store-id') . '/invoices/' . $this->invoice_db->transaction_id
                );
            $payment_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                    config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                    config('payment.btc-pay-server-store-id') . '/invoices/' . $this->invoice_db->transaction_id . '/payment-methods'
                );

            if ( $response->ok() && $payment_response->ok() ) {
                $amount_paid = $payment_response->json()[0]['totalPaid'];
                $amount_due = $payment_response->json()[0]['due'];
                $this->recordTransactions($payment_response->json()[0]['payments']);
                $this->invoice_db->update([
                    'expiration_time' => Carbon::createFromTimestamp($response->json()['expirationTime']),
                    'status' => $response->json()['status'],
                    'additional_status' => $response->json()['additionalStatus'],
                    'paid_amount' => $amount_paid,
                    'due_amount' => $amount_due
                ]);

                switch ($this->invoice_db->payable_type) {
                    case 'Order' :
                        $this->resolve(new OrderProcessor($this->invoice_db));
                        break;
                    case 'DepositWallet':
                        $this->resolve(new WalletProcessor($this->invoice_db));
                        break;
                }

            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            //TODO send admin AND dev-team notification
            Log::error('InvoiceResolverBTCPayServerJob error for InvoiceID => ' . $this->invoice_db->id);
            Log::error('InvoiceResolverBTCPayServerJob Exception => ' . $e->getMessage() . ' - Line => ' . $e->getLine() . '\n Trace String => ' . $e->getTraceAsString());
        }

    }

    private function resolve(ProcessorAbstract $processor)
    {
        switch ($this->invoice_db->full_status) {
            case 'New PaidPartial':
                $processor->partial();
                break;

            case 'Complete Paid':
            case 'Settled Paid':
            case 'Complete None':
            case 'Settled None':
            case 'Paid PaidOver':
            case 'Complete PaidOver':
            case 'Settled PaidOver':
                $processor->completed();
                break;

            case 'Processing Paid':
            case 'Processing None':
            case 'Processing PaidOver':

                break;

            case 'Expired PaidPartial':
            case 'Expired None':
            case 'Expired PaidLate':
                $processor->expired();
                break;

            case 'Invalid Paid':
            case 'Invalid Marked':
            case 'Invalid PaidOver':
                // send admin email notification

                $processor->invalid();
                break;

                // send admin email notification and complete user request
                break;

            default:
                throw new \Exception('BTCPayServerError');
                break;

        }
    }

    private function recordTransactions($transactions)
    {
        try {
            $db_transactions = [];
            if (is_array($transactions)) {
                foreach ($transactions AS $transaction) {
                    $now = now()->toDateTimeString();
                    if (
                        is_array($transaction) AND
                        array_key_exists('id', $transaction) AND !empty($transaction['id']) AND
                        array_key_exists('receivedDate', $transaction) AND !empty($transaction['receivedDate']) AND
                        array_key_exists('value', $transaction) AND !empty($transaction['value']) AND
                        array_key_exists('fee', $transaction) AND !empty($transaction['fee']) AND
                        array_key_exists('status', $transaction) AND !empty($transaction['status']) AND
                        array_key_exists('destination', $transaction) AND !empty($transaction['destination'])
                    ) {
                        $this->invoice_db->transactions()->updateOrCreate(
                            ['hash' => $transaction['id']],
                            [
                                'received_date' => date("Y-m-d H:m:s", $transaction['receivedDate']),
                                'value' => $transaction['value'],
                                'fee' => $transaction['fee'],
                                'status' => $transaction['status'],
                                'destination' => $transaction['destination'],
                                'created_at' => $now,
                                'updated_at' => $now
                            ]
                        );
                        $db_transactions[] = [
                            'invoice_id' => $this->invoice_db->id,
                            'hash' => $transaction['id'],
                            'received_date' => date("Y-m-d H:m:s", $transaction['receivedDate']),
                            'value' => $transaction['value'],
                            'fee' => $transaction['fee'],
                            'status' => $transaction['status'],
                            'destination' => $transaction['destination'],
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }
                return $db_transactions;
            }
        } catch (\Throwable $exception) {
            Log::error('Record transactions error , InvoiceID ' . $this->invoice_db->id);
        }
    }
}
