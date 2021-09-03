<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\WalletResource;
use Wallets\Services\BankService;

class EarningWalletController extends Controller
{
    private $bankService;
    private $wallet;

    private function prepareEarningWallet()
    {

        $this->bankService = new BankService(request()->user);
        $this->wallet = config('earningWallet');
        $this->bankService->getWallet($this->wallet);

    }

    /**
     * Get Deposit wallet
     * @group Public User > Earning Wallet
     */
    public function index()
    {

        $this->prepareEarningWallet();
        return api()->success(null,WalletResource::make($this->bankService->getWallet($this->wallet)));

    }

    /**
     * Get transactions
     * @group Public User > Earning Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {

        $this->prepareEarningWallet();
        $data = $this->bankService->getTransactions($this->wallet)->select('*')
            ->selectRaw('( SELECT IFNULL(SUM(amount), 0) FROM transactions t2 WHERE t2.confirmed=1 AND t2.wallet_id=transactions.wallet_id AND t2.id <= transactions.id ) new_balance')
            ->simplePaginate();
        return api()->success(null,TransactionResource::collection($data)->response()->getData());

    }

    /**
     * Get transfers
     * @group Public User > Earning Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $this->prepareEarningWallet();
        $data = $this->bankService->transfers($this->wallet)->simplePaginate();
        return api()->success(null,TransferResource::collection($data)->response()->getData());

    }
}
