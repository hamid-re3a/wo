<?php

namespace Payments\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\PaymentType\RemovePaymentTypeRequest;
use Payments\Http\Requests\PaymentType\StorePaymentTypeRequest;
use Payments\Http\Requests\PaymentType\UpdatePaymentTypeRequest;
use Payments\Http\Resources\PaymentTypeResource;
use Payments\Services\PaymentService;

class PaymentTypeController extends Controller
{
    private $payment_service;

    public function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }


    /**
     * payment types list
     * @group
     * Admin User > Payments > types
     * @return JsonResponse
     */
    public function index()
    {
        return api()->success('payment.successfully-fetched-all-payment-types',
            \Payments\Http\Resources\Admin\PaymentTypeResource::collection($this->payment_service->getAllPaymentTypes()));

    }

    /**
     * Update payment types
     * @group
     * Admin User > Payments > types
     * @param UpdatePaymentTypeRequest $request
     * @return JsonResponse
     */
    public function update(UpdatePaymentTypeRequest $request)
    {
        $payment_currency_data = $this->payment_service->updatePaymentType($request);
        return api()->success('payment.updated-payment-types',new PaymentTypeResource($payment_currency_data));

    }

    /**
     * Create payment types
     * @group
     * Admin User > Payments > types
     * @param StorePaymentTypeRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentTypeRequest $request)
    {
        $payment_currency_data = $this->payment_service->createPaymentType($request);
        return api()->success('payment.create-payment-types',new PaymentTypeResource($payment_currency_data));
    }

    /**
     * Delete payment types
     * @group
     * Admin User > Payments > types
     * @param RemovePaymentTypeRequest $request
     * @return JsonResponse
     */
    public function delete(RemovePaymentTypeRequest $request)
    {
        $this->payment_service->deletePaymentType($request);
        return api()->success('payment.delete-payment-types');
    }



}
