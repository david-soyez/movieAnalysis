<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MovieSubtitle;

class SubtitleConversation extends Model
{
    public $timestamps = false;

     /**
     * Get the moviesubtitle.
     */
    public function moviesubtitle()
    {
        return $this->belongsTo('App\MovieSubtitle');
    }
}
