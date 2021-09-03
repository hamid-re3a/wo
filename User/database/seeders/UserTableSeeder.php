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
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'member_id' => 1000,
                'email' => 'work@sajidjaved.com',
                'username' => 'admin',
            ]);
        }

    }
}
