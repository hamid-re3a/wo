<?php

namespace Orders\database\seeders;

use Illuminate\Database\Seeder;
use Orders\Models\Order;
use User\Models\User;
use User\Services\UserService;

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
        // Load local seeder
        $data = [];
        if (!in_array(app()->environment(), ['production']) AND Order::query()->count() == 0) {

            $data = [
                ['id' => 1, 'user_id' => 1, 'position' => null, 'parent_id' => null],
                ['id' => 2, 'user_id' => 2, 'position' => 'left', 'parent_id' => 1],
//                ['id' => 3, 'user_id' => 3, 'position' => 'right', 'parent_id' => 1],
                ['id' => 4, 'user_id' => 4, 'position' => 'right', 'parent_id' => 2],
                ['id' => 5, 'user_id' => 5, 'position' => 'left', 'parent_id' => 2],
                ['id' => 6, 'user_id' => 6, 'position' => 'right', 'parent_id' => 3],
                ['id' => 7, 'user_id' => 7, 'position' => 'left', 'parent_id' => 3],
                ['id' => 8, 'user_id' => 8, 'position' => 'right', 'parent_id' => 4],
                ['id' => 9, 'user_id' => 9, 'position' => 'left', 'parent_id' => 4],
                ['id' => 10, 'user_id' => 10, 'position' => 'right', 'parent_id' => 5],
                ['id' => 11, 'user_id' => 11, 'position' => 'left', 'parent_id' => 5],
                ['id' => 12, 'user_id' => 12, 'position' => 'left', 'parent_id' => 6],
                ['id' => 13, 'user_id' => 13, 'position' => 'right', 'parent_id' => 6],
                ['id' => 14, 'user_id' => 14, 'position' => 'right', 'parent_id' => 7],
                ['id' => 15, 'user_id' => 15, 'position' => 'left', 'parent_id' => 7],
                ['id' => 16, 'user_id' => 16, 'position' => 'right', 'parent_id' => 8],
                ['id' => 17, 'user_id' => 17, 'position' => 'left', 'parent_id' => 9],
                ['id' => 18, 'user_id' => 18, 'position' => 'right', 'parent_id' => 10],
                ['id' => 19, 'user_id' => 19, 'position' => 'left', 'parent_id' => 11],
                ['id' => 20, 'user_id' => 20, 'position' => 'right', 'parent_id' => 12],
                ['id' => 21, 'user_id' => 21, 'position' => 'left', 'parent_id' => 13],
                ['id' => 22, 'user_id' => 22, 'position' => 'right', 'parent_id' => 13],
                ['id' => 3, 'user_id' => 3, 'position' => 'right', 'parent_id' => 14],
            ];
        }
        foreach ($data as $item) {
            if (app()->environment() != 'testing') {
                app(UserService::class)->findByIdOrFail($item['user_id']);
            } else {
                if (!User::query()->where('id',$item['user_id'])->exists()) {

                    $user = User::query()->firstOrCreate(['id' => $item['user_id']]);
                    $attributes = User::factory()->raw();
                    $user->update($attributes);
                }

            }
            Order::query()->create([
                'user_id' => $item['user_id'],
                'total_cost_in_pf' => 118.99,
                'packages_cost_in_pf' => 99,
                'registration_fee_in_pf' => 19.99,
                'is_paid_at' => now()->toDateString(),
                'is_resolved_at' => now()->toDateString(),
                'is_commission_resolved_at' => now()->toDateString(),
                'payment_type' => 'giftcode',
                'validity_in_days' => 200,
                'expires_at' => now()->addDays(200)->toDateString(),
                'package_id' => 1,
                'plan' => ORDER_PLAN_START
            ]);
        }


    }
}
