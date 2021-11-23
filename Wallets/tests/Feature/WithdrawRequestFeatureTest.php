<?php


namespace Wallets\tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Kyc\Services\Grpc\Acknowledge;
use MLM\Services\Grpc\Rank;
use Payments\Services\Processors\PayoutFacade;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;
use Kyc\Services\KycClientFacade;
use MLM\Services\MlmClientFacade;
use Wallets\tests\WalletTest;

class WithdrawRequestFeatureTest extends WalletTest
{
    private function addPaymentCurrency()
    {
        return \Payments\Models\PaymentCurrency::query()->firstOrCreate([
            'name' => 'BTC',
            'is_active' => true,
            'available_services' => [CURRENCY_SERVICE_WITHDRAW]
        ]);
    }

    /**
     * @test
     */
    public function get_withdrawal_requests_list()
    {
        $response = $this->json('GET', route('wallets.customer.withdrawRequests.index'), [
            'statuses' => [
                1
            ]
        ]);
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
    public function ask_withdraw_request_preview_insufficient_balance()
    {
        Mail::fake();
        $this->mockWithdrawServices();
        $payment_currency = $this->addPaymentCurrency();
        $response = $this->postJson(route('wallets.customer.withdrawRequests.preview'), [
            'amount' => 101,
            'currency' => $payment_currency->name,
            'transaction_password' => '123'
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
    public function ask_withdraw_request_preview_sufficient_balance()
    {
        Mail::fake();
        $payment_currency = $this->addPaymentCurrency();
        $this->mockWithdrawServices();

        $bank_service = new BankService($this->user);
        $bank_service->deposit(WALLET_NAME_EARNING_WALLET, 30000);

        $response = $this->postJson(route('wallets.customer.withdrawRequests.preview'), [
            'amount' => 101,
            'currency' => $payment_currency->name,
            'transaction_password' => '123'
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
        $this->mockWithdrawServices();
        $payment_currency = $this->addPaymentCurrency();
        $response = $this->postJson(route('wallets.customer.withdrawRequests.create'), [
            'amount' => 101,
            'currency' => $payment_currency->name,
            'transaction_password' => 'password'
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
        $this->mockWithdrawServices();
        $payment_currency = $this->addPaymentCurrency();

        $bank_service = new BankService($this->user);
        $bank_service->deposit(WALLET_NAME_EARNING_WALLET, 300000);

        $response = $this->postJson(route('wallets.customer.withdrawRequests.create'), [
            'amount' => 101,
            'currency' => $payment_currency->name,
            'transaction_password' => 'password'
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
                'act_reason',
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


    /**
     * @test
     */
    public function process_withdraw_request()
    {
        $this->create_withdraw_request_sufficient_balance();
        $withdraw_request = WithdrawProfit::query()->first();
        PayoutFacade::shouldReceive('pay')->andReturn([true,null]);
        $response = $this->patch(route('wallets.admin.withdraw-requests.update'), [
            'ids' => [$withdraw_request->uuid],
            'status' => WALLET_WITHDRAW_COMMAND_PROCESS,
            'act_reason' => 'Nothing to say',
        ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function reject_withdraw_request()
    {
        $this->process_withdraw_request();
        $withdraw_request = WithdrawProfit::query()->first();
        $response = $this->patch(route('wallets.admin.withdraw-requests.update'), [
            'ids' => [$withdraw_request->uuid],
            'status' => WALLET_WITHDRAW_COMMAND_REJECT,
            'act_reason' => 'Nothing to say',
        ]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function revert_withdraw_request()
    {
        $this->mockWithdrawServices();
        $this->reject_withdraw_request();
        $withdraw_request = WithdrawProfit::query()->where('status', WALLET_WITHDRAW_COMMAND_REJECT)->first();
        $response = $this->patch(route('wallets.admin.withdraw-requests.update'), [
            'ids' => [$withdraw_request->uuid],
            'status' => WALLET_WITHDRAW_COMMAND_REVERT,
        ]);
        $response->assertStatus(200);
    }

    private function mockWithdrawServices(): void
    {
        $mock_response = new class {
            function ok()
            {
                return true;
            }

            function json()
            {
                $data['USD']['15m'] = 51000;
                return $data;
            }
        };
        Http::shouldReceive('get')->andReturn($mock_response);

        $mock_rank = new Rank();
        $mock_rank->setWithdrawalLimit(35000);
        MlmClientFacade::shouldReceive('getUserRank')->andReturn($mock_rank);

        $mock_acknowledge = new Acknowledge();
        $mock_acknowledge->setStatus(true);
        $mock_acknowledge->setMessage('Its true');
        KycClientFacade::shouldReceive('checkKYCStatus')->andReturn($mock_acknowledge);

        $this->mockTransactionPasswordGrpcRequest();

    }


}
