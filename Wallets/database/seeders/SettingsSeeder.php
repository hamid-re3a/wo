<?php

namespace Wallets\database\seeders;

use Illuminate\Database\Seeder;
use Wallets\Models\Setting;

/**
 * Class AuthTableSeeder.
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (defined('WALLET_SETTINGS') AND is_array(WALLET_SETTINGS)) {
            $settings = [];
            $now = now()->toDateTimeString();
            foreach (WALLET_SETTINGS AS $key => $setting)
                $settings[] = [
                    'name' => $key,
                    'value' => $setting['value'],
                    'title' => $setting['title'],
                    'description' => $setting['description'],
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            Setting::query()->upsert($settings,'name');
            cache(['wallet_settings' => $settings]);
        }


    }
}
