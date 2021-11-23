<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Services\BankService;

class TransferFundFromEarningWalletRequest extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
    private $wallet_balance;
    private $member_id;
    private $fee;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->prepare();
    }

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
            'amount' => [
                'required',
                'numeric',
                'min:' . $this->minimum_amount,
                'max:' . $this->maximum_amount,
                function($attribute,$value,$fail){
                    if(($value + $this->fee) > $this->wallet_balance)
                        return $fail(trans('wallet.responses.not-enough-balance'));
                }
            ],
        ];

    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed is ' . formatCurrencyFormat($this->minimum_amount) . " PF",
            'amount.max' => 'Maximum allowed is ' . formatCurrencyFormat($this->maximum_amount) ." PF",
            'member_id.not_in' => 'You are not allowed transfer PF to your account .',
            'member_id.exists' => 'Invalid membership ID .',
        ];
    }

    private function prepare()
    {
        try {
            if(auth()->check()){
                /**@var $user User*/
                $user = auth()->user();
                $bank_service = new BankService($user);
                $this->wallet_balance = $bank_service->getBalance(WALLET_NAME_EARNING_WALLET);
                $this->member_id = auth()->user()->member_id;

                $this->minimum_amount = getWalletSetting('minimum_transfer_from_earning_to_deposit_wallet_fund_amount');
                $this->maximum_amount = getWalletSetting('maximum_transfer_from_earning_to_deposit_wallet_fund_amount');

                list($total, $fee) = $this->request->has('amount') ? calculateTransferFee($this->request->get('amount')) : [0,0];
                $this->fee = $fee;
            }
        } catch (\Throwable $exception){
            Log::error('Wallets\Http\Requests\Front\TransferFundFromEarningWalletRequest@prepare => ' . $exception->getMessage());
            throw new \Exception(trans('wallets.responses.something-went-wrong'), 400);
        }
    }




}
