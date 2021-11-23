<?php


namespace Wallets\tests\Feature;


use Illuminate\Support\Facades\Mail;
use MLM\Services\MlmClientFacade;
use Payments\Services\Processors\PaymentFacade;
use User\Services\GatewayClientFacade;
use User\Services\Grpc\Acknowledge;
use User\Services\Grpc\UserTransactionPassword;
use Wallets\Services\BankService;
use Wallets\tests\WalletTest;

class DepositWalletFeatureTest extends WalletTest
{

    /**
     * @test
     */
    public function get_deposit_wallet()
    {
        Mail::fake();
        $response = $this->get(route('wallets.customer.deposit.get-wallet'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'balance',
                'name',
                'total_received',
                'total_spent',
                'total_transfer',
                'transactions_count',
            ]
        ]);
    }

    /**
     * @test
     */
    public function get_transactions_list()
    {
        $response = $this->get(route('wallets.customer.deposit.get-transactions'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination',
            ],
        ]);
    }

    /**
     * @test
     */
    public function get_transfers_list()
    {
        $response = $this->get(route('wallets.customer.deposit.get-transfers'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data',
                'links',
            ],
        ]);
    }

    /**
     * @test
     */
    public function ask_fund_from_another_member_without_transaction_password()
    {
        Mail::fake();
        $this->mockTransactionPasswordGrpcRequest();
        $response = $this->postJson(route('wallets.customer.deposit.payment-request'),[
            'amount' => 1000,
            'member_id' => $this->user_2->member_id,
        ]);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'transaction_password'
            ]
        ]);
    }

    /**
     * @test
     */
    public function ask_fund_from_another_member_with_valid_transaction_password()
    {
        Mail::fake();
        $this->mockTransactionPasswordGrpcRequest();
        $response = $this->postJson(route('wallets.customer.deposit.payment-request'),[
            'amount' => 1000,
            'member_id' => $this->user_2->member_id,
            'transaction_password' => 123
        ]);
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'amount',
                'receiver_full_name'
            ]
        ]);
    }

    /**
     * @test
     */
    public function transfer_fund_preview_insufficient_balance()
    {
        $this->refreshDatabase();
        $this->mockTransactionPasswordGrpcRequest();
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund-preview'), [
            'amount' => 10000000000000000000000000,
            'member_id' => $this->user_2->member_id,
            'transaction_password' => 123
        ]);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'amount'
            ],
        ]);
    }

    /**
     * @test
     */
    public function transfer_fund_preview_sufficient_balance()
    {
        $this->refreshDatabase();
        $this->mockTransactionPasswordGrpcRequest();
        $bank_service = new BankService($this->user);
        $bank_service->deposit('Deposit Wallet', 30000);

        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund-preview'), [
            'amount' => 101,
            'member_id' => $this->user_2->member_id,
            'transaction_password' => 123
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
                'status',
                'message',
                'data'
            ]
        );
    }

    /**
     * @test
     */
    public function transfer_fund_insufficient_balance()
    {
        $this->refreshDatabase();
        $this->mockTransactionPasswordGrpcRequest();
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund'), [
            'amount' => 1010000000000000,
            'member_id' => $this->user_2->member_id,
            'transaction_password' => 123
        ]);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'amount'
            ],
        ]);
    }

    /**
     * @test
     */
    public function transfer_fund_sufficient_balance()
    {
        Mail::fake();
        $this->mockTransactionPasswordGrpcRequest();
        $bank_service = new BankService($this->user);
        $bank_service->deposit('Deposit Wallet', 30000);
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund'), [
            'amount' => 101,
            'member_id' => $this->user_2->member_id,
            'transaction_password' => 123
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
                'status',
                'message',
                'data'
            ]
        );
    }

    /**
     * @test
     */
    public function deposit_fund()
    {
        $this->refreshDatabase();
        Mail::fake();
        PaymentFacade::shouldReceive('pay')->once()->andReturn([true, [
            "payment_currency" => "nothing",
            "amount" => "nothing",
            "checkout_link" => "nothing",
            "transaction_id" => "nothing",
            "expiration_time" => "nothing",
        ]]);
        $response = $this->postJson(route('wallets.customer.deposit.deposit-funds'), [
            'amount' => 102,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'payment_currency',
                    'amount',
                    'checkout_link',
                    'transaction_id',
                    'expiration_time'
                ]
            ]
        );
    }
}
