<?php


namespace User\Services;

use User\Services\Grpc\Acknowledge;
use User\Services\Grpc\Id;
use User\Services\Grpc\User;
use User\Services\Grpc\UserTransactionPassword;
use User\Services\Grpc\WalletInfo;
use User\Services\Grpc\WalletRequest;

class GatewayGrpcClientProvider
{
    protected static $client;

    public function __construct()
    {
        self::$client = getGatewayGrpcClient();
    }

    public static function getUserById(Id $id) : User
    {
        /** @var $submit_response User */
        list($submit_response, $flag) = self::$client->getUserById($id)->wait();
        if ($flag->code != 0)
            throw new \Exception('Gateway not responding', 406);
        return $submit_response;
    }

    public static function getUserByMemberId(Id $id) : User
    {
        /** @var $submit_response User */
        list($submit_response, $flag) = self::$client->getUserByMemberId($id)->wait();
        if ($flag->code != 0)
            throw new \Exception('Gateway not responding', 406);
        return $submit_response;
    }

    public static function getUserWalletInfo(WalletRequest $walletRequest) : WalletInfo
    {
        /** @var $submit_response WalletInfo */
        list($submit_response, $flag) = self::$client->getUserWalletInfo($walletRequest)->wait();
        if ($flag->code != 0)
            throw new \Exception('Gateway not responding', 406);
        return $submit_response;
    }

    public function checkTransactionPassword(UserTransactionPassword $userTransactionPassword): Acknowledge
    {
        /** @var $submit_response Acknowledge */
        list($submit_response,$flag) = self::$client->checkTransactionPassword($userTransactionPassword)->wait();
        if($flag->code != 0)
            throw new \Exception('Gateway not responding', 406);
        return $submit_response;
    }

}
