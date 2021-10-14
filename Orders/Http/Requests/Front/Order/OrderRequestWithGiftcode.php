<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;

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

}
