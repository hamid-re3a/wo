<?php

namespace Payments\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Mix\Grpc\Context;
use Orders\Services\OrderService;
use Payments\Mail\Payment\EmailInvoiceExpired;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Mail\Payment\EmailInvoicePaidPartial;
use Payments\Models\Invoice;
use Payments\Services\PaymentService;

class BtcpayserverInvoiceResolveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $invoice_db;

    public function __construct(Invoice $invoice_db)
    {
        $this->invoice_db = $invoice_db;
    }

    public function handle()
    {
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
            case 'Settled Paid':
            case 'Complete None':
            case 'Settled None':
            case 'Paid PaidOver':
            case 'Complete PaidOver':
            case 'Settled PaidOver':
                // send thank you email notification
                EmailJob::dispatch(new EmailInvoicePaidComplete($order_service->getUser(), $invoice_model),$order_service->getUser()->getEmail());
                $this->invoice_db->is_paid = true;
                $this->invoice_db->save();
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
                // throw new \Exception('btc pay server issues');
                break;
        }
    }
}
