<?php

namespace Packages\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Payments\Http\Requests\PaymentCurrency\StorePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentDriver\StorePaymentDriverRequest;
use Payments\Http\Requests\PaymentDriver\UpdatePaymentDriverRequest;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentDriver;
use Payments\Models\PaymentType;

class PaymentDriverController extends Controller
{
    /**
     * Update payment drivers
     * @group
     * Admin > Payments
     */
    public function update(UpdatePaymentDriverRequest $request)
    {
        $driver = PaymentDriver::find($request->id);
        $driver->update($request->validated());
        return api()->success('payment.updated-payment-currencies',$driver);

    }

    /**
     * Store payment driver
     * @group
     * Admin > Payments
     */
    public function store(StorePaymentDriverRequest $request)
    {
        $driver = PaymentDriver::query()->create($request->validated());
        return api()->success('payment.updated-payment-currencies',$driver);

    }
}
