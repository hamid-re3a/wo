<?php

namespace Wallets\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use User\Models\User;
use Wallets\Services\BankService;

class SubmitRemarkRequest extends FormRequest
{
    private $wallet_balance;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->prepare();
    }

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
            'remarks' => 'nullable|string',
            'amount' => [
                'required',
                'numeric',
                function($attribute,$value,$fail){
                    if($value > $this->wallet_balance)
                        return $fail(trans('wallet.responses.not-enough-balance'));
                }
            ]
        ];

    }

    private function prepare()
    {
        if (auth()->check()) {
            /**
             * @var $user User
             */
            $user = User::query()->find(1);
            $bank_service = new BankService($user);
            $this->wallet_balance = $bank_service->getBalance(WALLET_NAME_CHARITY_WALLET);
        }
    }

}
