<?php


namespace Payments\Http\Requests\PaymentType;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentTypeRequest extends FormRequest
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
            'name' => array('string','required',
                Rule::unique('payment_types')->ignore($this->request->get('id'), 'id')),
            'is_active' => 'boolean|required'
        ];
    }
}
