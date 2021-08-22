<?php


namespace Giftcode\Observers;

use Giftcode\Models\Setting;

class SettingObserver
{
    public function updating(Setting $setting)
    {
        $data = array_merge($setting->getOriginal(),[
            'actor_id' => request()->user->id,
            'setting_id' => $setting->id,
        ]);
        unset($data['id']);
        $setting->histories()->create($data);

    }
}
