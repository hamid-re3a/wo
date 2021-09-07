<?php


namespace Giftcode\tests;


use Giftcode\GiftCodeConfigure;
use Giftcode\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;
use Tests\TestCase;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;

class GiftcodeTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');
        $this->withHeaders($this->getHeaders());
        GiftCodeConfigure::seed();
        Package::query()->firstOrCreate([
            "id" => 1,
            "name" => 'Beginner',
            "short_name" => 'B',
            "validity_in_days" => 200,
            "price" => 99,
        ]);
    }

    public function hasMethod($class, $method)
    {
        $this->assertTrue(
            method_exists($class, $method),
            "$class must have method $method"
        );
    }

    public function getHeaders()
    {
        User::query()->firstOrCreate([
            'id' => '1',
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'member_id' => 1000,
            'email' => 'work@sajidjaved.com',
            'username' => 'admin',
        ]);
        $user = User::query()->first();
        $hash = Hash::make(serialize($user->getUserService()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }

    protected function getTransaction($confirmed = TRUE)
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

    protected function deposit_user()
    {
        $user = User::query()->first();
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_object = app(Deposit::class);
        $deposit_object->setConfirmed(true);
        $deposit_object->setUserId($user->id);
        $deposit_object->setAmount(10000000);
        $deposit_object->setWalletName('Deposit Wallet');
        $deposit_object->setType('Deposit');

        //Deposit transaction
        $deposit = $wallet_service->deposit($deposit_object);

        return $user;

    }

    protected function getGiftcodeJsonStructure()
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

    protected function createGiftCode()
    {
        $this->deposit_user();

        return $this->postJson(route('giftcodes.create'), [
            'package_id' => 1,
            'include_registration_fee' => true,
            'wallet' => 'Deposit Wallet'
        ]);

    }
}
