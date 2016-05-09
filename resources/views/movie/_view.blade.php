<?php 
$subtitle = $movie->subtitle();
$conversations = $subtitle->conversations;
$hardestDialogue = $subtitle->getHardest();
$uniqueWords = count($subtitle->words);
$totalWords = $subtitle->getWordsCount();
?>
          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <header class="section__play-btn mdl-cell mdl-cell--3-col-desktop mdl-cell--2-col-tablet mdl-cell--4-col-phone mdl-color--teal-100 mdl-color-text--white">
                <img src="/images/posters/{{ $movie->poster_path}}" width="215px"> 
            </header>
            <div class="mdl-card mdl-cell mdl-cell--9-col-desktop mdl-cell--6-col-tablet mdl-cell--4-col-phone">
              <div class="mdl-card__supporting-text">

<h4>{{ $movie->title }}
        <span class="preview_movie_rate"><i class="material-icons">hearing</i>Level {{ round($movie->subtitle()->getLevel(),1) }}</span>
</h4>
                {{ $movie->overview}}
              </div>
            </div>
          </section>          

          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>Dialogue Chart</h4>
                  <div id="chart_divRare{{ $movie->id}}" style="height:400px">Loading...</div>
              </div>
            </div>
          </section>   

          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>Comprehension</h4>
                  <div id="chart_progress" style="height:400px">Loading...</div>
              </div>
            </div>
          </section>             

          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>Stats</h4>
                    <ul>
                    <li>Dialogues: {{ count($subtitle->conversations) }}</li>
                    <li>Unique words: {{ $uniqueWords }}</li>
                    <li>90% of the movie is dispersed in the range of the {{ $subtitle->cword_80 }} most used words of the language</li>
                    <li>Dialogue comprehension deviation: {{ round($subtitle->std_score,2) }}%</li>
                    <li>Bad words: {{ $subtitle->count_badwords }}</li>
                    <li>Contractions: {{ $subtitle->count_contractions }}</li>
                    </ul>
              </div>
            </div>
          </section>             

          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>Most challenging dialogue ({{ $hardestDialogue->count_words }} words - {{ floor($hardestDialogue->score) }} % comprehension)</h4>
                    <pre>
                    {{ $hardestDialogue->caption }}
                    </pre>
              </div>
            </div>
          </section>   
<script>
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawRare{{ $movie->id}});
google.charts.setOnLoadCallback(drawProgress);

function drawRare{{ $movie->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Dialogue streak (#words)');

      data.addRows([
        @foreach ($conversations as $conversation)
        <?php 
            if(isset($end)) {
                for($i=$end/1000;$i<$conversation->timeline_start/1000;$i++) {
                ?>
                    [{{ $i/60 }}, 0],
                <?php
                }
            }
            for($i=$conversation->timeline_start/ 1000/6;$i<=$conversation->timeline_end/ 1000/6;$i++):?>
        [{{ $i/10 }},      {{ (float)($conversation->count_words) }}],
<?php 
            $end=$conversation->timeline_end+1; 
            $endReadingSpeed =(float)($conversation->count_words- (($conversation->count_words*(100-$conversation->score))/100)) ;
            
            endfor; ?>
        @endforeach
      ]);

      var options = {
      bar: {groupWidth: 90},
          colors: ['blue','green'],
        hAxis: {
          title: '{{ $movie->title}} (length in minutes)'
        },
        series: {0: {type: 'bar',targetAxisIndex:0}},
        vAxes: { 0: {logScale: false,title: 'Dialogue streak (#words)',maxValue:300} },
  
      };

      var chart = new google.visualization.AreaChart(document.getElementById('chart_divRare{{ $movie->id}}'));

      chart.draw(data, options);
    }

function drawProgress() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Vocabulary');

      data.addRows([
          @foreach(json_decode($subtitle->cword,true) as $_key => $_data)
<?php if($_key<80) continue; ?>
          [{{ $_data }}, {{ $_key }}], 
          @endforeach
      ]);

      var options = {
        hAxis: {
          title: 'Number of words'
        },
        vAxis: {
          title: 'Comprehension(%)'
        }
      };

      var chartProgress = new google.visualization.LineChart(document.getElementById('chart_progress'));

      chartProgress.draw(data, options);
    }
 </script>

