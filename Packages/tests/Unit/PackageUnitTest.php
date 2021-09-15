<?php

namespace Packages\tests\Unit;

use Packages\tests\PackageTest;
use function Swoole\Coroutine\run;
use Swoole\Coroutine\WaitGroup;

class PackageUnitTest extends PackageTest
{

    /**
     * @test
     */
    public function pay_an_order_return_invoice()
    {
        $this->runGrpcServer();
        $this->createNewOrder();


    }

    private function runGrpcServer(): void
    {
//        $_this = $this;
//        run(function() use ($_this){
//
//            $wg = new WaitGroup();
//
//
//            $grpc = new \Mix\Grpc\Server();
//            $grpc->register(\Packages\Services\PackageService::class);
//            $server = new \Swoole\Coroutine\Http\Server('0.0.0.0', 9595, false);
//            $server->set([
//                'open_http2_protocol' => true,
//                'http_compression' => false,
//            ]);
//            $server->handle('/', $grpc->handler());
//            go(function() use ($server){
//                $server->start();
//            });
//
//
//            go(function() use ($server,$_this,$wg){
//                $wg->add();
//                $client = new \Mix\Grpc\Client('127.0.0.1', 9595);
//                $package_service = new PackagesServiceClient($client);
//                $ctx = new \Mix\Grpc\Context();
//                $request = new \Packages\Services\Id();
//                $request->setId((int)1);
//                $response = $package_service->packageById($ctx, $request);
//
//                $_this->assertEquals(1,$response->getId());
//                $server->shutdown();
//                $wg->done();
//            });
//
//            $wg->wait(1);
//        });

    }
}
