<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MovieSubtitle;
use DB;

class SubtitleDownloaderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sub:missing {lang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing subtitles';

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
        $lang = $this->argument('lang'); 
        
        // finds movies without subtitles
        $movies = DB::select("SELECT * FROM movies where id NOT IN(SELECT movie_id FROM movie_subtitles)");
        $this->output->progressStart(count($movies));
        foreach($movies as $movie) {
            switch($movie->original_language) {
                case 'fr':
                    $lang = 'fre';
                    break;
                case 'en':
                default:
                    $lang = 'eng';
                    break;
            }

            $output = shell_exec('node '.__DIR__.'/findsub.js '.$movie->imdb_id." ".$lang);
            $data = json_decode($output,true);
            if(empty($data)) {
                $this->error('error or no subtitle found with movie '.$movie->imdb_id);
                $this->output->progressAdvance();
                continue; 
            }

            // find the best subtitle
            if(isset($data[$movie->original_language])) {
                $subs = $data[$movie->original_language];
                $lang = $movie->original_language;
            }
            elseif(isset($data[$lang])) {
                $subs = $data[$lang];
                $lang = $lang;
            }

            if(empty($subs)) {
                $this->error('no subtitle for that language with movie '.$movie->imdb_id);
                $this->output->progressAdvance();
                continue; 
            }

            $best = null;
            if(false) {
                foreach($subs as $sub) {
                    if(!empty($best) && $best['downloads']< $sub['downloads'] && $sub['lang'] == $lang) {
                       $best = $sub; 
                    }
                    if(empty($best)) {
                       $best = $sub; 
                    }
                }
            } else {
                $best = $subs;
            }

            // if we have a winner download it
            if(!empty($best)) {
                $subPath = 'opensubtitles/'.$best['id'].'.srt';
                @file_put_contents(__DIR__.'/../../../storage/'.$subPath,file_get_contents($best['url']));
                
                // if file is there then add a subtitleMovie line to database
                $subMovie = new MovieSubtitle();
                $subMovie->movie_id = $movie->id;
                $subMovie->filename = $subPath;
                $subMovie->is_pending = true;
                $subMovie->language = $movie->original_language;
                $subMovie->save();
            }

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
