<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesIndirectCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_indirect_commissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id');

            $table->integer('level');
            $table->integer('percentage')->default(0);

            $table->unique(['category_id', 'level']);

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
        Schema::dropIfExists('categories_indirect_commissions');
    }
}
