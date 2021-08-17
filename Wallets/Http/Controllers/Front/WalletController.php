<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\Front\GetTransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\WalletResource;
use Wallets\Services\BankService;

class WalletController extends Controller
{
    private $bankService;
    private $depositWallet;
    private $earningWallet;

    public function __construct()
    {
        $this->bankService = new BankService(request()->wallet_user);
        $this->earningWallet = config('earningWallet');
        $this->depositWallet = config('depositWallet');
        if($this->bankService->getAllWallets()->count() == 0) {
            $this->bankService->getWallet($this->depositWallet);
            $this->bankService->getWallet($this->earningWallet);
        }
    }
    /**
     * Wallets list
     * @group Wallets
     */
    public function index()
    {
        return api()->success(null,WalletResource::collection($this->bankService->getAllWallets()));
    }


    /**
     * Get transaction details
     * @group Wallets
     * @param GetTransactionRequest $request
     * @return JsonResponse
     */
    public function getTransaction(GetTransactionRequest $request)
    {
        return api()->success(null,TransactionResource::make($this->bankService->getTransaction($request->get('id'))));
    }
}
