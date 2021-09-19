<?php


namespace Wallets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\Admin\UpdateWithdrawRequest;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\Transaction;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class WithdrawRequestController extends Controller
{

//    /**
//     * Withdraws list
//     * @group Admin User > Wallets > Withdraw Requests
//     */
    public function index()
    {
        try {
            return api()->success(null,WithdrawProfitResource::collection(WithdrawProfit::query()->simplePaginate())->response()->getData());
        } catch (\Throwable $exception) {
            Log::error('Admin/WithdrawRequestController@index => ' . serialize(request()->all()));
            return api()->error(null,[
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
            $withdraw_request = WithdrawProfit::query()->where('uuid','=',$request->get('id'))->first();

            $refund_transaction = null;
            if($request->get('status') == 2) { //Withdrawal request has been rejected, So we have to refund amount to user's earning wallet
                //Withdraw Admin deposit wallet
                $bank_service = new BankService(User::query()->first());
                $w = $bank_service->withdraw(config('depositWallet'),abs($withdraw_request->withdrawTransaction->amountFloat),[
                    'description' => 'Refund withdrawal request',
                    'withdrawal_request_id' => $withdraw_request->id
                ],'Withdrawal refund');

                $bank_service = new BankService($withdraw_request->user()->first());
                $refund_transaction = $bank_service->deposit(config('earningWallet'),abs($withdraw_request->withdrawTransaction->amountFloat),[
                    'description' => 'Withdrawal request rejected',
                    'withdrawal_request_id' => $withdraw_request->id,
                ],true,'Withdrawal refund');



            }

            $withdraw_request->update([
                'status' => $request->get('status'),
                'rejection_reason' => $request->has('rejection_reason') ? $request->get('rejection_reason') : $withdraw_request->rejection_reason,
                'network_hash' => $request->has('network_hash') ? $request->get('network_hash') : $withdraw_request->network_hash,
                'actor_id' => auth()->user()->id,
                'refund_transaction_id' => $refund_transaction instanceof Transaction ? $refund_transaction->id : $withdraw_request->refund_transaction_id
            ]);

            $withdraw_request->refresh();
            DB::commit();
            return api()->success(null,WithdrawProfitResource::make($withdraw_request));
        } catch (\Throwable $exception) {
            DB::rollback();
            Log::error('Admin/WithdrawRequestController@update => ' . serialize($request->all()));
            return api()->error(null,[
                'subject' => $exception->getMessage()
            ]);
        }
    }

}
