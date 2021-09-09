<?php

namespace Packages\Http\Controllers\Admin;

use Packages\Http\Requests\Admin\Package\PackageEditRequest;
use Illuminate\Routing\Controller;
use Packages\Http\Requests\Admin\Package\PackageTypeFilterRequest;
use Packages\Http\Resources\PackageCountByMonthResource;
use Packages\Http\Resources\PackageResource;
use Packages\Services\Id;
use Packages\Services\Package;
use Packages\Services\PackageService;
use Packages\Http\Resources\CountDataResource;

class PackageController extends Controller
{
    private $package_service;

    public function __construct(PackageService $package_service)
    {
        $this->package_service = $package_service;
    }

    /**
     * All packages
     * @group
     * Public User > Packages
     * @unauthenticated
     * @param PackageEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(PackageEditRequest $request)
    {
        $id = new Id();
        $package = new Package();
        $id->setId($request->get('package_id'));
        $package->setName($request->get('name'));
        $package->setBinaryPercentage($request->get('binary_percentage'));
        $package->setCategoryId($request->get('category_id'));
        $package->setDirectPercentage($request->get('direct_percentage'));
        $package->setPrice($request->get('price'));
        $package->setRoiPercentage($request->get('roi_percentage'));
        $package->setShortName($request->get('short_name'));
        $package->setValidityInDays($request->get('validity_in_days'));
        $updatePackage = $this->package_service->editPackage($id,$package);
        return api()->success('packages.successfully-fetched-all-packages',new PackageResource($updatePackage));
    }


    /**
     * Count Package Between two date
     * @group
     * Public User > Packages > admin
     * @unauthenticated
     * @param PackageEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountPackageByDate(PackageTypeFilterRequest $request)
    {
        return api()->success('packages.successfully-fetched-all-packages',($this->package_service->getCountPackageByDate($request->type)));
    }



}
