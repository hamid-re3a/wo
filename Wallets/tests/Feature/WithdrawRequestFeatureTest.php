<?php


namespace Wallets\tests\Feature;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Kyc\Services\Grpc\Acknowledge;
use MLM\Services\Grpc\Rank;
use User\Models\User;
use Wallets\Services\BankService;
use Wallets\Services\KycClientFacade;
use Wallets\Services\MlmClientFacade;
use Wallets\tests\WalletTest;

class WithdrawRequestFeatureTest extends WalletTest
{
    private function addPaymentCurrency()
    {
        return \Payments\Models\PaymentCurrency::query()->firstOrCreate([
            'name' => 'BTC',
            'is_active' => true,
        ]);
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
            'currency' => $payment_currency->name,
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
        $this->mockWithdrawServices();


        $user = User::query()->where('username', '=', 'admin')->first();
        $bank_service = new BankService($user);
        $bank_service->deposit(WALLET_NAME_EARNING_WALLET, 30000);

        $response = $this->postJson(route('wallets.customer.withdrawRequests.preview'), [
            'amount' => 101,
            'currency' => $payment_currency->name,
        ]);
        dd($response->json());
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
        $user = User::query()->where('username', '=', 'admin')->first();
        $bank_service = new BankService($user);
        $bank_service->deposit('Earning Wallet', 30000);

        $response = $this->postJson(route('wallets.customer.withdrawRequests.create'), [
            'amount' => 101,
            'currency' => $payment_currency->name,
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

        $payment = \Payments\Models\PaymentCurrency::query()->firstOrCreate();
        $payment->update(['available_services'=>[CURRENCY_SERVICE_WITHDRAW]]);
    }


}
