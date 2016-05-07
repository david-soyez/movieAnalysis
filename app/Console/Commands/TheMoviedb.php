<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Movie;
use App\Genre;
use App\MovieGenre;
use DB;

class TheMoviedb extends Command
{
    /**
    * The movie database api key
     */
    const API_KEY = 'dda5d2c2eb74ee9b5da15dfc69bc3c94';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moviedb:popular {page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The movie database API wrapper';

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
        $page = $this->argument('page');
        $url = "http://api.themoviedb.org/3/movie/popular?page=$page&api_key=".self::API_KEY;
        $json = file_get_contents($url);
        if($json == null) {
            die('Error retrieving data');
        }
        $movies = json_decode($json,true);
        $this->output->progressStart(count($movies['results']));
        foreach($movies['results'] as $key => $movie) {

            // check if already existing
            $check = DB::select("SELECT id FROM movies where tmdb_id = '".$movie['id']."'" );
            if(!empty($check)) {
                continue;
            }

            $movieObj = new Movie();
            $movieObj->tmdb_id = $movie['id'];
            $movieObj->poster_path = $movie['poster_path'];
            $movieObj->overview = $movie['overview'];
            $movieObj->release_date = $movie['release_date'];
            $movieObj->original_title = $movie['original_title'];
            $movieObj->original_language = $movie['original_language'];
            $movieObj->title = $movie['title'];
            $movieObj->backdrop_path = $movie['backdrop_path'];
            $movieObj->popularity = $movie['popularity'];
            $movieObj->vote_count = $movie['vote_count'];
            $movieObj->video = $movie['video'];
            $movieObj->vote_average = $movie['vote_average'];
            $saved = $movieObj->save();
            //echo "Adding movie ".$movie['title']."...".($saved?'ok':'error')."\n";

            // creates genres
            foreach($movie['genre_ids'] as $genre_id) {
                $moviegenreObj = new MovieGenre();
                $moviegenreObj->movie_id = $movieObj->id;
                $moviegenreObj->genre_id = $genre_id;
                $saved = $moviegenreObj->save();
                //echo "Adding genre ".$genre_id."...".($saved?'ok':'error')."\n";
            }

            // get the movie page
            $url = "http://api.themoviedb.org/3/movie/".$movieObj->tmdb_id."?api_key=dda5d2c2eb74ee9b5da15dfc69bc3c94";

            $json = file_get_contents($url);
            if($json == null) {
                echo "Error retrieving data";
            }
            $movieDetails = json_decode($json,true);

            $movieObj->imdb_id = $movieDetails['imdb_id'];
            $movieObj->overview = $movieDetails['overview'];
            $movieObj->production_companies = json_encode($movieDetails['production_companies']);
            $movieObj->production_countries = json_encode($movieDetails['production_countries']);
            $movieObj->spoken_languages = json_encode($movieDetails['spoken_languages']);


            // download poster
            file_put_contents(__DIR__."/../../../public/images/posters/".trim($movieObj->poster_path,'/'),file_get_contents("https://image.tmdb.org/t/p/w300/".trim($movieObj->poster_path,'/'))); 

            // download backdrop
            file_put_contents(__DIR__."/../../../public/images/backdrops/".trim($movieObj->backdrop_path,'/'),file_get_contents("https://image.tmdb.org/t/p/w300/".trim($movieObj->backdrop_path,'/'))); 
            
            // activate movie

            $movieObj->is_active = false;
            $movieObj->is_pending = true;
            $movieObj->save();
            $this->output->progressAdvance();
            //echo "----------------------------------------------------------------\n";
        }
        $this->output->progressFinish();
    }


}
