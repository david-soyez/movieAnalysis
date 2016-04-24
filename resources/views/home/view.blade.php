@extends('layouts.master')

@section('content')
    @each('book._view', $books, 'book')
@endsection
