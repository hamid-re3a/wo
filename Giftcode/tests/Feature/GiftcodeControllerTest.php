<?php


namespace Wallets\tests\Feature;


use Giftcode\Models\Package;
use Giftcode\tests\GiftcodeTest;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\WalletService;

class GiftcodeControllerTest extends GiftcodeTest
{

    /**
     * @test
     */
    public function create_new_giftcode()
    {
        $response = $this->createGiftCode();
        $response->assertOk();
        $response->assertJsonStructure($this->getGiftcodeJsonStructure());
    }


    /**
     * @test
     */
    public function get_gift_code()
    {
        $response = $this->createGiftCode();
        $response->assertOk();
        $uuid = $response->json()['data']['id'];
        $response = $this->getJson(route('giftcodes.show', [
            'uuid' => $uuid
        ]));
        $response->assertOk();
        $response->assertOk();
        $response->assertJsonStructure($this->getGiftcodeJsonStructure());
    }

    /**
     * @test
     */
    public function cancel_gift_code()
    {
        $response = $this->createGiftCode();
        $response->assertOk();
        $uuid = $response->json()['data']['id'];
        $response = $this->postJson(route('giftcodes.cancel'), [
            'id' => $uuid
        ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function redeem_gift_code()
    {

        $response = $this->createGiftCode();
        $response->assertOk();
        $uuid = $response->json()['data']['id'];
        $response = $this->postJson(route('giftcodes.redeem'), [
            'id' => $uuid
        ]);
        $response->assertOk();

    }

    private function getTransaction($confirmed = TRUE)
    {
        $transaction_service = app(Transaction::class);
        $transaction_service->setConfiremd($confirmed);
        $transaction_service->setAmount(10000);
        $transaction_service->setToWalletName('Deposit Wallet');
        $transaction_service->setToUserId(1);
        $transaction_service->setType('Deposit');
        $transaction_service->setDescription(serialize([
            'description' => 'Deposit Test #12'
        ]));

        return $transaction_service;
    }

    private function deposit_user()
    {
        $user = User::factory()->create();
        $user_object = $user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($user_object);

        //Deposit transaction
        $deposit_transaction = $wallet_service->deposit($deposit_service);
        return $user;

    }

    private function getGiftcodeJsonStructure()
    {
        return [
            'status',
            'message',
            'data' => [
                'id',
                'package_name',
                'code',
                'expiration_date',
                'packages_cost_in_usd',
                'registration_fee_in_usd',
                'total_cost_in_usd',
                'created_at',
            ]
        ];
    }

    private function createGiftCode()
    {
        $this->deposit_user();
        $package = Package::create();
        return $this->postJson(route('giftcodes.create'), [
            'package_id' => $package->id,
            'include_registration_fee' => true,
            'wallet' => 'Deposit Wallet'
        ]);

    }

}
