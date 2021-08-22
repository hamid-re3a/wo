<?php
namespace Payments\tests\Feature;

use Payments\tests\PaymentTest;

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
    public function submit_order_with_email()
    {

        $response =$this->post(route('orders.store'),[
            'package_ids' => [['id'=>1,'qty'=>1]],
            'to_user_id' => '1',
            'plan' => ORDER_PLAN_START,
            'payment_type' => 'purchase',
            'payment_driver' => 'btc-pay-server',
            'payment_currency' => 'BTC',
        ],[
            'X-user-id'=>1,
            'X-user-first-name'=>'admin',
            'X-user-last-name'=>'ni',
            'X-user-email'=>'admin@site.com',
            'X-user-username'=>'admin',
        ]);
        dd($response->json());
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
