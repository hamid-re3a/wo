<?php

namespace Wallets\Http\Controllers\Admin;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Models\Transaction;
use Wallets\Repositories\WalletRepository;

class DashboardController extends Controller
{
    //TODO Refactor whole controller :|
    private $wallet_repository;

    public function __construct(WalletRepository $wallet_repository)
    {
        $this->wallet_repository = $wallet_repository;
    }

    /**
     * Count for all deposit wallets
     * @group Admin User > Wallets
     */
    public function count_deposit_wallets()
    {
        $total_transferred = Transaction::query()->where('type', '=', 'withdraw')
            ->where('wallet_id','!=',1)
            ->whereHas('metaData', function (Builder $subQuery) {
                $subQuery->where('name', '=', 'Funds transferred');
            })->sum('amount');

        $current_balance = Wallet::query()->sum('balance');

        return api()->success(null,[
            'total_transferred' => $total_transferred / 100,
            'current_balance' => $current_balance / 100
        ]);
    }

    /**
     * Commissions sum
     * @group Admin User > Wallets
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function commissionsSum(ChartTypeRequest $request)
    {
        $commissions = [
            'Binary Commissions',
            'Direct Commissions',
            'Indirect Commissions',
            'ROI',
        ];

        return api()->success(null, $this->wallet_repository->getTypesSum(null,$commissions));
    }

    /**
     * Balance vs time chart
     * @group Admin User > Wallets
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function overallBalanceChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->wallet_repository->getWalletOverallBalance($request->get('type')));
    }

    /**
     * Investment vs time chart
     * @group Admin User > Wallets
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function investmentsChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->wallet_repository->getWalletInvestmentChart($request->get('type')));
    }

    /**
     * Investment vs time chart
     * @group Admin User > Wallets
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function commissionsChart(ChartTypeRequest $request)
    {
        $commissions = [
            'Binary Commissions',
            'Direct Commissions',
            'Indirect Commissions',
            'ROI',
        ];

        return api()->success(null, $this->wallet_repository->getCommissionsChart($request->get('type'),$commissions));
    }

}
