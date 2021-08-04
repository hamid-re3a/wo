<?php

namespace Payments\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Mix\Grpc;
use Mix\Grpc\Context;
use Orders\Services;
use Orders\Services\OrderService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceCreated;

class PaymentService implements PaymentsServiceInterface
{
    /**
     * @inheritDoc
     */
    public function pay(Context $context, Services\Order $request): Invoice
    {

        $invoice = new Invoice();
        switch ($request->getPaymentType()) {
            case 'gate-way':
                return $this->payFromGateway($request);
            default:
                break;
        }

        return $invoice;

    }

    private function payFromGateway(Services\Order $order_request): Invoice
    {
        $invoice = new Invoice();
        if ($order_request->getPaymentDriver() == 'btc-pay-server') {

            $response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->post(
                    config('payment.btc-pay-server-domain') .
                    'api/v1/stores/' . config('payment.btc-pay-server-store-id') . '/invoices',
                    [
                        'amount' => $order_request->getTotalCostInUsd(),
                        'currency' => $order_request->getPaymentCurrency()
                    ]
                );

            if ($response->ok()) {
                $invoice->setOrderId((int)$order_request->getId());
                $invoice->setAmount((double)$response->json()['amount']);
                $invoice->setTransactionId($response->json()['id']);
                $invoice->setCheckoutLink($response->json()['checkoutLink']);
                $invoice->setStatus($response->json()['status']);
                $invoice->setAdditionalStatus($response->json()['additionalStatus']);
                $invoice->setExpirationTime($response->json()['expirationTime']);


                \Payments\Models\Invoice::query()->create([
                    'order_id' => $invoice->getOrderId(),
                    'amount' => $invoice->getAmount(),
                    'transaction_id' => $invoice->getTransactionId(),
                    'checkout_link' => $invoice->getCheckoutLink(),
                    'expiration_time' => Carbon::createFromTimestamp($invoice->getExpirationTime()),
                    'status' => $invoice->getStatus(),
                    'additional_status' => $invoice->getAdditionalStatus(),
                ]);

                EmailJob::dispatch(new EmailInvoiceCreated($order_request->getUser(), $invoice),$order_request->getUser()->getEmail());
            }

        }
        return $invoice;
    }


    /**
     * @inheritDoc
     */
    public function getInvoiceById(Context $context, Id $request): Invoice
    {

        $response_invoice = new Invoice;
        $invoice = \Payments\Models\Invoice::query()->find($request->getId());
        $invoice->refresh();
        $response_invoice->setOrderId((int)$invoice->order_id);
        $response_invoice->setAmount((double)$invoice->amount);
        $response_invoice->setTransactionId($invoice->transaction_id);
        $response_invoice->setCheckoutLink($invoice->checkout_link);
        $response_invoice->setStatus($invoice->status);
        $response_invoice->setAdditionalStatus($invoice->additional_status);
        $response_invoice->setPaidAmount((double)$invoice->paid_amount);
        $response_invoice->setDueAmount((double)$invoice->due_amount);
        if ($invoice->expiration_time)
            $response_invoice->setExpirationTime((string)Carbon::make($invoice->expiration_time)->timestamp);
        $response_invoice->setIsPaid((boolean)$invoice->is_paid);


        $order_service = new OrderService;
        $order_id = new Services\Id();
        $order_id->setId($response_invoice->getOrderId());
        $order = $order_service->OrderById(new Context(), $order_id);
        $response_invoice->setOrder($order);

        return $response_invoice;


    }

    /**
     * @inheritDoc
     */
    public function getPaymentCurrencies(Context $context, EmptyObject $request): PaymentCurrencies
    {
        // TODO: Implement getPaymentCurrencies() method.
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTypes(Context $context, EmptyObject $request): PaymentTypes
    {
        // TODO: Implement getPaymentTypes() method.
    }
}
