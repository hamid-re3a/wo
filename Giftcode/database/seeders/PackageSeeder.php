<?php
namespace Giftcode\database\seeders;

use Giftcode\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run()
    {
        // Load local seeder
        if (app()->environment() === 'local')
        {
            Package::create([
                'package_id' => 1,
                'name' => 'Package 1',
                'price' => 100
            ]);
        }

    }

}
