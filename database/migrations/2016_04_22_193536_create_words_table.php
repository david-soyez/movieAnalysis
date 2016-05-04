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
            $table->bigInteger('frequence_spoken')->nullable();
            $table->bigInteger('frequence_written')->nullable();
            $table->string('pos',5);
            $table->decimal('dispersion', 3, 2)->nullable(); // from 0 to 1
            $table->integer('range')->nullable(); // corpus range domain from 0 to 100
            $table->enum('language',array('en','fr','es','de','it'));
            $table->boolean('is_lemma')->default(0); // if this is a root word
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
