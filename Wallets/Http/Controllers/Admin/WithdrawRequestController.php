<?php


namespace Wallets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Http\Requests\IndexWithdrawRequest;
use Wallets\Http\Requests\Admin\PayoutGroupWithdrawRequest;
use Wallets\Http\Requests\Admin\UpdateWithdrawRequest;
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
     * Get payout wallets balance
     * @group Admin User > Wallets > Withdraw Requests
     */
    public function walletsBalance()
    {
        try {
            $bps_wallet_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                    config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                    config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/');
            if (!$bps_wallet_response->ok())
                throw new \Exception(trans('payment.responses.payment-service.btc-pay-server-error'));

            $bps_wallet_balance = $bps_wallet_response->json()['confirmedBalance'];

            //TODO Janex wallet

            return api()->success(null, [
                'btc_wallet' => $bps_wallet_balance
            ]);

        } catch (\Throwable $exception) {
            Log::error('Wallets\Http\Controllers\Admin\WithdrawRequestController@walletBalance => ' . $exception->getMessage());
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Get counts
     * @group Admin User > Wallets > Withdraw Requests
     */
    public function counts()
    {
        $model = app(WithdrawProfit::class);

        return api()->success(null, [
            'count_all_requests' => $model->count(),
            'count_pending_requests' => $model->where('status', '1')->count(),
            'count_rejected_requests' => $model->where('status', '2')->count(),
            'count_processed_requests' => $model->where('status', '3')->count(),
            'count_postponed_requests' => $model->where('status', '4')->count(),
            'sum_amount_pending_requests' => $model->where('status', '1')->sum('pf_amount'),
            'sum_amount_rejected_requests' => $model->where('status', '2')->sum('pf_amount'),
            'sum_amount_processed_requests' => $model->where('status', '3')->sum('pf_amount'),
            'sum_amount_postponed_requests' => $model->where('status', '4')->sum('pf_amount'),
        ]);
    }

    /**
     * Withdraw requests list
     * @group Admin User > Wallets > Withdraw Requests
     * @queryParam status integer Field to filter withdraw requests. The value must be one of 1 = Under review, 2 = Rejected, 3 = Processed OR 4 = Postponed.
     * @param IndexWithdrawRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function index(IndexWithdrawRequest $request)
    {
        try {
            $withdrawRequests = WithdrawProfit::query();
            if ($request->has('statuses'))
                $withdrawRequests->whereIn('status', $request->get('statuses'));

            $list = $withdrawRequests->paginate();
            return api()->success(null, [
                'list' => WithdrawProfitResource::collection($list),
                'pagination' => [
                    'total' => $list->total(),
                    'per_page' => $list->perPage()
                ]
            ]);
        } catch (\Throwable $exception) {
            Log::error('Admin/WithdrawRequestController@index => ' . serialize(request()->all()));
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Update withdraw request
     * @group Admin User > Wallets > Withdraw Requests
     * @param UpdateWithdrawRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(UpdateWithdrawRequest $request)
    {
        try {
            DB::beginTransaction();

            $withdraw_requests = WithdrawProfit::query()->whereIn('uuid', $request->get('ids'))->get();
            $this->withdraw_repository->update($request->validated(), $withdraw_requests);

            DB::commit();
            return api()->success();
        } catch (\Throwable $exception) {
            DB::rollback();
            Log::error('Admin/WithdrawRequestController@update => ' . serialize($request->all()));
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Pending amount vs time chart
     * @group Admin User > Wallets > Withdraw Requests
     * @param ChartTypeRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function pendingAmountVsTimeChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->withdraw_repository->getPendingAmountVsTimeChart($request->get('type')));
    }

    /**
     * Paid amount vs time chart
     * @group Admin User > Wallets > Withdraw Requests
     * @param ChartTypeRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function paidAmountVsTimeChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->withdraw_repository->getPaidAmountVsTimeChart($request->get('type')));
    }


}
