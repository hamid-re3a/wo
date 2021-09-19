<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('giftcodes', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('user_id')->constrained('users','id');
            $table->foreignId('package_id')->constrained('giftcode_packages','id');

            $table->unsignedBigInteger('packages_cost_in_usd')->default(0);
            $table->unsignedBigInteger('registration_fee_in_usd')->default(0);
            $table->unsignedBigInteger('total_cost_in_usd')->default(0);

            $table->string('code')->unique();
            $table->timestamp('expiration_date')->nullable();
            $table->foreignId('redeem_user_id')->nullable()->constrained('users','id');
            $table->timestamp('redeem_date')->nullable();

            $table->boolean('is_canceled')->default(FALSE);
            $table->boolean('is_expired')->default(FALSE);

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
        Schema::dropIfExists('giftcodes');
    }
}
