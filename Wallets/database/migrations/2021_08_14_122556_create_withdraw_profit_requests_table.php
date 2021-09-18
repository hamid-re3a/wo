<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawProfitRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_withdraw_profit_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('withdraw_transaction_id')->constrained('transactions');
            $table->foreignId('refund_transaction_id')->nullable()->constrained('transactions');
            $table->tinyInteger('status')->default('1')->comment('Possible values(integer) : 1 = Under review, 2 = Rejected, 3 = Processed');

            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->tinyText('rejection_reason')->nullable();
            $table->mediumText('network_hash')->nullable();
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
        Schema::dropIfExists('wallet_withdraw_profit_requests');
    }
}
