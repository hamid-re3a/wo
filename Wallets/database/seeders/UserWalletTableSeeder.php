<?php

namespace Wallets\database\seeders;

use Illuminate\Database\Seeder;
use User\Models\User;

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
            'first_name'=>'Admin',
            'last_name'=>'Admin',
            'member_id' => 1000,
            'email'=>'admin@site.com',
            'username'=>'admin',
        ]);

//        $bankService = new BankService($user);
//        $bankService->deposit('Deposit Wallet',10000);
//        $bankService->deposit('Earning Wallet',1000);


        $user = User::query()->firstOrCreate(['id'=>2]);
        $user->update([
            'first_name'=>'Nima',
            'last_name'=>'Nouri',
            'member_id' => 13,
            'email'=>'niman2d@gmail.com',
            'username'=>'NimaN2D',
        ]);


    }
}
