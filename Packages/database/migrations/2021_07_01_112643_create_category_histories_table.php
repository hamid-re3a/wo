<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id');

            $table->string('key');
            $table->string('name');
            $table->string('short_name',20);

            $table->integer('roi_percentage');
            $table->integer('direct_percentage');
            $table->integer('binary_percentage');

            $table->integer('package_validity_in_days');
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
        Schema::dropIfExists('category_histories');
    }
}
