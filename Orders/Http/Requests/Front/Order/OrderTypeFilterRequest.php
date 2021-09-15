<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Orders\Services\OrderService;
use Payments\Services\Grpc\PaymentCurrency;

class OrderTypeFilterRequest extends FormRequest
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
            'type' => array('required', Rule::in(["week","month","year"])),
        ];
    }


}
