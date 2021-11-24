<?php

namespace Giftcode\database\seeders;

use Giftcode\Models\EmailContent;
use Giftcode\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        if(defined('GIFTCODE_SETTINGS')) {
            $now = now()->toDateTimeString();
            foreach (GIFTCODE_SETTINGS AS $name => $setting) {
                Setting::query()->firstOrCreate(
                    ['name' => $name],
                    [
                        'value' => $setting['value'],
                        'title' => $setting['title'],
                        'description' => $setting['description'],
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
            }
            cache(['giftcode_settings' => GIFTCODE_SETTINGS]);
        }
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
    }

}
