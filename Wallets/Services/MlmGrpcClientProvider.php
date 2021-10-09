<?php


namespace Wallets\Services;


use MLM\Services\Grpc\Acknowledge;
use MLM\Services\Grpc\Rank;
use Orders\Services\Grpc\Order;
use User\Services\Grpc\User;

class MlmGrpcClientProvider
{
    protected static $client;

    public function __construct()
    {
        self::$client = getMLMGrpcClient();
    }

    public static function submitOrder(Order $order) : Acknowledge
    {
        /** @var $submit_response Acknowledge */
        list($submit_response, $flag) = self::$client->submitOrder($order)->wait();
        if ($flag->code != 0)
            throw new \Exception('MLM not responding', 406);
        return $submit_response;
    }

    public static function simulateOrder(Order $order): Acknowledge
    {
        /** @var $submit_response Acknowledge */
        list($submit_response, $flag) = self::$client->simulateOrder($order)->wait();
        if ($flag->code != 0)
            throw new \Exception('MLM not responding', 406);
        return $submit_response;
    }

    public static function hasValidPackage(User $user) : Acknowledge
    {
        /** @var $submit_response Acknowledge */
        list($submit_response, $flag) = self::$client->hasValidPackage($user)->wait();
        if ($flag->code != 0)
            throw new \Exception('MLM not responding', 406);
        return $submit_response;
    }


    public static function getUserRank(User $user) : Rank
    {
        /** @var $submit_response Rank */
        list($submit_response, $flag) = self::$client->getUserRank($user)->wait();
        if ($flag->code != 0)
            throw new \Exception('MLM not responding', 406);
        return $submit_response;
    }
}
