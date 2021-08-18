<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftcodeSettingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Giftcodesetting_histories', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->id();
            $table->foreignId('setting_id')->constrained('Giftcodesettings');
            $table->foreignId('actor_id')->constrained('Giftcodeusers');
            $table->string('name')->nullable();
            $table->string('value')->nullable();
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
        Schema::dropIfExists('Giftcodesetting_histories');
    }
}
