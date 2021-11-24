<?php

namespace Wallets\database\seeders;

use Illuminate\Database\Seeder;
use User\Models\User;
use Wallets\Services\BankService;

/**
 * Class AuthTableSeeder.
 */
class AdminWalletsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        //Default wallets for admin, This wallets MUST be seed into database
        $user = User::query()->firstOrCreate(['id' => 1]);
        $bankService = new BankService($user);
        $bankService->getWallet(WALLET_NAME_DEPOSIT_WALLET);
        $bankService->getWallet(WALLET_NAME_EARNING_WALLET);
        $bankService->getWallet(WALLET_NAME_CHARITY_WALLET);
//        $bankService->deposit(WALLET_NAME_DEPOSIT_WALLET,10000000);
        fwrite(STDOUT, __CLASS__.PHP_EOL);
    }
}
