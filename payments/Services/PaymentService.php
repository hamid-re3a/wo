<?php

namespace Payments\Services;

use Illuminate\Support\Facades\Http;
use Mix\Grpc;
use Mix\Grpc\Context;
use Orders\Services;
use Orders\Services\OrderService;
use Payments\Repository\PaymentCurrencyRepository;
use Payments\Repository\PaymentTypesRepository;

class PaymentService implements PaymentsServiceInterface
{
    private $payment_type_repository;
    private $payment_currency_repository;

    public function __construct(PaymentTypesRepository $payment_type_repository, PaymentCurrencyRepository $payment_currency_repository)
    {
        $this->payment_type_repository = $payment_type_repository;
        $this->payment_currency_repository = $payment_currency_repository;
    }

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
        $response_invoice->setDueAmount((double)$invoice->due_amount);
        $response_invoice->setIsPaid((boolean)$invoice->is_paid);


        $order_service = new OrderService;
        $order_id = new Services\Id();
        $order_id->setId($response_invoice->getOrderId());
        $order = $order_service->OrderById(new Context(),$order_id);
        $response_invoice->setOrder($order);

        return $response_invoice;


    }

    /**
     * @inheritDoc
     */
    public function getPaymentCurrencies(Context $context, EmptyObject $request): PaymentCurrencies
    {
        $payment_currency_data = $this->payment_currency_repository->getAll();
        $payment_currencies =  new PaymentCurrencies();
        $payment_currencies->setPaymentCurrencies($this->mapPaymentCurrency($payment_currency_data));
        return $payment_currencies;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTypes(Context $context, EmptyObject $request): PaymentTypes
    {
        $payment_types_data = $this->payment_type_repository->getAll();
        $payment_types =  new PaymentTypes();
        $payment_types->setPaymentTypes($this->mapPaymentType($payment_types_data));
       return $payment_types;
    }

    /**
     * this function get collection of data paymentCurrency to array of paymentCurrency class
     * @param $payment_currencies
     * @return mixed
     */
    private function mapPaymentCurrency($payment_currencies)
    {
        $payment_currency = new PaymentCurrency();
        $data_array = $payment_currencies->map(function ($item) use($payment_currency){
            $payment_currency->setId($item->id);
            $payment_currency->setName($item->name);
            $payment_currency->setIsActive($item->is_active);
            $payment_currency->setPaymentDriver($this->mapPaymentDriver($item->paymentDriver));
            return $payment_currency;
        });
        return $data_array->toArray();
    }

    /**
     * this function for get paymentDriver collection to array of paymentDriver class
     * @param $payment_drivers
     * @return mixed
     */
    private function mapPaymentDriver($payment_drivers)
    {
        $payment_driver = new PaymentDriver();
        $data_array = $payment_drivers->map(function ($item) use($payment_driver){
            $payment_driver->setId($item->id);
            $payment_driver->setName($item->name);
            $payment_driver->setIsActive($item->is_active);
            return $payment_driver;
        });
        return $data_array->toArray();
    }

    /**
     * this function get collection of data paymentType to array of paymentType class
     * @param $payment_types
     * @return mixed
     */
    private function mapPaymentType($payment_types)
    {
        $payment_type= new PaymentType();
        $data_array = $payment_types->map(function ($item) use($payment_type){
            $payment_type->setId($item->id);
            $payment_type->setName($item->name);
            $payment_type->setIsActive($item->is_active);
            return $payment_type;
        });
        return $data_array->toArray();
    }
}
