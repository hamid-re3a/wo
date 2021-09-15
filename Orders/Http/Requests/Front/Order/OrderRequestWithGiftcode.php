<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orders\Services\OrderService;
use Payments\Services\Grpc\PaymentCurrency;

class OrderRequestWithGiftcode extends FormRequest
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
            'package_id' => 'required|numeric',
            'plan' => array('in:' . implode(',', ORDER_PLANS)),
            'giftcode' => 'required|string'
        ];
    }

    /**
     * get name of payment currency
     * @return array
     */
    private function getNamePaymentCurrency()
    {
        $payment_currencies = app(OrderService::class)->getPaymentCurrencies()->getPaymentCurrencies();
        $payment_currency_name_array = array();
        foreach ($payment_currencies as $payment_currency) {
            $payment_currency_name_array[] = $payment_currency->getName();
        }
        return $payment_currency_name_array;
    }

    /**
     * get name payment type
     * @return array
     */
    private function getNamePaymentType()
    {
        $payment_types = app(OrderService::class)->getPaymentTypes()->getPaymentTypes();
        $payment_type_name_array = array();
        foreach ($payment_types as $payment_type) {
            $payment_type_name_array[] = $payment_type->getName();
        }
        return $payment_type_name_array;
    }


}
