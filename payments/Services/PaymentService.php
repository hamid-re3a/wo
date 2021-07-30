<?php

namespace Payments\Services;

use Illuminate\Support\Facades\Http;
use Mix\Grpc;
use Mix\Grpc\Context;
use Orders\Services;

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


                \Payments\Models\Invoice::query()->create([
                    'order_id' => $invoice->getOrderId(),
                    'amount' => $invoice->getAmount(),
                    'transaction_id' => $invoice->getTransactionId(),
                    'checkout_link' => $invoice->getCheckoutLink(),
                    'status' => $invoice->getStatus(),
                    'additional_status' => $invoice->getAdditionalStatus(),
                ]);
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

        $response_invoice->setOrderId((int)$invoice->order_id);
        $response_invoice->setAmount((double)$invoice->amount);
        $response_invoice->setTransactionId($invoice->transaction_id);
        $response_invoice->setCheckoutLink($invoice->checkout_link);
        $response_invoice->setStatus($invoice->status);
        $response_invoice->setAdditionalStatus($invoice->additional_status);
        $response_invoice->setPaidAmount((double)$invoice->paid_amount);

        return $response_invoice;


    }
}