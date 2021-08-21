<?php

namespace Wallets\database\seeders;

use Illuminate\Database\Seeder;
use User\Models\User;
use Wallets\Services\BankService;

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
        $bankService = new BankService($user);
        $bankService->deposit('Deposit Wallet',10000);
        $bankService->deposit('Earning Wallet',1000);


    }
}
