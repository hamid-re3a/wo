<?php
require './vendor/autoload.php';

$client = new \Wallets\Services\Grpc\WalletServiceClient('127.0.0.1:9596', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure()
]);
$request = new \Wallets\Services\Grpc\Wallet();
$request->setName(\Wallets\Services\Grpc\WalletNames::DEPOSIT);
$request->setUserId(1);

list($reply, $status) = $client->getBalance($request)->wait();

print_r($reply->getBalance());
