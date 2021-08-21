<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftcodeEmailContentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('giftcode_email_content_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('giftcode_email_contents');
            $table->foreignId('actor_id')->constrained('users');
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
        Schema::dropIfExists('giftcode_email_content_histories');
    }
}
