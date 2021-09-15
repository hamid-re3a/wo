<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use User\Models\User;
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
        $this->wallet = config('depositWallet');
        $this->bankService->getWallet($this->wallet);

    }

    /**
     * Get counts
     * @group Public User > Earning Wallet
     */
    public function counts()
    {
        $this->prepareEarningWallet();
        $counts = User::query()->where('id', '=', request()->user->id)
            ->withSumQuery(['transactions.amount AS binary_commissions_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Binary Commissions');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS direct_commissions_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function ($subQuery) {
                        $subQuery->where('name', '=', 'Direct Commissions');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS indirect_commissions_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Indirect Commissions');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS roi_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'ROI');
                    });
                }]
            )
            ->first();

        return api()->success(null, [
            'binary_commissions_sum' => $counts->binary_commissions_sum,
            'direct_commissions_sum' => $counts->direct_commissions_sum,
            'indirect_commissions_sum' => $counts->indirect_commissions_sum,
            'roi_sum' => $counts->roi_sum,
        ]);

    }

    /**
     * Get wallet
     * @group Public User > Earning Wallet
     */
    public function index()
    {

        $this->prepareEarningWallet();
        return api()->success(null, WalletResource::make($this->bankService->getWallet($this->wallet)));

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
        return api()->success(null, TransactionResource::collection($data)->response()->getData());

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
        return api()->success(null, TransferResource::collection($data)->response()->getData());

    }
}
