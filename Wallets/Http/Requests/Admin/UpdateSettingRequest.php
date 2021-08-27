<?php

namespace Wallets\Http\Requests\Admin;

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
            'name' => 'required|string|exists:wallet_settings,name',
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
            case 'percentage_transfer_fee':
            case 'fix_transfer_fee':
                return 'integer|min:1';
                break;
            case 'transaction_fee_calculation' :
                return 'in:fix,percentage';
        }
    }

}
