<?php

namespace Payments\database\seeders;

use Payments\Models\EmailContentSetting;
use Illuminate\Database\Seeder;
use Payments\Models\Setting;

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
        if(defined('PAYMENT_SETTINGS') AND is_array(PAYMENT_SETTINGS)) {
            foreach (PAYMENT_SETTINGS as $key => $setting) {
                $key = Setting::query()->firstOrCreate([
                    'key' => $key
                ]);
                if (is_null($key->value)) {
                    $key->value = $setting['value'];
                    $key->description = $setting['description'];
                    $key->category = $setting['category'];
                    $key->save();
                }
            }
        }

        if(defined('PAYMENT_EMAIL_CONTENT_SETTINGS') AND is_array(PAYMENT_EMAIL_CONTENT_SETTINGS)){
            foreach (PAYMENT_EMAIL_CONTENT_SETTINGS as $key => $setting) {

                if (!EmailContentSetting::query()->whereKey($key)->exists()) {
                    if (filter_var(env('MAIL_FROM', $setting['from']), FILTER_VALIDATE_EMAIL))
                        $from = env('MAIL_FROM', $setting['from']);
                    else
                        $from = $setting['from'];

                    EmailContentSetting::query()->firstOrCreate(
                        ['key' => $key],
                        [
                            'is_active' => $setting['is_active'],
                            'subject' => $setting['subject'],
                            'from' => env('MAIL_FROM', $from),
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
}
