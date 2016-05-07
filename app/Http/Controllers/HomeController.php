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
        $moviesObj= Movie::where(array('is_active'=>true,'is_pending'=>false,'is_deleted'=>false))->get();

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

