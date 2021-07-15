<?php

namespace User\Http\Controllers\Front;


use User\Models\Setting;
use Illuminate\Routing\Controller;

class SettingController extends Controller
{
    /**
     * Get All Settings
     * @group
     * General
     * @unauthenticated
     */
    public function index()
    {
        return api()->success(trans('responses.ok'),Setting::all());
    }
}
