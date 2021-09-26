<?php

namespace Packages\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Packages\Http\Requests\Admin\Package\PackageCommissionDeleteRequest;
use Packages\Http\Requests\Admin\Package\PackageCommissionEditRequest;
use Packages\Services\PackageService;

class PackageIndirectCommissionController extends Controller
{
    private $package_service;

    public function __construct(PackageService $package_service)
    {
        $this->package_service = $package_service;
    }

    /**
     * Add or Edit package commission
     * @group
     * Admin User > Packages
     */
    public function edit(PackageCommissionEditRequest $request)
    {
        $this->package_service->addOrEditPackageCommission($request->get('package_id'),$request->get('level'),$request->get('percentage'));
        return api()->success('packages.successfully-edited-commission');
    }

    /**
     * Delete package commission
     * @group
     * Admin User > Packages
     */
    public function delete(PackageCommissionDeleteRequest $request)
    {
         $this->package_service->deletePackageCommission($request->get('package_id'),$request->get('level'));
        return api()->success('packages.successfully-deleted');
    }


}
