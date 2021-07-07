<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesIndirectCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages_indirect_commissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('package_id');

            $table->integer('level');
            $table->integer('percentage');

            $table->unique(['package_id', 'level']);

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
        Schema::dropIfExists('packages_indirect_commissions');
    }
}
