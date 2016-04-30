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
            $table->string('title');
            $table->string('director')->nullable();
            $table->string('storyline')->nullable();
            $table->string('category')->nullable();
            $table->string('country')->nullable();
            $table->enum('language',array('en','fr','es','de','it'));
            $table->date('release')->nullable();
            $table->string('image_filename')->nullable();
            $table->boolean('is_active')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
            $table->index('user_id');
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
