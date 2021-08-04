<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;

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

            'items' => 'required|array|min:1|max:1',
//            'items.*.id' => 'exists:packages,id',
//            'items.*.qty' => 'required|numeric|min:1',
//            'to_user_id' => 'nullable|exists:order_users,id',
            'plan' => array('in:'.implode(',',ORDER_PLANS)),

//            'payment_type' => array('required','in:'.implode(',',ORDER_PAYMENT_TYPES)),
            'payment_type' => array('required'),
            'payment_driver' => array('required_if:payment_type,=,gate-way'),
//            'payment_currency' => array('required','in:'.implode(',',ORDER_PAYMENT_CURRENCIES)),
            'payment_currency' => array('required'),

        ];
    }
}
