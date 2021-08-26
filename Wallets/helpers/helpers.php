<?php


if (!function_exists('walletGetSetting')) {

    function walletGetSetting($key)
    {
        //Check if settings are available in cache
        if(cache()->has('wallet_settings'))
            if($setting = collect(cache('wallet_settings'))->where('name', $key)->first())
                return $setting['value'];

        $setting = \Wallets\Models\Setting::query()->where('name',$key)->first();
        if($setting)
            return $setting->value;

        return config("wallet.{$key}");
    }
}
