<?php

namespace Giftcode\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
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
            'name' => 'required|string|exists:giftcode_settings,name',
            'value' => 'required|' . $this->valueValidation(),
            'title' => 'nullable|string',
            'description' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'name.exists' => 'selected setting is not valid'
        ];
    }

    private function valueValidation()
    {
        switch ($this->name) {
            case 'characters':
                return 'string|min:6';
                break;
            case 'length':
                return 'integer|min:4';
                break;
            case 'separator':
                return Rule::in(['-','_']);
                break;
            case 'postfix':
            case 'prefix':
                return 'string';
                break;
            case 'use_postfix':
            case 'use_prefix':
            case 'include_cancellation_fee':
            case 'include_expiration_fee':
            case 'include_registration_fee':
                return 'boolean';
                break;
            case 'cancellation_fee':
            case 'registration_fee':
            case 'expiration_fee':
            case 'giftcode_lifetime':
                return 'integer|min:1';
                break;
        }
    }

}
