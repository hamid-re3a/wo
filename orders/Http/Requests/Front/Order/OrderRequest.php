<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orders\Http\Resources\OrderPaymentCurrencyResource;
use Orders\Services\OrderService;
use Payments\Services\PaymentCurrency;

class OrderRequest extends FormRequest
{
    private $order_service;

    public function __construct(OrderService $order_service)
    {
        $this->order_service = $order_service;
    }

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

            'items' => 'required|array',
//            'items.*.id' => 'exists:packages,id',
//            'items.*.qty' => 'required|numeric|min:1',
//            'to_user_id' => 'nullable|exists:order_users,id',
            'plan' => array('in:' . implode(',', ORDER_PLANS)),

            'payment_type' => array('required', Rule::in($this->getNamePaymentCurrency())),
            'payment_driver' => array('required_if:payment_type,=,gate-way'),
            'payment_currency' => array('required', Rule::in($this->getNamePaymentType())),
        ];
    }

    /**
     * get name of payment currency
     * @return array
     */
    private function getNamePaymentCurrency()
    {
        $payment_currencies = $this->order_service->getPaymentCurrencies()->getPaymentCurrencies();
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
        $payment_types = $this->order_service->getPaymentTypes()->getPaymentTypes();
        $payment_type_name_array = array();
        foreach ($payment_types as $payment_type) {
            $payment_type_name_array[] = $payment_type->getName();
        }
        return $payment_type_name_array;
    }


}
