<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class TransferFundFromDepositWallet extends FormRequest
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
        $this->minimum_amount = walletGetSetting('minimum_transfer_fund_amount');
        $this->maximum_amount = walletGetSetting('maximum_transfer_fund_amount');
        return [
            'member_id' => 'required|integer|exists:users,member_id',
            'amount' => "required|integer|min:{$this->minimum_amount}|max:{$this->maximum_amount}"
        ];

    }

    public function messages()
    {
        return [
            'amount.min' => "Minimum allowed transfer is {$this->minimum_amount} PF",
            'amount.max' => "Maximum allowed transfer is {$this->maximum_amount} PF",
        ];
    }

}
