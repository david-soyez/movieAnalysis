@extends('layouts.master')

@section('content')
          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>What is this website?</h4>
                This website is an attempt to classify the difficulty behind the understanding of english movies when learning the language. The score shows the level of vocabulary you should have to understand 90% of the movie. Every movies are compared to the same vocabulary levels.
              </div>
              <div class="mdl-card__actions">
                <a href="#" class="mdl-button">Read more</a>
              </div>
            </div>
          </section>    

          @each('movie._preview', $movies, 'movie')
          <section class="section--footer mdl-color--white mdl-grid">
            <div class="section__circle-container mdl-cell mdl-cell--2-col mdl-cell--1-col-phone">
              <div class="section__circle-container__circle mdl-color--accent section__circle--big"></div>
            </div>
            <div class="section__text mdl-cell mdl-cell--4-col-desktop mdl-cell--6-col-tablet mdl-cell--3-col-phone">
              <h5>Movies and TV shows</h5>
                TV shows and movies do not have necessarily the same difficulty. While most of the tv shows tend to have a quicker listening speed compared to movies, some of some are more slow paced.
          </div>
            <div class="section__circle-container mdl-cell mdl-cell--2-col mdl-cell--1-col-phone">
              <div class="section__circle-container__circle mdl-color--accent section__circle--big"></div>
            </div>
            <div class="section__text mdl-cell mdl-cell--4-col-desktop mdl-cell--6-col-tablet mdl-cell--3-col-phone">
              <h5>Speed vs Words</h5>
                The two import criterias that you should keep in mind when choosing a movie are conversations speed and word difficulties. Even a movie with a few tough words can become difficult because of the speed of the dialogues. 
            </div>
          </section>
@endsection
