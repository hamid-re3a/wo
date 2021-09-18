<?php


namespace Wallets\Observers;

use Ramsey\Uuid\Uuid;
use Wallets\Models\WithdrawProfit;

class WithdrawProfitObserver
{
    public function creating(WithdrawProfit $withdrawProfit)
    {
        //UUID field
        $uuid = Uuid::uuid4()->toString();
        while ($withdrawProfit->where('uuid', $uuid)->first())
            $uuid = Uuid::uuid4()->toString();

        $withdrawProfit->uuid = $uuid;

    }
}
