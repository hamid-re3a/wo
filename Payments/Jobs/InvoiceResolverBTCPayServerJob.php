<?php

namespace Payments\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Orders\Services\OrderService;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;
use Payments\Models\Invoice;
use Payments\Services\PaymentService;
use User\Services\User;
use Wallets\Services\Deposit;
use Wallets\Services\WalletService;

class InvoiceResolverBTCPayServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $invoice_db;

    public function __construct(Invoice $invoice_db)
    {
        $this->invoice_db = $invoice_db;
    }

    public function handle()
    {
        switch ($this->invoice_db->full_status) {
            case 'Complete Paid':
            case 'Settled Paid':
            case 'Complete None':
            case 'Settled None':
            case 'Paid PaidOver':
            case 'Complete PaidOver':
            case 'Settled PaidOver':
                return;
        }


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
        if ($response->ok() && $payment_response->ok()) {
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

            $this->resolve();
        }

    }

    private function resolve()
    {


        $invoice_db = $this->invoice_db;
        $payment_Id = new \Payments\Services\Id();
        $payment_Id->setId((int)$invoice_db->id);
        $invoice_model = app(PaymentService::class)->getInvoiceById($payment_Id);


        if (is_null($invoice_db->order_id)) {
            switch ($invoice_db->status) {
                case 'Complete':
                case 'Settled':
                case 'Paid':

                    $pf_paid = number_format(
                        ($invoice_model->getPfAmount() / $invoice_model->getAmount()) * $invoice_model->getPaidAmount()
                        , 2, '.', '');
                    if ($pf_paid > (double)$invoice_db->deposit_amount) {
                        $deposit_amount = $pf_paid - ((double)$invoice_db->deposit_amount);
                        $deposit_model = new Deposit;
//                    $deposit_model
                        app(WalletService::class)->deposit();
                    }
                    break;
            }
            return;
        }

        $order_model = $invoice_model->getOrder();

        switch ($invoice_db->full_status) {
            case 'New PaidPartial':
                // send web socket notification
                Http::post('http://0.0.0.0:2121/socket', [
                    "uid" => $invoice_model->getTransactionId(),
                    "content" => [
                        "name" => "partial_paid",
                        "amount" => $invoice_model->getDueAmount(),
                        "checkout_link" => $invoice_model->getCheckoutLink(),
                        "payment_currency" => $invoice_model->getPaymentCurrency()
                    ]]);
                // send email notification for due amount
                EmailJob::dispatch(new EmailInvoicePaidPartial($order_model->getUser(), $invoice_model), $order_model->getUser()->getEmail());


                break;
            case 'Complete Paid':
            case 'Settled Paid':
            case 'Complete None':
            case 'Settled None':
            case 'Paid PaidOver':
            case 'Complete PaidOver':
            case 'Settled PaidOver':

                // send thank you email notification
                EmailJob::dispatch(new EmailInvoicePaidComplete($order_model->getUser(), $invoice_model), $order_model->getUser()->getEmail());
                $this->invoice_db->is_paid = true;
                $this->invoice_db->save();
                $order_model->setIsPaidAt(now()->toString());
                app(OrderService::class)->updateOrder($order_model);
                // send web socket notification
                Http::post('http://0.0.0.0:2121/socket', [
                    "uid" => $invoice_model->getTransactionId(),
                    "content" => [
                        "name" => "confirmed",
                        "amount" => $invoice_model->getDueAmount(),
                        "checkout_link" => $invoice_model->getCheckoutLink(),
                        "payment_currency" => $invoice_model->getPaymentCurrency()
                    ]]);

                //MLM dispatch job dispatch
                break;
            case 'Processing Paid':
            case 'Processing None':
            case 'Processing PaidOver':

                // send web socket notification
                Http::post('http://0.0.0.0:2121/socket', [
                    "uid" => $invoice_model->getTransactionId(),
                    "content" => [
                        "name" => "paid",
                        "amount" => $invoice_model->getDueAmount(),
                        "checkout_link" => $invoice_model->getCheckoutLink(),
                        "payment_currency" => $invoice_model->getPaymentCurrency()
                    ]]);
                break;
            case 'Expired PaidPartial':
            case 'Expired None':
            case 'Expired PaidLate':
                // send web socket notification
                Http::post('http://0.0.0.0:2121/socket', [
                    "uid" => $invoice_model->getTransactionId(),
                    "content" => [
                        "name" => "expired",
                        "amount" => $invoice_model->getDueAmount(),
                        "checkout_link" => $invoice_model->getCheckoutLink(),
                        "payment_currency" => $invoice_model->getPaymentCurrency()
                    ]
                ]);
                // send email to user to regenerate new invoice for due amount
                EmailJob::dispatch(new EmailInvoiceExpired($order_model->getUser(), $invoice_model), $order_model->getUser()->getEmail());
                break;
            case 'Invalid Paid':
            case 'Invalid Marked':
            case 'Invalid PaidOver':
                // send admin email notification
                break;
                // send admin email notification and complete user request
                break;
            default:
                // throw new \Exception('btc pay server issues');
                break;
        }
    }

    private function recordTransactions($transactions)
    {
        try{
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
                        $db_transactions[] = [
                            'invoice_id' => 1,
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
                $this->invoice_db->transactions()->insert($db_transactions);
                return $db_transactions;
            }
        } catch (\Throwable $exception) {
            Log::error('Record transactions error , InvoiceID ' . $this->invoice_db->id);
        }
    }
}
