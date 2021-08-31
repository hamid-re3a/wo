<?php


namespace Payments\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Packages\Models\Package;
use Packages\PackageConfigure;
use Payments\PaymentConfigure;
use Tests\CreatesApplication;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use CreatesApplication;
//    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
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
        return [
            'X-user-id' => '1',
            'X-user-first-name' => 'Admin',
            'X-user-last-name' => 'Admin',
            'X-user-email' => 'admin@site.com',
            'X-user-username' => 'Admin',
            'X-user-member-id' => '1000',
            'X-user-sponsor-id' => '',
            'X-user-block-type' => '',
            'X-user-is-freeze' => '',
            'X-user-is-deactivate' => '',
        ];
    }
}
