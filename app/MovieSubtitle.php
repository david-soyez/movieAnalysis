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
     * Get the movie that owns the cue.
     */
    public function movie()
    {
        return $this->belongsTo('App\Movie', 'movie_id');
    }
    
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

LIMIT 200000');

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

        $percentages = array();
        $incPercent = 1;

        // finds the row number in $words
        $n = 0;
        foreach($words as $id => $word) {
            $n++;
            if(isset($wordsSub[$id])) {
                $sum += $wordsSub[$id];
            }
            $percent_material = ($sum*100)/$sumSub;
            if($percent_material >= $incPercent && !isset($percentages[$incPercent])) {
                $percentages[$incPercent] = $n;
                $incPercent += 1;
            }
            if($percent_material >= $coverPercent) {
                $percentages[$coverPercent] = $n;
               break;
            }
        }
        $this->cword = json_encode($percentages);
        $this->save();

        return $percentages['95'] - $percentages['80'];
    }

    public function getWordsCount() {
       return DB::select('select sum(frequence) as total from subtitle_words where subtitle_id = '.$this->id)[0]->total; 
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

    public function getHardest($limit=1) {
        $res = DB::select('SELECT subtitle_conversations.*, (count_words*(100-score))/100 as note FROM subtitle_conversations  where subtitle_id = '.$this->id.' order by  note desc limit '.$limit);
        if($limit==1)
            return $res[0];
        else 
            return $res;
    }

    public function getVocabulary() {
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

LIMIT 20000');


        $_words = array();
        foreach($words as $word) {
            if(!empty($word->id2)) {
                $_words[$word->id2] = true;
            }
                $_words[$word->id1] = true;
        }
        $words = $_words;

        $words = array_slice($words,0,$this->cword_80,true);

        return DB::select('SELECT ifnull(w2.value,words.value) as value,subtitle_words.frequence FROM subtitle_words  INNER JOIN words ON words.id = subtitle_words.word_id INNER JOIN words as w2 ON w2.id = words.lemma_word_id where (words.id IN ('.implode(',',array_keys($words)).') OR w2.id IN ('.implode(',',array_keys($words)).')) AND subtitle_id = '.$this->id.' order by words.frequence_spoken desc,words.frequence_written desc,words.dispersion desc,words.range desc');
    }


    public function getLevel() {
        $hardestSub = MovieSubtitle::where(array())->orderBy('cword_80','desc')->first();

        $devi1 = $this->sd(json_decode($hardestSub->cword,true)); 
        $devi2 = $this->sd(json_decode($this->cword,true)); 
        $scoreWords = (($this->cword_80/15)*80)/($hardestSub->cword_80/15);
        $scoreLength = (($this->getWordsCount())*20)/($hardestSub->getWordsCount());
        
        //return (($devi2)*10)/($devi1);
        return ($scoreWords+$scoreLength*10)/100;
    }
    // Function to calculate square of value - mean
    function sd_square($x, $mean) { 
        return pow($x - $mean,2); 
    }

    // Function to calculate standard deviation (uses sd_square)    
    function sd($array) {
        // square root of sum of squares devided by N-1
        return sqrt(array_sum(array_map(array($this,"sd_square"), $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
    }}

