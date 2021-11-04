<?php


namespace Wallets\Observers;

use Wallets\Jobs\UrgentEmailJob;
use Wallets\Mail\EarningWallet\WithdrawProfitRequestedUpdated;
use Wallets\Mail\EarningWallet\WithdrawProfitRequestedSubmitted;
use Wallets\Models\WithdrawProfit;

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
    }
}
