<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Wallets\Models\Setting;

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
            'value' => $this->valueValidation(),
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
        if ($this->has('name')) {
            $settings = Setting::all();
            switch ($this->get('name')) {
                case 'percentage_transfer_fee':
                case 'fix_transfer_fee':
                    return 'required|integer|min:1';
                    break;
                case 'transaction_fee_calculation' :
                    return 'required|in:fix,percentage';
                    break;
                case 'minimum_deposit_fund_amount':
                    return 'required|min:1|lte:' . $settings->where('name', '=', 'maximum_deposit_fund_amount')->pluck('value');
                    break;
                case 'maximum_deposit_fund_amount':
                    return 'required|gte:' . $settings->where('name', '=', 'minimum_deposit_fund_amount')->pluck('value');
                    break;
                case 'minimum_transfer_fund_amount':
                    return 'required|min:1|lte:' . $settings->where('name', '=', 'maximum_transfer_fund_amount')->pluck('value');
                    break;
                case 'maximum_transfer_fund_amount':
                    return 'required|gte:' . $settings->where('name', '=', 'minimum_transfer_fund_amount')->pluck('value');
                    break;
                case 'minimum_payment_request_amount':
                    return 'required|lte:' . $settings->where('name', '=', 'maximum_payment_request_amount')->pluck('value');
                    break;
                case 'maximum_payment_request_amount':
                    return 'required|gte:' . $settings->where('name', '=', 'minimum_payment_request_amount')->pluck('value');
                    break;
                default:
                    return 'required';
            }
        }

        return 'required';
    }

}
