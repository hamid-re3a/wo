<?php

namespace Packages\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Payments\Models\PaymentCurrency;

class PaymentCurrencyController extends Controller
{
    /**
     * All payment currencies
     * @group
     * Public User > Payments
     */
    public function index()
    {
        return api()->success('payment.successfully-fetched-all-payment-currencies',PaymentCurrency::query()->get());
    }
}
