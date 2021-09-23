<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PayoutGroupWithdrawRequest extends FormRequest
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
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:wallet_withdraw_profit_requests,uuid,status,1',
        ];

    }

    public function messages()
    {
        return [
            'ids.*.required' => 'Invalid withdraw request selected .',
            'ids.*.uuid' => 'Invalid withdraw request selected .',
            'ids.*.exists' => 'Invalid withdraw request selected .',
        ];
    }

}
