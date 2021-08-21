<?php


namespace Giftcode\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\Admin\UpdateSettingRequest;
use Giftcode\Http\Resources\Admin\SettingResource;
use Giftcode\Models\Setting;

class SettingController extends Controller
{

    /**
     * Get settings list
     * @group Admin > Giftcode
     */
    public function index()
    {
        $settings = Setting::all();
        return api()->success(null,SettingResource::collection($settings));
    }

    /**
     * Update setting
     * @group Admin > Giftcode
     * @param UpdateSettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSettingRequest $request)
    {
        $setting = Setting::query()->whereName($request->get('name'))->first();
        $setting->update([
            'value' => $request->get('value'),
            'title' => $request->has('title') ? $request->get('title') : $setting->title,
            'description' => $request->has('description') ? $request->get('description') : $setting->description
        ]);

        return api()->success(null, SettingResource::make($setting->fresh()));
    }
}
