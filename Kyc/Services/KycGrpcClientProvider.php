<?php


namespace Kyc\Services;


use Kyc\Services\Grpc\Acknowledge;

class KycGrpcClientProvider
{
    protected static $client;

    public function __construct()
    {
        self::$client = getKycGrpcClient();
    }

    public static function checkKYCStatus(\User\Services\Grpc\User $user) : Acknowledge
    {
        /** @var $submit_response Acknowledge */
        list($submit_response, $flag) = self::$client->checkKYCStatus($user)->wait();
        if ($flag->code != 0)
            throw new \Exception('Kyc not responding', 406);
        return $submit_response;
    }

}
