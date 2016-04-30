<?php $conversations = $movie->subtitle()->conversations;
?>
  <div id="chart_divRare{{ $movie->id}}" style="height:400px"></div>

<script>
google.charts.setOnLoadCallback(drawRare{{ $movie->id}});

function drawRare{{ $movie->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Conversation complexity');
      data.addColumn('number', 'Speed');

      data.addRows([
        @foreach ($conversations as $conversation)
        <?php for($i=$conversation->timeline_start/ 1000;$i<=$conversation->timeline_end/ 1000;$i++):?>
        [{{ $i/60 }},      {{ (float)($conversation->score) }},      {{ (float)($conversation->readingspeed) }}],
        <?php endfor; ?>
        @endforeach
      ]);

      var options = {
      bar: {groupWidth: "90%"},
        hAxis: {
          title: '{{ $movie->title}}'
        },
        seriesType: 'bars',
        series: {1: {type: 'line',targetAxisIndex:1},0: {type: 'bar',targetAxisIndex:0}},
        vAxes: { 0: {logScale: false,maxValue: 10000}, 1: {logScale: false,maxValue: 40} },
        vAxis: {
          title: 'Conversation Complexity score',
              viewWindow: {
            }
        }
      };

      var chart = new google.visualization.ComboChart(document.getElementById('chart_divRare{{ $movie->id}}'));

      chart.draw(data, options);
    }
 </script>
<hr>
