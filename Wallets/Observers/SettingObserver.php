<?php


namespace Wallets\Observers;

use Wallets\Models\Setting;

class SettingObserver
{
    public function updating(Setting $setting)
    {
        $data = array_merge($setting->getOriginal(),[
            'actor_id' => request()->wallet_user->id,
            'setting_id' => $setting->id,
        ]);
        unset($data['id']);
        $setting->histories()->create($data);

    }
}
