<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class ChargeDepositWalletRequest extends FormRequest
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
     * @throws \Exception
     */
    public function rules()
    {

        $minimum_amount = walletGetSetting('minimum_deposit_fund_amount');
        $maximum_amount = walletGetSetting('maximum_deposit_fund_amount');

        return [
            'amount' => "required|numeric|between:{$minimum_amount},{$maximum_amount}"
        ];

    }


}
