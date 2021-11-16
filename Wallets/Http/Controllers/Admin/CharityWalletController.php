<?php

namespace Wallets\Http\Controllers\Admin;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use User\Models\User;
use Wallets\Http\Requests\Admin\SubmitRemarkRequest;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Http\Resources\DepositWalletResource;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Repositories\WalletRepository;
use Wallets\Services\BankService;

class CharityWalletController extends Controller
{
    /**@var $bankService BankService*/
    private $bankService;
    private $walletName;
    /**@var $walletObject Wallet*/
    private $walletObject;
    /**@var $user User*/
    private $user;
    private $wallet_repository;

    public function __construct()
    {
        $this->wallet_repository = new WalletRepository();
    }

    private function prepareWallet()
    {
        //Charity wallet is 3d wallet of admin
        $this->user = User::query()->find(1);

        $this->bankService = new BankService($this->user);
        $this->walletName = WALLET_NAME_CHARITY_WALLET;
        $this->walletObject = $this->bankService->getWallet($this->walletName);
    }

    /**
     * Get wallet
     * @group Admin User > Charity Wallet
     */
    public function index()
    {
        $this->prepareWallet();
        return api()->success(null, DepositWalletResource::make($this->bankService->getWallet($this->walletName)));

    }

    /**
     * Get transactions
     * @group Admin User > Charity Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {
        $this->prepareWallet();
        $list = $this->bankService->getTransactions($this->walletName)->paginate();
        return api()->success(null, [
            'list' => TransactionResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]]);

    }

    /**
     * Donate
     * @group Admin User > Charity Wallet
     * @param SubmitRemarkRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function donate(SubmitRemarkRequest $request)
    {
        $this->prepareWallet();
        $list = $this->bankService->withdraw($this->walletName,$request->get('amount'),$request->get('remarks'),'Donation',null,true,false);
        return api()->success();

    }

    /**
     * Overall balance chart
     * @group Admin User > Charity Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function overallBalanceChart(ChartTypeRequest $request)
    {
        $this->prepareWallet();
        return api()->success(null, $this->wallet_repository->getWalletOverallBalanceChart($request->get('type'),$this->walletObject->id));
    }


}
