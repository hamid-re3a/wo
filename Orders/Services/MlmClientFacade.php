<?php


namespace Orders\Services;


use Illuminate\Support\Facades\Facade;
use MLM\Services\Grpc\Acknowledge;
use MLM\Services\Grpc\Rank;
use Orders\Services\Grpc\Order;
use User\Services\Grpc\User;

/**
 * @method static Acknowledge submitOrder(Order $order)
 * @method static Acknowledge simulateOrder(Order $order)
 * @method static Acknowledge hasValidPackage(User $user)
 * @method static Rank getUserRank(User $user)
 */

class MlmClientFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'MlmClientFacade';
    }

    public static function shouldProxyTo($class)
    {
        return app()->bind(self::getFacadeAccessor(),$class);
    }
}
