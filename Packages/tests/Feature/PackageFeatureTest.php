<?php


namespace Packages\tests\Feature;


use Packages\Models\Package;
use Packages\tests\PackageTest;

class PackageFeatureTest extends PackageTest
{

    /**
     * @test
     */

    public function return_package_list_green()
    {
        $response = $this->get(route('customer.packages.index'));
        $response->assertOk();
        $response->json()['data'];
    }


    /**
     * @test
     */

    public function package_commission_edit()
    {

        $response = $this->post(route('admin.packages.create-or-edit-package-commission'),[
            'package_id' => '1',
            'level' => '1',
            'percentage' => '2',
        ]);
        $response = $this->post(route('admin.packages.create-or-edit-package-commission'),[
            'package_id' => '1',
            'level' => '2',
            'percentage' => '2',
        ]);
        $response->assertOk();
        $this->assertCount(2,Package::query()->find(1)->packageIndirectCommission);
        $response->json()['data'];
    }
}
