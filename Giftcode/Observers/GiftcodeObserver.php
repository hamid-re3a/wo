<?php


namespace Giftcode\Observers;

use Exception;
use Giftcode\Models\Giftcode;
use Illuminate\Support\Facades\Log;
use User\Models\User;

class GiftcodeObserver
{
    public function creating(Giftcode $giftcode)
    {

        //Giftcode code and expiration date
        list($code, $expirationDate) = $giftcode->generateCode();
        $giftcode->code = $code;
        $giftcode->expiration_date = $expirationDate;

        //Giftcode UUID field
        if(is_null($giftcode->user_id)) {
            Log::error('GiftcodeObserver Line 22, user_id is null');
            throw new Exception(trans('giftcode.responses.global-error'),500);
        }

        $user = User::query()->find($giftcode->user_id);
        $uuid = $user->member_id . mt_rand(1000,9999) . time();
        while(Giftcode::query()->where('uuid','=',$uuid)->exists())
            $uuid = $user->member_id . mt_rand(1000,9999) . time();

        $giftcode->uuid = $uuid;

        //Giftcode costs
        if(empty($giftcode->package_id)) {
            Log::error('GiftcodeObserver Line 29, package_id is null');
            throw new Exception(trans('giftcode.responses.global-error'),500);
        }

        $giftcode->packages_cost_in_pf = (float)$giftcode->package->price ;

        if (giftcodeGetSetting('include_registration_fee') AND request()->has('include_registration_fee') AND request()->get('include_registration_fee'))
            $giftcode->registration_fee_in_pf = (float)giftcodeGetSetting('registration_fee');

        $giftcode->total_cost_in_pf = $giftcode->packages_cost_in_pf + $giftcode->registration_fee_in_pf;

        if(empty($giftcode->user_id))
            $giftcode->user_id = request()->user('api')->id;

        if(empty($giftcode->package_id) AND request()->has('package_id'))
            $giftcode->package_id = request()->get('package_id');

    }
}
