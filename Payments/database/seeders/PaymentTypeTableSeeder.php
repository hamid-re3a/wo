<?php
namespace Payments\database\seeders;

use Illuminate\Database\Seeder;
use Payments\Models\PaymentType;

class PaymentTypeTableSeeder extends Seeder
{
    public function run()
    {
        if (PaymentType::query()->count() > 0)
        return;
        if(is_null(config('payment.payment_types')))
            throw new \Exception('payments.config-key-setting-missing');

        foreach (config('payment.payment_types') as $key => $setting) {
            if (!PaymentType::query()->whereName($setting['name'])->exists()) {
                PaymentType::query()->create([
                    'name' => $setting['name'],
                    'is_active' => $setting['is_active'],
                ]);
            }
        }
    }

}
