<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMovieGenres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('movie_genres', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('movie_id');
            $table->integer('genre_id');
            $table->index('genre_id');
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
        Schema::drop('movie_genres');
    }
}
