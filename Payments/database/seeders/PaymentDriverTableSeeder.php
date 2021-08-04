<?php
namespace Payments\database\seeders;

use Illuminate\Database\Seeder;
use Payments\Models\PaymentDriver;

class PaymentDriverTableSeeder extends Seeder
{
    public function run()
    {
        if (PaymentDriver::query()->count() > 0)
        return;
        if(is_null(config('payment.payment_drivers')))
            throw new \Exception('payments.config-key-setting-missing');

        foreach (config('payment.payment_drivers') as $key => $setting) {
            if (!PaymentDriver::query()->whereName($setting['name'])->exists()) {
                PaymentDriver::query()->create([
                    'name' => $setting['name'],
                    'is_active' => $setting['is_active'],
                ]);
            }
        }
    }

}
