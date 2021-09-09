<?php

namespace Payments\tests\Feature;

use Illuminate\Support\Facades\Mail;
use Payments\Mail\Payment\EmailInvoiceCreated;
use Payments\Mail\Payment\EmailInvoicePaidComplete;
use Payments\Services\Invoice;
use Payments\tests\PaymentTest;
use User\Mail\User\TooManyLoginAttemptTemporaryBlockedEmail;
use User\Services\User;

class PaymentFeatureTest extends PaymentTest
{

    /**
     * @test
     */
    public function get_payment_types()
    {
        $response = $this->get(route('payments.type.index'));
        $response->assertOk();
        $response->json()['data'];
    }

    /**
     * @test
     */
    public function submit_order_with_email()
    {

        Mail::fake();
        $response = $this->post(route('orders.store'), [
            'package_id' => 1,
            'to_user_id' => 1,
            'plan' => ORDER_PLAN_START,
            'payment_type' => 'purchase',
            'payment_driver' => 'btc-pay-server',
            'payment_currency' => 'BTC',
        ], [
            'X-user-id' => 1,
            'X-user-hash'=>3,
        ]);
        $response->assertOk();

        Mail::assertSent(EmailInvoiceCreated::class);
    }


    /**
     * @test
     */
    public function get_payment_currencies()
    {
        $response = $this->get(route('payments.currency.index'));
        $response->assertOk();
        $response->json()['data'];
    }

}
