<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('order_users')->onDelete('cascade');

            $table->unsignedBigInteger('to_user_id')->nullable();
            $table->foreign('to_user_id')->references('id')->on('users');

            $table->unsignedBigInteger('total_cost')->default(0);
            $table->unsignedBigInteger('packages_cost')->default(0);

            $table->boolean('is_paid')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->boolean('is_commission_resolved')->default(false);

            $table->string('payment_type')->default(ORDER_PAYMENT_TYPE_WALLET);
            $table->string('payment_currency')->default(ORDER_PAYMENT_TYPE_WALLET);
            $table->string('payment_driver')->nullable()->default(null);

            $table->string('plan')->default(ORDER_PLAN_DEFAULT);
            $table->softDeletes();
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
        Schema::dropIfExists('orders');
    }
}
