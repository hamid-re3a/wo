<?php


namespace Wallets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\Admin\PayoutGroupWithdrawRequest;
use Wallets\Http\Requests\Admin\UpdateWithdrawRequest;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Jobs\ProcessBTCTransactionsJob;
use Wallets\Models\Transaction;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class WithdrawRequestController extends Controller
{

    /**
     * Withdraws list
     * @group Admin User > Wallets > Withdraw Requests
     */
    public function index()
    {
        try {
            return api()->success(null, WithdrawProfitResource::collection(WithdrawProfit::query()->simplePaginate())->response()->getData());
        } catch (\Throwable $exception) {
            Log::error('Admin/WithdrawRequestController@index => ' . serialize(request()->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Update withdraw request
     * @group Admin User > Wallets > Withdraw Requests
     * @param UpdateWithdrawRequest $request
     * @return JsonResponse
     */
    public function update(UpdateWithdrawRequest $request)
    {
        try {
            DB::beginTransaction();
            $withdraw_request = WithdrawProfit::query()->where('uuid', '=', $request->get('id'))->first();

            if($request->get('status') == 3) {
                $this->checkBPSWalletBalance($withdraw_request->crypto_amount);

                ProcessBTCTransactionsJob::dispatch([
                    [
                        'destination' => $withdraw_request->wallet_hash,
                        'amount' => $withdraw_request->crypto_amount
                    ]
                ], [$withdraw_request->id]);
            } else if ($request->get('status') == 2) { //Withdrawal request has been rejected, So we have to refund amount to user's earning wallet
                //Withdraw Admin deposit wallet
                $bank_service = new BankService(User::query()->first());
                $bank_service->withdraw(config('depositWallet'), abs($withdraw_request->withdrawTransaction->amountFloat), [
                    'description' => 'Refund withdrawal request',
                    'withdrawal_request_id' => $withdraw_request->id
                ], 'Withdrawal refund');

                $bank_service = new BankService($withdraw_request->user()->first());
                $refund_transaction = $bank_service->deposit(config('earningWallet'), abs($withdraw_request->withdrawTransaction->amountFloat), [
                    'description' => 'Withdrawal request rejected',
                    'withdrawal_request_id' => $withdraw_request->id,
                ], true, 'Withdrawal refund');


                $withdraw_request->update([
                    'status' => $request->get('status'),
                    'rejection_reason' => $request->has('rejection_reason') ? $request->get('rejection_reason') : $withdraw_request->rejection_reason,
                    'actor_id' => auth()->user()->id,
                    'refund_transaction_id' => $refund_transaction instanceof Transaction ? $refund_transaction->id : $withdraw_request->refund_transaction_id
                ]);

            }


            $withdraw_request->refresh();
            DB::commit();
            return api()->success(null, WithdrawProfitResource::make($withdraw_request));
        } catch (\Throwable $exception) {
            DB::rollback();
            Log::error('Admin/WithdrawRequestController@update => ' . serialize($request->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }
    }


    /**
     * Payout group withdraw request
     * @group Admin User > Wallets > Withdraw Requests
     * @param PayoutGroupWithdrawRequest $request
     * @return JsonResponse
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
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
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
