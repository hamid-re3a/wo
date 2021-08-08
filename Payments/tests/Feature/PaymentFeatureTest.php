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
    public function get_payment_types()
    {
        $response =$this->get(route('payments.type.index'));
        $response->assertOk();
        $response->json()['data'];
    }
    /**
     * @test
     */
    public function get_payment_currencies()
    {
        $response =$this->get(route('payments.currency.index'));
        $response->assertOk();
            $response->json()['data'];
    }

}
