<?php


namespace Wallets\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use User\Models\User;
use Wallets\WalletConfigure;
use Tests\CreatesApplication;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');
        $this->withHeaders($this->getHeaders());
        WalletConfigure::seed();
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
        if(defined('USER_ROLES'))
            foreach (USER_ROLES as $role)
                Role::query()->firstOrCreate(['name' => $role]);
        $user->assignRole([USER_ROLE_CLIENT,USER_ROLE_SUPER_ADMIN]);
        $hash = md5(serialize($user->getUserService()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }
}
