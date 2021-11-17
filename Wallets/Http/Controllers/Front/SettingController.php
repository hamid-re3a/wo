<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Models\Setting;

class SettingController extends Controller
{
    /**
     * Settings list
     * @group Public User > Settings
     */
    public function index()
    {
        return api()->success(null,Setting::query()->select(['name','value'])->get()->toArray());
    }

}
