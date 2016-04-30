@extends('layouts.master')
 <div id="chart_divLines" style="height:990px;"></div>
      
@section('content')
<script>
google.charts.setOnLoadCallback(drawBasicLines);

function drawBasicLines() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Speech Complexity(Higher is harder)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($movies as $movie)
        [ '{{ $movie->subtitle()->filename}}',   {{ $movie->subtitle()->std_score}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Movies'
        },
        vAxis: {
          title: 'Speech Complexity score'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_divLines'));

      chart.draw(data, options);
    }
 </script>

 <div id="chart_divWords" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasicWords);

function drawBasicWords() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Total speech length');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($movies as $movie)
        [ '{{ $movie->subtitle()->filename}}',   {{ $movie->subtitle()->std_score}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Movies'
        },
        vAxis: {
          title: 'Letters'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_divWords'));

      chart.draw(data, options);
    }
 </script>
@endsection 
