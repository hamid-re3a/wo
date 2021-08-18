<?php
namespace Giftcode\database\seeders;

use Illuminate\Database\Seeder;
use Giftcode\Models\User;

class TestGiftcodeUserSeeder extends Seeder
{
    public function run()
    {
        // Load local seeder
        if (app()->environment() === 'local')
        {
            User::create([
                'user_id' => 1,
                'first_name' => 'Nima',
                'last_name' => 'Nouri',
                'username' => 'niamn2d',
                'email' => 'nima.nouri.d@gmail.com'
            ]);

            User::create([
                'user_id' => 2,
                'first_name' => 'Ali',
                'last_name' => 'Gholami',
                'username' => 'Gholami',
                'email' => 'ali@gmail.com'
            ]);
        }

    }

}
