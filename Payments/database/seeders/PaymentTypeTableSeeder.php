<?php
namespace Payments\database\seeders;

use Illuminate\Database\Seeder;
use Payments\Models\PaymentType;

class PaymentTypeTableSeeder extends Seeder
{
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        if (PaymentType::query()->count() > 0)
        return;
        if(is_null(config('payment.payment_types')))
            throw new \Exception('payments.config-key-setting-missing',400);

        foreach (config('payment.payment_types') as $key => $setting) {
            if (!PaymentType::query()->whereName($setting['name'])->exists()) {
                PaymentType::query()->create([
                    'name' => $setting['name'],
                    'is_active' => $setting['is_active'],
                ]);
            }
        }
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
    }

}
