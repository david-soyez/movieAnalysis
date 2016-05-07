<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('movies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('tmdb_id')->nullable();
            $table->string('imdb_id')->nullable();
            $table->string('poster_path')->nullable();
            $table->text('overview')->nullable();
            $table->date('release_date')->nullable();
            $table->string('original_title')->nullable();
            $table->string('original_language');
            $table->string('title');
            $table->string('backdrop_path')->nullable();
            $table->decimal('popularity',10,6)->nullable();
            $table->integer('vote_count')->nullable();
            $table->boolean('video')->default(0);
            $table->decimal('vote_average',10,6)->nullable();
            $table->text('production_companies')->nullable();
            $table->text('production_countries')->nullable();
            $table->text('spoken_languages')->nullable();
            $table->boolean('is_pending')->default(0);
            $table->boolean('is_active')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
            $table->index('user_id');
            $table->unique('imdb_id');
            $table->unique('tmdb_id');
         });    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('movies');
    }
}
