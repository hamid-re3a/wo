<?php


namespace Wallets\Observers;

use User\Models\User;
use Wallets\Models\Transaction;

class TransactionObserver
{
    public function creating(Transaction $transaction)
    {
        //UUID field
        /**@var $user User*/
        $user = $transaction->payable;
        $uuid = $user->member_id .  time() . mt_rand(100,999);
        while(Transaction::query()->where('uuid', $uuid)->exists())
            $uuid = $user->member_id .  time() . mt_rand(100,999);
        $transaction->uuid = $uuid;
    }

}
