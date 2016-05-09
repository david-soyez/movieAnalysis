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
            $table->string('language');
            $table->boolean('is_processed')->default(0);
            $table->integer('cword_80')->nullable();
            $table->text('cword')->nullable();
            $table->decimal('sum_score',17,9)->nullable();
            $table->decimal('mean_score',17,9)->nullable();
            $table->decimal('std_score',17,9)->nullable();
            $table->decimal('readingspeed',9,4)->nullable();
            $table->decimal('std_readingspeed',9,4)->nullable();
            $table->decimal('cps',9,4)->nullable();
            $table->integer('sum_strlen')->nullable();
            $table->integer('mean_strlen')->nullable();
            $table->integer('std_strlen')->nullable();
            $table->integer('count_badwords')->nullable();
            $table->integer('count_contractions')->nullable();
            $table->integer('delay_conversation')->nullable();
            $table->boolean('is_pending')->default(0);
            $table->boolean('is_error')->default(0);
            $table->string('code_error')->nullable();
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
