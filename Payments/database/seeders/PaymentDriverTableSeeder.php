<?php
namespace Payments\database\seeders;

use Illuminate\Database\Seeder;
use Payments\Models\PaymentDriver;

class PaymentDriverTableSeeder extends Seeder
{
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        if (PaymentDriver::query()->count() > 0)
        return;
        if(is_null(config('payment.payment_drivers')))
            throw new \Exception('payments.config-key-setting-missing',400);

        foreach (config('payment.payment_drivers') as $key => $setting) {
            if (!PaymentDriver::query()->whereName($setting['name'])->exists()) {
                PaymentDriver::query()->create([
                    'name' => $setting['name'],
                    'is_active' => $setting['is_active'],
                ]);
            }
        }
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
    }

}
