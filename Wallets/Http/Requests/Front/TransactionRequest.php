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
            'from_date' => [
                'nullable',
                'date',
                request()->has('to_date') ? 'lt:' . request()->get('to_date') : 'before:tomorrow',
            ],
            'to_date' => [
                'nullable',
                'date',
                request()->has('from_date') ? 'gt:' . request()->get('from_date') : '',
                'before:tomorrow'
            ],
            "description" => 'nullable|string'
        ];

    }

}
