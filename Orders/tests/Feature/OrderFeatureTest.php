<?php


namespace Orders\tests\Feature;


use Illuminate\Support\Facades\Mail;
use MLM\Services\Grpc\Acknowledge;
use Orders\Models\Order;
use Orders\OrderConfigure;
use MLM\Services\MlmClientFacade;
use Orders\tests\OrderTest;
use Payments\Models\PaymentCurrency;
use Payments\Services\Processors\PaymentFacade;
use User\Services\GatewayClientFacade;

class OrderFeatureTest extends OrderTest
{
    /**
     * @test
     */
    public function dashboard_subscription_type_chart()
    {
        OrderConfigure::seed();
        $this->withHeaders($this->getHeaders());
        $response = $this->post(
            route('admin.orders.dashboard.package-type'),
            ['type' => 'week']
        );
//        dd($response->json());
        $response->assertOk();
    }
    /**
     * @test
     */
    public function dashboard_subscription_chart()
    {
        OrderConfigure::seed();
        $this->withHeaders($this->getHeaders());
        $response = $this->post(
            route('admin.orders.dashboard.package-overview'),
            ['type' => 'week']
        );
        $response->assertOk();
    }
    /**
     * @test
     */
    public function submit_order_with_email()
    {
        $this->mockFacades();
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

    /**
     * @test
     */
    public function purchase_package_from_deposit_wallet()
    {
        $this->mockFacades();
        $payment = PaymentCurrency::query()->first();
        $payment->update(['available_services'=>[CURRENCY_SERVICE_PURCHASE]]);
        $response = $this->post(route('customer.orders.store'), [
            'package_id' => 1,
            'plan' => ORDER_PLAN_START,
            'payment_type' => 'deposit',
            'payment_driver' => 'btc-pay-server',
            'payment_currency' => 'BTC',
            'transaction_password' => 123
        ]);
        $response->assertOk();
    }

    /**
     * @test
     */
    public function purchase_package_with_giftcode()
    {
        $this->mockFacades();
        $payment = PaymentCurrency::query()->first();
        $payment->update(['available_services'=>[CURRENCY_SERVICE_PURCHASE]]);
        $response = $this->post(route('customer.orders.store'), [
            'package_id' => 1,
            'plan' => ORDER_PLAN_START,
            'payment_type' => 'giftcode',
            'payment_driver' => 'btc-pay-server',
            'payment_currency' => 'BTC',
            'giftcode' => 123
        ]);
        $response->assertOk();
    }


    private function mockFacades(): void
    {
        Mail::fake();
        $this->withHeaders($this->getHeaders());
        $acknowledge = new Acknowledge;
        $acknowledge->setStatus(true);
        $acknowledge->setCreatedAt(now()->toDateString());
        MlmClientFacade::shouldReceive('simulateOrder')->andReturn($acknowledge);
        MlmClientFacade::shouldReceive('submitOrder')->andReturn($acknowledge);
        PaymentFacade::shouldReceive('pay')->once()->andReturn([true, '']);

        $ack = new \User\Services\Grpc\Acknowledge();
        $ack->setStatus(true);
        GatewayClientFacade::shouldReceive('checkTransactionPassword')->andReturn($ack);
    }

}
