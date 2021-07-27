<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentDriver;
use Payments\Models\PaymentType;

class PaymentTypeController extends Controller
{

    /**
     * All payment types
     * @group
     * Public User > Payments
     */
    public function index()
    {
        return api()->success('payment.successfully-fetched-all-payment-types',PaymentType::query()->get());

    }

}
