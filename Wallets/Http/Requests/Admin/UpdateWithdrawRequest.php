<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWithdrawRequest extends FormRequest
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
            'id' => 'required|uuid|exists:wallet_withdraw_profit_requests,uuid',
            'status' => 'required|in:2,3', // 2=Rejected, 3=Processed
            'rejection_reason' => 'required_if:status,2|string',
            'network_hash' => 'required_if:status,3',
        ];

    }

    public function messages()
    {
        return [
            'rejection_reason.required_if' => 'The rejection reason field is required when status is rejected',
            'network_hash.required_if' => 'The network hash field is required when status is processed',
        ];
    }

}
