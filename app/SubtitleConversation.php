<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MovieSubtitle;
use App\SubtitleCue;
use DB;

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

    /*
     * Find the words in the cue and add the frequence
     *
     */
    protected function findWordsFrequences() {

        // filter content
        $content = $this->caption;
        $content = str_replace('’',"'",$content);
        $content = str_replace('--'," ",$content);

        // calculate mean line frequence
        $lines = preg_split('/[\.\n]+/',$content);

        $frequences = array();
        $i=0;
        foreach($lines as $line) {
            $matches = str_word_count($line,1,'’1234567890');
            foreach($matches as $word) {
                $word = SubtitleCue::filterWord($word);
                $word = strtolower($word);
                if(!SubtitleCue::isWord($word)){
                    continue;
                }
                if(!isset($frequences[strtolower($word)])) {
                    $frequences[strtolower($word)] = 0;
                }
                $frequences[strtolower($word)]++;
                $i++;
            }
        }

        $this->count_words = $i;
        return $frequences;
    }

    public function findScore($cword75) {
        static $wordsResult;
        $wordsSubResult= $this->findWordsFrequences();
        if(!isset($wordsResult)) {
            $wordsResult= DB::select('SELECT w1.value as value1,w2.value as value2 FROM paretobook.words as w1 lEFT JOIN words as w2 ON w1.id = w2.lemma_word_id where isnull(w1.lemma_word_id) order by w1.frequence_spoken desc, w2.frequence_spoken desc,w1.frequence_written DESC, w2.frequence_written DESC, w1.dispersion desc, w2.dispersion desc, w1.range desc,
 w2.range desc limit '.$cword75);
        }

        $words = array();
        $sumSub = 0;
        foreach($wordsResult as $word) {
            $words[$word->value1] = $word; 
            $words[$word->value2] = $word; 
        }

        $sum = 0; // total to understand the dialogue
        $n = 0;// should know
        foreach($wordsSubResult as $word => $frequence) {
            if(isset($words[$word])) {
                $n += $frequence;
            }
            $sum += $frequence;
        }

        return $sum > 0 ? ($n*100)/$sum : 100;
    }



}
