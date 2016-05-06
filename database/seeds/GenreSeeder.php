<?php

use Illuminate\Database\Seeder;
use App\Genre;

class GenreSeeder extends Seeder
{
    /**
    * The movie database api key
     */
    const API_KEY = 'dda5d2c2eb74ee9b5da15dfc69bc3c94';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $result = json_decode(file_get_contents('http://api.themoviedb.org/3/genre/movie/list?api_key='.self::API_KEY),true);

        foreach($result['genres'] as $genre) {
            $genreObj = new Genre();
            $genreObj->id = $genre['id'];
            $genreObj->name = $genre['name'];
            $genreObj->save();
        }
    }
}
