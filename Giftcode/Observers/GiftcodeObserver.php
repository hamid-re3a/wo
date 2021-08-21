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

        //Giftcode code and expiration date
        list($code, $expirationDate) = $giftcode->generateCode();
        $giftcode->code = $code;
        $giftcode->expiration_date = $expirationDate;

        //Giftcode UUID field
        $uuid = Uuid::uuid4()->toString();
        while ($giftcode->where('uuid', $uuid)->first())
            $uuid = Uuid::uuid4()->toString();
        $giftcode->uuid = $uuid;

        //Giftcode costs

        $giftcode->packages_cost_in_usd = $giftcode->package()->exists() ? (float)$giftcode->package->price : null;

        if (giftcodeGetSetting('include_registration_fee') AND request()->has('include_registration_fee') AND request()->get('include_registration_fee'))
            $giftcode->registration_fee_in_usd = (float)giftcodeGetSetting('registration_fee');

        $giftcode->total_cost_in_usd = $giftcode->packages_cost_in_usd + $giftcode->registration_fee_in_usd;

        if(empty($giftcode->user_id))
            $giftcode->user_id = request()->user->id;

        if(empty($giftcode->package_id) AND request()->has('package_id'))
            $giftcode->package_id = request()->get('package_id');

    }

    public function created(Giftcode $giftcode)
    {
        UrgentEmailJob::dispatch(new GiftcodeCreatedEmail($giftcode->creator, $giftcode), $giftcode->creator->email);
    }
}
