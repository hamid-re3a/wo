<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\Front\CreateWithdrawRequest;
use Wallets\Http\Requests\IndexWithdrawRequest;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\WithdrawProfit;
use Wallets\Repositories\WithdrawRepository;
use Wallets\Services\WithdrawResolver;

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
            ->first()->toArray();

        $sums = User::query()->whereId(auth()->user()->id)
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_pending_requests' => function (Builder $query) {
                    $query->where('status', '=', 1);
                }]
            )
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_rejected_requests' => function (Builder $query) {
                    $query->where('status', '=', 2);
                }]
            )
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_processed_requests' => function (Builder $query) {
                    $query->where('status', '=', 3);
                }]
            )
            ->withSumQuery(['withdrawRequests.pf_amount AS sum_amount_postponed_requests' => function (Builder $query) {
                    $query->where('status', '=', 4);
                }]
            )
            ->first()->toArray();

        return api()->success(null, [
            'count_all_requests' => isset($counts['count_all_requests']) ? $counts['count_all_requests'] : 0,
            'count_pending_requests' => isset($counts['count_pending_requests']) ? $counts['count_pending_requests'] : 0,
            'count_rejected_requests' => isset($counts['count_rejected_requests']) ? $counts['count_rejected_requests'] : 0,
            'count_processed_requests' => isset($counts['count_processed_requests']) ? $counts['count_processed_requests'] : 0,
            'count_postponed_requests' => isset($counts['count_postponed_requests']) ? $counts['count_postponed_requests'] : 0,
            'sum_amount_pending_requests' => !empty($sums['sum_amount_pending_requests']) ? $sums['sum_amount_pending_requests'] : 0,
            'sum_amount_rejected_requests' => !empty($sums['sum_amount_rejected_requests']) ? $sums['sum_amount_rejected_requests'] : 0,
            'sum_amount_processed_requests' => !empty($sums['sum_amount_processed_requests']) ? $sums['sum_amount_processed_requests'] : 0,
            'sum_amount_postponed_requests' => !empty($sums['sum_amount_postponed_requests']) ? $sums['sum_amount_postponed_requests'] : 0,
        ]);
    }

    /**
     * Withdraw requests
     * @group Public User > Withdraw Requests
     * @queryParam status integer Field to filter withdraw requests. The value must be one of 1 = Under review, 2 = Rejected, 3 = Processed OR 4 = Postponed.
     * @param IndexWithdrawRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function index(IndexWithdrawRequest $request)
    {
        try {
            /**
             * @var $withdrawRequests WithdrawProfit
             * @var $user User
             */
            $user = auth()->user();
            $withdrawRequests = $user->withdrawRequests();
            if ($request->has('statuses'))
                $withdrawRequests->whereIn('statuses', $request->get('statuses'));

            $list = $withdrawRequests->paginate();
            return api()->success(null, [
                'list' => WithdrawProfitResource::collection($list),
                'pagination' => [
                    'total' => $list->total(),
                    'per_page' => $list->perPage()
                ]
            ]);
        } catch (\Throwable $exception) {
            Log::error('WithdrawRequestController@withdraw_requests => ' . serialize(request()->all()));
            return api()->error(null,[
                'subject' => $exception->getMessage()
            ],$exception->getCode());
        }
    }

    /**
     * Withdraw request preview
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create_withdraw_request_preview(CreateWithdrawRequest $request)
    {

        try {

            $withdraw_request = $this->createRequest($request->validated(), true);

            return api()->success(null, [
                'fee' => $withdraw_request->fee,
                'pf_amount' => $withdraw_request->pf_amount,
                'crypto_amount' => $withdraw_request->crypto_amount,
                'wallet_hash' => $withdraw_request->wallet_hash,
                'currency' => $withdraw_request->currency,
            ]);

        } catch (\Throwable $exception) {
            Log::error('WithdrawRequestController@create_withdraw_request_preview => ' . serialize($request->all()));
            return api()->error(null,[
                'subject' => $exception->getMessage()
            ],$exception->getCode());
        }

    }

    /**
     * Submit withdraw request
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create_withdraw_request(CreateWithdrawRequest $request)
    {
        try {
            DB::beginTransaction();
            $withdraw_request = $this->createRequest($request->validated());

            DB::commit();
            return api()->success(null, WithdrawProfitResource::make($withdraw_request->refresh()));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('WithdrawRequestController@create_withdraw_request, User => ' . auth()->user()->id . ' => ' . serialize($request->all()));
            return api()->error(null,[
                'subject' => $exception->getMessage()
            ],$exception->getCode());
        }

    }

    private function createRequest(array $request, $simulate = false): WithdrawProfit
    {
        try {
            DB::beginTransaction();
            $withdraw_request = $this->withdraw_repository->makeWithdrawRequest($request);

            $withdraw_resolver = new WithdrawResolver($withdraw_request);
            list($flag, $response) = $withdraw_resolver->verifyWithdrawRequest();

            if (!$flag) {
                DB::rollBack();
                Throw new \Exception($response,406);
            }

            if ($simulate)
                DB::rollBack();
            else
                DB::commit();

            return $withdraw_request;

        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

}
