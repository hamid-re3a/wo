<?php

use Orders\Services\Grpc\Order;
use Packages\Services\Grpc\PackageCheck;

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




//$client = new \Packages\Services\Grpc\PackagesServiceClient('development.dreamcometrue.ai:9596', [
////$client = new \Packages\Services\Grpc\PackagesServiceClient('127.0.0.1:9596', [
//    'credentials' => \Grpc\ChannelCredentials::createInsecure()
//]);
//$request = new \Packages\Services\Grpc\Id();
//$request->setId((int)6);
//
//list($reply, $status) = $client->packageById($request)->wait();
//
//var_dump($status);
//var_dump($reply->getId());
//var_dump($reply->getName());





$client = new \Packages\Services\Grpc\PackagesServiceClient('development.dreamcometrue.ai:9596', [
//$client = new \Packages\Services\Grpc\PackagesServiceClient('127.0.0.1:9596', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure()
]);

$packages = new PackageCheck;
$packages->setPackageIndexId((int)1);
$packages->setPackageToBuyId((int)2);

list($reply, $status) = $client->packageIsInBiggestPackageCategory($packages)->wait();

var_dump($status);
var_dump($reply->getId());
var_dump($reply->getName());



//
//$order = new Order();
//$order->setFromUserId((int)2);
//$order->setUserId((int)3);
////$order->setIsPaidAt(now()->toString());
////$order->setPlan(OrderPlans::ORDER_PLAN_START);
////$order->setPlan(OrderPlans::ORDER_PLAN_PURCHASE);
//$order->setPackageId((int)1);
//$client = new \Orders\Services\Grpc\OrdersServiceClient('staging.janex.org:9596', [
////$client = new \MLM\Services\Grpc\MLMServiceClient('127.0.0.1:9598', [
//    'credentials' => \Grpc\ChannelCredentials::createInsecure()
//]);
//list($reply, $status) = $client->sponsorPackage($order)->wait();
//var_dump($status);
//var_dump($reply->getStatus());
//var_dump($reply->getMessage());



//
//
//
//$client = new \User\Services\Grpc\UserServiceClient(env('API_GATEWAY_GRPC_URL','staging-api-gateway.janex.org:9595'), [
//    'credentials' => \Grpc\ChannelCredentials::createInsecure()
//]);
//$req = app(\User\Services\Grpc\WalletRequest::class);
//$req->setUserId((int)2);
//$req->setWalletType(\User\Services\Grpc\WalletType::BTC);
//list($reply, $status) = $client->getUserWalletInfo($req)->wait();
////if (!$status->code != 0 OR !$reply->getAddress())
////    throw new \Exception(trans('wallet.withdraw-profit-request.cant-find-wallet-address', [
////        'name' => WalletType::name(0)
////    ]));
//
//var_dump($reply->getAddress());
