<?php


use Illuminate\Support\Carbon;

if (!function_exists('giftcodeGetSetting')) {

    function giftcodeGetSetting($key)
    {
//        Check if settings are available in cache
        if(cache()->has('giftcode_settings'))
            if($setting = collect(cache('giftcode_settings'))->where('name', $key)->first())
                return $setting['value'];

        $setting = \Giftcode\Models\Setting::whereName($key)->first();
        if($setting)
            return $setting->value;

        if(defined('GIFTCODE_SETTINGS') AND is_array(GIFTCODE_SETTINGS) AND array_key_exists($key,GIFTCODE_SETTINGS) AND array_key_exists('value',GIFTCODE_SETTINGS[$key]))
            return GIFTCODE_SETTINGS[$key]['value'];

        throw new \Exception(trans('giftcode.responses.setting-key-doesnt-exists',['key' => $key]),400);
    }
}

if(!function_exists('giftcodeGetEmailContent')) {

    function giftcodeGetEmailContent($key)
    {
        $email = null;
//        Check if email content is available in cache
        if(cache()->has('giftcode_email_contents'))
            if( $setting = collect(cache('giftcode_email_contents'))->where('key',$key)->first())
                $email = $setting;

        if(empty($email) AND $email = \Giftcode\Models\EmailContent::where('key',$key)->first())
            $email = $email->toArray();

        if(empty($email) AND defined('GIFTCODE_EMAIL_CONTENTS') AND is_array(GIFTCODE_EMAIL_CONTENTS) AND array_key_exists($key,GIFTCODE_EMAIL_CONTENTS))
            $email = GIFTCODE_EMAIL_CONTENTS[$key];

        if($email AND is_array($email)) {
            $email['from'] = env('MAIL_FROM', $email['from']);
            return $email;
        }

        \Illuminate\Support\Facades\Log::error('giftcodeEmailContentError => ' . $key);
        throw new \Exception(trans('giftcode.responses.email-key-doesnt-exists',['key' => $key]),400);
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


