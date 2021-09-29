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
            'id' => 'required|integer|exists:wallet_withdraw_profit_requests,uuid,status,!2',
            'status' => 'required|in:2,3,4', // 2=Rejected, 3=Processed, 4=Postponed
            'rejection_reason' => 'required_if:status,2|string',
            'postponed_to' => 'required_if:status,4|datetime|after:today'
        ];

    }

    public function messages()
    {
        return [
            'rejection_reason.required_if' => 'The rejection reason field is required when status is rejected',
            'postponed_to.required_if' => 'The postponed date field is required when status is postponed',
        ];
    }

}
