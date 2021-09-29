<?php

namespace Giftcode\Mail\User;

use Giftcode\Mail\SettingableMail;
use Giftcode\Models\Giftcode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GiftcodeExpiredEmail extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $giftcode;

    /**
     * Create a new message instance.
     *
     * @param $giftcode
     */
    public function __construct(Giftcode $giftcode)
    {
        $this->giftcode = $giftcode->load('creator');
    }


    /**
     * Build the message.
     *
     * @return $this
     * @throws \Throwable
     */
    public function build()
    {
        $setting = $this->getSetting();

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->giftcode->creator->full_name) || empty($this->giftcode->creator->full_name)) ? 'Unknown': $this->giftcode->creator->full_name,$setting['body']);
        $setting['body'] = str_replace('{{code}}',(is_null($this->giftcode->code) ) ? 'Unknown': $this->giftcode->code,$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        try {
            return giftcodeGetEmailContent('GIFTCODE_EXPIRED_EMAIL');
        } catch (\Throwable $exception) {
            Log::error('giftcodeGetEmailContent [Giftcode\Mail\User\GiftcodeWillExpireSoonEmail]');
            throw new $exception;
        }
    }
}
