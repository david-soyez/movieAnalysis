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
        $moviesObj= Movie::all();

        return view('home.view', ['movies' => $moviesObj]);
    }    
    
    /**
     * Show the homepage.
     *
     * @return Response
     */
    public function view()
    {
        $moviesObj= Movie::all();

        return view('home.view', ['movies' => $moviesObj]);
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

