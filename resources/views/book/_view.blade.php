Book Title: {{ $book->title }}<br>
Top 100 words: {{ round($book->book_top100,2) }}%<br>
20% -> {{ round($book->book_pareto_20,9) }}%<br>
+20% -> {{ round($book->book_pareto_above_20,9) }}%<br>
Mean 20/line -> {{ round($book->getMean20(),8) }}%<br>
Mean +20/line  higher is easier -> {{ round($book->getMeanAbove20(),8) }}%<br>
Rapport mean Line +20/20 -> {{ round($book->getMeanAbove20()/($book->getMean20()),8) }} <br>
Total top100 -> {{ round($book->getTotal100(),8) }}<br>
Tota 20% -> {{ round($book->getTotal20(),8) }}<br>
Total +20% -> {{ round($book->getTotalAbove20(),8) }}<br>
Rapport % +20/20 -> {{ round(($book->getTotalAbove20()*100)/($book->gettotal20()+$book->getTotalAbove20()),9) }}%<br>
Above20/Top100 -> {{ round($book->getTotal100()/$book->getTotal20(),9) }} <br>
Mean Above20/Top100 -> {{ round($book->getMeanTop100()/$book->getMean20(),9) }} <br>
Mean -> {{ round($book->getMean20(),9) }} <br>
style -> {{ round(($book->getMeanAbove20()/($book->getMeanTop100()+$book->getMean20()))+($book->getMean20()/$book->getMeanTop100()),9) }} <br>
 <!-- <div id="chart_div100{{ $book->id}}" style="height:400px"></div>
 -->     
<script>
//google.charts.setOnLoadCallback(drawBasic100{{ $book->id}});

function drawBasic100{{ $book->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Common words');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},       {{ $line->count_top100 > 0 ? ( $line->sum_top100) / $line->count_top100: 0 }}],
        @endforeach
      ]);

      var options = {
        hAxis: {
          title: '{{ $book->title}}'
        },
        vAxis: {
          title: '% of top 100 words'
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_div100{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>
<!--  <div id="chart_div{{ $book->id}}" style="height:400px"></div>
-->      
<script>
//google.charts.setOnLoadCallback(drawBasic{{ $book->id}});

function drawBasic{{ $book->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Common words');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},       {{ $line->count_pareto_20 > 0 ? ( $line->sum_pareto_20)  : 0 }}],
        @endforeach
      ]);

      var options = {
        hAxis: {
          title: '{{ $book->title}}'
        },
        vAxis: {
          title: '% of common words in the line'
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_div{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>
  <div id="chart_divRare{{ $book->id}}" style="height:400px"></div>

<script>
google.charts.setOnLoadCallback(drawRare{{ $book->id}});

function drawRare{{ $book->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Line complexity');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},      {{ $line->count_pareto_above_20 > 0 ? $line->sum_pareto_above_20  / $line->count_words: 0 }}],
        @endforeach
      ]);

      var options = {
        hAxis: {
          title: '{{ $book->title}}'
        },
        vAxis: {
          title: 'Complexity score'
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_divRare{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>
<!--  <div id="chart_divLength{{ $book->id}}" style="height:400px"></div>
-->
<script>
//google.charts.setOnLoadCallback(drawLength{{ $book->id}});

function drawLength{{ $book->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Line length');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},      {{ $line->count_pareto_above_20 + $line->count_pareto_20 }}],
        @endforeach
      ]);

      var options = {
        hAxis: {
          title: '{{ $book->title}}'
        },
        vAxis: {
          title: 'Words per line'
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_divLength{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>
<hr>
