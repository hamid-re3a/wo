<?php


namespace Wallets\tests\Feature;


use Illuminate\Support\Facades\Mail;
use User\Models\User;
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
    public  function get_transactions_list()
    {
        $response = $this->get(route('wallets.customer.deposit.get-transactions'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data',
                'links',
                'meta'
            ],
        ]);
    }

    /**
     * @test
     */
    public  function get_transfers_list()
    {
        $response = $this->get(route('wallets.customer.deposit.get-transfers'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data',
                'links',
                'meta'
            ],
        ]);
    }

    /**
     * @test
     */
    public  function transfer_fund_preview_insufficient_balance()
    {
        $user_2 = User::factory()->create();
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund-preview'),[
            'amount' => 101,
            'member_id' => $user_2->member_id
        ]);
        $response->assertStatus(406);
        $response->assertJsonStructure([
            'status',
            'message',
            'data',
            'errors' => [
                'subject'
            ]
        ]);
    }

    /**
     * @test
     */
    public function transfer_fund_preview_sufficient_balance()
    {
        Mail::fake();
        $user_1 = User::query()->where('username','=','admin')->first();
        $bank_service = new BankService($user_1);
        $bank_service->deposit('Deposit Wallet',30000);

        $user_2 = User::factory()->create();
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund-preview'),[
            'amount' => 101,
            'member_id' => $user_2->member_id
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

        $user_2 = User::factory()->create();
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund'),[
            'amount' => 101,
            'member_id' => $user_2->member_id
        ]);
        $response->assertStatus(406);
        $response->assertJsonStructure([
            'status',
            'message',
            'data',
            'errors' => [
                'subject'
            ]
        ]);
    }

    /**
     * @test
     */
    public function transfer_fund_sufficient_balance()
    {
        Mail::fake();
        $user_1 = User::query()->where('username','=','admin')->first();
        $bank_service = new BankService($user_1);
        $bank_service->deposit('Deposit Wallet',30000);

        $user_2 = User::factory()->create();
        $response = $this->postJson(route('wallets.customer.deposit.transfer-fund'),[
            'amount' => 101,
            'member_id' => $user_2->member_id
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
        Mail::fake();
        $response = $this->postJson(route('wallets.customer.deposit.deposit-funds'),[
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
