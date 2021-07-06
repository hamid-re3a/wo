<?php
namespace Packages\database\seeders;

use Illuminate\Database\Seeder;
use Packages\Models\CategoriesIndirectCommission;

class CategoriesIndirectCommissionTableSeeder extends Seeder
{
    public function run()
    {
        if (CategoriesIndirectCommission::query()->count() > 0)
            return;
        if(is_null(config('package.categories-indirect-commissions')))
            throw new \Exception('packages.config-key-setting-missing');

        foreach (config('package.categories-indirect-commissions') as $key => $commission) {
            if (!CategoriesIndirectCommission::query()->whereCategoryId($commission['category_id'])->whereLevel($commission['level'])->exists()) {
                CategoriesIndirectCommission::query()->create([
                    'category_id' => $commission['category_id'],
                    'level' => $commission['level'],
                    'percentage' => $commission['percentage']
                ]);
            }
        }

    }

}
