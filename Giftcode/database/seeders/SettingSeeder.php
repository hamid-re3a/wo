<?php

namespace Giftcode\database\seeders;

use Giftcode\Models\EmailContent;
use Giftcode\Models\Package;
use Giftcode\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        if(defined('GIFTCODE_SETTINGS')) {
            Setting::query()->upsert(GIFTCODE_SETTINGS, 'name');
            cache(['giftcode_settings' => GIFTCODE_SETTINGS]);
        }
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
    }

}
