<?php


namespace Wallets\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
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
