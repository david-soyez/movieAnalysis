<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Book;
use App\Movie;

class HomeController extends Controller
{    
    /**
     * Show the homepage.
     *
     * @return Response
     */
    public function index()
    {
        $moviesObj= Movie::where(array('movies.is_active'=>true,'movies.is_pending'=>false,'movies.is_deleted'=>false))
        ->orderBy('level','asc')     
        ->get();


        return view('home.view', ['movies' => $moviesObj]);
    }    
    
    /**
     * Show the homepage.
     *
     * @return Response
     */
    public function view($id)
    {
        $movieObj= Movie::where(array('id'=>$id))->first();

        return view('movie.view', ['movie' => $movieObj]);
    }

    /**
     * Compare the books on chart.
     *
     * @return Response
     */
    public function compare()
    {
        $moviesObj = Movie::all();

        return view('movie.compare', ['movies' => $moviesObj]);
    }
}

