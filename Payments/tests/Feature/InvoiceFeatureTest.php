<?php

namespace Payments\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Payments\Models\Invoice;
use Payments\tests\PaymentTest;
use Wallets\Models\Transaction;

class InvoiceFeatureTest extends PaymentTest
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function admin_fetch_invoices_list()
    {
        $response = $this->get(route('payments.admin.invoices.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination' => [
                    'total',
                    'per_page'
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function admin_fetch_over_paid_invoices_list()
    {
        $response = $this->get(route('payments.admin.invoices.over-paid-invoices'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination' => [
                    'total',
                    'per_page'
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function admin_refund_an_over_paid_invoice()
    {
        $invoice = $this->createOverPaidInvoice();
        $response = $this->postJson(route('payments.admin.invoices.refund-over-paid-invoice'),[
            'transaction_id' => $invoice->transaction_id
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('invoices',[
            'id' => $invoice->id,
            'refunder_user_id' => $this->user->id,
            'refund_status' => 'user'
        ]);
        $this->assertDatabaseHas('transactions',[
            'payable_type' => 'User\Models\User',
            'payable_id' => $this->user_2->id,
            'type' => 'deposit',
            'amount' => $invoice->getRefundableAmount() * 100,
            'confirmed' => 1
        ]);
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * @test
     */
    public function admin_cancel_an_over_paid_invoice()
    {
        $invoice = $this->createOverPaidInvoice();
        $response = $this->postJson(route('payments.admin.invoices.cancel-over-paid-invoice'),[
            'transaction_id' => $invoice->transaction_id
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('invoices',[
            'id' => $invoice->id,
            'refunder_user_id' => $this->user->id,
            'refund_status' => 'admin'
        ]);
        $this->assertDatabaseHas('transactions',[
            'payable_type' => 'User\Models\User',
            'payable_id' => 1,
            'wallet_id' => 1,
            'type' => 'deposit',
            'amount' => $invoice->getRefundableAmount() * 100,
            'confirmed' => 1
        ]);
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * @test
     */
    public function admin_fetch_pending_invoices_for_orders()
    {
        $response = $this->get(route('payments.admin.invoices.pending-order-invoice'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination' => [
                    'total',
                    'per_page'
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function admin_fetch_pending_invoices_for_deposit_wallet()
    {
        $response = $this->get(route('payments.admin.invoices.pending-wallet-invoice'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination' => [
                    'total',
                    'per_page'
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function admin_fetch_an_invoice()
    {
        $invoice = $this->createOverPaidInvoice();
        $response = $this->post(route('payments.admin.invoices.show'),[
            'transaction_id' => $invoice->transaction_id
        ]);
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    private function createOverPaidInvoice() : Invoice
    {
        return Invoice::query()->create([
            'payable_type' => 'Order',
            'payable_id' => 1,
            'user_id' => $this->user_2->id,
            'pf_amount' => 100,
            'amount' => 1,
            'transaction_id' => Str::random(12),
            'checkout_link' => Str::random(30),
            'status' => 'Settled',
            'additional_status' => 'PaidOver',
            'is_paid' => 1,
            'paid_amount' => 50000,
            'deposit_amount' => 50,
            'rate' => 1,
            'payment_type' => 'purchase',
            'payment_currency' => 'BTC',
            'payment_driver' => 'btc-pay-server'
        ]);
    }
}
