<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MovieSubtitle;
use App\SubtitleWord;
use DB;

class SubtitleCue extends Model
{
    public $timestamps = false;

    protected $words = array();

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
        foreach($lines as $line) {
            $matches = str_word_count($line,1,'’1234567890');
            foreach($matches as $word) {
                $word = self::filterWord($word);
                $word = strtolower($word);
                if(!self::isWord($word)){
                    continue;
                }
                if(!isset($frequences[strtolower($word)])) {
                    $frequences[strtolower($word)] = 0;
                }
                $frequences[strtolower($word)]++;
            }
        }

        return $frequences;
    }

    public function addWordsFrequences() {
        $frequences = $this->findWordsFrequences();

        arsort($frequences);

        // write the words to database
        foreach($frequences as $word => $frequence) {
 
            $wordObj=Word::where(array('value'=>($word),'language'=>$this->subtitle->language))
                ->orderBy('frequence_spoken','desc')->orderBy('frequence_written','desc')->orderBy('dispersion','desc')->orderBy('range','desc')->limit(1)->first();

            if(empty($wordObj)) {
                $wordObj = new Word();
                $wordObj->value = strtolower($word);
                $wordObj->language = $this->subtitle->language;
                $wordObj->frequence_movie = 0;
                $wordObj->frequence_spoken = 0;
                $wordObj->frequence_written = 0;
            } else {
                // don't take proper name
                if($wordObj->pos == 'NoP') {
                    continue;
                } 
                
                // if word existing try to match the lemma if one existing
                if($wordObj->lemma != null) {
                    // adds the frequence before changing word
                    $wordObj->lemma->frequence_movie += $frequence;
                    $wordObj->lemma->save();
                }
            }
                $wordObj->frequence_movie += $frequence;
                $wordObj->save();

                // save the subtitleWord
                $subWordObj=SubtitleWord::where(array('word_id'=>$wordObj->id,'subtitle_id'=>$this->subtitle_id))->first();
                if(empty($subWordObj)) {
                    $subWordObj = new SubtitleWord();
                    $subWordObj->subtitle_id= $this->subtitle->id;
                    $subWordObj->word_id = $wordObj->id;
                    $subWordObj->frequence= 0;
                }

                $subWordObj->frequence += $frequence;
                $subWordObj->save();                
        }

    }

    public function findCovering($coverPercent) {
        $wordsSubResult= $this->findWordsFrequences();
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

        $wordsSub = array();
        $sumSub = 0;
        foreach($wordsSubResult as $word => $frequence) {
            $wordsSub[$word] = $frequence; 
            $sumSub += $frequence;
        }

        // find count to have to get 75% of the global content
        $percent_material = 0;

        $sum = 0;

        $_words = array();
        foreach($words as $word) {
            $_words[$word->value1] = $word->value1;
            $_words[$word->value2] = $word->value2;
        }
        $words = $_words;

        // finds the row number in $words
        $n = 0;
        foreach($words as $id => $word) {
            $n++;
            if(isset($wordsSub[$word])) {
                $sum += $wordsSub[$word];
            }
            $percent_material = ($sum*100)/$sumSub;
            if($percent_material >= $coverPercent)
               break;
        }

        return $n;
    }

    public function computeScore() {



        return true;


        $sum_score = 0;

        // most frequent word
        $mostUsedWord = Word::where(array())->orderBy('frequence_spoken','desc')->take(1)->get()->first();     
        
        // all words in db
        $sum_words = DB::select('SELECT sum(frequence_spoken) as frequence FROM words')[0]->frequence;

        $words = Word::get20Words();         

        $percent20words = array();
        foreach($words as $word) {
           $percent20words[$word->id] = $word;
        }

        // compute score
        foreach($frequences as $word => $frequence) {
            $wordObj=Word::where(array('value'=>strtolower($word),'language'=>$this->subtitle->language))->orderBy('frequence_spoken','desc')->first();
            // we substracte to the most used word to reverse the result
            if(!isset($percent20words[$wordObj->id]))
                $sum_score += (($mostUsedWord->frequence*100)/$sum_words) - (($wordObj->frequence*100)/$sum_words);
        }

        // we calculate the difficuly / millisecond times the number of words (times a rate of 10 to reduce the float)
        return $this->score = $sum_score;
    }

    static function isWord($word) {

        $err = false;

        // one letter word
        if(strlen($word)==1) {
            if(!in_array($word,array('a','i','o','1','2','3','4','5','6','7','8','9','0')))
                $err = true;
        }
    
        // two letters word
        if(strlen($word)==2) {
            $twoLettersWords = array(
            'aa',
            'ab',
            'ad',
            'ag',
            'ah',
            'ai',
            'am',
            'an',
            'as',
            'at',
            'aw',
            'ax',
            'ay',
            'ba',
            'be',
            'bi',
            'bo',
            'by',
            'da',
            'do',
            'dy',
            'ee',
            'eh',
            'El',
            'em',
            'en',
            'er',
            'ex',
            'fa',
            'Ga',
            'gi',
            'go',
            'ha',
            'he',
            'hi',
            'ho',
            'id',
            'if',
            'in',
            'io',
            'is',
            'it',
            'ja',
            'jo',
            'Ju',
            'ka',
            'ki',
            'la',
            'li',
            'lo',
            'ma',
            'me',
            'mi',
            'mo',
            'mu',
            'my',
            'né',
            'no',
            'nu',
            'ob',
            'od',
            'of',
            'og',
            'oh',
            'oi',
            'OK',
            'om',
            'on',
            'op',
            'or',
            'os',
            'ou',
            'ow',
            'ox',
            'oy',
            'Oz',
            'pa',
            'pi',
            'po',
            'qi',
            'ra',
            're',
            'ri',
            'se',
            'si',
            'so',
            'ta',
            'te',
            'ti',
            'to',
            'uh',
            'um',
            'up',
            'us',
            'Wa',
            'we',
            'Wu',
            'xi',
            'xu',
            'ye',
            'Yi',
            'yo',
            'yu',);

            if(!in_array($word,$twoLettersWords))
                $err = true;
        }
    
        if( (preg_match('/[^\x20-\x7f]/', $word)))
            $err = true;

        return !$err;
    }

    static function filterWord($word) {
       $word = trim($word,"'"); 
       $word = trim($word,"-"); 

       return $word;
    }

    /**
     * Get the subtitle that owns the cue.
     */
    public function subtitle()
    {
        return $this->belongsTo('App\MovieSubtitle', 'subtitle_id');
    }
}
