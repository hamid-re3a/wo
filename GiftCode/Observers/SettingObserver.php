<?php


namespace Giftcode\Observers;

use Giftcode\Models\GiftcodeUser;
use Giftcode\Models\Setting;

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
