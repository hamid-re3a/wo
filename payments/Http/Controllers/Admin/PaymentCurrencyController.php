<?php

namespace Packages\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Payments\Http\Requests\PaymentCurrency\StorePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentCurrency\UpdatePaymentCurrencyRequest;
use Payments\Models\PaymentCurrency;

class PaymentCurrencyController extends Controller
{

    /**
     * Update payment currencies
     * @group
     * Admin > Payments
     */
    public function update(UpdatePaymentCurrencyRequest $request)
    {
        $currency = PaymentCurrency::find($request->id);
        $currency->update($request->validated());
        return api()->success('payment.updated-payment-currencies',$currency);

    }
    /**
     * Store payment currencies
     * @group
     * Admin > Payments
     */
    public function store(StorePaymentCurrencyRequest $request)
    {
        $currency = PaymentCurrency::query()->create($request->validated());
        return api()->success('payment.updated-payment-currencies',$currency);

    }

}
