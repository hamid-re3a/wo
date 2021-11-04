<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Payments\Services\PaymentService;
use User\Models\User;
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
        $this->minimum_amount = getWalletSetting('minimum_withdraw_request_from_earning_wallet_amount');
        $this->maximum_amount = getWalletSetting('maximum_withdraw_request_from_earning_wallet_amount');

        list($total,$fee) = $this->request->has('amount') AND $this->request->has('currency') ? calculateWithdrawalFee($this->request->get('amount'),$this->request->get('currency')) : [0,0];
        $this->request->set('amount_original', (double) $this->request->get('amount'));
        $this->request->set('amount', (double) $this->request->get('amount') + $fee);

        return [
            'amount' => "required|numeric|min:{$this->minimum_amount}|max:{$this->maximum_amount}|lte:" . $this->wallet_balance,
            'currency' => 'required|in:' . implode(',', $this->getNamePaymentCurrency()),
        ];
    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed is ' . formatCurrencyFormat($this->minimum_amount) . " PF",
            'amount.max' => 'Maximum allowed is ' . formatCurrencyFormat($this->maximum_amount) . " PF",
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
            $bank_service = new BankService($user);
            $this->wallet_balance = $bank_service->getBalance(WALLET_NAME_EARNING_WALLET);
        }

    }


    private function getNamePaymentCurrency()
    {

        $currencies = app(PaymentService::class)->getPaymentCurrencies(CURRENCY_SERVICE_WITHDRAW);
        if ($currencies)
            return $currencies->pluck('name')->toArray();
        else
            return [];

    }

}
