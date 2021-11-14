<?php


namespace Wallets\Observers;

use User\Models\User;
use Wallets\Models\Transfer;

class TransferObserver
{
    public function creating(Transfer $transfer)
    {
        //UUID field
        /**@var $user User*/
        $user = $transfer->from;
        $uuid = $user->member_id .  mt_rand(1000,9999) .time();
        while(Transfer::query()->where('uuid', $uuid)->exists())
            $uuid = $user->member_id . mt_rand(1000,9999) . time();
        $transfer->uuid = $uuid;
    }

}
