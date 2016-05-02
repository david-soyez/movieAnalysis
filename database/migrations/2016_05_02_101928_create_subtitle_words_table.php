<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubtitleWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('subtitle_words', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subtitle_id');
            $table->integer('word_id');
            $table->integer('frequence');
            $table->index('subtitle_id');
            $table->index('word_id');
         });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('subtitle_words');
    }
}
