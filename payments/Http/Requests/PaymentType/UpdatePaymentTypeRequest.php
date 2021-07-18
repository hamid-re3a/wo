<?php


namespace Payments\Http\Requests\PaymentType;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentTypeRequest extends FormRequest
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
            'id' => 'required|exists:payment_types',
            'name' => array('string',
                Rule::unique('payment_types')->ignore($this->request->get('id'), 'id')),
            'is_active' => 'boolean'
        ];
    }
}
