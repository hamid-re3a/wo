<?php

namespace User\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
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
        if(defined('USER_ROLES'))
            foreach (USER_ROLES as $role)
                Role::query()->firstOrCreate(['name' => $role]);

        // Load local seeder
        if (app()->environment() === 'local') {
            $admin = User::query()->firstOrCreate(['id' => 1]);
            $admin->update([
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'member_id' => 1000,
                'email' => 'work@sajidjaved.com',
                'username' => 'admin',
            ]);

            if(defined('USER_ROLE_SUPER_ADMIN'))
                $admin->assignRole(USER_ROLE_SUPER_ADMIN);
        }

    }
}
