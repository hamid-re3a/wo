<?php


namespace User\Services;


use Illuminate\Support\Facades\Facade;
use User\Services\Grpc\Acknowledge;
use User\Services\Grpc\Id;
use User\Services\Grpc\User;
use User\Services\Grpc\UserTransactionPassword;
use User\Services\Grpc\WalletInfo;
use User\Services\Grpc\WalletRequest;

/**
 * @method static User getUserById(Id $id )
 * @method static User getUserByMemberId(Id $id )
 * @method static WalletInfo getUserWalletInfo(WalletRequest $walletRequest)
 * @method static Acknowledge checkTransactionPassword(UserTransactionPassword $userTransactionPassword)
 */

class GatewayClientFacade extends Facade
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
