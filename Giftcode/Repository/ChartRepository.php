<?php


namespace Giftcode\Repository;


use Exception;
use Giftcode\Models\Giftcode;
use Giftcode\Models\Package;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChartRepository
{

    private $model;
    public function __construct()
    {
        $this->model = new Giftcode();
    }

    public function getGiftcodeByDateCollection($date_field,$from_date,$to_date,$user_id = null,$package_ids = [],$select=['created_at'])
    {
        try {
            $giftcodes = $this->model->query()->select($select);

            if($user_id)
                $giftcodes->where('user_id','=',$user_id);

            if($package_ids AND count($package_ids) != 0)
                $giftcodes->whereIn('package_id',$package_ids);

            $from_date = Carbon::parse($from_date)->toDateTimeString();
            $to_date = Carbon::parse($to_date)->toDateTimeString();

            return $giftcodes->whereBetween($date_field,[$from_date,$to_date])->get();

        } catch (\Throwable $exception) {
            Log::error('Giftcode\Repository\GiftcodeRepository@getGiftcodeByDateCollection => ' . $exception->getMessage());
            throw new Exception(trans('giftcode.responses.something-went-wrong'));
        }
    }

    public function getGiftcodeVsTimeChart($type, $user_id = null)
    {

        $that = $this;
        $function_giftcode_collection = function($from_date,$to_date) use($that,$user_id) {
          return $that->getGiftcodeByDateCollection('created_at',$from_date,$to_date,$user_id,[],['created_at','expiration_date','is_canceled','redeem_user_id']);
        };

        $sub_function_total = function ($collection, $intervals) {
            /**@var $collection Collection*/
            return $collection->whereBetween('created_at', $intervals)->count();
        };

        $sub_function_expired = function ($collection, $intervals) {
            /**@var $collection Collection*/
            return $collection->whereBetween('created_at', $intervals)->where('expiration_date', '>', now()->toDateTimeString())->count();
        };

        $sub_function_canceled = function ($collection, $intervals) {
            /**@var $collection Collection*/
            return $collection->whereBetween('created_at', $intervals)->where('is_canceled','!=',0)->count();
        };

        $sub_function_used = function ($collection, $intervals) {
            /**@var $collection Collection*/
            return $collection->whereBetween('created_at', $intervals)->whereNotNull('redeem_user_id')->count();
        };

        $result = [];
        $result['total'] = chartMaker($type, $function_giftcode_collection, $sub_function_total);
        $result['expired'] = chartMaker($type, $function_giftcode_collection, $sub_function_expired);
        $result['canceled'] = chartMaker($type, $function_giftcode_collection, $sub_function_canceled);
        $result['used'] = chartMaker($type, $function_giftcode_collection, $sub_function_used);
        return $result;

    }

    public function getPackageVsTimeChart($type, $user_id = null)
    {

        $that = $this;
        $function_giftcode_bPackage_collection = function($from_date,$to_date) use($that,$user_id) {
            $packages = Package::query()->where('short_name','like','B%')->pluck('id');
            return $that->getGiftcodeByDateCollection('created_at',$from_date,$to_date,$user_id,$packages);
        };

        $function_giftcode_iPackage_collection = function($from_date,$to_date) use($that,$user_id) {
            $packages = Package::query()->where('short_name','like','I%')->pluck('id');
            return $that->getGiftcodeByDateCollection('created_at',$from_date,$to_date,$user_id,$packages);
        };

        $function_giftcode_aPackage_collection = function($from_date,$to_date) use($that,$user_id) {
            $packages = Package::query()->where('short_name','like','A%')->pluck('id');
            return $that->getGiftcodeByDateCollection('created_at',$from_date,$to_date,$user_id,$packages);
        };

//        $function_giftcode_pPackage_collection = function($from_date,$to_date) use($that,$user_id) {
//            $packages = Package::query()->where('short_name','like','P%')->pluck('id');
//            return $that->getGiftcodeByDateCollection('created_at',$from_date,$to_date,$user_id,$packages);
//        };

        $sub_function_total = function ($collection, $intervals) {
            /**@var $collection Collection*/
            return $collection->whereBetween('created_at', $intervals)->count();
        };


        $result = [];
        $result['beginner'] = chartMaker($type, $function_giftcode_bPackage_collection, $sub_function_total);
        $result['intermediate'] = chartMaker($type, $function_giftcode_iPackage_collection, $sub_function_total);
        $result['advance'] = chartMaker($type, $function_giftcode_aPackage_collection, $sub_function_total);
//        $result['pro'] = chartMaker($type, $function_giftcode_pPackage_collection, $sub_function_total);
        return $result;

    }

}
