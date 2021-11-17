<?php


namespace Payments\Services\Processors;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array pay(Collection $payout_requests,string $dispatchType)
 */

class PayoutFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return __CLASS__;
    }

    public static function shouldProxyTo($class)
    {
        return app()->singleton(self::getFacadeAccessor(),$class);
    }
}
