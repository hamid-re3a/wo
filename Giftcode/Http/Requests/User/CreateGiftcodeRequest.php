<?php

namespace Giftcode\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'wallet' => 'required|in:Deposit Wallet,Earning Wallet'
        ];
    }

}
