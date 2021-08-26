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
        $settings = [
            [
                'name' => 'fix_transfer_fee',
                'value' => '10',
                'title' => 'Fix transfer amount',
                'description' => 'Fix transfer amount'
            ],
            [
                'name' => 'percentage_transfer_fee',
                'value' => 6,
                'title' => 'Transfer fee percentage',
                'description' => 'Transfer fee in percentage'
            ],
        ];
        Setting::insert($settings);
        cache(['wallet_settings' =>  $settings]);


    }
}
