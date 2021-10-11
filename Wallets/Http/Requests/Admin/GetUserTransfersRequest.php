<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GetUserTransfersRequest extends FormRequest
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
        ];

    }

}
