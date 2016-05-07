<?php $conversations = $movie->subtitle()->conversations;
?>
  <div id="chart_divRare{{ $movie->id}}" style="height:400px"></div>

<script>
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawRare{{ $movie->id}});

function drawRare{{ $movie->id}}() {

      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Dialogue streak (#words)');
      data.addColumn('number', 'Comprehension');

      data.addRows([
        @foreach ($conversations as $conversation)
        <?php 
            if(isset($end)) {
                for($i=$end/1000;$i<$conversation->timeline_start/1000;$i++) {
                ?>
                    [{{ $i/60 }}, 0, 0 ],
                <?php
                }
            }
            for($i=$conversation->timeline_start/ 1000/6;$i<=$conversation->timeline_end/ 1000/6;$i++):?>
        [{{ $i/10 }},      {{ (float)($conversation->count_words) }},      {{ (float)($conversation->count_words- (($conversation->count_words*(100-$conversation->score))/100)) }}],
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
        series: {1: {type: 'bar',targetAxisIndex:1},0: {type: 'bar',targetAxisIndex:0}},
        vAxes: { 0: {logScale: false,title: 'Dialogue streak (#words)',maxValue:300}, 1: {title: 'Comprehension',maxValue:300} },
  
      };

      var chart = new google.visualization.AreaChart(document.getElementById('chart_divRare{{ $movie->id}}'));

      chart.draw(data, options);
    }
 </script>

