<?php
namespace Giftcode\database\seeders;

use Giftcode\Models\Package;
use Giftcode\Models\Setting;
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
