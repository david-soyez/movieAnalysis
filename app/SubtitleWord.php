<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MovieSubtitle;

class SubtitleWord extends Model
{
    public $timestamps = false;

    /**
     * Get the subtitle that owns the word.
     */
    public function post()
    {
        return $this->belongsTo('App\MovieSubtitle', 'subtitle_id');
    }
}
