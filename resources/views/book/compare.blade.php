@extends('layouts.master')

@section('content')
 <div id="chart_div" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasic);

function drawBasic() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Top 100 words');
      data.addColumn('number', '+Common words');
      data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->mean_line_percent_100 }} , {{ $book->mean_line_percent_5 +  $book->mean_line_percent_10  +  $book->mean_line_percent_15  +  $book->mean_line_percent_20 }} ,{{ $book->mean_line_percent_5 + $book->mean_line_percent_10 +  $book->mean_line_percent_15  +  $book->mean_line_percent_20 + $book->mean_line_percent }}] ,
        @endforeach
      ]);

      var options = {
          isStacked: true,
        hAxis: {
          title: 'Books'
        },
        vAxis: {
          title: 'Complexity of a line'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div'));

      chart.draw(data, options);
    }
 </script>

@endsection 
