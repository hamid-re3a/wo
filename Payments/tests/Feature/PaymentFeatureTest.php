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
    public function get_payment_currencies()
    {
        $response =$this->get(route('payments.currency.index'));
        $response->assertOk();
            $response->json()['data'];
    }

}
