<?php

namespace Payments\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\PaymentDriver\RemovePaymentDriverRequest;
use Payments\Http\Requests\PaymentDriver\StorePaymentDriverRequest;
use Payments\Http\Requests\PaymentDriver\UpdatePaymentDriverRequest;
use Payments\Http\Resources\PaymentDriverResource;
use Payments\Services\PaymentDriver;
use Payments\Services\PaymentService;

class PaymentDriverController extends Controller
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
     * @param UpdatePaymentDriverRequest $request
     * @return JsonResponse
     */
    public function update(UpdatePaymentDriverRequest $request)
    {
        $payment_currency_data = $this->payment_service->updatePaymentDriver($this->paymentDriver($request));
        return api()->success('payment.updated-payment-driver',new PaymentDriverResource($payment_currency_data));

    }

    /**
     * Store payment drivers
     * @group
     * Admin > Payments
     * @param StorePaymentDriverRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentDriverRequest $request)
    {
        $payment_currency_data = $this->payment_service->createPaymentDriver($this->paymentDriver($request));
        return api()->success('payment.create-payment-driver',new PaymentDriverResource($payment_currency_data));
    }

    /**
     * Store payment drivers
     * @group
     * Admin > Payments
     * @param RemovePaymentDriverRequest $request
     * @return JsonResponse
     */
    public function delete(RemovePaymentDriverRequest $request)
    {
        $this->payment_service->deletePaymentDriver($this->paymentDriver($request));
        return api()->success('payment.delete-payment-driver');
    }

    /*
     * this function convert to paymentDriver
     */
    private function paymentDriver($request)
    {
        $payment_currency = new PaymentDriver();
        if($request->id){
            $payment_currency->setId($request->id);
        }
        $payment_currency->setName($request->name);
        $payment_currency->setIsActive($request->is_active);
        return$payment_currency;
    }
}
