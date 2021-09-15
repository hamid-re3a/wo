<?php
require getcwd() . '/vendor/autoload.php';
require getcwd() .' /public/index.php';

$grpc = new \Mix\Grpc\Server();
$grpc->register(\Wallets\Services\WalletGrpcService::class);

Swoole\Coroutine\run(function () use ($grpc) {
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9596, false);
    $server->handle('/', $grpc->handler());
    $server->set([
        'open_http2_protocol' => true,
        'http_compression' => false,
    ]);
    $server->start();
});
