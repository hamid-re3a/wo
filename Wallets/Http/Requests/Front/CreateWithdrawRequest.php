<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Orders\Services\OrderService;
use Wallets\Services\BankService;

class CreateWithdrawRequest extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
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
        $this->minimum_amount = walletGetSetting('minimum_withdraw_request_from_earning_wallet_amount');
        $this->maximum_amount = walletGetSetting('maximum_withdraw_request_from_earning_wallet_amount');

        return [
            'amount' => "required|integer|min:{$this->minimum_amount}|max:{$this->maximum_amount}|lte:" . $this->wallet_balance,
            'currency' => 'required|in:'. implode(',', $this->getNamePaymentCurrency()),
        ];
    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed is ' . walletPfAmount($this->minimum_amount) . " PF",
            'amount.max' => 'Maximum allowed is ' . walletPfAmount($this->maximum_amount) ." PF",
            'amount.lte' => 'Insufficient amount'
        ];
    }

    private function prepare()
    {
        if(auth()->check()){
            $bank_service = new BankService(auth()->user());
            $this->wallet_balance = $bank_service->getBalance(config('earningWallet'));
        }
    }

    /**
     * get name of payment currency
     * @return array
     */
    private function getNamePaymentCurrency()
    {
        $payment_currencies = app(OrderService::class)->getPaymentCurrencies()->getPaymentCurrencies();
        $payment_currency_name_array = array();
        foreach ($payment_currencies as $payment_currency) {
            $payment_currency_name_array[] = $payment_currency->getName();
        }
        return $payment_currency_name_array;
    }

}
