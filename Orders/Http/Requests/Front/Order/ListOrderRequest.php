<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;

class ListOrderRequest extends FormRequest
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
            'is_paid_from' => [
                'nullable',
                'date',
                request()->has('is_paid_to') ? 'before_or_equal:is_paid_to' : null
            ],
            'is_paid_to' => [
                'nullable',
                'date',
                request()->has('is_paid_from') ? 'after_or_equal:is_paid_from' : null
            ],

            'created_at_from' => [
                'nullable',
                'date',
                request()->has('created_at_to') ? 'before_or_equal:created_at_to' : null
            ],
            'created_at_to' => [
                'nullable',
                'date',
                request()->has('created_at_from') ? 'after_or_equal: created_at_from' : null
            ],
            'is_paid' => 'nullable|boolean',
            'is_refunded' => 'nullable|boolean',
            'is_expired' => 'nullable|boolean'
        ];
    }


}
