<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Routing\Controller;
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
     * All payment currencies
     * @group
     * Public User > Payments
     */
    public function index()
    {
        return api()->success('payment.successfully-fetched-all-payment-currencies',
            PaymentCurrencyResource::collection($this->payment_service->getPaymentCurrencies(CURRENCY_SERVICE_PURCHASE))
        );
    }
}
