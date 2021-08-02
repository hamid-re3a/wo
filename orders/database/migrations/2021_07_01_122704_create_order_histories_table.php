<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('legacy_id');
            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('to_user_id')->nullable();


            $table->unsignedBigInteger('total_cost_in_usd')->default(0);
            $table->unsignedBigInteger('packages_cost_in_usd')->default(0);
            $table->unsignedBigInteger('registration_fee_in_usd')->default(0);

            $table->timestamp('is_paid_at')->nullable();
            $table->timestamp('is_resolved_at')->nullable();
            $table->timestamp('is_refund_at')->nullable();
            $table->timestamp('is_expired_at')->nullable();
            $table->timestamp('is_commission_resolved_at')->nullable();

            $table->string('payment_type');
            $table->string('payment_currency');
            $table->string('payment_driver')->nullable()->default(null);


            $table->string('plan')->default(ORDER_PLAN_DEFAULT);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_histories');
    }
}
