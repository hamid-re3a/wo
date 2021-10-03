<?php


namespace Wallets\tests\Feature;


use Illuminate\Support\Facades\Mail;
use Payments\Services\Grpc\PaymentCurrency;
use Payments\Services\PaymentService;
use Payments\Services\Processors\PaymentFacade;
use User\Models\User;
use Wallets\Services\BankService;
use Wallets\tests\WalletTest;

class WithdrawRequestFeatureTest extends WalletTest
{
    private function addPaymentCurrency()
    {
        $payment_service = app(PaymentService::class);
        $payment_currency = app(PaymentCurrency::class);
        $payment_currency->setIsActive(true);
        $payment_currency->setName('BTC');
        return $payment_service->createPaymentCurrency($payment_currency);
    }

    /**
     * @test
     */
    public function get_withdrawal_requests_list()
    {
        $response = $this->get(route('wallets.customer.withdrawRequests.index'));
        $response->assertStatus(200);
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
    public function withdraw_request_preview_insufficient_balance()
    {
        Mail::fake();
        $payment_currency = $this->addPaymentCurrency();
        $response = $this->postJson(route('wallets.customer.withdrawRequests.preview'), [
            'amount' => 101,
            'currency' => $payment_currency->getName(),
        ]);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'amount'
            ]
        ]);
    }

    /**
     * @test
     */
    public function withdraw_request_preview_sufficient_balance()
    {
        Mail::fake();
        $payment_currency = $this->addPaymentCurrency();
        $user = User::query()->where('username', '=', 'admin')->first();
        $bank_service = new BankService($user);
        $bank_service->deposit('Earning Wallet', 30000);

        $response = $this->postJson(route('wallets.customer.withdrawRequests.preview'), [
            'amount' => 101,
            'currency' => $payment_currency->getName(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'fee',
                'pf_amount',
                'crypto_amount',
                'wallet_hash',
                'currency'
            ]
        ]);
    }

    /**
     * @test
     */
    public function create_withdraw_request_insufficient_balance()
    {
        Mail::fake();
        $payment_currency = $this->addPaymentCurrency();
        $response = $this->postJson(route('wallets.customer.withdrawRequests.create'), [
            'amount' => 101,
            'currency' => $payment_currency->getName(),
        ]);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'amount'
            ]
        ]);
    }

    /**
     * @test
     */
    public function create_withdraw_request_sufficient_balance()
    {
        Mail::fake();
        $payment_currency = $this->addPaymentCurrency();
        $user = User::query()->where('username', '=', 'admin')->first();
        $bank_service = new BankService($user);
        $bank_service->deposit('Earning Wallet', 30000);

        $response = $this->postJson(route('wallets.customer.withdrawRequests.create'), [
            'amount' => 101,
            'currency' => $payment_currency->getName(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'user_member_id',
                'user_full_name',
                'withdraw_transaction_id',
                'refund_transaction_id',
                'actor_full_name',
                'rejection_reason',
                'wallet_hash',
                'currency',
                'crypto_rate',
                'fee',
                'pf_amount',
                'crypto_amount',
                'status',
                'created_at',
            ]
        ]);
    }


}
