          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <header class="section__play-btn mdl-cell mdl-cell--3-col-desktop mdl-cell--2-col-tablet mdl-cell--4-col-phone mdl-color--teal-100 mdl-color-text--white">
                <img src="/images/posters/{{ $movie->poster_path}}" width="215px"> 
            </header>
            <div class="mdl-card mdl-cell mdl-cell--9-col-desktop mdl-cell--6-col-tablet mdl-cell--4-col-phone">
              <div class="mdl-card__supporting-text">

<h4><a class="movie_preview_title_rate" id="movie_{{ $movie->id}}" href="/movie/{{ $movie->id}}/{{ $movie->title}}" title="{{ $movie->title }}" alt="{{ $movie->title }}">{{ $movie->title }}</a>
        <span class="preview_movie_rate">Level <i class="material-icons">hearing</i>{{ round($movie->subtitle()->getLevel(),1) }}/10</span>
</h4>
                {{ $movie->overview}}
              </div>
              <div class="mdl-card__actions">
                <a href="/movie/{{ $movie->id}}/{{ $movie->title}}" class="mdl-button">Show details</a>
              </div>
            </div>
          </section>
