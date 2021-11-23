<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;
use Orders\Services\OrderService;
use Payments\Services\PaymentService;

class OrderRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'to_user_id' => 'sometimes|exists:users,id',
            'package_id' => 'required|numeric',
            'payment_type' => [
                'required',
                'in:' . implode(',', $this->getNamePaymentType())
            ],
            'payment_currency' => [
                'required_if:payment_type,purchase',
                'in:' . implode(',', $this->getNamePaymentCurrency())
            ],
            'payment_driver' => [
                'required_if:payment_type,purchase',
                'in:btc-pay-server'
            ],
            'giftcode' => 'required_if:payment_type,giftcode',
            'transaction_password' => [
                function ($attribute, $value, $fail) {
                    if ($this->request->has('payment_type') AND $this->request->get('payment_type') == 'deposit') {
                        if ($this->request->has('transaction_password') AND !checkTransactionPassword(auth()->user()->id, $this->request->get('transaction_password')))
                            return $fail(trans('order.responses.incorrect-transaction-password'));
                    }
                }
            ]
        ];
    }

    private function getNamePaymentCurrency()
    {
        $currencies = app(PaymentService::class)->getPaymentCurrencies(CURRENCY_SERVICE_PURCHASE);
        if ($currencies)
            return $currencies->pluck('name')->toArray();
        else
            return [];
    }

    /**
     * get name payment type
     * @return array
     */
    private function getNamePaymentType()
    {
        $payment_types = app(PaymentService::class)->getActivePaymentTypes();
        if ($payment_types)
            return $payment_types->pluck('name')->toArray();
        else
            return [];
    }


}
