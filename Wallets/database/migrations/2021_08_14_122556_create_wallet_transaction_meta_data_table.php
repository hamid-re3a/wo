<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionMetaDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transaction_meta_data', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('type_id')->constrained('wallet_transaction_types');
            $table->decimal('wallet_before_balance', 64, 2)->nullable();
            $table->decimal('wallet_after_balance', 64, 2)->nullable();
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
        Schema::dropIfExists('wallet_transaction_meta_data');
    }
}
