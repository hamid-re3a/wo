<?php


namespace Payments\Http\Requests\PaymentDriver;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentDriverRequest extends FormRequest
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
            'id' => 'required|exists:payment_drivers',
            'name' => array('required','string',
                Rule::unique('payment_drivers')->ignore($this->request->get('id'), 'id')),
            'is_active' => 'required|boolean'
        ];
    }
}
