<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelationPaymentCurrenciesDriver extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_currencies', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->after('is_active')->unsigned()->nullable();
        });

        Schema::table('payment_currencies', function (Blueprint $table) {
            $table->foreign('driver_id')->on('payment_drivers')
                ->references('id')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_currencies');
    }
}
