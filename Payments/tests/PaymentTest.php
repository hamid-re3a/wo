<?php


namespace Payments\tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Packages\PackageConfigure;
use Payments\PaymentConfigure;
use Spatie\Permission\Models\Role;
use Tests\CreatesApplication;
use Tests\TestCase;
use User\UserConfigure;
use User\Models\User;

class PaymentTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected $user;
    protected $user_2;

    public function setUp(): void
    {
        parent::setUp();
        UserConfigure::seed();
        $this->withHeaders($this->getHeaders());
        PackageConfigure::seed();
        PaymentConfigure::seed();
        $this->app->setLocale('en');
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
        if (defined('USER_ROLES'))
            foreach (USER_ROLES as $role)
                Role::query()->firstOrCreate(['name' => $role]);

        Artisan::call('db:seed', ['--class' => "Wallets\database\seeders\UserWalletTableSeeder"]);
        $this->user = User::factory()->create();
        $this->user->assignRole([USER_ROLE_CLIENT, USER_ROLE_SUPER_ADMIN]);
        $this->user_2 = User::factory()->create();
        $this->user_2->assignRole([USER_ROLE_CLIENT, USER_ROLE_SUPER_ADMIN]);
        return [
            'X-user-id' => $this->user->refresh()->id,
            'X-user-hash' => md5(serialize($this->user->getGrpcMessage())),
        ];
    }
}
