<?php
require './vendor/autoload.php';

$client = new \Wallets\Services\Grpc\WalletServiceClient('staging-api-gateway.janex.org:9596', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure()
]);
$request = new \Wallets\Services\Grpc\Wallet();
$request->setName(\Wallets\Services\Grpc\WalletNames::DEPOSIT);
$request->setUserId((int)1);

list($reply, $status) = $client->getBalance($request)->wait();

print_r($status);
//print_r($reply->getBalance());
