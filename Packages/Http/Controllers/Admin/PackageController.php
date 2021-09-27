<?php

namespace Packages\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Packages\Http\Requests\Admin\Package\PackageCreateRequest;
use Packages\Http\Requests\Admin\Package\PackageDeleteRequest;
use Packages\Http\Requests\Admin\Package\PackageEditRequest;
use Packages\Http\Requests\Admin\Package\PackageTypeFilterRequest;
use Packages\Http\Resources\PackageResource;
use Packages\Services\Grpc\Id;
use Packages\Services\Grpc\Package;
use Packages\Services\PackageService;

class PackageController extends Controller
{
    private $package_service;

    public function __construct(PackageService $package_service)
    {
        $this->package_service = $package_service;
    }

    /**
     * Edit package
     * @group
     * Admin User > Packages
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
     * Create package
     * @group
     * Admin User > Packages
     * @param PackageEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(PackageCreateRequest $request)
    {
        $package = new Package();
        $package->setName($request->get('name'));
        $package->setBinaryPercentage($request->get('binary_percentage'));
        $package->setCategoryId($request->get('category_id'));
        $package->setDirectPercentage($request->get('direct_percentage'));
        $package->setPrice($request->get('price'));
        $package->setRoiPercentage($request->get('roi_percentage'));
        $package->setShortName($request->get('short_name'));
        $package->setValidityInDays($request->get('validity_in_days'));
        $updatePackage = $this->package_service->createPackage($package);
        return api()->success('packages.successfully-created',new PackageResource($updatePackage));
    }
    /**
     * Delete package
     * @group
     * Admin User > Packages
     * @param PackageEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(PackageDeleteRequest $request)
    {
        $this->package_service->deletePackage($request->get('package_id'));
        return api()->success('packages.successfully-deleted');
    }
    /**
     * Count Package Between two date
     * @group
     * Admin User > Packages
     * @param PackageEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountPackageByDate(PackageTypeFilterRequest $request)
    {
        return api()->success('packages.successfully-fetched-all-packages',($this->package_service->getCountPackageByDate($request->type)));
    }



}
