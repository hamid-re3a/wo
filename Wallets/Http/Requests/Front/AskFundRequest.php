<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class AskFundRequest extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
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
        $this->minimum_amount = walletGetSetting('minimum_payment_request_amount');
        $this->maximum_amount = walletGetSetting('maximum_payment_request_amount');
        return [
            'member_id' => 'required|integer|exists:users,member_id|not_in:' . $this->user->member_id,
            'amount' => "required|integer|min:{$this->minimum_amount}|max:{$this->maximum_amount}"
        ];

    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed amount is ' . walletPfAmount($this->minimum_amount) . ' PF',
            'amount.max' => 'Maximum allowed amount is ' . walletPfAmount($this->maximum_amount) . ' PF',
            'member_id.exists' => 'Invalid Membership ID',
            'member_id.not_in' => 'You are not allowed to ask PF from your own .'
        ];
    }

}
