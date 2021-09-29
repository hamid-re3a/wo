<?php

namespace Giftcode\Mail\User;

use Giftcode\Mail\SettingableMail;
use Giftcode\Models\Giftcode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use User\Models\User;

class RedeemedGiftcodeCreatorEmail extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $user;
    public $giftcode;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $giftcode
     */
    public function __construct(User $user,Giftcode $giftcode)
    {
        $this->user = $user;
        $this->giftcode = $giftcode;
    }


    /**
     * Build the message.
     *
     * @return $this
     * @throws \Exception
     */
    public function build()
    {
        $setting = $this->getSetting();

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->user->full_name) || empty($this->user->full_name)) ? 'Unknown': $this->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{code}}',(is_null($this->giftcode->code) ) ? 'Unknown': $this->giftcode->code,$setting['body']);
        $setting['body'] = str_replace('{{package_name}}',(is_null($this->giftcode->package_name)) ? 'Unknown': $this->giftcode->package_name,$setting['body']);
        $setting['body'] = str_replace('{{redeem_date}}',(is_null($this->giftcode->redeem_date)) ? 'Unknown': $this->giftcode->redeem_date,$setting['body']);
        $setting['body'] = str_replace('{{redeem_user_full_name}}',(is_null($this->giftcode->redeem_user_id)) ? 'Unknown': $this->giftcode->redeemer_full_name,$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        try {
            return giftcodeGetEmailContent('GIFTCODE_REDEEMED_CREATOR_EMAIL');
        } catch (\Throwable $exception) {
            Log::error('giftcodeGetEmailContent [Giftcode\Mail\User\RedeemedGiftcodeCreatorEmail]');
            throw new $exception;
        }
    }
}
