<?php

namespace Orders\database\seeders;

use Illuminate\Database\Seeder;
use Orders\Models\Order;
use Orders\Models\OrderPackage;
use Orders\Models\OrderUser;

/**
 * Class AuthTableSeeder.
 */
class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderUser::query()->firstOrCreate(['id'=>1]);
        $order = Order::query()->create([
            'user_id' => 1,
            'total_cost_in_usd' => 119,
            'packages_cost_in_usd' => 99,
            'registration_fee_in_usd' => 20,
            'payment_type' => 'purchase',
            'payment_currency' => 'BTC',
            'payment_driver' => 'btc-pay-server',
            'plan' => 'ORDER_PLAN_START',
        ]);
        $order->packages()->create([
            'package_id' => 1
        ]);




    }
}
