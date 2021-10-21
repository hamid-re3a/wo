<?php


namespace Wallets\Services;

use Illuminate\Support\Facades\Facade;
use Kyc\Services\Grpc\Acknowledge;

/**
 * @method static Acknowledge checkKYCStatus(\User\Services\Grpc\User $user)
 */

class KycClientFacade extends Facade
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
