<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'id' => [
                'required',
                Rule::exists('wallet_withdraw_profit_requests','uuid')->where('uuid',$this->get('id'))->whereNotIn('status',[
//                    WALLET_WITHDRAW_COMMAND_REJECT,
                    WALLET_WITHDRAW_COMMAND_PROCESS
                ])
            ],
            'status' => 'required|in:' . implode(',', [
                    WALLET_WITHDRAW_COMMAND_REJECT,
                    WALLET_WITHDRAW_COMMAND_PROCESS,
                    WALLET_WITHDRAW_COMMAND_POSTPONE,
                    WALLET_WITHDRAW_COMMAND_REVERT
                ]),
            'act_reason' => 'required_if:status,'. WALLET_WITHDRAW_COMMAND_REJECT .'|string',
            'postponed_to' => 'required_if:status,' .WALLET_WITHDRAW_COMMAND_POSTPONE. '|date|date_format:Y/m/d|after:today'
        ];

    }

    public function messages()
    {
        return [
            'act_reason.required_if' => 'The rejection reason field is required when status is rejected',
            'postponed_to.required_if' => 'The postponed date field is required when status is postponed',
        ];
    }

}
