<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Services\BankService;

class EarningWalletController extends Controller
{
    private $bankService;
    private $wallet;

    public function __construct()
    {
        $this->bankService = new BankService(request()->wallet_user);
        $this->wallet = config('earningWallet');
        $this->bankService->getWallet($this->wallet);
    }

    /**
     * Get transactions
     * @group Wallets > Earning Wallet
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
     * @group Wallets > Earning Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $data = $this->bankService->transfers($this->wallet)->simplePaginate();
        return api()->success(null,TransferResource::collection($data)->response()->getData());

    }
}
