<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('words', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lemma_word_id')->nullable();
            $table->string('value');
            $table->bigInteger('frequence_book')->nullable();
            $table->bigInteger('frequence_subtitle')->nullable();
            $table->enum('language',array('en','fr','es','de','it'));
            $table->index('lemma_word_id');
         });    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('words');
    }
}
