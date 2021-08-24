<?php

namespace Orders\database\seeders;

use Illuminate\Database\Seeder;
use Orders\Models\Order;
use User\Models\User;

/**
 * Class AuthTableSeeder.
 */
class OrderTableSeeder extends Seeder
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

        }


    }
}
