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
        // takes all the words from the subtitle
        // order them with the frequency of the words table
        $wordsSubResult = DB::select('SELECT * FROM subtitle_words  INNER JOIN words ON words.id = subtitle_words.word_id WHERE subtitle_id = '.$this->id.' order by words.frequence_spoken desc ');

        $words= DB::select('SELECT * FROM words order by frequence_spoken desc');

        $sumSub = 0;
        foreach($wordsSubResult as $_wb) {
            $sumSub += $_wb->frequence;
        }

        // find count to get $coverPercent of the global content
        $percent_material = 0;

        $sum = 0;
        $word_id = 0;
        foreach($wordsSubResult as $word) {
            $word_id = $word->id;
            $sum += $word->frequence;
            $percent_material = ($sum*100)/$sumSub;
            if($percent_material >= $coverPercent)
               break;
        }

        // finds the row number in $words
        $n = 0;
        foreach($words as $word) {
            $n++;
            if($word->id == $word_id)
                break;
        }

        return $n;
    }

    /*
     * Returns n hard word with their frequences or <=0 for all
     * @return array
     */
    public function getHardWords($n=0) {
        $words= DB::select('SELECT * FROM words order by frequence_spoken desc limit '.$this->cword_80 );
        $wordsIds = array();
        foreach($words as $_w) {
            $wordsIds[$_w->id] = $_w->id; 
        }

        $limit = " limit 99999";
        if($n>0) {
            $limit = "limit $n";
        }

        return DB::select('SELECT * FROM subtitle_words  INNER JOIN words ON words.id = subtitle_words.word_id where words.id NOT IN ('.implode(',',$wordsIds).') AND subtitle_id = '.$this->id.' order by words.frequence_spoken desc '.$limit);
    }

}
