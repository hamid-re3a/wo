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
//        $bankService->deposit('Deposit Wallet',10000000);

        $user = User::query()->firstOrCreate([
            'id' => 2,
            'member_id' => '2000',
            'email' => 'janexstaging@gmail.com',
            'first_name' => 'John',
            'last_name' => 'Due',
            'username' => 'johny',
        ]);
        $bankService = new BankService($user);
        $bankService->getWallet('Deposit Wallet');
        $bankService->deposit('Deposit Wallet',10000000);



    }
}
