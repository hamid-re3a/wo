<?php

namespace Giftcode\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateGiftcodeRequest extends FormRequest
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
     */
    public function rules()
    {
        return [
            'package_id' => 'required|exists:giftcode_packages,id',
            'include_registration_fee' => 'required|boolean',
            'wallet' => 'required|in:Deposit Wallet',
            'transaction_password' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($this->request->has('transaction_password') AND !checkTransactionPassword(auth()->user()->id, $this->request->get('transaction_password')))
                        return $fail(trans('giftcode.responses.incorrect-transaction-password'));
                }
            ]
        ];
    }

}
