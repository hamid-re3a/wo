<?php

if (!function_exists('giftcodeGetSetting')) {

    function giftcodeGetSetting($name)
    {
        $setting = \Giftcode\Models\Setting::whereName($name)->first();
        if($setting)
            return $setting->value;

        dd(config("Giftcode{$name}"));
        return config($name);
    }
}