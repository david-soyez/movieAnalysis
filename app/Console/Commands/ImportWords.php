<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Word;

class ImportWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pareto:importwords {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all the words in the book to the global table, incrementing the word frequences';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = $this->argument('filename'); 
        $language = 'en';
        $content = file_get_contents($filename);
        $matches = array();
        bcscale(14);

        $matches = str_word_count($content,1,'’');
        foreach($matches as $key => $word) {
            echo "Processing word $key/".count($matches)."\n";
            $word = $this->filterWord($word);
            if(!$this->isWord($word)){
                continue;
            }
            $wordObj=Word::where(array('value'=>strtolower($word),'language'=>$language))->first();
            if(empty($wordObj)) {
                $wordObj = new Word();
                $wordObj->value = strtolower($word);
                $wordObj->language = $language;
                $wordObj->frequence = 0;
            }
            $wordObj->frequence = $wordObj->frequence + 1;
            $wordObj->save();
        }

    }

    protected function filterWord($word) {
       $word = trim($word,"'"); 
       $word = trim($word,"-"); 

       return $word;
    }

    protected function isWord($word) {

        $err = false;

        // one letter word
        if(strlen($word)==1) {
            if(!in_array($word,array('a','i','o')))
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
}
