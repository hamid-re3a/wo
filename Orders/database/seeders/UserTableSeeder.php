<?php

namespace Orders\database\seeders;

use Illuminate\Database\Seeder;
use Orders\Models\OrderUser;

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
        $user = OrderUser::query()->firstOrCreate(['id'=>1]);
        $user->update([
            'first_name'=>'hamid',
            'last_name'=>'noruzi',
            'email'=>'hamidrezanoruzinejad@gmail.com',
            'username'=>'hamid_re3a',
        ]);


    }
}
