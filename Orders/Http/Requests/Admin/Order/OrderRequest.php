<?php

namespace Orders\Http\Requests\Admin\Order;

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
            'package_id' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'plan' => 'required|in:'.ORDER_PLAN_SPECIAL.'|'.ORDER_PLAN_COMPANY
        ];
    }


}
