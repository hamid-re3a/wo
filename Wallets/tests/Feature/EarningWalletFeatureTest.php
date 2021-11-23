<?php


namespace Wallets\tests\Feature;


use Illuminate\Support\Facades\Mail;
use User\Models\User;
use Wallets\Services\BankService;
use Wallets\tests\WalletTest;

class EarningWalletFeatureTest extends WalletTest
{

    /**
     * @test
     */
    public function get_earning_wallet()
    {
        Mail::fake();
        $response = $this->get(route('wallets.customer.earning.get-wallet'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'name',
                'balance',
                'transactions_count',
                'transfers_count',
            ]
        ]);
    }

    /**
     * @test
     */
    public function get_transactions_list()
    {
        $response = $this->get(route('wallets.customer.earning.get-transactions'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination' => [
                    'total',
                    'per_page'
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function get_transfers_list()
    {
        $response = $this->get(route('wallets.customer.earning.get-transfers'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'list',
                'pagination' => [
                    'total',
                    'per_page'
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function transfer_fund_preview_sufficient_balance()
    {
        Mail::fake();
        $this->mockTransactionPasswordGrpcRequest();
        $bank_service = new BankService($this->user);
        $bank_service->deposit('Earning Wallet', 30000);

        $response = $this->postJson(route('wallets.customer.earning.transfer-funds-preview'), [
            'amount' => 101,
            'member_id' => $this->user_2->member_id,
            'own_deposit_wallet' => false,
            'transaction_password' => 123
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'receiver_member_id',
                    'receiver_full_name',
                    'received_amount',
                    'transfer_fee',
                    'current_balance',
                    'balance_after_transfer',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function transfer_fund_preview_insufficient_balance()
    {
        Mail::fake();
        $this->mockTransactionPasswordGrpcRequest();
        $response = $this->postJson(route('wallets.customer.earning.transfer-funds-preview'), [
            'amount' => 101,
            'member_id' => $this->user_2->member_id,
            'own_deposit_wallet' => false,
            'transaction_password' => 123
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
    public function transfer_fund_insufficient_balance()
    {
        Mail::fake();
        $this->mockTransactionPasswordGrpcRequest();
        $response = $this->postJson(route('wallets.customer.earning.transfer-funds'), [
            'amount' => 101,
            'member_id' => $this->user_2->member_id,
            'own_deposit_wallet' => false,
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
        $bank_service->deposit('Earning Wallet', 30000);

        $response = $this->postJson(route('wallets.customer.earning.transfer-funds'), [
            'amount' => 101,
            'member_id' => $this->user_2->member_id,
            'own_deposit_wallet' => false,
            'transaction_password' => 123
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'from_user_full_name',
                    'from_user_member_id',
                    'from' => [
                        'transaction_id',
                        'wallet'
                    ],
                    'to_user_full_name',
                    'to_user_member_id',
                    'to' => [
                        'transaction_id',
                        'wallet'
                    ],
                    'amount',
                    'fee',
                    'created_at'
                ]
            ]
        );
    }

}
