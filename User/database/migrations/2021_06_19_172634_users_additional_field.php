<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersAdditionalField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('sponsor_id')->after('email')->unsigned()->nullable();
            $table->unsignedBigInteger('member_id')->after('sponsor_id')->unsigned()->nullable();
            $table->string('block_type')->after('member_id')->nullable();
            $table->boolean('is_freeze')->after('block_type')->nullable();
            $table->boolean('is_deactivate')->after('is_freeze')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
