<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Book;
use App\BookWord;
use App\BookLine;
use App\Word;
use DB;

class ImportBook extends Command
{
    /*
     * Number of unique words in the book
     */
    protected $unique_words_count = 0;
    
    /*
     * Number of totaltotal  words in the book
     */
    protected $total_words_count = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pareto:importbook {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a book with the given filename';

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
        /*
        DB::table('book_words')->truncate();
        DB::table('book_lines')->truncate();
        DB::table('words')->truncate();
        DB::table('books')->truncate();
        */ 

        $filename = $this->argument('filename'); 
        $language = 'en';
        $content = file_get_contents($filename);
        $matches = array();
        $frequencies = array();
        $percents = array();
        bcscale(14);

        // creates the book in database
        $bookObj = new Book();
        $bookObj->title = $filename;
        $bookObj->author = 'NA';
        $bookObj->description = 'NA';
        $bookObj->language = $language;
        $bookObj->save();

        // filter content
        $content = str_replace('’',"'",$content);
        $content = str_replace('--'," ",$content);

        // calculate mean line frequence
        $lines = preg_split('/[\.\n]+/',$content);

        $wordsFound=0;
        foreach($lines as $line) {
            $matches = str_word_count($line,1,'’');
            foreach($matches as $word) {
                $word = $this->filterWord($word);
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
        arsort($frequencies);
        $this->total_words_count = $wordsFound;
        $this->unique_words_count = count($frequencies);

        // write the words to database
        echo "Writing words to database\n";
        foreach($frequencies as $word => $frequence) {
            $wordObj=Word::where(array('value'=>strtolower($word),'language'=>$language))->first();
            if(empty($wordObj)) {
                $wordObj = new Word();
                $wordObj->value = strtolower($word);
                $wordObj->language = $language;
                $wordObj->save();
            }
            
            // adds the word entry for the book
            $bookWord = new BookWord();
            $bookWord->book_id = $bookObj->id;
            $bookWord->word_id = $wordObj->id;
            $bookWord->frequence = $frequence;
            $bookWord->percentage = bcdiv(($frequence*100),$this->total_words_count);
            $bookWord->save();
        }
        
        // adds the words in database
        $linesAdded=0;
        $i=0;
        $timeToProcessALine=0;
        $timeBeforeProcessingLines = time();
        foreach($lines as $keyLine => $line) {
            $lineWordsAdded=0;

            // displays time left
            $timeLeftUnit = 'seconds';
            $timeLeft = ((microtime(true)-$timeBeforeProcessingLines)/($keyLine+1) *(count($lines)-($keyLine+1)));
            if($timeLeft/60 > 0) {
               $timeLeftUnit = 'minutes'; 
               $timeLeft = ceil($timeLeft/60);
            }
            echo "Time left ".$timeLeft." ".$timeLeftUnit.".\n" ;
            // ***************

            $matches = str_word_count($line,1,'’');
            $lineWords = array();
            foreach($matches as $word ) {
                $word = $this->filterWord($word);
                if(!$this->isWord($word)){
                    continue;
                }
                // increase the number of words in this line
                $lineWordsAdded++;
                $word = strtolower($word);
                $wordTopPosition = (array_search($word,array_keys($frequencies))+1);
                // check if the word already exists in database 
                $wordObj=Word::where(array('value'=>strtolower($word),'language'=>$language))->first();

                $bookWord=BookWord::where(array('book_id'=>$bookObj->id,'word_id'=>$wordObj->id))->first();

                $lineWords[] = $bookWord;
            }        

            // Adds the line only if there are words added in this line
            if($lineWordsAdded> 0) {
                $linesAdded++;
                $bookLine = new BookLine();
                $bookLine->book_id = $bookObj->id;
                $bookLine->position = $linesAdded;
                $bookLine->save();
            }

            $this->lineWriteStats($bookObj, $bookLine, $lineWords);
            $bookLine->save();
        }

        // lines processing done
        $bookObj->book_top100 = $this->getBookTop100($bookObj);
        $bookObj->book_pareto_20 = $this->getBookPareto20($bookObj);
        $bookObj->book_pareto_above_20 = $this->getBookParetoAbove20($bookObj);
        $bookObj->save();

        // loops through all words to write word stats
        echo "Writing words stats..\n";
        $percentage_cumul = 0;
        $percentage_book_cumul = 0;
        $bookWords = BookWord::where(array('book_id'=>$bookObj->id))->orderBy('frequence', 'desc')->get();
        $countWords = count($bookWords);
        $i=0;
        foreach($bookWords as & $_bookWord) {
            $i++;
            
            // percentage_book_cumul
            $percentage_book_cumul = bcadd($percentage_book_cumul,$_bookWord->percentage); 
            $_bookWord->percentage_book_cumul += $percentage_book_cumul;
            
            // percentage_cumul
            $percentage_cumul = bcdiv($i*100,$countWords);
            $_bookWord->percentage_cumul = $percentage_cumul;

            // save the word
            $_bookWord->save();
        }
    }

    protected function lineWriteStats($book, & $line, & $lineWords) {
        $line->count_words = count($lineWords);

        // count the top100 words
        $top100words = array();
        $countTop100Words = 0;
        $sum_top100 = 0;
        $bookWords = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->take(100)->get(); 
        foreach($bookWords as $_bookWord) {
           $top100words[$_bookWord->word_id] = $_bookWord;
        }
        foreach($lineWords as $_bookWord) {
            if(isset($top100words[$_bookWord->word_id])) {
                $countTop100Words++; 
                $sum_top100 = bcadd($sum_top100,$_bookWord->percentage);
            } 
        }
        $line->count_top100 = $countTop100Words;

        // count the 20% pareto words
        $wordsFrequences = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->sum('frequence'); 

        // how many words to get 20%
        $countWords20Percent = ceil(($this->unique_words_count*20)/100);
        
        // get the total frequence for this 20%
        $bookWords = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->take($countWords20Percent)->get();         

        $percent20words = array();
        foreach($bookWords as $_bookWord) {
           $percent20words[$_bookWord->word_id] = $_bookWord;
        }

        $count20PercentWords = 0;
        $sum_pareto_20 = 0;
        foreach($lineWords as $_bookWord) {
            if(isset($percent20words[$_bookWord->word_id])) {
                $count20PercentWords++; 
                $sum_pareto_20 = bcadd($sum_pareto_20,$_bookWord->percentage);
            } 
        }

        $line->count_pareto_20 = $count20PercentWords;

        // count the above 20% pareto words
        $wordsFrequences = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->sum('frequence'); 

        // get the total frequence for above 20%
        $bookWords = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->limit($this->unique_words_count)->offset($countWords20Percent)->get(); 


        $percentAbove20words = array();
        foreach($bookWords as $_bookWord) {
           $percentAbove20words[$_bookWord->word_id] = $_bookWord;
        }

        $countAbove20PercentWords = 0;
        $sum_pareto_above_20 = 0;
        foreach($lineWords as $_bookWord) {
            if(isset($percentAbove20words[$_bookWord->word_id])) {
                $countAbove20PercentWords++; 
                $sum_pareto_above_20 = bcadd($sum_pareto_above_20,$_bookWord->percentage);
            } 
        }
        $line->count_pareto_above_20 = $countAbove20PercentWords;

        // sum of top100 words
        $line->sum_top100 = $sum_top100;

        // sum of 20% pareto words
        $line->sum_pareto_20 = $sum_pareto_20;

        // sum of above 20% pareto words
        $line->sum_pareto_above_20 = $sum_pareto_above_20;
    }

    /*
     * Return the percentage of the book that the top 100 words covers
     * @return float
     */
    protected function getBookTop100($book) {
        $top100words = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->take(100)->get(); 
        $wordsFrequences = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->sum('frequence'); 
        $frequences = 0;
        foreach($top100words as $word) {
            $frequences += $word->frequence;
        }
        return ($frequences*100)/$wordsFrequences;
    }

    /*
     * Return the corresponding percentage to Pareto 20%
     * @return float
     */
    protected function getBookPareto20($book) {
        // total number of words in the book
        $wordsFrequences = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->sum('frequence'); 

        // number of unique words in the book
        $countUniqueWords = BookWord::where(array('book_id'=>$book->id))->count(); 
        
        // how many words to get to have 20%
        $countWords20Percent = ceil(($countUniqueWords*20)/100);
        
        // get the total frequence for this 20%
        $percent20words = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->take($countWords20Percent)->get(); 
        
        $frequences = 0;
        foreach($percent20words as $word) {
            $frequences += $word->frequence;
        }
        return ($frequences*100)/$wordsFrequences;
    }

    /*
     * Return the corresponding percentage to Pareto above 20%
     * @return float
     */
    protected function getBookParetoAbove20($book) {
        // total number of words in the book
        $wordsFrequences = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->sum('frequence'); 

        // number of unique words in the book
        $countUniqueWords = BookWord::where(array('book_id'=>$book->id))->count(); 
        
        // how many words to get to have 20%
        $countWords20Percent = ceil(($countUniqueWords*20)/100);
        
        // get the total frequence for above 20%
        $percent20words = BookWord::where(array('book_id'=>$book->id))->orderBy('frequence','desc')->limit($countUniqueWords)->offset($countWords20Percent)->get(); 
        
        $frequences = 0;
        foreach($percent20words as $word) {
            $frequences += $word->frequence;
        }
        return ($frequences*100)/$wordsFrequences;
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

    protected function filterWord($word) {
       $word = trim($word,"'"); 
       $word = trim($word,"-"); 

       return $word;
    }
}
