<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
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
                case 'transfer_fee':
                case 'maximum_auto_handle_withdrawals_payout':
                case 'count_withdraw_requests_to_automatic_payout_process':
                    return 'required|integer|min:1';
                    break;
                case 'transaction_fee_calculation' :
                    return 'required|in:fix,percentage';
                    break;
                case 'minimum_deposit_fund_amount':
                    return 'required|min:1|lte:' . getWalletSetting('maximum_deposit_fund_amount');
                    break;
                case 'maximum_deposit_fund_amount':
                    return 'required|gte:' . getWalletSetting('minimum_deposit_fund_amount');
                    break;
                case 'minimum_transfer_fund_amount':
                    return 'required|min:1|lte:' . getWalletSetting('maximum_transfer_fund_amount');
                    break;
                case 'maximum_transfer_fund_amount':
                    return 'required|gte:' . getWalletSetting('minimum_transfer_fund_amount');
                    break;
                case 'minimum_payment_request_amount':
                    return 'required|lte:' . getWalletSetting('maximum_payment_request_amount');
                    break;
                case 'maximum_payment_request_amount':
                    return 'required|gte:' . getWalletSetting('minimum_payment_request_amount');
                    break;
                case 'minimum_transfer_from_earning_to_deposit_wallet_fund_amount':
                    return 'required|lte:' . getWalletSetting('maximum_transfer_from_earning_to_deposit_wallet_fund_amount');
                    break;
                case 'maximum_transfer_from_earning_to_deposit_wallet_fund_amount':
                    return 'required|gte:' . getWalletSetting('minimum_transfer_from_earning_to_deposit_wallet_fund_amount');
                    break;
                case 'minimum_withdraw_request_from_earning_wallet_amount':
                    return 'required|lte:' . getWalletSetting('maximum_withdraw_request_from_earning_wallet_amount');
                    break;
                case 'maximum_withdraw_request_from_earning_wallet_amount':
                    return 'required|gte:' . getWalletSetting('minimum_withdraw_request_from_earning_wallet_amount');
                    break;
                case 'withdrawal_request_is_enabled':
                case 'auto_payout_withdrawal_request_is_enable':
                case 'withdrawal_distribution_is_enabled':
                    return 'required|boolean';
                    break;
                case 'payout_btc_fee_fixed_or_percentage':
                case 'payout_janex_fee_fixed_or_percentage':
                return 'required|in:percentage,fixed';
                    break;
                case 'payout_btc_fee':
                case 'payout_janex_fee':
                    return 'required|integer';
                    break;
                case 'withdrawal_distribution_in_btc' :
                    $withdrawal_distribution_in_janex = (int) getWalletSetting('withdrawal_distribution_in_janex');
                    return 'required|min:0|max:' . (100 - $withdrawal_distribution_in_janex);
                    break;
                case 'withdrawal_distribution_in_janex' :
                    $withdrawal_distribution_in_btc = (int) getWalletSetting('withdrawal_distribution_in_janex');
                    return 'required|min:0|max:' . (100 - $withdrawal_distribution_in_btc);
                    break;
                default:
                    return 'required';
            }
        }

        return 'required';
    }

}
