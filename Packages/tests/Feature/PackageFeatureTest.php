<?php


namespace Packages\tests\Feature;


use Packages\tests\PackageTest;

class PackageFeatureTest extends PackageTest
{

    /**
     * @test
     */

    public function return_package_list_green()
    {
        $response =$this->get(route('packages.index'), [
            'X-user-id' => 1,
            'X-user-first-name' => 'admin',
            'X-user-last-name' => 'ni',
            'X-user-email' => 'admin@site.com',
            'X-user-username' => 'admin',
        ]);
        $response->assertOk();
        $response->json()['data'];
    }
}
