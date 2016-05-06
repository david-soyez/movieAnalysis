@extends('layouts.master')

@section('content')
          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>What is this website?</h4>
                This website is an attempt to classify the difficulty behind the understanding of english movies when learning the language.
              </div>
              <div class="mdl-card__actions">
                <a href="#" class="mdl-button">Read more</a>
              </div>
            </div>
          </section>    

          @each('movie._preview', $movies, 'movie')
@endsection
