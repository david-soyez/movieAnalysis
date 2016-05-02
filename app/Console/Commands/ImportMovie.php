<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Captioning\Format\SubripFile;
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
    protected $signature = 'pareto:importmovie {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import movie from srt subtitle file';

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
        $filename = getcwd().'/'.$this->argument('filename'); 
        
        // removes trailing white spaces in file
        $content = file_get_contents($filename);
        file_put_contents($filename,trim($content));

        $language = 'en';
        bcscale(14);
        
        // opens the subtitle file
        $this->subrip = new SubripFile($filename);

        if(empty($this->subrip)) {
            die('error opening subtitle. exiting.');
        }

        // creates the movie in database
        $movieObj = new Movie();
        $movieObj->title = $filename;
        $movieObj->language = $language;
        $movieObj->save();

        if(empty($movieObj)) {
            die('error creating movie in database. exiting.');
        }

        // creates the subtitle in database
        $subtitleObj = new MovieSubtitle();
        $subtitleObj->movie_id = $movieObj->id;
        $subtitleObj->filename = $filename;
        $subtitleObj->language = $language;
        $subtitleObj->save();
        
        if(empty($subtitleObj)) {
            die('error creating subtitle in database. exiting.');
        }

        // regroups the lines in every cues to remove unecessary line breaks
        $this->regroupCuesLines();

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
            //$cueObject->computeScore();
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
                $score+= $cue->score;
            }
            
            // if above max delay conversation finish the conversation and start a new one
            if($cue->timeline_start - $currentConversation->timeline_end >= 1000) {
                $currentConversation->cues_time = $cuesTime;
                $currentConversation->count_words = $countWords;
                $currentConversation->strlen = $strlen;
                $currentConversation->score = $score;
                $cuesTime = 0;
                $score = 0;
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
                $score+= $cue->score;
                if ($i == count($this->cues)) {
                    $currentConversation->cues_time = $cuesTime;
                    $currentConversation->count_words = $countWords;
                    $currentConversation->strlen = $strlen;
                    $currentConversation->score = $score;
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
                $score+= $cue->score;
                $currentConversation->cues_time = $cuesTime;
                $currentConversation->count_words = $countWords;
                $currentConversation->strlen = $strlen;
                $currentConversation->score = $score;
                $cuesTime = 0;
                $score = 0;
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
                $score+= $cue->score;
            }

        }

        // loops through conversation to score them
        $conversationScore = array();
        $conversationStrlen = array();
        $conversationReadingSpeed = 0;
        $countConversations= count($this->conversations);
        $i=0;
        foreach($this->conversations as $conversation) {
            $i++;
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
        $subtitleObj->cover_75 = $subtitleObj->findCovering(75);
        $subtitleObj->save();
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
