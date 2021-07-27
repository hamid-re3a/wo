<?php

namespace Payments\Services;

use Illuminate\Support\Facades\Http;
use Mix\Grpc\Context;
use Orders\Services;

class PaymentService extends GrpcMainService implements PaymentsServiceInterface
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

    private function payFromGateway(Services\Order $request): Invoice
    {
        $invoice = new Invoice();
        if ($request->getPaymentDriver() == 'btc-pay-server') {

            $response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->post(
                    config('payment.btc-pay-server-domain') .
                    'api/v1/stores/' . config('payment.btc-pay-server-store-id') . '/invoices',
                    [
                        'amount' => $request->getTotalCostInUsd(),
                        'currency' => $request->getPaymentCurrency()
                    ]
                );

            $invoice->setOrderId($request->getId());
            $invoice->setAmount($response->json()['amount']);
            $invoice->setTransactionId($response->json()['id']);
            $invoice->setCheckoutLink($response->json()['checkoutLink']);
            $invoice->setStatus($response->json()['status']);
            $invoice->setAdditionalStatus($response->json()['additionalStatus']);

        }
        return $invoice;
    }


}
