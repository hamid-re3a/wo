<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Services\BankService;

class DepositWalletController extends Controller
{
    private $bankService;
    private $wallet = 'Deposit Wallet';

    public function __construct()
    {
        $this->bankService = new BankService(request()->wallet_user);
        $this->bankService->getWallet($this->wallet);
    }

    /**
     * transactions
     * @group Wallets > Deposit Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {
        return api()->success(null,TransactionResource::collection($this->bankService->getTransactions($this->wallet)->simplePaginate()));
    }

    /**
     * transfers
     * @group Wallets > Deposit Wallet
     * @return JsonResponse
     */
    public function transfers()
    {
        return api()->success(null,TransferResource::collection($this->bankService->transfers($this->wallet)->simplePaginate()));
    }
}
