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
            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('to_user_id')->nullable();

            $table->unsignedBigInteger('total_cost')->default(0);
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('products_cost')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tax_cost')->default(0);

            $table->boolean('is_paid')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->boolean('is_commission_resolved')->default(false);

            $table->string('payment_type')->default(ORDER_PAYMENT_TYPE_WALLET);
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
