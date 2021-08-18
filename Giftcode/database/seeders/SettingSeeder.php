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
            ],
            [
                'name' => 'separator',
                'value' => '-'
            ],
            [
                'name' => 'prefix',
                'value' => 'janex'
            ],
            [
                'name' => 'use_prefix',
                'value' => true
            ],
            [
                'name' => 'postfix',
                'value' => 'team'
            ],
            [
                'name' => 'use_postfix',
                'value' => true
            ],
            [
                'name' => 'has_expiration_date',
                'value' => true
            ],
            [
                'name' => 'giftcode_lifetime',
                'value' => '10'
            ]
        ]);
    }

}
