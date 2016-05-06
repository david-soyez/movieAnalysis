          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <header class="section__play-btn mdl-cell mdl-cell--3-col-desktop mdl-cell--2-col-tablet mdl-cell--4-col-phone mdl-color--teal-100 mdl-color-text--white">
                <img src="/images/posters/{{ $movie->poster_path}}" width="215px"> 
            </header>
            <div class="mdl-card mdl-cell mdl-cell--9-col-desktop mdl-cell--6-col-tablet mdl-cell--4-col-phone">
              <div class="mdl-card__supporting-text">
                <h4>{{ $movie->title }}</h4>
                {{ $movie->overview}}
              </div>
              <div class="mdl-card__actions">
                <a href="#" class="mdl-button">Show details</a>
              </div>
            </div>
          </section>