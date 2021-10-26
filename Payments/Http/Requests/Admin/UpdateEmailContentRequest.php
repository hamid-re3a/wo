<?php

namespace Payments\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailContentRequest extends FormRequest
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
            'key' => 'required|string|exists:payment_email_content_settings,key',
            'is_active' => 'required|boolean',
            'subject' => 'required|string',
//            'from' => 'required|email',
//            'from_name' => 'required|string',
            'body' => 'required|string',
//            'variables' => 'required|string',
//            'variable_description' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'key.exists' => 'selected email is not valid'
        ];
    }

}
