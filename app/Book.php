<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /**
     * Get the lines.
     */
    public function lines()
    {
        return $this->hasMany('App\BookLine');
    }
}
