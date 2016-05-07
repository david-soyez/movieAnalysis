<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Captioning\Format\SubripFile;
use App\Word;
use App\Movie;
use App\MovieSubtitle;
use App\SubtitleCue;
use App\SubtitleConversation;

class ImportMovie extends Command
{
    /*
     * Max delay between conversation in milliseconds
     */
    const MAX_DELAY_CONVERSATION = 30000;

    /*
     * Max delay between conversation in milliseconds
     */
    const MIN_DELAY_CONVERSATION = 1;

    protected $subrip;

    protected $cues=array();

    protected $conversations = array();

    protected $delay_conversation = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pareto:next';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the next pending subtitle';

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
        // get the next subtitle to process
        $subtitleObj = MovieSubtitle::where(array('is_error'=>false,'is_pending'=>true))->first();
        
        // if no pending subtitle
        if(empty($subtitleObj)) {
            $this->error('No pending subtitle.');
            die();
        }

        bcscale(14);
 
        $filename = __DIR__.'/../../../storage/'.$subtitleObj->filename; 
        
        // removes trailing white spaces in file
        $content = file_get_contents($filename);

        // convert english contractions
        if($subtitleObj->movie->original_language == 'en') {
            $content = Word::reverseEnglishContractions($content);
        }       
 
        file_put_contents($filename.'.tmp',trim($content));
        

        try {
            // opens the subtitle file
            $this->subrip = new SubripFile($filename.'.tmp');
        } catch(Exception $e) {
            $subtitleObj->is_error = true;
            $subtitleObj->code_error = var_export($e,true);
            $subtitleObj->save();
            die();
        }

        if(empty($this->subrip)) {
            $subtitleObj->is_error = true;
            $subtitleObj->code_error = 'error opening file empty';
            $subtitleObj->save();
            die('error opening subtitle. exiting.');
        }

        // loads the movie in database
        $movieObj = $subtitleObj->movie;


        // regroups the lines in every cues to remove unecessary line breaks
        $this->regroupCuesLines();

        // finds the length of the movie
        $movieLength = 0;
        if(($lastCue = $this->subrip->getLastCue())!=null) {
            $movieLength = round($lastCue->getStopMS()/1000/60,2);
        }

        // is not a movie if length < 15 minutes
        if($movieLength <= 15) {
            $error = 'Length is too short to be a movie or a tv show: '.$movieLength.' minutes.';
            $subtitleObj->is_error = true;
            $subtitleObj->code_error = $error;
            $subtitleObj->save();
            $this->error($error);
            return false;
        } 

        // finds the best delay between conversation
        $delays = array();
        $previousCue = null;
        foreach($this->subrip->getCues() as $cue) {
            if($previousCue == null) {
                    $previousCue = $cue;
                    continue;
            } 
            if(($cue->getStopMS() - $previousCue->getStartMS()) <= self::MAX_DELAY_CONVERSATION && ($cue->getStopMS() - $previousCue->getStartMS()) >= self::MIN_DELAY_CONVERSATION) {
                $delays[] = $cue->getStopMS() - $previousCue->getStartMS();
            }
            $previousCue = $cue;
        }
        $this->delay_conversation = 2000;//$this->sd($delays);
        
        // creates cues objects
        $i=0;
        foreach($this->subrip->getCues() as $cue) {
            $i++;
            echo "Writing cues to database $i/".count($this->subrip->getCues())."\n";
            $cueObject = new SubtitleCue();
            $cueObject->subtitle_id = $subtitleObj->id;
            $cueObject->timeline_start = $cue->getStartMS();
            $cueObject->timeline_end = $cue->getStopMS();
            $cueObject->caption = $cue->getStrippedText(true);
            $cueObject->readingspeed = $cue->getReadingSpeed();
            if($cueObject->readingspeed > 100) {
                $cueObject->readingspeed = 20;
            }
            $cueObject->cps = $cue->getCPS();
            $cueObject->strlen = $cue->strlen();
            $cueObject->addWordsFrequences();
            //$cueObject->score = $cueObject->findCovering(80);

            // do not take ads
            if(stripos($cueObject->caption,'opensubtitle')!== false ||
                (stripos($cueObject->caption, 'rate')!== false && stripos($cueObject->caption, 'subtitle')!== false) 
                || ($i >= count($this->subrip->getCues())-3 && stripos($cueObject->caption,'made by')!== false)
                || ($i >= count($this->subrip->getCues())-3 && stripos($cueObject->caption,'rate')!== false)
                || ($i >= count($this->subrip->getCues())-3 && stripos($cueObject->caption,'support')!== false)
            ) {
                continue;
            } 

            
            $cueObject->save();
            $this->cues[] = $cueObject;
        }

        // loops on cues and creates new conversation when needed
        $newConversation = true;
        $endConversation = false;
        $readingspeed = array();
        $cps = array();
        $cuesTime = 0;
        $countWords = 0;
        $strlen = 0;
        $score = 0;
        $i=0;
        foreach($this->cues as $cue) {
            $i++;
            echo "Finding conversation for cue #$i\n";
            if($i==1) {
                $currentConversation = new SubtitleConversation();
                $currentConversation->subtitle_id = $subtitleObj->id;
                $currentConversation->timeline_start = $cue->timeline_start; 
                $currentConversation->timeline_end = $cue->timeline_end; 
                $currentConversation->caption = $cue->caption;
                $readingspeed[] = $cue->readingspeed;
                $cps[] = $cue->cps;
                $cuesTime += $cue->timeline_end - $cue->timeline_start;
                $countWords+= $cue->count_words;
                $strlen+= $cue->strlen;
            }
            
            // if above max delay conversation finish the conversation and start a new one
            if($cue->timeline_start - $currentConversation->timeline_end >= 1000) {
                $currentConversation->cues_time = $cuesTime;
                $currentConversation->count_words = $countWords;
                $currentConversation->strlen = $strlen;
                $cuesTime = 0;
                $countWords = 0;
                $strlen = 0;
                $currentConversation->readingspeed = array_sum($readingspeed)/count($readingspeed);
                $currentConversation->cps= array_sum($cps)/count($cps);
                $currentConversation->strlen = mb_strlen($currentConversation->caption, 'UTF-8'); 
                $currentConversation->save();
                $this->conversations[] = $currentConversation;

                $currentConversation = new SubtitleConversation();
                $currentConversation->subtitle_id = $subtitleObj->id;
                $currentConversation->timeline_start = $cue->timeline_start; 
                $currentConversation->timeline_end = $cue->timeline_end; 
                $readingspeed = array();
                $cps = array();
                $currentConversation->caption = $cue->caption;
                $readingspeed[] = $cue->readingspeed;
                $cps[] = $cue->cps;
                $cuesTime += $cue->timeline_end - $cue->timeline_start;
                $countWords+= $cue->count_words;
                $strlen+= $cue->strlen;
                if ($i == count($this->cues)) {
                    $currentConversation->cues_time = $cuesTime;
                    $currentConversation->count_words = $countWords;
                    $currentConversation->strlen = $strlen;
                    $currentConversation->readingspeed = array_sum($readingspeed)/count($readingspeed);
                    $currentConversation->cps= array_sum($cps)/count($cps);
                    $currentConversation->strlen = mb_strlen($currentConversation->caption, 'UTF-8'); 
                    $currentConversation->save();
                    $this->conversations[] = $currentConversation;
                }
            } elseif ($i == count($this->cues)) {
                $currentConversation->timeline_end = $cue->timeline_end; 
                if(!empty($currentConversation->caption)) {
                    $currentConversation->caption .= "\n";
                }
                $currentConversation->caption .= $cue->caption;
                $readingspeed[] = $cue->readingspeed;
                $cps[] = $cue->cps;
                $cuesTime += $cue->timeline_end - $cue->timeline_start;
                $countWords+= $cue->count_words;
                $strlen+= $cue->strlen;
                $currentConversation->cues_time = $cuesTime;
                $currentConversation->count_words = $countWords;
                $currentConversation->strlen = $strlen;
                $cuesTime = 0;
                $countWords = 0;
                $strlen = 0;
                $currentConversation->readingspeed = array_sum($readingspeed)/count($readingspeed);
                $currentConversation->cps= array_sum($cps)/count($cps);
                $currentConversation->strlen = mb_strlen($currentConversation->caption, 'UTF-8'); 
                $currentConversation->save();
                $this->conversations[] = $currentConversation;
            } elseif($i!=1) {
                $currentConversation->timeline_end = $cue->timeline_end; 
                if(!empty($currentConversation->caption)) {
                    $currentConversation->caption .= "\n";
                }
                $currentConversation->caption .= $cue->caption;
                $readingspeed[] = $cue->readingspeed;
                $cps[] = $cue->cps;
                $cuesTime += $cue->timeline_end - $cue->timeline_start;
                $countWords+= $cue->count_words;
                $strlen+= $cue->strlen;
            }

        }

        // computes subtitle score
        $cword80 = $subtitleObj->findCovering(90);

        // loops through conversation to score them
        $conversationScore = array();
        $conversationStrlen = array();
        $conversationReadingSpeed = 0;
        $countConversations= count($this->conversations);
        $i=0;
        foreach($this->conversations as $conversation) {
            $i++;
            echo "Find score for cword $cword80 in conversation #$i\n";
            // finds how much you can read with these words
            $conversation->score = $conversation->findScore($cword80);
            $conversation->save();
            $conversationScore[] = $conversation->score;
            $conversationStrlen[] = $conversation->strlen;
        }

        // mean reading speed from cues
        $meanReadingSpeed = array();
        $meanCPS = 0;
        $strlen = 0;
        foreach($this->cues as $cue) {
            $meanReadingSpeed[] = $cue->readingspeed;
            $meanCPS += $cue->cps;
            $strlen += $cue->strlen;
        }
        $meanCPS = $meanCPS/ count($this->cues);

        $subtitleObj->cps = $meanCPS;
        $subtitleObj->sum_strlen = $strlen;
        $subtitleObj->mean_strlen = array_sum($conversationStrlen)/count($conversationStrlen);
        $subtitleObj->std_strlen = $this->sd($conversationStrlen);
        $subtitleObj->sum_score = array_sum($conversationScore);
        $subtitleObj->mean_score = array_sum($conversationScore)/count($conversationScore);
        $subtitleObj->std_score = $this->sd($conversationScore);
        $subtitleObj->readingspeed = array_sum($meanReadingSpeed) / count($meanReadingSpeed);
        $subtitleObj->std_readingspeed = $this->sd($meanReadingSpeed);
        $subtitleObj->delay_conversation = $this->delay_conversation;
        $subtitleObj->cword_80 = $cword80;
        $subtitleObj->count_badwords = Word::$count_badwords;
        $subtitleObj->count_contractions = Word::$count_contractions;
        $subtitleObj->is_pending = 0;
        $subtitleObj->save();

        // set the movie active
        $movieObj->is_pending = false;
        $movieObj->is_active = true;
        $movieObj->save();
    }

    protected function regroupLines($lines) {
        $newLines = array();
        $content = '';
        $startNew = true;
        $i = 0;
        foreach($lines as $line) {
            $i++;
            $line = trim($line);

            // ce qui delimite une fin de ligne
            // commence par un tiret  OU termine par un point
            if($line[0] == '-') {
                if(!empty($content)) {
                    $newLines[] = $content;
                }
                $newLines[] = $line;
                $content = '';
                continue;
            } 
            else if($line[mb_strlen($line,'UTF-8')-1] == '.' || $line[mb_strlen($line,'UTF-8')-1] == '?' || $line[mb_strlen($line,'UTF-8')-1] == '!') {
                $content .= ' '.$line;
                $newLines[] = $content;
                $content = '';
            } else if($i == count($lines)) {
                $content .= ' '.$line;
                $newLines[] = $content;
            } else {
                $content .= ' '.$line;
            }

        }

        return $newLines;
    }

    public function regroupCuesLines() {
        foreach($this->subrip->getCues() as $cue) {
            $cue->setText(implode("\n",$this->regroupLines($cue->GetTextLines())));
        }
    }
    
    // Function to calculate square of value - mean
    function sd_square($x, $mean) { 
        return pow($x - $mean,2); 
    }

    // Function to calculate standard deviation (uses sd_square)    
    function sd($array) {
        // square root of sum of squares devided by N-1
        return sqrt(array_sum(array_map(array($this,"sd_square"), $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
    }
}
