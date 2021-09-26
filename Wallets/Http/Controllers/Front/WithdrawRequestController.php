<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Wallets\Http\Requests\Front\CreateWithdrawRequest;
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
     * Withdraw requests
     * @group Public User > Withdraw Requests
     */
    public function index()
    {
        try {
            return api()->success(null, WithdrawProfitResource::collection(auth()->user()->withdrawRequests()->simplePaginate())->response()->getData());
        } catch (\Throwable $exception) {
            Log::error('EarningWalletController@withdraw_requests => ' . serialize(request()->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
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

            /**@var $withdraw_request WithdrawProfit*/
            $withdraw_request = $this->withdraw_repository->makeWithdrawRequest($request);

            DB::rollBack();
            return api()->success(null, [
                'pf_amount' => $withdraw_request->pf_amount,
                'crypto_amount' => $withdraw_request->crypto_amount,
                'wallet_hash' => $withdraw_request->wallet_hash,
                'currency' => $withdraw_request->currency,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('EarningWalletController@create_withdraw_request_preview => ' . serialize($request->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
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

            /**@var $withdraw_request WithdrawProfit*/
            $withdraw_request = $this->withdraw_repository->makeWithdrawRequest($request);

            DB::commit();
            return api()->success(null, WithdrawProfitResource::make($withdraw_request->refresh()));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('EarningWalletController@create_withdraw_request => ' . serialize($request->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }

    }

}
