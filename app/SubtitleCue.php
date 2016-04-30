<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class SubtitleCue extends Model
{
    public $timestamps = false;
    public function computeScore() {
        $sum_score = 0;

        // must retrieve language from the movie
        $language = 'en';

        // filter content
        $content = $this->caption;
        $content = str_replace('’',"'",$content);
        $content = str_replace('--'," ",$content);

        // calculate mean line frequence
        $lines = preg_split('/[\.\n]+/',$content);

        $wordsFound=0;
        foreach($lines as $line) {
            $matches = str_word_count($line,1,'’1234567890');
            foreach($matches as $word) {
                $word = $this->filterWord($word);
                $word = strtolower($word);
                if(!$this->isWord($word)){
                    continue;
                }
                if(!isset($frequencies[strtolower($word)])) {
                    $frequencies[strtolower($word)] = 0;
                }
                $frequencies[strtolower($word)]++;
                $wordsFound++;
            }
        }
        if(!isset($frequencies)) {
            return false;
        }
        arsort($frequencies);
        $this->count_words = $wordsFound;

        // most frequent word
        $mostUsedWord = Word::where(array())->orderBy('frequence','desc')->take(1)->get()->first();         
        
        // write the words to database
        foreach($frequencies as $word => $frequence) {
            $wordObj=Word::where(array('value'=>strtolower($word),'language'=>$language))->first();
            if(empty($wordObj)) {
                $wordObj = new Word();
                $wordObj->value = strtolower($word);
                $wordObj->language = $language;
                $wordObj->frequence = $frequence;
                $wordObj->save();
            }
        }
        
        // all words in db
        $sum_words = DB::select('SELECT sum(frequence) as frequence FROM words as frequence')[0]->frequence;

        // compute score
        foreach($frequencies as $word => $frequence) {
            $wordObj=Word::where(array('value'=>strtolower($word),'language'=>$language))->first();
            // we substracte to the most used word to reverse the result
            $sum_score += (($mostUsedWord->frequence*100)/$sum_words) - (($wordObj->frequence*100)/$sum_words);
        }

        // we calculate the difficuly / millisecond times the number of words (times a rate of 10 to reduce the float)
        return $this->score = $sum_score;
    }

    protected function isWord($word) {

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

    protected function filterWord($word) {
       $word = trim($word,"'"); 
       $word = trim($word,"-"); 

       return $word;
    }
}
