<?php

namespace Wallets\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use User\Models\User;
use Wallets\Http\Requests\Admin\GetUserWalletBalanceRequest;
use Wallets\Http\Requests\Admin\GetUserTransfersRequest;
use Wallets\Http\Requests\Admin\GetUserTransactionsRequest;
use Wallets\Http\Requests\Admin\UserIdRequest;
use Wallets\Http\Requests\Admin\GetTransactionsRequest;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Http\Resources\Admin\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Models\Transaction;
use Wallets\Repositories\WalletRepository;
use Wallets\Services\BankService;

class DashboardController extends Controller
{
    //TODO Refactor whole controller :|
    private $wallet_repository;

    public function __construct(WalletRepository $wallet_repository)
    {
        $this->wallet_repository = $wallet_repository;
    }

    /**
     * Get all transactions
     * @group Admin User > Wallets
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function overallBalanceChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->wallet_repository->getWalletOverallBalance($request->get('type')));
    }

    /**
     * Get all transactions
     * @group Admin User > Wallets
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function investmentsChart(ChartTypeRequest $request)
    {
        return api()->success(null,$this->wallet_repository->getWalletInvestmentChart($request->get('type')));
    }

}
