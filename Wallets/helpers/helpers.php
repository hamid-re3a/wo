<?php


use Illuminate\Support\Carbon;

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
        $email = null;
        //Check if email content is available in cache
        if (cache()->has('wallet_email_contents'))
            if($check = collect(cache('wallet_email_contents'))->where('key', $key)->first())
                $email = $check;


        if ($email = \Wallets\Models\EmailContent::query()->where('key', $key)->first())
            $email = $email->toArray();

        if (defined('WALLET_EMAIL_CONTENTS') AND
            is_array(WALLET_EMAIL_CONTENTS) AND
            array_key_exists($key, WALLET_EMAIL_CONTENTS))
            $email = WALLET_EMAIL_CONTENTS[$key];

        if($email AND is_array($email)) {
            $email['from'] = env('MAIL_FROM', $email['from']);
            return $email;
        }

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
        return new \Kyc\Services\Grpc\KycServiceClient(env('KYC_GRPC_URL', 'staging.janex.org:9597'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}

if (!function_exists('chartMaker')) {
    function chartMaker($duration_type, $repo_function, $sub_function)
    {
        switch ($duration_type) {
            default:
            case "week":

                $from_day = Carbon::now()->endOfDay()->subDays(7);
                $to_day = Carbon::now();

                $processing_collection = $repo_function($from_day, $to_day);

                $result = [];
                foreach (range(-1, 5) as $day) {

                    $timestamp = Carbon::now()->startOfDay()->subDays($day)->timestamp;
                    $interval = [Carbon::now()->startOfDay()->subDays($day+1), Carbon::now()->startOfDay()->subDays($day)];


                    $result[$timestamp] = $sub_function($processing_collection, $interval);

                }
                return $result;
                break;
            case "month":
                $from_day = Carbon::now()->endOfMonth()->subMonths(12);
                $to_day = Carbon::now();

                $processing_collection = $repo_function($from_day, $to_day);
                $result = [];
                foreach (range(-1, 10) as $month) {
                    $timestamp = Carbon::now()->startOfMonth()->subMonths($month)->timestamp;
                    $interval = [Carbon::now()->startOfMonth()->subMonths($month+1), Carbon::now()->startOfMonth()->subMonths($month)];

                    $result[$timestamp] = $sub_function($processing_collection, $interval);
                }
                return $result;
                break;
            case "year":

                $from_day = Carbon::now()->endOfYear()->subYears(3);
                $to_day = Carbon::now();

                $processing_collection = $repo_function($from_day, $to_day);
                $result = [];
                foreach (range(-1, 3) as $year) {
                    $timestamp = Carbon::now()->startOfYear()->subYears($year)->timestamp;
                    $interval = [Carbon::now()->startOfYear()->subYears($year+1), Carbon::now()->startOfYear()->subYears($year)];

                    $result[$timestamp] = $sub_function($processing_collection, $interval);
                }
                return $result;
                break;
        }

    }
}

if(!function_exists('calculateTransferAmount')) {
    function calculateTransferAmount($amount)
    {
        $transfer_fee = (double)getWalletSetting('transfer_fee');
        $transaction_fee_way = getWalletSetting('transaction_fee_calculation');

        if (!empty($transaction_fee_way) AND $transaction_fee_way == 'percentage' AND !empty($transfer_fee) AND $transfer_fee > 0)
            $transfer_fee = (double)$amount * (double)($transfer_fee / 100);

        if (empty($transfer_fee) OR $transfer_fee <= 0)
            $transfer_fee = (double)10;

        $total = (double)$amount + (double)$transfer_fee;
        return [(double)$total, (double)$transfer_fee];
    }
}

