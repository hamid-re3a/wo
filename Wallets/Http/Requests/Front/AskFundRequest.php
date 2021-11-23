<?php

namespace Wallets\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class AskFundRequest extends FormRequest
{
    private $minimum_amount;
    private $maximum_amount;
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
        $this->minimum_amount = getWalletSetting('minimum_payment_request_amount');
        $this->maximum_amount = getWalletSetting('maximum_payment_request_amount');
        return [
            'member_id' => 'required|integer|exists:users,member_id|not_in:' . $this->member_id,
            'amount' => "required|numeric|min:{$this->minimum_amount}|max:{$this->maximum_amount}",
            'transaction_password' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($this->request->has('transaction_password') AND strlen($value) > 0 AND !checkTransactionPassword(auth()->user()->id, $value))
                        return $fail(trans('wallet.responses.incorrect-transaction-password'));
                }
            ]
        ];

    }

    public function messages()
    {
        return [
            'amount.min' => 'Minimum allowed amount is ' . formatCurrencyFormat($this->minimum_amount) . ' PF',
            'amount.max' => 'Maximum allowed amount is ' . formatCurrencyFormat($this->maximum_amount) . ' PF',
            'member_id.exists' => 'Invalid Membership ID',
            'member_id.not_in' => 'You are not allowed to ask PF from your own .'
        ];
    }

    private function prepare()
    {
        if (auth()->check())
            $this->member_id = auth()->user()->member_id;
    }

}
