<?php
namespace Payments\tests\Feature;

use Orders\Services\Order;
use Payments\Services\PaymentService;
use Payments\Services\PaymentsServiceClient;
use Payments\tests\PaymentTest;
use function Swoole\Coroutine\run;

class PaymentFeatureTest extends PaymentTest
{

    /**
     * @test
     */
    public function pay_an_order_return_invoice()
    {
        $_this = $this;
        run(function () use ($_this) {

            $grpc = new \Mix\Grpc\Server();
            $grpc->register(PaymentService::class);
            $server = new \Swoole\Coroutine\Http\Server('0.0.0.0', 9595, false);
            $server->set([
                'open_http2_protocol' => true,
                'http_compression' => false,
            ]);
            $server->handle('/', $grpc->handler());
            go(function () use ($server) {
                $server->start();
            });

            go(function () use ($server, $_this) {
                $client = new \Mix\Grpc\Client('127.0.0.1', 9595);
                $payment_client = new PaymentsServiceClient($client);
                $ctx = new \Mix\Grpc\Context();
                $request = $_this->createNewOrder();
                $response = $payment_client->pay($ctx, $request);
                $_this->assertEquals('New', $response->getStatus());
                $server->shutdown();

            });
        });
    }

    private function createNewOrder()
    {
        $order = new Order();
        $order->setId((int)1);
        $order->setPackagesCostInUsd((int)100);
        $order->setPaymentCurrency('BTC');
        $order->setUserId((int)1);
        $order->setPaymentType('gate-way');
        $order->setPaymentDriver('btc-pay-server');
        $order->setRegistrationFeeInUsd((int)20);
        $order->setTotalCostInUsd((int)120);
        return $order;
    }
}
