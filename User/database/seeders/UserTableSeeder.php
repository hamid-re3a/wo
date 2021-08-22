<?php

namespace User\database\seeders;

use Illuminate\Database\Seeder;
use User\Models\User;

/**
 * Class AuthTableSeeder.
 */
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load local seeder
        if (app()->environment() === 'local') {
            $user = User::query()->firstOrCreate(['id' => 1]);
            $user->update([
                'first_name' => 'hamid',
                'last_name' => 'noruzi',
                'email' => 'hamidrezanoruzinejad@gmail.com',
                'username' => 'hamid_re3a',
            ]);
        }

    }
}
