<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\Front\CreateWithdrawRequest;
use Wallets\Http\Requests\IndexWithdrawRequest;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\WithdrawProfit;
use Wallets\Repositories\WithdrawRepository;

class WithdrawRequestController extends Controller
{
    private $withdraw_repository;

    public function __construct(WithdrawRepository $withdrawRepository)
    {
        $this->withdraw_repository = $withdrawRepository;
    }

    /**
     * Get counts
     * @group Public User > Withdraw Requests
     */
    public function counts()
    {
        $counts = User::query()->whereId(auth()->user()->id)
            ->withCount([
                'withdrawRequests AS count_all_requests',
                'withdrawRequests AS count_pending_requests' => function (Builder $query) {
                    $query->where('status', '=', 1);
                },
                'withdrawRequests AS count_rejected_requests' => function (Builder $query) {
                    $query->where('status', '=', 2);
                },
                'withdrawRequests AS count_processed_requests' => function (Builder $query) {
                    $query->where('status', '=', 3);
                },
                'withdrawRequests AS count_postponed_requests' => function (Builder $query) {
                    $query->where('status', '=', 4);
                },
            ])
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_pending_requests' => function (Builder $query) {
                    $query->where('status', '=', 2);
                }]
            )
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_rejected_requests' => function (Builder $query) {
                    $query->where('status', '=', 1);
                }]
            )
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_processed_requests' => function (Builder $query) {
                    $query->where('status', '=', 3);
                }]
            )
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_postponed_requests' => function (Builder $query) {
                    $query->where('status', '=', 4);
                }]
            )->first()->toArray();

        return api()->success(null, [
            'count_all_requests' => $counts['count_all_requests'],
            'count_pending_requests' => $counts['count_pending_requests'],
            'count_rejected_requests' => $counts['count_rejected_requests'],
            'count_processed_requests' => $counts['count_processed_requests'],
            'count_postponed_requests' => $counts['count_postponed_requests'],
            'sum_amount_pending_requests' => empty($counts['sum_amount_pending_requests']) ? 0 : formatCurrencyFormat($counts['sum_amount_pending_requests']),
            'sum_amount_rejected_requests' => empty($counts['sum_amount_rejected_requests']) ? 0 : formatCurrencyFormat($counts['sum_amount_rejected_requests']),
            'sum_amount_processed_requests' => empty($counts['sum_amount_processed_requests']) ? 0 : formatCurrencyFormat($counts['sum_amount_processed_requests']),
            'sum_amount_postponed_requests' => empty($counts['sum_amount_postponed_requests']) ? 0 : formatCurrencyFormat($counts['sum_amount_postponed_requests'],)
        ]);
    }


    /**
     * Withdraw requests
     * @group Public User > Withdraw Requests
     * @queryParam status integer Field to filter withdraw requests. The value must be one of 1 = Under review, 2 = Rejected, 3 = Processed OR 4 = Postponed.
     * @param IndexWithdrawRequest $request
     * @return JsonResponse
     */
    public function index(IndexWithdrawRequest $request)
    {
        try {
            /** @var $withdrawRequests WithdrawProfit */
            $withdrawRequests = auth()->user()->withdrawRequests();
            if($request->has('status'))
                $withdrawRequests->where('status','=', $request->get('status'));

            return api()->success(null, WithdrawProfitResource::collection($withdrawRequests->simplePaginate())->response()->getData());
        } catch (\Throwable $exception) {
            Log::error('EarningWalletController@withdraw_requests => ' . serialize(request()->all()));
            throw $exception;
        }
    }

    /**
     * Withdraw request preview
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     */
    public function create_withdraw_request_preview(CreateWithdrawRequest $request)
    {

        try {
            DB::beginTransaction();

            /**@var $withdraw_request WithdrawProfit */
            $withdraw_request = $this->withdraw_repository->makeWithdrawRequest($request);

            DB::rollBack();
            return api()->success(null, [
                'fee' => formatCurrencyFormat($withdraw_request->fee),
                'pf_amount' => formatCurrencyFormat($withdraw_request->pf_amount),
                'crypto_amount' => $withdraw_request->crypto_amount,
                'wallet_hash' => $withdraw_request->wallet_hash,
                'currency' => $withdraw_request->currency,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('EarningWalletController@create_withdraw_request_preview => ' . serialize($request->all()));
            throw $exception;
        }

    }

    /**
     * Submit withdraw request
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function create_withdraw_request(CreateWithdrawRequest $request)
    {
        try {
            DB::beginTransaction();

            /**@var $withdraw_request WithdrawProfit */
            $withdraw_request = $this->withdraw_repository->makeWithdrawRequest($request);

            DB::commit();
            return api()->success(null, WithdrawProfitResource::make($withdraw_request->refresh()));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('EarningWalletController@create_withdraw_request, User => ' . auth()->user()->id . ' => ' . serialize($request->all()));
            throw $exception;
        }

    }

}
