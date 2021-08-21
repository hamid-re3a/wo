<?php

if (!function_exists('giftcodeGetSetting')) {

    function giftcodeGetSetting($key)
    {
        //Check if settings are available in cache
        if(cache()->has('giftcode_settings'))
            if($setting = collect(cache('giftcode_settings'))->where('name', $key)->first())
                return $setting['value'];

        $setting = \Giftcode\Models\Setting::whereName($key)->first();
        if($setting)
            return $setting->value;

        return config("giftcode.{$key}");
    }
}

if(!function_exists('giftcodeGetEmailContent')) {

    function giftcodeGetEmailContent($key)
    {
        //Check if email content is available in cache
        if(cache()->has('giftcode_email_contents'))
            if($email = collect(cache('giftcode_email_contents'))->where('key', $key)->first())
                return $email;


        if($email = \Giftcode\Models\EmailContent::where('key',$key)->first())
            return $email->toArray();

        if(defined('EMAIL_CONTENTS') AND is_array(EMAIL_CONTENTS) AND array_key_exists($key,EMAIL_CONTENTS))
            return EMAIL_CONTENTS[$key];

        \Illuminate\Support\Facades\Log::error('giftcodeEmailContentError => ' . $key);
        throw new Exception(trans('giftcode.responses.email-key-doesnt-exists'));
    }

}

