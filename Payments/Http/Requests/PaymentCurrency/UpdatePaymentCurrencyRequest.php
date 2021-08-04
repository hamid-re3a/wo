<?php


namespace Payments\Http\Requests\PaymentCurrency;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentCurrencyRequest extends FormRequest
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
            'name' => array('string',
                Rule::unique('payment_currencies')->ignore($this->request->get('id'), 'id')),
            'is_active' => 'boolean'
        ];
    }
}
