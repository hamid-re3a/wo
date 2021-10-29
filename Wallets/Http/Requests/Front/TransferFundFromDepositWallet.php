<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use User\Models\User;
use Wallets\Services\BankService;

class TransferFundFromDepositWallet extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
    private $member_id;
    private $wallet_balance;

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
        $this->minimum_amount = getWalletSetting('minimum_transfer_fund_amount');
        $this->maximum_amount = getWalletSetting('maximum_transfer_fund_amount');
        list($total, $fee) = $this->request->has('amount') ? calculateTransferAmount($this->request->get('amount')) : 0;
        $this->request->set('amount', (double) $this->request->get('amount') + $fee);

        return [
            'member_id' => 'required|integer|exists:users,member_id|not_in:' . $this->member_id,
            'amount' => "required|integer|min:{$this->minimum_amount}|max:{$this->maximum_amount}|lte:{$this->wallet_balance}",
        ];

    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed transfer is ' . formatCurrencyFormat($this->minimum_amount) . " PF .",
            'amount.max' => 'Maximum allowed transfer is ' . formatCurrencyFormat($this->maximum_amount) ." PF .",
            'member_id.not_in' => 'You are not allowed transfer PF to your account .',
            'member_id.exists' => 'Invalid membership ID .',
            'amount.lte' => 'Insufficient amount'
        ];
    }

    private function prepare()
    {
        if (auth()->check()) {
            /**
             * @var $user User
             */
            $user = auth()->user();
            $this->member_id = $user->member_id;
            $bank_service = new BankService($user);
            $this->wallet_balance = $bank_service->getBalance(WALLET_NAME_DEPOSIT_WALLET);
        }
    }

}
