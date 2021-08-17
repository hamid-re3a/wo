<?php

if (!function_exists('giftcodeGetSetting')) {

    function giftcodeGetSetting($name)
    {
        $setting = \Giftcode_\Models\Setting::whereName($name)->first();
        if($setting)
            return $setting->value;

        dd(config("giftcode_{$name}"));
        return config($name);
    }
}
