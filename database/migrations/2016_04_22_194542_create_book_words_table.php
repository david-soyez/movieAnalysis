<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('book_words', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->integer('word_id');
            $table->integer('frequence');
            $table->decimal('percent_total',9,4);
            $table->decimal('cumul_percent_unique',9,4);
            $table->decimal('cumul_percent_total',9,4);
            $table->decimal('gain',9,4);
            $table->index('book_id');
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
        Schema::drop('book_words');
    }
}
