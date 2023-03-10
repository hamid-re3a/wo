<?php

namespace Payments\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Mix\Grpc\Context;
use Payments\Http\Requests\PaymentCurrency\RemovePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentCurrency\StorePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentCurrency\UpdatePaymentCurrencyRequest;
use Payments\Http\Resources\PaymentCurrencyResource;
use Payments\Services\EmptyObject;
use Payments\Services\PaymentCurrency;
use Payments\Services\PaymentService;

class PaymentCurrencyController extends Controller
{
    private $payment_service;

    public function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }

    /**
     * Update payment currencies
     * @group
     * Admin > Payments
     * @param UpdatePaymentCurrencyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePaymentCurrencyRequest $request)
    {
        $payment_currency_data = $this->payment_service->updatePaymentCurrency($this->paymentCurrency($request));
        return api()->success('payment.updated-payment-currencies',new PaymentCurrencyResource($payment_currency_data));

    }

    /**
     * Store payment currencies
     * @group
     * Admin > Payments
     * @param StorePaymentCurrencyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePaymentCurrencyRequest $request)
    {
        $payment_currency_data = $this->payment_service->createPaymentCurrency($this->paymentCurrency($request));
        return api()->success('payment.create-payment-currencies',new PaymentCurrencyResource($payment_currency_data));
    }

    /**
     * Store payment currencies
     * @group
     * Admin > Payments
     * @param StorePaymentCurrencyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(RemovePaymentCurrencyRequest $request)
    {
        $this->payment_service->deletePaymentCurrency($this->paymentCurrency($request));
        return api()->success('payment.delete-payment-currencies');
    }

    /*
     * this function convert to paymentCurrency
     */
    private function paymentCurrency($request)
    {
        $payment_currency = new PaymentCurrency();
        if($request->id){
            $payment_currency->setId($request->id);
        }
        $payment_currency->setName($request->name);
        $payment_currency->setIsActive($request->is_active);
        return$payment_currency;
    }

    public function tt(){
        dd($this->payment_service->getPaymentCurrencies(new Context(), new EmptyObject()));
    }
}
