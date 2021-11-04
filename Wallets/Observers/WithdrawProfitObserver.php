<?php


namespace Wallets\Observers;

use Wallets\Jobs\UrgentEmailJob;
use Wallets\Mail\EarningWallet\WithdrawProfitRequestedUpdated;
use Wallets\Mail\EarningWallet\WithdrawProfitRequestedSubmitted;
use Wallets\Models\WithdrawProfit;
use Wallets\Models\WithdrawProfitHistory;

class WithdrawProfitObserver
{
    public function creating(WithdrawProfit $withdrawProfit)
    {
        $uuid = $withdrawProfit->user->member_id . mt_rand(1000,9999) . time();
        while(WithdrawProfit::query()->where('uuid',$uuid)->exists())
            $uuid = $withdrawProfit->user->member_id . mt_rand(1000,9999) . time();
        $withdrawProfit->uuid = $uuid;

    }

    public function created(WithdrawProfit $withdrawProfit)
    {
        UrgentEmailJob::dispatch(new WithdrawProfitRequestedSubmitted($withdrawProfit), $withdrawProfit->user->email);
    }

    public function updating(WithdrawProfit $withdrawProfit)
    {
        if(auth()->check()) { //Check WithdrawRequest is updating with QUEUE or logged-in user
            $withdrawProfit->actor_id = auth()->user()->id;
            if(request()->has('act_reason'))
                $withdrawProfit->act_reason = request()->get('act_reason');
        }
    }


    public function updated(WithdrawProfit $withdrawProfit)
    {
        UrgentEmailJob::dispatch(new WithdrawProfitRequestedUpdated($withdrawProfit), $withdrawProfit->user->email);
        $this->createHistory($withdrawProfit);
    }

    /**
     * @param WithdrawProfit $withdrawProfit
     */
    private function createHistory(WithdrawProfit $withdrawProfit): void
    {
        WithdrawProfitHistory::query()->insert([
            'withdraw_profit_id' => $withdrawProfit->id,
            'uuid' => $withdrawProfit->uuid,
            'wallet_hash' => $withdrawProfit->wallet_hash,
            'user_id' => $withdrawProfit->user_id,
            'withdraw_transaction_id' => $withdrawProfit->withdraw_transaction_id,
            'refund_transaction_id' => $withdrawProfit->refund_transaction_id,
            'network_transaction_id' => $withdrawProfit->network_transaction_id,
            'status' => $withdrawProfit->status,
            'payout_service' => $withdrawProfit->payout_service,
            'currency' => $withdrawProfit->currency,
            'pf_amount' => $withdrawProfit->pf_amount,
            'crypto_amount' => $withdrawProfit->crypto_amount,
            'crypto_rate' => $withdrawProfit->crypto_rate,
            'fee' => $withdrawProfit->fee,
            'actor_id' => $withdrawProfit->actor_id,
            'act_reason' => $withdrawProfit->act_reason,
            'postponed_to' => $withdrawProfit->postponed_to,
        ]);
    }
}
