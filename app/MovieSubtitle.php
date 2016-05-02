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
        $results = DB::select('SELECT * FROM subtitle_words  where subtitle_id = '.$this->id.' order by frequence desc');

        $sum = 0;
        foreach($results as $result) {
            $sum += $result->frequence;
        }

        // find count to have to get 75% of the global content
        $n = 1;
        $percent_material = 0;
        $percent_words = 0;
        while($percent_material < $coverPercent) {
            $percent_words = ($n*100)/count($results);
            $sliced = array_slice($results,0,$n);  
            $sum_sliced = 0;
            foreach($sliced as $slice) {
                $sum_sliced += $slice->frequence;
            }
            $percent_material = ($sum_sliced*100)/$sum;
        }
    }

}
