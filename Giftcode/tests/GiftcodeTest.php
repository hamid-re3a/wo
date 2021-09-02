<?php


namespace Giftcode\tests;


use Giftcode\GiftCodeConfigure;
use Giftcode\Jobs\UpdatePackages;
use Giftcode\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

class GiftcodeTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');
        $this->withHeaders($this->getHeaders());
        GiftCodeConfigure::seed();
        Package::query()->firstOrCreate([
            "id" => 1,
            "name" => 'Beginner',
            "short_name" => 'B',

            "roi_percentage" => 1,
            "direct_percentage" => 8,
            "binary_percentage" => 7,

            "package_validity_in_days" => 200,
        ]);
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
