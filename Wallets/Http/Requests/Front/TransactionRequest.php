<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'transaction_id' => 'nullable|integer',
            'type' => 'nullable|in:deposit,withdraw',
            'amount' => 'nullable|numeric',
            'amount_from' => [
                'nullable',
                'numeric',
                request()->has('amount_to') ? 'lt:amount_to' : '',
            ],
            'amount_to' => [
                'nullable',
                'numeric',
                request()->has('amount_from') ? 'gt:amount_from' : '',
            ],
            'created_at_from' => [
                'nullable',
                'date',
                request()->has('created_at_to') ? 'lt:' . request()->get('created_at_to') : 'before:tomorrow',
            ],
            'created_at_to' => [
                'nullable',
                'date',
                request()->has('created_at_from') ? 'gt:' . request()->get('created_at_from') : 'before:tomorrow',
            ],
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'order_id' => 'nullable|integer',
            'member_id' => 'nullable|integer'
        ];

    }

}
