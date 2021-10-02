<?php


namespace Giftcode\tests;


use Giftcode\GiftCodeConfigure;
use Giftcode\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Packages\PackageConfigure;
use Payments\PaymentConfigure;
use Spatie\Permission\Models\Role;
use Tests\CreatesApplication;
use Tests\TestCase;
use User\Models\User;
use User\UserConfigure;
use Wallets\Services\Grpc\Deposit;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\WalletService;

class GiftcodeTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');

        UserConfigure::seed();
        GiftCodeConfigure::seed();
        $this->withHeaders($this->getHeaders());
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

        $user = User::query()->first();
        $hash = md5(serialize($user->getUserService()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }

    protected function deposit_user()
    {
        $user = User::query()->first();
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_object = app(Deposit::class);
        $deposit_object->setUserId($user->id);
        $deposit_object->setAmount(10000000);
        $deposit_object->setWalletName(WalletNames::DEPOSIT);
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
                'packages_cost_in_pf',
                'registration_fee_in_pf',
                'total_cost_in_pf',
                'created_at',
            ]
        ];
    }

    protected function createGiftCode()
    {

        $this->deposit_user();
        return $this->postJson(route('giftcodes.customer.create'), [
            'package_id' => 1,
            'user_id' => 1,
            'include_registration_fee' => true,
            'wallet' => 'Deposit Wallet'
        ]);

    }
}
