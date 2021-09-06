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
        $user = User::query()->firstOrCreate(['id' => 1]);
        $user->update([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'member_id' => 1000,
            'email' => 'work@sajidjaved.com',
            'username' => 'admin',
        ]);

        $bankService = new BankService($user);
        $bankService->getWallet('Deposit Wallet');
//        $bankService->deposit('Earning Wallet',1000);



    }
}
