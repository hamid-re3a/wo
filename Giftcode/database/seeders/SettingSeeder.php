<?php
namespace Giftcode_\database\seeders;

use Giftcode_\Models\Package;
use Giftcode_\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        Setting::insert([
            [
                'name' => 'characters',
                'value' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'
            ],
            [
                'name' => 'length',
                'value' => 6
            ]
        ]);
    }

}
