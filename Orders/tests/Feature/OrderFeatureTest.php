<?php


namespace Orders\tests\Feature;


use Illuminate\Support\Facades\Mail;
use MLM\Services\Grpc\Acknowledge;
use Orders\Services\MlmClientFacade;
use Orders\tests\OrderTest;
use Payments\Models\PaymentCurrency;
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
        $payment = PaymentCurrency::query()->first();
        $payment->update(['available_services'=>[CURRENCY_SERVICE_PURCHASE]]);
        $response = $this->post(route('customer.orders.store'), [
            'package_id' => 1,
            'plan' => ORDER_PLAN_START,
            'payment_type' => 'purchase',
            'payment_driver' => 'btc-pay-server',
            'payment_currency' => 'BTC',
        ]);
        $response->assertOk();

    }

}
