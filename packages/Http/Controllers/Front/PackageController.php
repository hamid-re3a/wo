<?php

namespace Packages\Http\Controllers\Front;

use ApiGatewayUser\Http\Resources\PackageResource;
use Illuminate\Routing\Controller;
use Packages\Models\Package;

class PackageController extends Controller
{
    /**
     * All packages
     * @group
     * Admin > User
     */
    public function index()
    {
        return api()->success('packages.successfully-fetched-all-packages',PackageResource::collection(Package::query()->get()));
    }

}
