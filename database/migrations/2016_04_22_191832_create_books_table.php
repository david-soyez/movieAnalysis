<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('title');
            $table->string('author');
            $table->string('description');
            $table->string('fx')->nullable();
            $table->enum('language',array('en','fr'));
            $table->date('release_date')->nullable();
            $table->string('ISBN_13')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('publisher')->nullable();
            $table->boolean('is_processed')->default(0);
            $table->string('content_filename')->nullable();
            $table->string('image_filename')->nullable();
            $table->decimal('book_top100',17,14)->nullable();
            $table->decimal('book_pareto_20',17,14)->nullable();
            $table->decimal('book_pareto_above_20',17,14)->nullable();
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
        Schema::drop('books');
    }
}
