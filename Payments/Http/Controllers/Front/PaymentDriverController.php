<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentDriver;
use Payments\Models\PaymentType;
use Payments\Services\PaymentService;

class PaymentDriverController extends Controller
{
    private $payment_service;

    public function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }
    /**
     * All payment drivers
     * @group
     * Public User > Payments
     */
    public function index()
    {
        return api()->success('payment.successfully-fetched-all-payment-drivers',PaymentDriver::query()->get());

    }

}
