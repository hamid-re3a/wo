<?php
namespace Packages\database\seeders;

use Illuminate\Database\Seeder;
use Packages\Models\Package;

class PackageTableSeeder extends Seeder
{
    public function run()
    {
        if (Package::query()->count() > 0)
            return;
        if(is_null(config('package.packages')))
            throw new \Exception('packages.config-key-setting-missing');

        foreach (config('package.packages') as $key => $setting) {
            if (!Package::query()->whereName($setting['name'])->exists()) {
                Package::query()->create([
                    'name' => $setting['name'],
                    'short_name' => $setting['short_name'],
                    'price' => $setting['price'],
                    'category_id' => $setting['category_id'],
                ]);
            }
        }

    }

}
