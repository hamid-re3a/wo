<?php

namespace Wallets\database\seeders;

use Illuminate\Database\Seeder;
use User\Models\User;
use Wallets\Models\WalletUser;

/**
 * Class AuthTableSeeder.
 */
class UserWalletTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::query()->firstOrCreate(['id'=>1]);
        $user->update([
            'first_name'=>'hamid',
            'last_name'=>'noruzi',
            'email'=>'hamidrezanoruzinejad@gmail.com',
            'username'=>'hamid_re3a',
        ]);


    }
}
