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
        $uuid = $user->member_id . mt_rand(1000,9999) .  time() ;
        while(Transaction::query()->where('uuid', $uuid)->exists())
            $uuid = $user->member_id . mt_rand(1000,9999) .  time() ;
        $transaction->uuid = $uuid;
    }

}
