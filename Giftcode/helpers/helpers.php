<?php

use Giftcode\Models\Giftcode;
use Giftcode\Traits\CodeGenerator;
use Illuminate\Support\Str;

if (!function_exists('giftcodeGetSetting')) {

    function giftcodeGetSetting($name)
    {
        $setting = \Giftcode\Models\Setting::whereName($name)->first();
        if($setting)
            return $setting->value;

        return config("api.giftcode.{$name}");
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
