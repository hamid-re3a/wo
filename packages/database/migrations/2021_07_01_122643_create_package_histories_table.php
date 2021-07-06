<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id');

            $table->string('name');
            $table->string('short_name',20);
            $table->integer('validity_in_days')->nullable();
            $table->double('price');

            $table->integer('roi_percentage')->nullable();
            $table->integer('direct_percentage')->nullable();
            $table->integer('binary_percentage')->nullable();

            $table->unsignedBigInteger('category_id');

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
        Schema::dropIfExists('package_histories');
    }
}
