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
use User\Services\GatewayClientFacade;
use User\UserConfigure;
use Wallets\Services\Grpc\Deposit;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\WalletService;

class GiftcodeTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    /**@var $user User*/
    public $user;
    /**@var $user_2 User*/
    public $user_2;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');

        UserConfigure::seed();
        GiftCodeConfigure::seed();
        $this->prepareUsersAndMockGatewayFacade();
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

    private function prepareUsersAndMockGatewayFacade()
    {
        if(defined('USER_ROLES'))
            foreach (USER_ROLES as $role)
                Role::query()->firstOrCreate(['name' => $role]);
        $this->user = User::factory()->create();
        $this->user->refresh();
        $this->user->assignRole([USER_ROLE_CLIENT,USER_ROLE_SUPER_ADMIN]);
        $this->user_2 = User::factory()->create();
        $this->user_2->refresh();
        $this->user_2->assignRole([USER_ROLE_CLIENT,USER_ROLE_SUPER_ADMIN]);
        GatewayClientFacade::shouldReceive('getUserById')->andReturn($this->user->refresh()->getGrpcMessage());
    }

    private function getHeaders()
    {
        return [
            'X-user-id' => $this->user->refresh()->id,
            'X-user-hash' => md5(serialize($this->user->getGrpcMessage())),
        ];
    }

    protected function deposit_user()
    {
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_object = app(Deposit::class);
        $deposit_object->setUserId($this->user->id);
        $deposit_object->setAmount(10000000);
        $deposit_object->setWalletName(WalletNames::DEPOSIT);
        $deposit_object->setType('Deposit');

        //Deposit transaction
        $deposit = $wallet_service->deposit($deposit_object);

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
            'user_id' => $this->user->id,
            'include_registration_fee' => true,
            'wallet' => 'Deposit Wallet'
        ]);

    }
}
