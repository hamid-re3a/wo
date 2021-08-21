<?php


namespace Giftcode\Observers;

use Giftcode\Jobs\UrgentEmailJob;
use Giftcode\Mail\User\GiftcodeCreatedEmail;
use Giftcode\Models\Giftcode;
use Ramsey\Uuid\Uuid;

class GiftcodeObserver
{
    public function creating(Giftcode $giftcode)
    {

        list($code,$expirationDate) = $giftcode->generateCode();
        $giftcode->code = $code;
        $giftcode->expiration_date = $expirationDate;

        $uuid = Uuid::uuid4()->toString();
        while($giftcode->where('uuid', $uuid)->first())
            $uuid = Uuid::uuid4()->toString();
        $giftcode->uuid = $uuid ;

    }
    public function created(Giftcode $giftcode)
    {
        UrgentEmailJob::dispatch(new GiftcodeCreatedEmail($giftcode->creator,$giftcode),$giftcode->creator->email);
    }
}
