<?php
namespace Giftcode\database\seeders;

use Giftcode\Jobs\UpdatePackages;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        UpdatePackages::dispatchSync();
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
    }

}
