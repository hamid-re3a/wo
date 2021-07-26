<?php

require 'vendor/autoload.php';

$grpc = new Mix\Grpc\Server();

$grpc->register(\Packages\Services\PackageService::class);

$http = new Swoole\Http\Server('0.0.0.0', 9595);
$http->on('Request', $grpc->handler());
$http->set([
    'worker_num' => 4,
    'open_http2_protocol' => true,
    'http_compression' => false,
]);
$http->start();
