<?php


namespace Orders\tests\Feature;


use Illuminate\Support\Facades\Mail;
use MLM\Services\Grpc\Acknowledge;
use Orders\Services\MlmClientFacade;
use Orders\tests\OrderTest;
use Payments\Services\Processors\PaymentFacade;

class OrderFeatureTest extends OrderTest
{
    /**
     * @test
     */
    public function submit_order_with_email()
    {
        $this->withHeaders($this->getHeaders());

        Mail::fake();
        $acknowledge = new Acknowledge;
        $acknowledge->setStatus(true);
        $acknowledge->setCreatedAt(now()->toDateString());
        MlmClientFacade::shouldReceive('simulateOrder')->once()->andReturn($acknowledge);
        PaymentFacade::shouldReceive('pay')->once()->andReturn([true,'']);

        $response = $this->post(route('orders.store'), [
            'package_id' => 1,
            'plan' => ORDER_PLAN_START,
            'payment_type' => 'purchase',
            'payment_driver' => 'btc-pay-server',
            'payment_currency' => 'BTC',
        ]);
        $response->assertOk();

    }

}
