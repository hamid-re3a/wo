<?php

namespace Payments\database\seeders;

use Payments\Models\EmailContentSetting;
use Illuminate\Database\Seeder;

/**
 * Class AuthTableSeeder.
 */
class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach (PAYMENT_EMAIL_CONTENT_SETTINGS as $key => $setting) {

            if (!EmailContentSetting::query()->whereKey($key)->exists()) {
                EmailContentSetting::query()->create([
                    'key' => $key,
                    'is_active' => $setting['is_active'],
                    'subject' => $setting['subject'],
                    'from' => $setting['from'],
                    'from_name' => $setting['from_name'],
                    'body' => $setting['body'],
                    'variables' => $setting['variables'],
                    'variables_description' => $setting['variables_description'],
                    'type' => $setting['type'],
                ]);
            }
        }


    }
}
