<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Book;

class HomeController extends Controller
{    
    /**
     * Show the homepage.
     *
     * @return Response
     */
    public function view()
    {
        $booksObj = Book::all();

        return view('home.view', ['books' => $booksObj]);
    }

    /**
     * Compare the books on chart.
     *
     * @return Response
     */
    public function compare()
    {
        $booksObj = Book::all();

        return view('book.compare', ['books' => $booksObj]);
    }
}

