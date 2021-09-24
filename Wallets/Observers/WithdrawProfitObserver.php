<?php


namespace Wallets\Observers;

use Ramsey\Uuid\Uuid;
use Wallets\Jobs\UrgentEmailJob;
use Wallets\Mail\Admin\WithdrawalRequests\WithdrawProfitRequestedUpdated;
use Wallets\Mail\EarningWallet\WithdrawProfitRequestedSubmitted;
use Wallets\Models\WithdrawProfit;

class WithdrawProfitObserver
{
    public function creating(WithdrawProfit $withdrawProfit)
    {
        //UUID field
        $withdrawProfit->uuid = $withdrawProfit->user->member_id . mt_rand(100,999) . time();

    }

    public function created(WithdrawProfit $withdrawProfit)
    {
        UrgentEmailJob::dispatch(new WithdrawProfitRequestedSubmitted($withdrawProfit), $withdrawProfit->user->email);
    }


    public function updated(WithdrawProfit $withdrawProfit)
    {
        UrgentEmailJob::dispatch(new WithdrawProfitRequestedUpdated($withdrawProfit), $withdrawProfit->user->email);
    }
}
