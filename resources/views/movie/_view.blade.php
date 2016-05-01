<?php $conversations = $movie->subtitle()->conversations;
?>
  <div id="chart_divRare{{ $movie->id}}" style="height:400px"></div>

<script>
google.charts.setOnLoadCallback(drawRare{{ $movie->id}});

function drawRare{{ $movie->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Dialogue streak');
      data.addColumn('number', 'Dialogue speed (words/sec)');

      data.addRows([
        @foreach ($conversations as $conversation)
        <?php 
            if(isset($end)) {
                for($i=$end/1000;$i<$conversation->timeline_start/1000;$i++) {
                ?>
                    [{{ $i/60 }}, 0, {{ $endReadingSpeed }}],
                <?php
                }
            }
            for($i=$conversation->timeline_start/ 1000/6;$i<=$conversation->timeline_end/ 1000/6;$i++):?>
        [{{ $i/10 }},      {{ (float)($conversation->strlen) }},      {{ (float)(($conversation->readingspeed/5)) }}],
<?php 
            $end=$conversation->timeline_end+1; 
            $endReadingSpeed =(float)($conversation->readingspeed/5) ;
            
            endfor; ?>
        @endforeach
      ]);

      var options = {
        bar: {groupWidth: 90},
        hAxis: {
          title: '{{ $movie->title}} (minutes)'
        },
        series: {1: {type: 'line',targetAxisIndex:1},0: {type: 'bar',targetAxisIndex:0}},
        vAxes: { 0: {logScale: false,title: 'Dialogue streak score',maxValue:2000}, 1: {logScale: false,title: 'Dialogue speed (words/sec)',maxValue:10} },
  
      };

      var chart = new google.visualization.AreaChart(document.getElementById('chart_divRare{{ $movie->id}}'));

      chart.draw(data, options);
    }
 </script>
<hr>
