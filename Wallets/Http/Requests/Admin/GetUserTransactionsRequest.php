<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GetUserTransactionsRequest extends FormRequest
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
        $depositWallet = config('depositWallet');
        $earningWallet = config('earningWallet');
        return [
            'user_id' => 'required|exists:users,member_id',
            'wallet_name' => "required|in:{$depositWallet},{$earningWallet}",
            'transaction_id' => 'nullable|uuid|exists:transactions,uuid,payable_id,' . request()->get('user_id'),
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
