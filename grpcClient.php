<?php

require 'vendor/autoload.php';

use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {

    $client    = new Mix\Grpc\Client('127.0.0.1', 9595);
    $package_service = new \Packages\Services\PackagesServiceClient($client);
    $ctx = new Mix\Grpc\Context();
    $request = new \Packages\Services\Id();
    $request->setId((int)1);
    $response = $package_service->packageById($ctx, $request);

    var_dump($response->getName());

});
