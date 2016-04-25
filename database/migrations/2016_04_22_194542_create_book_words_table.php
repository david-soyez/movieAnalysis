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
            
            // Number of time the word appears in the book
            $table->integer('frequence');
            
            // Percentage the word appears in book over the total number of words
            $table->decimal('percentage',17,14)->nullable();
            
            // Cumulative percentage of the most used words. 
            // In other words, it is the cumulative sum of the above percentage with the words order by frequences descending.
            // We expect to have the percentage 20% matching with 80 of percentage_book_cumul
            $table->decimal('percentage_cumul',17,14)->nullable();
            
            // The cumulative percentage of the total words covered in the book. The 80 value should match the 20 value of percentage_cumul
            $table->decimal('percentage_book_cumul',17,14)->nullable();
            
            // indexes
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
