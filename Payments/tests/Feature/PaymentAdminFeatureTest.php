<?php

namespace Payments\tests\Feature;

use Payments\Models\PaymentCurrency;
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
        $response = $this->get(route('payments.admin.type.index'));
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
        $response = $this->patch(route('payments.admin.type.update',$data,$header));
        $response->assertOk();
        $response->json()['data'];
    }


    /**
     * edit payment currency
     * @test
     */
    public function edit_payment_currency_admin()
    {
        $payment_currency = PaymentCurrency::query()->first();
        $data['name'] = 'btc';
        $data['is_active'] = true;
        $data['id'] = $payment_currency->id;
        $data['available_services'] = ['purchase'];
        $response = $this->patch(route('payments.admin.currency.update',$data));
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
        $response = $this->delete(route('payments.admin.type.delete',$data,$header));
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
        $response = $this->post(route('payments.admin.type.store',$data,$header));
        $response->assertOk();
        $response->json()['data'];
    }

}
