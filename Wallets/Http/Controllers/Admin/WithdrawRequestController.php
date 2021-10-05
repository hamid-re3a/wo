<?php


namespace Wallets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Payments\Services\Processors\PayoutProcessor;
use User\Models\User;
use Wallets\Http\Requests\IndexWithdrawRequest;
use Wallets\Http\Requests\Admin\PayoutGroupWithdrawRequest;
use Wallets\Http\Requests\Admin\UpdateWithdrawRequest;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\Transaction;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class WithdrawRequestController extends Controller
{

    private $payout_processor;

    public function __construct(PayoutProcessor $payout_processor)
    {
        $this->payout_processor = $payout_processor;
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
            if(!$bps_wallet_response->ok())
                throw new \Exception(trans('payment.responses.payment-service.btc-pay-server-error'));

            $bps_wallet_balance = $bps_wallet_response->json()['confirmedBalance'];

            //TODO Janex wallet

            return api()->success(null,[
                'btc_wallet' => $bps_wallet_balance
            ]);

        } catch (\Throwable $exception) {
            Log::error('Wallets\Http\Controllers\Admin\WithdrawRequestController@walletBalance => ' . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * Get counts
     * @group Admin User > Wallets > Withdraw Requests
     */
    public function counts()
    {
        $counts = User::query()
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
            ])->where('id','!=',1)->first()->toArray();

        $sums = User::query()
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
            )->where('id','!=',1)->first()->toArray();

        return api()->success(null, [
            'count_all_requests' => isset($counts['count_all_requests']) ? $counts['count_all_requests'] : 0 ,
            'count_pending_requests' => isset($counts['count_pending_requests']) ? $counts['count_pending_requests'] : 0 ,
            'count_rejected_requests' => isset($counts['count_rejected_requests']) ? $counts['count_rejected_requests'] : 0 ,
            'count_processed_requests' => isset($counts['count_processed_requests']) ? $counts['count_processed_requests'] : 0 ,
            'count_postponed_requests' => isset($counts['count_postponed_requests']) ? $counts['count_postponed_requests'] : 0 ,
            'sum_amount_pending_requests' => isset($sums['sum_amount_pending_requests']) AND !empty($sums['sum_amount_pending_requests']) ? $sums['sum_amount_pending_requests'] : 0 ,
            'sum_amount_rejected_requests' => isset($sums['sum_amount_rejected_requests']) AND !empty($sums['sum_amount_rejected_requests']) ? $sums['sum_amount_rejected_requests'] : 0 ,
            'sum_amount_processed_requests' => isset($sums['sum_amount_processed_requests']) AND !empty($sums['sum_amount_processed_requests']) ? $sums['sum_amount_processed_requests'] : 0 ,
            'sum_amount_postponed_requests' => isset($sums['sum_amount_postponed_requests']) AND !empty($sums['sum_amount_postponed_requests']) ? $sums['sum_amount_postponed_requests'] : 0 ,
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
            if($request->has('status'))
                $withdrawRequests->where('status','=',$request->get('status'));

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
            throw $exception;
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
            /**
             * @var $withdraw_request WithdrawProfit
             * @var $user User
             */
            $withdraw_request = WithdrawProfit::query()->where('uuid', '=', $request->get('id'))->first();

            if($request->get('status') == 3) {
                $this->checkBPSWalletBalance($withdraw_request->crypto_amount);

                $this->payout_processor->pay($withdraw_request->payout_service,[
                    [
                        'destination' => $withdraw_request->wallet_hash,
                        'amount' => $withdraw_request->crypto_amount
                    ]
                ], [$withdraw_request->id]);

            } else if ($request->get('status') == 2) { //Withdrawal request has been rejected, So we have to refund amount to user's earning wallet
                //Withdraw Admin deposit wallet
                $bank_service = new BankService(User::query()->first());
                $bank_service->withdraw(config('depositWallet'), $withdraw_request->pf_amount, [
                    'description' => 'Refund withdrawal request',
                    'withdrawal_request_id' => $withdraw_request->id
                ], 'Withdrawal refund');

                //Deposit user wallet
                $user = $withdraw_request->user()->first();
                $bank_service = new BankService($user);
                $refund_transaction = $bank_service->deposit(config('earningWallet'), $withdraw_request->pf_amount, [
                    'description' => 'Withdrawal request rejected',
                    'withdrawal_request_id' => $withdraw_request->id,
                ], true, 'Withdrawal refund');


                $withdraw_request->update([
                    'status' => $request->get('status'),
                    'rejection_reason' => $request->has('rejection_reason') ? $request->get('rejection_reason') : $withdraw_request->rejection_reason,
                    'actor_id' => auth()->user()->id,
                    'refund_transaction_id' => $refund_transaction instanceof Transaction ? $refund_transaction->id : $withdraw_request->refund_transaction_id
                ]);

            } else if ($request->get('status') == 4) {
                $withdraw_request->update($request->validated());
            }


            $withdraw_request->refresh();
            DB::commit();
            return api()->success(null, WithdrawProfitResource::make($withdraw_request));
        } catch (\Throwable $exception) {
            DB::rollback();
            Log::error('Admin/WithdrawRequestController@update => ' . serialize($request->all()));
            throw $exception;
        }
    }


    /**
     * Payout group withdraw requests
     * @group Admin User > Wallets > Withdraw Requests
     * @param PayoutGroupWithdrawRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function payout_group(PayoutGroupWithdrawRequest $request)
    {

        try {
            DB::beginTransaction();
            $withdraw_requests = WithdrawProfit::query()->where('status', '=', 1)->whereIn('uuid', $request->get('ids'));
            $this->checkBPSWalletBalance($withdraw_requests->sum('crypto_amount'));
            $withdraw_requests->chunkById(50, function ($chunked){
                $wallets = [];
                $ids = [];
                foreach ($chunked AS $withdraw_request) {
                    /** @var $withdraw_request WithdrawProfit*/
                    $ids[] = $withdraw_request->id;
                    $wallets[] = [
                        'destination' => $withdraw_request->wallet_hash,
                        'amount' => $withdraw_request->crypto_amount
                    ];
                }
                if (count($wallets) > 0 AND count($ids) > 0 AND (count($wallets) == count($ids)))
                    ProcessBTCTransactionsJob::dispatch($wallets, $ids);

                unset($chunked);
                unset($withdraw_request);
                unset($wallets);
                unset($ids);
            });


            DB::commit();
            return api()->success(null, null);
        } catch (\Throwable $exception) {
            DB::rollback();
            Log::error('Admin/WithdrawRequestController@payout_group => ' . serialize($request->all()));
            throw $exception;
        }
    }

    private function checkBPSWalletBalance($amount)
    {
        $wallet_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
            ->get(
                config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/');


        if ($wallet_response->ok() AND is_array($wallet_response->json()) AND isset($wallet_response->json()['confirmedBalance'])) {

            $wallet_balance = $wallet_response->json()['confirmedBalance'];


            if($amount > $wallet_balance)
                throw new \Exception(trans('wallet.withdraw-profit-request.insufficient-bpb-wallet-balance',[
                    'amount' => ($amount - $wallet_balance)
                ]));

        } else {
            throw new \Exception(trans('wallet.withdraw-profit-request.check-bps-or-blockchain.com-server', [
                'server' => 'BTCPayServer'
            ]));
        }
    }

}
