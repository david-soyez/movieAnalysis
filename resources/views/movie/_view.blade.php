<?php $conversations = $movie->subtitle()->conversations;
?>
  <div id="chart_divRare{{ $movie->id}}" style="height:400px"></div>

<script>
google.charts.setOnLoadCallback(drawRare{{ $movie->id}});

function drawRare{{ $movie->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Conversation complexity');

      data.addRows([
        @foreach ($conversations as $conversation)
        <?php for($i=$conversation->timeline_start/ 1000;$i<=$conversation->timeline_end/ 1000;$i++):?>
        [{{ $i }},      {{ (float)($conversation->score ) }}],
        <?php endfor; ?>
        @endforeach
      ]);

      var options = {
      bar: {groupWidth: "90%"},
        hAxis: {
          title: '{{ $movie->title}}'
        },
        vAxis: {
          title: 'Conversation Complexity score',
              viewWindow: {
              min:0
            }
        }
      };

      var chart = new google.visualization.ColumnChart(document.getElementById('chart_divRare{{ $movie->id}}'));

      chart.draw(data, options);
    }
 </script>
<hr>
