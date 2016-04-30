<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SubtitleConversation;

class MovieSubtitle extends Model
{
    /**
     * Get the conversations.
     */
    public function conversations()
    {
        return $this->hasMany('App\SubtitleConversation', 'subtitle_id');
    }

    /**
     * Get the cues.
     */
    public function cues()
    {
        return $this->hasMany('App\SubtitleCue', 'subtitle_id');
    }
}
