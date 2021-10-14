<?php

namespace Payments\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\PaymentCurrency\RemovePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentCurrency\StorePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentCurrency\UpdatePaymentCurrencyRequest;
use Payments\Http\Resources\PaymentCurrencyResource;
use Payments\Services\PaymentService;

class PaymentCurrencyController extends Controller
{
    private $payment_service;

    public function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }

    /**
     * payment currencies list
     * @group
     * Admin User > Payments > Currencies
     * @return JsonResponse
     */
    public function index()
    {
        return api()->success('payment.updated-payment-currencies',
            \Payments\Http\Resources\Admin\PaymentCurrencyResource::
            collection($this->payment_service->getPaymentCurrencies())
        );

    }

    /**
     * Update payment currency
     * @group
     * Admin User > Payments > Currencies
     * @param UpdatePaymentCurrencyRequest $request
     * @return JsonResponse
     */
    public function update(UpdatePaymentCurrencyRequest $request)
    {
        $payment_currency_data = $this->payment_service->updatePaymentCurrency($request);
        return api()->success('payment.updated-payment-currencies', new PaymentCurrencyResource($payment_currency_data));

    }

    /**
     * Store payment currency
     * @group
     * Admin User > Payments > Currencies
     * @param StorePaymentCurrencyRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentCurrencyRequest $request)
    {
        $payment_currency_data = $this->payment_service->createPaymentCurrency($request);
        return api()->success('payment.create-payment-currencies', new PaymentCurrencyResource($payment_currency_data));
    }

    /**
     * Delete payment currency
     * @group
     * Admin User > Payments > Currencies
     * @param StorePaymentCurrencyRequest $request
     * @return JsonResponse
     */
    public function delete(RemovePaymentCurrencyRequest $request)
    {
        $this->payment_service->deletePaymentCurrency($request);
        return api()->success('payment.delete-payment-currencies');
    }

}
