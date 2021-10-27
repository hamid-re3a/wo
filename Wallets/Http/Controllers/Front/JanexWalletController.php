<?php

namespace Wallets\Http\Controllers\Front;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use User\Models\User;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Repositories\WalletRepository;
use Wallets\Services\BankService;

class JanexWalletController extends Controller
{
    /**@var $bankService BankService*/
    private $bankService;
    private $walletName;
    /**@var $walletObject Wallet*/
    private $walletObject;
    /**@var $user User*/
    private $user;
    private $wallet_repository;

    public function __construct(WalletRepository $wallet_repository)
    {
        if(auth()->check())
            $this->prepareJanexWallet();
        $this->wallet_repository = $wallet_repository;
    }

    private function prepareJanexWallet()
    {
        /**@var $user User*/
        $this->user = auth()->user();

        $this->bankService = new BankService($this->user);
        $this->walletName = WALLET_NAME_JANEX_WALLET;
        $this->walletObject = $this->bankService->getWallet($this->walletName);

    }

    /**
     * Get wallet
     * @group Public User > Janex Wallet
     */
    public function index()
    {

        return api()->success(null, EarningWalletResource::make($this->walletObject));

    }

    /**
     * Get transactions
     * @group Public User > Janex Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {

        $list = $this->bankService->getTransactions($this->walletName)->paginate();
        return api()->success(null, [
            'list' => TransactionResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

    /**
     * Get transfers
     * @group Public User > Janex Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $list = $this->bankService->getTransfers($this->walletName)->paginate();
        return api()->success(null, [
            'list' => TransferResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

    /**
     * Overall balance chart
     * @group Public User > Janex Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function overallBalanceChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->wallet_repository->getWalletOverallBalanceChart($request->get('type'),$this->walletObject->id));
    }

    /**
     * Commissions chart
     * @group Public User > Janex Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function commissionsChart(ChartTypeRequest $request)
    {
        $commissions = [
//            'Binary',
//            'Direct Sale',
//            'Indirect Sale',
            'Trading Profit',
//            'Residual Bonus',
//            'Trainer Bonus',
        ];

        return api()->success(null,$this->wallet_repository->getCommissionsChart($request->get('type'),$commissions,$this->walletObject->id));
    }

}
