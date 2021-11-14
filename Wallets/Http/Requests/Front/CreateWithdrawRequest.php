<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Payments\Services\PaymentService;
use User\Models\User;
use Wallets\Services\BankService;

class CreateWithdrawRequest extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
    private $wallet_balance;
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
            'amount' => [
                'required',
                'numeric',
                'min:' . $this->minimum_amount,
                'max:' . $this->maximum_amount,
                function($attribute,$value,$fail){
                    if(($value + $this->fee) > $this->wallet_balance)
                        return $fail('Insufficient amount .');
                }
            ],
            'currency' => 'required|in:' . implode(',', $this->getNamePaymentCurrency()),
        ];
    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed is ' . formatCurrencyFormat($this->minimum_amount) . " PF",
            'amount.max' => 'Maximum allowed is ' . formatCurrencyFormat($this->maximum_amount) . " PF"
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

            $this->minimum_amount = getWalletSetting('minimum_withdraw_request_from_earning_wallet_amount');
            $this->maximum_amount = getWalletSetting('maximum_withdraw_request_from_earning_wallet_amount');
            Log::info('Withdrawal limits => ' . $this->minimum_amount . ' , ' . $this->maximum_amount);

            list($total, $fee) = $this->request->has('amount') AND $this->request->has('currency') ? calculateWithdrawalFee($this->request->get('amount'), $this->request->get('currency')) : [0, 0];
            $this->fee = $fee;
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
