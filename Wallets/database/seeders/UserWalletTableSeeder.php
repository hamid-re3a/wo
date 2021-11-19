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

        $bankService = new BankService($user);

        $bankService->getWallet(WALLET_NAME_DEPOSIT_WALLET);
        $bankService->getWallet(WALLET_NAME_EARNING_WALLET);
        $bankService->deposit(WALLET_NAME_DEPOSIT_WALLET,10000000);
        if (!in_array(app()->environment(), ['production'])) {
            /**
             *
             */
            $user = User::query()->firstOrCreate(['id' => 2]);
            $bankService = new BankService($user);
            $bankService->getWallet(WALLET_NAME_DEPOSIT_WALLET);
            $bankService->getWallet(WALLET_NAME_EARNING_WALLET);
            $bankService->deposit(WALLET_NAME_DEPOSIT_WALLET, 10000000000);
            $bankService->deposit(WALLET_NAME_EARNING_WALLET, 10000000000);
            $customer_user = User::query()->firstOrCreate(['id' => 3]);
            $bankService = new BankService($customer_user);

            $bankService->getWallet(WALLET_NAME_DEPOSIT_WALLET);
            $bankService->getWallet(WALLET_NAME_EARNING_WALLET);

            if ($bankService->getBalance(WALLET_NAME_DEPOSIT_WALLET) == 0)
                $bankService->deposit(WALLET_NAME_DEPOSIT_WALLET, 10000000000);

            if ($bankService->getBalance(WALLET_NAME_EARNING_WALLET) == 0)
                $bankService->deposit(WALLET_NAME_EARNING_WALLET, 10000000000);


        }
    }
}
