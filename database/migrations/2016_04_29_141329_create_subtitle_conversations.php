<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubtitleConversations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('subtitle_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subtitle_id');
            $table->integer('timeline_start');
            $table->integer('timeline_end');
            $table->integer('cues_time');
            $table->integer('count_words');
            $table->integer('strlen');
            $table->decimal('readingspeed',9,4);
            $table->decimal('cps',9,4);
            $table->decimal('score',17,9)->nullable();
            $table->text('caption');
            $table->index('subtitle_id');
         });      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('subtitle_conversations');
    }
}
