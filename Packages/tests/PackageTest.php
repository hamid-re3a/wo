<?php


namespace Packages\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Packages\PackageConfigure;
use Tests\CreatesApplication;
use Tests\TestCase;
use User\Models\User;
use User\UserConfigure;

class PackageTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');
        PackageConfigure::seed();
        UserConfigure::seed();
        $this->withHeaders($this->getHeaders());
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
        $user->assignRole([USER_ROLE_CLIENT,USER_ROLE_ADMIN_SUBSCRIPTIONS_PACKAGE]);
        $hash = md5(serialize($user->getGrpcMessage()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }
}
