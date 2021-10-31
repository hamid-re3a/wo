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
        $withdrawProfit = $this->calculateFee($withdrawProfit);
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
        $withdrawProfit->actor_id = auth()->user()->id;
        if(request()->has('act_reason'))
            $withdrawProfit->act_reason = request()->get('act_reason');
    }


    public function updated(WithdrawProfit $withdrawProfit)
    {
        UrgentEmailJob::dispatch(new WithdrawProfitRequestedUpdated($withdrawProfit), $withdrawProfit->user->email);
    }

    private function calculateFee(WithdrawProfit $withdrawProfit)
    {
        switch ($withdrawProfit->currency) {
            case 'BTC' :
                $fix_or_percentage = getWalletSetting('payout_btc_fee_fixed_or_percentage');
                $fee = getWalletSetting('payout_btc_fee');
                break;
            case 'Janex' :
                $fix_or_percentage = getWalletSetting('payout_janex_fee_fixed_or_percentage');
                $fee = getWalletSetting('payout_janex_fee');
                break;
            default:
                $fix_or_percentage = 'fixed';
                $fee = 0;
                break;
        }
        $fee = $fix_or_percentage == 'fixed' ? $fee : ($withdrawProfit->pf_amount * $fee / 100);

        $withdrawProfit->pf_amount = $withdrawProfit->pf_amount - $fee;
        $withdrawProfit->crypto_amount = $withdrawProfit->pf_amount / $withdrawProfit->crypto_rate;
        $withdrawProfit->fee = $fee;

        return $withdrawProfit;
    }
}
