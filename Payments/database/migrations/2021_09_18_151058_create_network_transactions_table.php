<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_payout_network_transactions', function (Blueprint $table) {
            $table->id();
            $table->mediumText('transaction_hash');
            $table->string('comment')->nullable();
            $table->json('labels')->nullable();
            $table->decimal('amount', 64, 0);
            $table->mediumText('block_hash')->nullable();
            $table->unsignedBigInteger('block_height')->nullable();
            $table->tinyInteger('confirmations')->default(0);
            $table->timestamp('timestamp');
            $table->string('status');
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
        Schema::dropIfExists('payment_payout_network_transactions');
    }
}
