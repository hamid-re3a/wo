<?php

namespace Packages\Http\Controllers\Front;

use Packages\Http\Resources\PackageResource;
use Illuminate\Routing\Controller;
use Packages\Models\Package;

class PackageController extends Controller
{
    /**
     * All packages
     * @group
     * Admin > User
     * @unathenticated
     */
    public function index()
    {
        return api()->success('packages.successfully-fetched-all-packages',PackageResource::collection(Package::query()->get()));
    }

}
