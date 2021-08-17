<?php


namespace Giftcode_\Observers;

use Giftcode_\Models\GiftcodeUser;
use Giftcode_\Models\Setting;

class SettingObserver
{
    public function updating(Setting $setting)
    {
        $data = array_merge($setting->getOriginal(),[
            'actor_id' => request()->giftcode_user->id,
            'setting_id' => $setting->id,
        ]);
        unset($data['id']);
        $setting->histories()->create($data);

    }
}
