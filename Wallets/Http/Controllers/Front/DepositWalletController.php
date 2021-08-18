<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\WalletResource;
use Wallets\Services\BankService;

class DepositWalletController extends Controller
{
    private $bankService;
    private $wallet;

    public function __construct()
    {
        $this->bankService = new BankService(request()->wallet_user);
        $this->wallet = config('depositWallet');
        $this->bankService->getWallet($this->wallet);
    }

    /**
     * Get Deposit wallet
     * @group Public User > Deposit Wallet
     */
    public function index()
    {

        return api()->success(null,WalletResource::make($this->bankService->getWallet($this->wallet)));

    }

    /**
     * Get transactions
     * @group Public User > Deposit Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {

        $data = $this->bankService->getTransactions($this->wallet)->simplePaginate();
        return api()->success(null,TransactionResource::collection($data)->response()->getData());

    }

    /**
     * Get transfers
     * @group Public User > Deposit Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $data = $this->bankService->getTransfers($this->wallet)->simplePaginate();
        return api()->success(null,TransferResource::collection($data)->response()->getData());

    }
}
