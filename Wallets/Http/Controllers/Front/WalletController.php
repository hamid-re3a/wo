<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use User\Models\User;
use Wallets\Http\Requests\Front\GetTransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Services\BankService;

class WalletController extends Controller
{
    /**
     * @var $bankService BankService
     */
    private $bankService;

    public function readyBankService()
    {
        /**
         * @var $user User
         * @var $bankService BankService
         */
        $user = auth()->user();
        $this->bankService = new BankService($user);
        if($this->bankService->getAllWallets()->count() != count(WALLET_NAMES))
            foreach(WALLET_NAMES AS $wallet_name)
                $this->bankService->getWallet($wallet_name);
    }
    /**
     * Wallets list
     * @group Public User > Wallets
     */
    public function index()
    {
        $this->readyBankService();
        return api()->success(null,EarningWalletResource::collection($this->bankService->getAllWallets()));
    }


    /**
     * Get transaction details
     * @group Public User > Wallets
     * @param GetTransactionRequest $request
     * @return JsonResponse
     */
    public function getTransaction(GetTransactionRequest $request)
    {
        $this->readyBankService();
        return api()->success(null,TransactionResource::make($this->bankService->getTransaction($request->get('id'))));
    }
}
