<?php


namespace Wallets\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Payments\PaymentConfigure;
use Spatie\Permission\Models\Role;
use User\Models\User;
use User\Services\GatewayClientFacade;
use Wallets\database\seeders\UserWalletTableSeeder;
use Wallets\WalletConfigure;
use Tests\CreatesApplication;
use Tests\TestCase;

class WalletTest extends TestCase
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
        $this->prepareUsersAndMockGatewayFacade();
        $this->withHeaders($this->getHeaders());
        $this->app->setLocale('en');
        WalletConfigure::seed();

        PaymentConfigure::seed(); }

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

        Artisan::call('db:seed', ['--class' => "Wallets\database\seeders\UserWalletTableSeeder"]);
        $this->user = User::factory()->create();
        $this->user->assignRole([USER_ROLE_CLIENT,USER_ROLE_SUPER_ADMIN]);
        $this->user_2 = User::factory()->create();
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
}
