<?php
namespace Giftcode\database\seeders;

use Giftcode\Jobs\UpdatePackages;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run()
    {
        UpdatePackages::dispatchSync();
    }

}
