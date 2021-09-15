<?php

namespace Payments\tests\Feature;

use Payments\Models\PaymentType;
use Payments\tests\PaymentTest;

class PaymentAdminFeatureTest extends PaymentTest
{

    /**
     * get payment types test
     * @test
     */
    public function get_payment_type_admin()
    {
        $response = $this->get(route('payments.type.index'));
        $response->assertOk();
        $response->json()['data'];
    }


    /**
     * edit payment type test
     * @test
     */
    public function edit_payment_type_admin()
    {
        $header = $this->getHeaders();
        $payment_type = PaymentType::query()->first();
        $data['is_active'] = true;
        $data['id'] = $payment_type->id;
        $response = $this->patch(route('admin-payment.type.update',$data,$header));
        $response->assertOk();
        $response->json()['data'];
    }

    /**
     * delete payment type test
     * @test
     */
    public function delete_payment_type_admin()
    {
        $header = $this->getHeaders();
        $payment_type = PaymentType::query()->first();
        $data['id'] = $payment_type->id;
        $response = $this->delete(route('admin-payment.type.delete',$data,$header));
        $response->assertOk();
        $response->json()['data'];
    }

    /**
     * create payment type test
     * @test
     */
    public function create_payment_type_admin()
    {
        $header = $this->getHeaders();
        $data['name'] = "test";
        $data['is_active'] = true;
        $response = $this->post(route('admin-payment.type.store',$data,$header));
        $response->assertOk();
        $response->json()['data'];
    }

}
