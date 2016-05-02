<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SubtitleConversation;
use App\SubtitleCue;
use App\SubtitleWord;
use DB;

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

    /**
     * Get the words.
     */
    public function words()
    {
        return $this->hasMany('App\SubtitleWord', 'subtitle_id');
    }

    public function findCovering($coverPercent) {
        $wordsSubResult= DB::select('SELECT * FROM subtitle_words  where subtitle_id = '.$this->id.' order by frequence desc');
        $words= DB::select('SELECT * FROM words order by frequence_subtitle desc');

        $wordsSub = array();
        $sumSub = 0;
        foreach($wordsSubResult as $_wb) {
            $wordsSub[$_wb->word_id] = $_wb; 
            $sumSub += $_wb->frequence;
        }

        // find count to have to get 75% of the global content
        $percent_material = 0;

        $sum = 0;
        $n = 0;
        foreach($words as $word) {
            $n++;
            if(isset($wordsSub[$word->id])) {
                $sum += $wordsSub[$word->id]->frequence;
                $percent_material = ($sum*100)/$sumSub;
                if($percent_material >= $coverPercent)
                   break;
            }
        }

        return $n;
    }

}
