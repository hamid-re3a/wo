<?php

namespace Wallets\database\seeders;

use Illuminate\Database\Seeder;
use Wallets\Models\TransactionType;

/**
 * Class AuthTableSeeder.
 */
class TransactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TransactionType::insert([
            [
                'name' => 'Deposit'
            ],
            [
                'name' => 'Withdraw'
            ],
            [
                'name' => 'Purchase'
            ],
//            [
//                'name' => 'Upgrade'
//            ],
            [
                'name' => 'Renewal'
            ],
            [
                'name' => 'Giftcode'
            ],
            [
                'name' => 'Transfer'
            ],
            [
                'name' => 'Transfer fee'
            ],
            [
                'name' => 'Giftcode cancelation fee'
            ],
            [
                'name' => 'Giftcode expiry fee'
            ],
        ]);
    }
}
