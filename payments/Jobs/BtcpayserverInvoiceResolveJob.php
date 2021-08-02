<?php

namespace Payments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mix\Grpc\Context;
use Orders\OrderServiceProvider;
use Orders\Services\OrderService;
use Packages\Services\Id;
use Packages\Services\PackagesServiceClient;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;
use Payments\Models\Invoice;
use Payments\Services\PaymentService;
use Payments\Services\PaymentsServiceClient;

class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function handle()
    {

        $event = json_decode($this->content,true);
        if (isset($event['type']) && isset($event['invoiceId']) &&
            isset($event['timestamp']) && !empty($event['invoiceId'])) {
            $response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/invoices/' . $event['invoiceId']
            );
            $payment_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/invoices/' . $event['invoiceId'] . '/payment-methods'
            );
            if ($response->ok() && $payment_response->ok()) {

                $invoice_db = Invoice::query()->where('transaction_id', $event['invoiceId'])->first();
                $amount_paid =0;
                foreach ($payment_response->json() as $item) {
                    $amount_paid += $item['totalPaid'];
                }
                $invoice_db->update([
                    'status' => $response->json()['status'],
                    'additional_status' => $response->json()['additionalStatus'],
                    'paid_amount' => $amount_paid
                ]);
                $this->resolve($invoice_db);


            } else{
                throw new \Exception('btc pay server issues');
            }

        }

    }

    private function resolve(Invoice $invoice_db)
    {
        $order_service = new OrderService();
        $Id = new \Orders\Services\Id();
        $Id->setId((int)$invoice_db->order_id);
        $order_service = $order_service->OrderById(new Context(), $Id);
        $invoice_service = new PaymentService();
        $payment_Id = new \Payments\Services\Id();
        $payment_Id->setId((int)$invoice_db->id);
        $invoice_model = $invoice_service->getInvoiceById(new Context(), $payment_Id);

        switch ($invoice_db->full_status) {
            case 'New PaidPartial':
                // send email notification for due amount
                EmailJob::dispatch(new EmailInvoicePaidPartial($order_service->getUser(), $invoice_model),$order_service->getUser()->getEmail());
                break;
            case 'Complete Paid':
            case 'Complete None':
            case 'Settled None':
            case 'Paid PaidOver':
            case 'Complete PaidOver':
            case 'Settled PaidOver':
                // send thank you email notification
                EmailJob::dispatch(new EmailInvoicePaidComplete($order_service->getUser(), $invoice_model),$order_service->getUser()->getEmail());
                break;
            case 'Expired PaidPartial':
            case 'Expired None':
            case 'Expired PaidLate':
                // send email to user to regenerate new invoice for due amount
                EmailJob::dispatch(new EmailInvoiceExpired($order_service->getUser(), $invoice_model),$order_service->getUser()->getEmail());
                break;
            case 'Invalid Paid':
            case 'Invalid Marked':
            case 'Invalid PaidOver':
                // send admin email notification
                break;
                // send admin email notification and complete user request
                break;
            default:
                throw new \Exception('btc pay server issues');
                break;
        }

    }
}
