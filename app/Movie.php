<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MovieSubtitle;

class Movie extends Model
{
    /**
     * Get the subtitles.
     */
    public function subtitles()
    {
        return $this->hasMany('App\MovieSubtitle');
    }

    /**
     * Get the subtitle just one.
     */
    public function subtitle()
    {
        return MovieSubtitle::where(array('movie_id'=>$this->id))->orderBy('cword_75','desc')->first();
    }
}
