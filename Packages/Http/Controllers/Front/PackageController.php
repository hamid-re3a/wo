<?php

namespace Packages\Http\Controllers\Front;

use Packages\Http\Resources\PackageResource;
use Illuminate\Routing\Controller;
use Packages\Services\PackageService;

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
     * @queryParam package_exactly optional bool
     */
    public function index()
    {
        return api()->success('packages.successfully-fetched-all-packages',PackageResource::collection($this->package_service->getPackages()));
    }

}
