
  <div id="chart_div{{ $book->id}}"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasic{{ $book->id}});

function drawBasic{{ $book->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Top 100');
      data.addColumn('number', 'Common words');
      data.addColumn('number', 'Rare words');

      data.addRows([
        @foreach ($book->lines as $line)
        [{{ $line->position }},      {{ $line->sum_words_100 }},   {{ $line->sum_words_5 + $line->sum_words_10 + $line->sum_words_15 + $line->sum_words_20 }},{{  $line->sum_words }}],
        @endforeach
      ]);

      var options = {
          isStacked: true,
        hAxis: {
          title: '{{ $book->title}}'
        },
        vAxis: {
          title: 'Popularity'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>
  <div id="chart_div_pie{{ $book->id}}" style="height:300px"></div>
      
<script>
google.charts.setOnLoadCallback(drawPie{{ $book->id}});

function drawPie{{ $book->id}}() {

        var data = google.visualization.arrayToDataTable([
          ['Top used words', 'Percent'],
          ['Top 100 words',     {{ $book->mean_line_percent_100 }}],
          ['Common words',      {{ $book->mean_line_percent_5 +  $book->mean_line_percent_10  +  $book->mean_line_percent_15  +  $book->mean_line_percent_20 }}],
          ['Rare words',  {{  $book->mean_line_percent }}],

        ]);

        var options = {
          title: '{{ $book->title }}'
        };

      var chart = new google.visualization.PieChart(document.getElementById('chart_div_pie{{ $book->id}}'));

      chart.draw(data, options);
    }
 </script>

