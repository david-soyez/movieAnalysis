@extends('layouts.master')
 <div id="chart_divLines" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasicLines);

function drawBasicLines() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Global Complexity(Higher is easier)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->getMeanLines()}} ] ,
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

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_divLines'));

      chart.draw(data, options);
    }
 </script>

@section('content')
 <div id="chart_divWords" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasicWords);

function drawBasicWords() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Global Complexity(Higher is easier)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->getMeanWords()}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Books'
        },
        vAxis: {
          title: 'Word mean complexity'
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
      data.addColumn('number', 'Global Complexity(Higher is easier)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->getMeanLines()}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Books'
        },
        vAxis: {
          title: 'Line mean complexity'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_divWords'));

      chart.draw(data, options);
    }
 </script>



<div id="chart_divSum20" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasicSum20);

function drawBasicSum20() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Global Complexity(Higher is easier)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->getTotal20()}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Books'
        },
        vAxis: {
          title: 'Sum20 complexity'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_divSum20'));

      chart.draw(data, options);
    }
 </script>






<div id="chart_divSumAbove20" style="height:990px;"></div>
      
<script>
google.charts.setOnLoadCallback(drawBasicSumAbove20);

function drawBasicSumAbove20() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'Global Complexity(Higher is easier)');
 //     data.addColumn('number', '+Common words');
  //    data.addColumn('number', '+Rare words');


      data.addRows([
        @foreach ($books as $book)
        [ '{{ $book->title }}',   {{ $book->getTotalAbove20()}} ] ,
        @endforeach
      ]);

      var options = {
//          isStacked: true,
        hAxis: {
          title: 'Books'
        },
        vAxis: {
          title: 'SumAbove20 complexity'
        }
      };

      var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_divSumAbove20'));

      chart.draw(data, options);
    }
 </script>

@endsection 
