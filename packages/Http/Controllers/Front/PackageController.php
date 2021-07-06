<?php

namespace Packages\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Packages\Models\Package;

class PackageController extends Controller
{
    /**
     * Activate Or Deactivate User Account
     * @group
     * Admin > User
     */
    public function index()
    {
        return api()->success('packages.successfully-fetched-all-packages',Package::query()->get());
    }

}
