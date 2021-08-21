<?php


namespace Giftcode\Observers;

use Giftcode\Jobs\UrgentEmailJob;
use Giftcode\Mail\User\GiftcodeCreatedEmail;
use Giftcode\Models\Giftcode;

class GiftcodeObserver
{
    public function created(Giftcode $giftcode)
    {
        UrgentEmailJob::dispatch(new GiftcodeCreatedEmail($giftcode->creator,$giftcode),$giftcode->creator->email);
    }
}
