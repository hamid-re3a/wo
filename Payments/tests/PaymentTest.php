<?php


namespace Payments\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Packages\Models\Package;
use Packages\PackageConfigure;
use Payments\PaymentConfigure;
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
        $user = User::query()->first();
        $hash = Hash::make(serialize($user->getUserService()));
        return [
            'X-user-id' => '1',
            'X-user-hash' => $hash,
        ];
    }
}
