<?php

namespace Packages\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentDriver;
use Payments\Models\PaymentType;

class PaymentDriverController extends Controller
{
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
