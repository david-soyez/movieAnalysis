<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('book_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->integer('position');
            $table->integer('count_words');
            $table->integer('count_top100');
            $table->integer('count_pareto_20');
            $table->integer('count_pareto_above_20');
            $table->decimal('sum_top100',17,14)->nullable();
            $table->decimal('sum_pareto_20',17,14)->nullable();
            $table->decimal('sum_pareto_above_20',17,14)->nullable();
            $table->index('book_id');
         });    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('book_lines');
    }
}
