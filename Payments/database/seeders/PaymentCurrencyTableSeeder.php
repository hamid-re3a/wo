<?php
namespace Payments\database\seeders;

use Illuminate\Database\Seeder;
use Payments\Models\PaymentCurrency;

class PaymentCurrencyTableSeeder extends Seeder
{
    public function run()
    {
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
        if (PaymentCurrency::query()->count() > 0)
        return;
        if(is_null(config('payment.payment_currencies')))
            throw new \Exception('payments.config-key-setting-missing',400);

        foreach (config('payment.payment_currencies') as $key => $setting) {
            if (!PaymentCurrency::query()->whereName($setting['name'])->exists()) {
                PaymentCurrency::query()->create([
                    'name' => $setting['name'],
                    'is_active' => $setting['is_active'],
                    'available_services' => CURRENCY_SERVICES
                ]);
            }
        }
        fwrite(STDOUT,  __CLASS__.PHP_EOL);
    }

}
