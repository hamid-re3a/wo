<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserDepositRequest extends FormRequest
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
            'user_id' => 'required|exists:wallet_users,user_id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'wallet_name' => "required|in:{$depositWallet},{$earningWallet}"
        ];

    }

}