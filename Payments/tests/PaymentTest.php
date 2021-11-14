<?php


namespace Payments\tests;

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
//    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
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
        $hash = md5(serialize($user->getGrpcMessage()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }
}
