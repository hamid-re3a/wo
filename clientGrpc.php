<?php
require './vendor/autoload.php';

//$client = new \Wallets\Services\Grpc\WalletServiceClient('staging-api-subscription.janex.org:9596', [
//    'credentials' => \Grpc\ChannelCredentials::createInsecure()
//]);
//$request = new \Wallets\Services\Grpc\Wallet();
//$request->setName(\Wallets\Services\Grpc\WalletNames::DEPOSIT);
//$request->setUserId((int)1);
//
//list($reply, $status) = $client->getBalance($request)->wait();
//
//print_r($status);
//
//print_r("\n \n \n");
$client = new \Packages\Services\Grpc\PackagesServiceClient('staging-subscription.janex.org:9596', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure()
]);
$request = new \Packages\Services\Grpc\Id();
$request->setId((int)1);

list($reply, $status) = $client->packageById($request)->wait();

print_r($reply);
print_r($status);
//print_r($reply->getBalance());









$client = new \User\Services\Grpc\UserServiceClient(env('API_GATEWAY_GRPC_URL','staging-api-gateway.janex.org:9595'), [
    'credentials' => \Grpc\ChannelCredentials::createInsecure()
]);
$req = app(\User\Services\Grpc\WalletRequest::class);
$req->setUserId((int)2);
$req->setWalletType(\User\Services\Grpc\WalletType::BTC);
list($reply, $status) = $client->getUserWalletInfo($req)->wait();
//if (!$status->code != 0 OR !$reply->getAddress())
//    throw new \Exception(trans('wallet.withdraw-profit-request.cant-find-wallet-address', [
//        'name' => WalletType::name(0)
//    ]));

var_dump($reply->getAddress());
