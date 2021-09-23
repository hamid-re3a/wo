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
        $transaction->uuid = $user->member_id . mt_rand(1,100) . time();
    }

}
