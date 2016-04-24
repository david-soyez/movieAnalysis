<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Book;

class BookController extends Controller
{
    /**
     * Show the book.
     *
     * @param  tile $title
     * @return Response
     */
    public function view($title)
    {
        $bookObj = Book::where('title',$title)->first();

        return view('book.view', ['book' => $bookObj]);
    }
}
