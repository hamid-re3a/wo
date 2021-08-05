<?php


namespace Payments\Http\Requests\PaymentCurrency;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemovePaymentCurrencyRequest extends FormRequest
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
            'id' => 'required|exists:payment_currencies',
        ];
    }
}
