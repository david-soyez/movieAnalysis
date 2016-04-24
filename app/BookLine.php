<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookLine extends Model
{
    public $timestamps = false;

     /**
     * Get the book.
     */
    public function book()
    {
        return $this->belongsTo('App\Book');
    }
}
