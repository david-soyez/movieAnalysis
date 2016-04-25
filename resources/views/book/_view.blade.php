Book Title: {{ $book->title }}<br>
Top 100 words: {{ round($book->book_top100,2) }}%<br>
20% -> {{ round($book->book_pareto_20,2) }}%<br>
+20% -> {{ round($book->book_pareto_above_20,2) }}%<br>
  <div id="chart_div100{{ $book->id}}" style="height:400px"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasic100{{ $book->id}});

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
  <div id="chart_div{{ $book->id}}" style="height:400px"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasic{{ $book->id}});

function drawBasic{{ $book->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Common words');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},       {{ $line->count_pareto_20 > 0 ? ( $line->sum_pareto_20) / $line->count_pareto_20 : 0 }}],
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
      data.addColumn('number', 'Rare words');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},      {{ $line->count_pareto_above_20 > 0 ? $line->sum_pareto_above_20  / $line->count_pareto_above_20: 0 }}],
        @endforeach
      ]);

      var options = {
        hAxis: {
          title: '{{ $book->title}}'
        },
        vAxis: {
          title: '% of rare words in the line'
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_divRare{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>
  <div id="chart_divLength{{ $book->id}}" style="height:400px"></div>
<script>
google.charts.setOnLoadCallback(drawLength{{ $book->id}});

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
