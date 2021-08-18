<?php

use Workerman\Worker;
use PHPSocketIO\SocketIO;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Connection\TcpConnection;

include __DIR__ . '/../../vendor/autoload.php';

$uidConnectionMap = array();

$sender_io = new SocketIO(2120);
$sender_io->on('connection', function ($socket) {
    fwrite(STDOUT, PHP_EOL);
    $socket->on('invoice_transaction_id', function ($uid) use ($socket) {
        global $uidConnectionMap;
        if (isset($socket->uid)) {
            return;
        }
        $uid = (string)$uid;
        if (!isset($uidConnectionMap[$uid])) {
            $uidConnectionMap[$uid] = 0;
        }
        ++$uidConnectionMap[$uid];
        $socket->join($uid);
        $socket->uid = $uid;
    });

    $socket->on('disconnect', function () use ($socket) {
        if (!isset($socket->uid)) {
            return;
        }
        global $uidConnectionMap;
        if (--$uidConnectionMap[$socket->uid] <= 0) {
            unset($uidConnectionMap[$socket->uid]);
        }
    });
});

$sender_io->on('workerStart', function () {
    $inner_http_worker = new Worker('http://0.0.0.0:2121');
    $inner_http_worker->onMessage = function (TcpConnection $connection, Request $request) {

        $path = $request->path();
        if ($path === '/socket') {
            $data = json_decode($request->rawBody(), true);

            global $sender_io;
            if (isset($data['uid']) && isset($data['content'])) {
                $sender_io->to($data['uid'])->emit('invoice_transaction_status', json_encode($data['content']));
                return $connection->send((new Response())->withBody('sent'));
            }
        } else {
            return $connection->send(new Response(404, array(), '<h3>404 Not Found</h3>'));
        }


        return $connection->send((new Response())->withBody('ok'));

    };
    $inner_http_worker->listen();
});

Worker::runAll();
