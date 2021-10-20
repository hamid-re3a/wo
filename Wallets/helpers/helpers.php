<?php


if (!function_exists('getWalletSetting')) {

    function getWalletSetting($key)
    {
        //Check if settings are available in cache
        if (cache()->has('wallet_settings'))
            if ($setting = collect(cache('wallet_settings'))->where('name', $key)->first())
                return $setting['value'];

        $setting = \Wallets\Models\Setting::query()->where('name', $key)->first();
        if ($setting)
            return $setting->value;


        if (defined('WALLET_SETTINGS') AND is_array(WALLET_SETTINGS) AND array_key_exists($key, WALLET_SETTINGS))
            return WALLET_SETTINGS[$key]['value'];

        \Illuminate\Support\Facades\Log::error('getWalletSetting => ' . $key);
        throw new Exception(trans('wallet.responses.setting-key-doesnt-exists'));
    }
}

if (!function_exists('getWalletEmailContent')) {

    function getWalletEmailContent($key)
    {
        //Check if email content is available in cache
        if (cache()->has('wallet_email_contents'))
            if ($email = collect(cache('wallet_email_contents'))->where('key', $key)->first())
                return $email;


        if ($email = \Wallets\Models\EmailContent::query()->where('key', $key)->first())
            return $email->toArray();

        if (defined('WALLET_EMAIL_CONTENTS') AND
            is_array(WALLET_EMAIL_CONTENTS) AND
            array_key_exists($key, WALLET_EMAIL_CONTENTS))
            return WALLET_EMAIL_CONTENTS[$key];

        \Illuminate\Support\Facades\Log::error('getWalletEmailContentError => ' . $key);
        throw new Exception(trans('wallet.responses.email-key-doesnt-exists'));
    }

}

if (!function_exists('formatCurrencyFormat')) {

    function formatCurrencyFormat($value)
    {
        if (is_numeric($value))
            $value = number_format($value, 2);
//            $value = floatval(preg_replace('/[^\d.]/', '', number_format($value,2)));

        return $value;
    }
}


if (!function_exists('getMLMGrpcClient')) {
    function getMLMGrpcClient()
    {
        return new \MLM\Services\Grpc\MLMServiceClient(env('MLM_GRPC_URL', 'staging-api-gateway.janex.org:9598'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}

if (!function_exists('getKycGrpcClient')) {
    function getKycGrpcClient()
    {
        return new \Kyc\Services\Grpc\KycServiceClient(env('MLM_GRPC_URL', 'staging-api-gateway.janex.org:9597'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}
