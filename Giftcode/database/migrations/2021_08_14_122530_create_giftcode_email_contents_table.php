<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftcodeEmailContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('giftcode_email_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->boolean('is_active')->default(true);
            $table->string('subject');
            $table->string('from');
            $table->string('from_name');
            $table->longText('body');
            $table->text('variables');
            $table->text('variables_description');
            $table->string('type');
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
        Schema::dropIfExists('giftcode_email_contents');
    }
}
