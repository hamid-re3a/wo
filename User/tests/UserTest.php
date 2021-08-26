<?php


namespace User\tests;


use Illuminate\Support\Facades\Artisan;
use Packages\PackageConfigure;
use Payments\PaymentConfigure;
use Tests\CreatesApplication;

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
    }

    public function hasMethod($class, $method)
    {
        $this->assertTrue(
            method_exists($class, $method),
            "$class must have method $method"
        );
    }
}
