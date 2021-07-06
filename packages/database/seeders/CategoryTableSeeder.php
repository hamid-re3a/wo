<?php
namespace Packages\database\seeders;

use Illuminate\Database\Seeder;
use Packages\Models\Category;

class CategoryTableSeeder extends Seeder
{
    public function run()
    {
        if (Category::query()->count() > 0)
            return;
        if(is_null(config('package.categories')))
            throw new \Exception('packages.config-key-setting-missing');

        foreach (config('package.categories') as $key => $setting) {

            if (!Category::query()->whereKey($key)->exists()) {
                Category::query()->create([
                    'key' => $key,
                    'name' => $setting['name'],
                    'short_name' => $setting['short_name'],
                    'roi_percentage' => $setting['roi_percentage'],
                    'direct_percentage' => $setting['direct_percentage'],
                    'binary_percentage' => $setting['binary_percentage'],
                    'package_validity_in_days' => $setting['package_validity_in_days'],
                ]);
            }
        }

    }

}
