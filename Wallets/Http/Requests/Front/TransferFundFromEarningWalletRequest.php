<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Wallets\Services\BankService;

class TransferFundFromEarningWalletRequest extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
    private $wallet_balance;
    private $member_id;

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
        $this->prepare();
        $this->minimum_amount = walletGetSetting('minimum_transfer_from_earning_to_deposit_wallet_fund_amount');
        $this->maximum_amount = walletGetSetting('maximum_transfer_from_earning_to_deposit_wallet_fund_amount');

        return [
            'member_id' => 'required_if:own_deposit_wallet,false|integer|exists:users,member_id|not_in:' . $this->member_id,
            'own_deposit_wallet' => [
                'required',
                'boolean',
                function($attribute,$value,$fail){
                    if(!$this->has('member_id') AND !$value)
                        return $fail('This field must be true if you may not send funds to another member deposit wallet');
                }
            ],
            'amount' => "required|integer|min:{$this->minimum_amount}|max:{$this->maximum_amount}|lte:" . $this->wallet_balance
        ];

    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed is ' . formatCurrencyFormat($this->minimum_amount) . " PF",
            'amount.max' => 'Maximum allowed is ' . formatCurrencyFormat($this->maximum_amount) ." PF",
            'member_id.not_in' => 'You are not allowed transfer PF to your account .',
            'amount.lte' => 'Insufficient amount'
        ];
    }

    private function prepare()
    {
        if(auth()->check()){
            $bank_service = new BankService(auth()->user());
            $this->wallet_balance = $bank_service->getBalance(config('earningWallet'));
            $this->member_id = auth()->user()->member_id;
        }
    }




}
