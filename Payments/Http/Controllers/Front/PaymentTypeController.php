<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Payments\Http\Resources\PaymentTypeResource;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentDriver;
use Payments\Models\PaymentType;
use Payments\Services\EmptyObject;
use Payments\Services\PaymentService;

class PaymentTypeController extends Controller
{
    private $payment_service;

    public function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }
    /**
     * All payment types
     * @group
     * Public User > Payments Types
     */
    public function index()
    {

        return api()->success('payment.successfully-fetched-all-payment-types',PaymentTypeResource::collection(collect($this->payment_service->getPaymentTypes( new EmptyObject())->getPaymentTypes())));

    }

}
