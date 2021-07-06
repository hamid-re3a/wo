<?php


namespace Packages\tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\PackageConfigure;
use Tests\CreatesApplication;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->app->setLocale('en');
        PackageConfigure::seed();
    }

    public function hasMethod($class, $method)
    {
        $this->assertTrue(
            method_exists($class, $method),
            "$class must have method $method"
        );
    }
}
