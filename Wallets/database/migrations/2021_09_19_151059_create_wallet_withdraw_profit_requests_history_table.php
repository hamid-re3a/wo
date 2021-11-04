<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletWithdrawProfitRequestsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_withdraw_profit_requests_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdraw_profit_id')->constrained('wallet_withdraw_profit_requests');
            $table->uuid('uuid')->unique();
            $table->mediumText('wallet_hash');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('withdraw_transaction_id')->constrained('transactions');
            $table->foreignId('refund_transaction_id')->nullable()->constrained('transactions');
            $table->unsignedBigInteger('network_transaction_id')->nullable();
            $table->tinyInteger('status')->default('1')->comment('Possible values(integer) : 1 = Under review, 2 = Rejected, 3 = Processed, 4 = Postponed');
            $table->boolean('is_update_email_sent')->default(false);
            $table->string('payout_service')->default('btc-pay-server');
            $table->string('currency');
            $table->unsignedDouble('pf_amount')->default(0);
            $table->unsignedDouble('crypto_amount')->default(0);
            $table->unsignedDouble('crypto_rate')->default(0);
            $table->unsignedDouble('fee')->default(0);

            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->text('act_reason')->nullable();

            $table->time('postponed_to')->nullable();
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
        Schema::dropIfExists('wallet_withdraw_profit_requests_history');
    }
}
