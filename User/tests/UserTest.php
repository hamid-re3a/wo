<?php


namespace User\tests;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Packages\PackageConfigure;
use Payments\PaymentConfigure;
use Tests\CreatesApplication;
use User\Models\User;

class UserTest extends \Tests\TestCase
{
    use CreatesApplication;
//    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
        PackageConfigure::seed();
        PaymentConfigure::seed();
        $this->app->setLocale('en');
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
        $hash = Hash::make(serialize($user->getGrpcMessage()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }
}
