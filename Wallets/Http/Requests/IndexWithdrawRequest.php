<?php

namespace Wallets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexWithdrawRequest extends FormRequest
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
            'statuses' => 'required|array',
            'statuses.*' => 'required|integer|in:' . implode(',',WALLET_WITHDRAW_COMMANDS)
        ];

    }

    public function bodyParameters()
    {
        return [
            'status' => [
                'description' => 'Query param to filter withdraw requests',
                'type' => 'Query parameter'
            ]
        ];
    }

}
