<?php

use Illuminate\Support\Carbon;
const ORDER_PLAN_PURCHASE = 'ORDER_PLAN_PURCHASE';
const ORDER_PLAN_START = 'ORDER_PLAN_START';
const ORDER_PLAN_SPECIAL = 'ORDER_PLAN_SPECIAL';
const ORDER_PLAN_COMPANY = 'ORDER_PLAN_COMPANY';
const ORDER_PLANS = [
    ORDER_PLAN_START,
    ORDER_PLAN_PURCHASE,
    ORDER_PLAN_SPECIAL,
    ORDER_PLAN_COMPANY
];

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
