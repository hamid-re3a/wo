<?php

namespace Giftcode\database\seeders;

use Giftcode\Models\Package;
use Giftcode\Repository\GiftcodeRepository;
use Illuminate\Database\Seeder;
use User\Models\User;
use Wallets\Services\BankService;

class TestGiftcodesForTestUserSeeder extends Seeder
{
    public function run()
    {
        $giftcode_repository = app(GiftcodeRepository::class);
        Package::query()->firstOrCreate([
            "id" => 1,
            "name" => 'Beginner',
            "short_name" => 'B',
            "validity_in_days" => 200,
            "price" => 99,
        ]);
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
        for ($i = 0; $i <= 10; $i++) {
            request()->merge([
                'user' => $user,
                'package_id' => 1,
                'include_registration_fee' => true,
                'wallet' => 'Deposit Wallet',
                'user_id' => $user->id
            ]);
            $giftcode_repository->create(request());
        }
    }

}
