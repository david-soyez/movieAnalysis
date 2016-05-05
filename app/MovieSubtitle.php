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
        $wordsSubResult = DB::select('SELECT subtitle_words.frequence as frequence, words.id as id, words.lemma_word_id as lemma_id, words.value as value FROM subtitle_words  INNER JOIN words ON words.id = subtitle_words.word_id  WHERE subtitle_id = '.$this->id);

        $words= DB::select('
SELECT 
     w1.id AS id1, w2.id AS id2,w1.value AS value1, w2.value AS value2
FROM
    paretobook.words AS w1
        LEFT JOIN
    words AS w2 ON w1.id = w2.lemma_word_id
where isnull(w1.lemma_word_id)    
ORDER BY 

 w1.frequence_spoken desc, w2.frequence_spoken desc,w1.frequence_written DESC, w2.frequence_written DESC, w1.dispersion desc, w2.dispersion desc, w1.range desc,
 w2.range desc

LIMIT 100000');

        $sumSub = 0;
        $wordsSub = array();
        foreach($wordsSubResult as $_wb) {
            if(!empty($_wb->lemma_id)) {
                if(!isset($wordsSub[$_wb->lemma_id])) {
                    $wordsSub[$_wb->lemma_id] = 0;
                }
                $wordsSub[$_wb->lemma_id]+=$_wb->frequence;
            } else {
                if(!isset($wordsSub[$_wb->id])) {
                    $wordsSub[$_wb->id] = 0;
                }
                $wordsSub[$_wb->id]+=$_wb->frequence;
            }
            $sumSub += $_wb->frequence;
        }

        // find count to get $coverPercent of the global content
        $percent_material = 0;

        $sum = 0;

        $_words = array();
        foreach($words as $word) {
            if(!empty($word->id2)) {
                $_words[$word->id2] = $word->value2;
            }
            $_words[$word->id1] = $word->value1;
        }
        $words = $_words;

        // finds the row number in $words
        $n = 0;
        foreach($words as $id => $word) {
            $n++;
            if(isset($wordsSub[$id])) {
                $sum += $wordsSub[$id];
            }
            $percent_material = ($sum*100)/$sumSub;
            if($percent_material >= $coverPercent)
               break;
        }

        return $n;
    }

    /*
     * Returns n hard word with their frequences or <=0 for all
     * @return array
     */
    public function getHardWords($n=0) {
        $words= DB::select('
SELECT 
     w1.id AS id1, w2.id AS id2,w1.value AS value1, w2.value AS value2
FROM
    paretobook.words AS w1
        LEFT JOIN
    words AS w2 ON w1.id = w2.lemma_word_id
where isnull(w1.lemma_word_id)    
ORDER BY 

 w1.frequence_spoken desc, w2.frequence_spoken desc,w1.frequence_written DESC, w2.frequence_written DESC, w1.dispersion desc, w2.dispersion desc, w1.range desc,
 w2.range desc

LIMIT '.$this->cword_80);


        $_words = array();
        foreach($words as $word) {
            if(!empty($word->id2)) {
                $_words[$word->id2] = true;
            }
            $_words[$word->id1] = true;
        }
        $words = $_words;


        $limit = " limit 99999";
        if($n>0) {
            $limit = "limit $n";
        }

        return DB::select('SELECT * FROM subtitle_words  INNER JOIN words ON words.id = subtitle_words.word_id where words.id NOT IN ('.implode(',',array_keys($words)).') AND subtitle_id = '.$this->id.' order by words.frequence_spoken asc,words.frequence_written asc,words.dispersion asc,words.range asc '.$limit);
    }

}
