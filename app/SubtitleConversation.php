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
        $wordsSubResult= $this->findWordsFrequences();
        $wordsResult= DB::select('SELECT * FROM words order by frequence_spoken limit '.$cword75);

        $words = array();
        $sumSub = 0;
        foreach($wordsResult as $word) {
            $words[$word->value] = $word; 
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
