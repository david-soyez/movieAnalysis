@extends('layouts.master')

@section('content')
 <div id="chart_div" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasic);

function drawBasic() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Global Complexity(Higher is harder)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->getMean()}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Books'
        },
        vAxis: {
          title: 'Book mean complexity'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div'));

      chart.draw(data, options);
    }
 </script>

@endsection 
