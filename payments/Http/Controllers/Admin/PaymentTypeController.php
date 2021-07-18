<?php

namespace Packages\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Payments\Http\Requests\PaymentCurrency\StorePaymentCurrencyRequest;
use Payments\Http\Requests\PaymentType\StorePaymentTypeRequest;
use Payments\Http\Requests\PaymentType\UpdatePaymentTypeRequest;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentDriver;
use Payments\Models\PaymentType;

class PaymentTypeController extends Controller
{
    /**
     * Update payment types
     * @group
     * Admin > Payments
     */
    public function update(UpdatePaymentTypeRequest $request)
    {
        $type = PaymentType::find($request->id);

        if($request->is_active == false && $this->isThisLastActiveType($request)){
            $errors = [
                'is_active' => 'payment.at-least-one-active-type-should-exist'
            ];
            return api()->error($errors['is_active'],'',422,$errors);
        }
        $type->update($request->validated());
        return api()->success('payment.updated-payment-types',$type);

    }

    /**
     * Store payment currencies
     * @group
     * Admin > Payments
     */
    public function store(StorePaymentTypeRequest $request)
    {
        $type = PaymentType::query()->create($request->validated());
        return api()->success('payment.updated-payment-currencies',$type);

    }

    /**
     * @param UpdatePaymentTypeRequest $request
     * @return bool
     */
    private function isThisLastActiveType(UpdatePaymentTypeRequest $request): bool
    {
        return !PaymentType::query()->where('id' != $request->id)->where('is_active', true)->exists();
    }

}
