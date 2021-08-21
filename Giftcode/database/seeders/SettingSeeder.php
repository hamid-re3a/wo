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
                'value' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
                'title' => 'Available characters',
                'description' => 'Gift code include this characters'
            ],
            [
                'name' => 'length',
                'value' => 6,
                'title' => 'Giftcode length',
                'description' => 'Giftcode total characters'
            ],
            [
                'name' => 'separator',
                'value' => '-',
                'title' => 'separator',
                'description' => 'Separator for prefix and postfix'
            ],
            [
                'name' => 'prefix',
                'value' => 'janex',
                'title' => 'Prefix',
                'description' => 'Prefix string for giftcode'
            ],
            [
                'name' => 'use_prefix',
                'value' => true,
                'title' => 'Include prefix',
                'description' => 'Use prefix for giftcode or not'
            ],
            [
                'name' => 'postfix',
                'value' => 'team',
                'title' => 'Postfix',
                'description' => 'Postfix string for giftcode'
            ],
            [
                'name' => 'use_postfix',
                'value' => true,
                'title' => 'Include postfix',
                'description' => 'Use postfix for giftcode or not'
            ],
            [
                'name' => 'has_expiration_date',
                'value' => true,
                'title' => 'Has expiration date',
                'description' => 'Giftcode has expiration date or not'
            ],
            [
                'name' => 'giftcode_lifetime',
                'value' => null,
                'title' => 'Giftcode life time',
                'description' => 'Giftcode life time'
            ],
            [
                'name' => 'include_cancellation_fee',
                'value' => true,
                'title' => 'Include cancellation fee',
                'description' => 'Include cancellation fee'
            ],
            [
                'name' => 'cancellation_fee',
                'value' => 1,
                'title' => 'Cancelation fee',
                'description' => 'Cancellation fee for giftcode in percent'
            ],
            [
                'name' => 'include_expiration_fee',
                'value' => true,
                'title' => 'Include expiration fee',
                'description' => 'Include expiration fee for giftcode or not'
            ],
            [
                'name' => 'expiration_fee',
                'value' => 1,
                'title' => 'Expiration fee',
                'description' => 'Giftcode expiration fee in percent'
            ]
        ]);
    }

}
