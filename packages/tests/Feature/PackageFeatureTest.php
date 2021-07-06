<?php


namespace Packages\Feature;


use Packages\tests\PackageTest;

class PackageFeatureTest extends PackageTest
{

    /**
     * @test
     */

    public function return_package_list_green()
    {
        $response =$this->get(route('packages.index'));
        $response->assertOk();
        $response->json()['data'];
    }
}
