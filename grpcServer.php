<?php
//require '/home/stagingapigateway/public_html/API-Gateway/vendor/autoload.php';
//require '/home/stagingapigateway/public_html/API-Gateway/public/index.php';

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/public/index.php';

$grpc = new \Mix\Grpc\Server();
$grpc->register(\Wallets\Services\WalletGrpcService::class);
$grpc->register(\Packages\Services\Grpc\PackageGrpcService::class);
$grpc->register(\Orders\Services\Grpc\OrderGrpcService::class);

Swoole\Coroutine\run(function () use ($grpc) {
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9596, false);
    $server->handle('/', $grpc->handler());
    $server->set([
        'open_http2_protocol' => true,
        'http_compression' => false,
        'worker_num' => 4,
    ]);
    $server->start();
});
