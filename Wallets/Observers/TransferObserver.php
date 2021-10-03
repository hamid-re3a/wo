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
        $uuid = $user->member_id .  time() . mt_rand(100,999);
        while(Transfer::query()->where('uuid', $uuid)->exists())
            $uuid = $user->member_id .  time() . mt_rand(100,999);
        $transfer->uuid = $uuid;
    }

}
