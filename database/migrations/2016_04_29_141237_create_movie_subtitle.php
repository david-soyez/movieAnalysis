<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovieSubtitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('movie_subtitles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('movie_id');
            $table->string('filename');
            $table->enum('language',array('en','fr','es','de','it'));
            $table->date('release')->nullable();
            $table->boolean('is_processed')->default(0);
            $table->decimal('sum_score',17,9)->nullable();
            $table->decimal('mean_score',17,9)->nullable();
            $table->decimal('std_score',17,9)->nullable();
            $table->decimal('readingspeed',9,4);
            $table->decimal('std_readingspeed',9,4);
            $table->decimal('cps',9,4);
            $table->integer('sum_strlen');
            $table->integer('mean_strlen');
            $table->integer('std_strlen');
            $table->boolean('is_active')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
            $table->index('movie_id');
         });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('movie_subtitles');
    }
}
