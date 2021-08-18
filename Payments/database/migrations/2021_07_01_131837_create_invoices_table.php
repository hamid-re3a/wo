<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedDouble('pf_amount')->default(0);
            $table->unsignedDouble('amount')->default(0);

            $table->string('transaction_id')->nullable();
            $table->string('checkout_link')->nullable();

            $table->string('status')->nullable();
            $table->string('additional_status')->default(false);

            $table->boolean('is_paid')->default(false);

            $table->unsignedDouble('paid_amount')->default(0);
            $table->unsignedDouble('due_amount')->default(0);

            $table->timestamp('expiration_time')->nullable();


            $table->string('payment_type')->nullable();
            $table->string('payment_currency')->nullable();
            $table->string('payment_driver')->nullable();

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
        Schema::dropIfExists('invoices');
    }
}
