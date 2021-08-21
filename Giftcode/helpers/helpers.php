<?php

use Giftcode\Models\Giftcode;
use Giftcode\Traits\CodeGenerator;
use Illuminate\Support\Str;

if (!function_exists('giftcodeGetSetting')) {

    function giftcodeGetSetting($key)
    {
        $setting = \Giftcode\Models\Setting::whereName($key)->first();
        if($setting)
            return $setting->value;

        return config("giftcode.{$key}");
    }
}

if (!function_exists('newGiftcode')) {

    function newGiftcode()
    {
        $giftcodeModel = new Giftcode();
        $code = $giftcodeModel->generateCode();
        while($giftcodeModel->whereCode($code)->first())
            $code = $giftcodeModel->generateCode();

        return $code;
    }
}

if(!function_exists('giftcodeGetEmailContent')) {

    function giftcodeGetEmailContent($key)
    {
        if($email = \Giftcode\Models\EmailContent::where('key',$key)->first())
            return $email->toArray();

        if(defined('EMAIL_CONTENTS') AND is_array(EMAIL_CONTENTS) AND array_key_exists($key,EMAIL_CONTENTS))
            return EMAIL_CONTENTS[$key];

        \Illuminate\Support\Facades\Log::error('giftcodeEmailContentError => ' . $key);
        throw new Exception(trans('giftcode.responses.email-key-doesnt-exists'));
    }

}

