<?php
include('../../inc/connection.php');
include('funcs.php'); 
?>  
 <div id="results" onload="">
   test
 </div>
 <script type="text/javascript">
  function get_data(){ 
      //   start = document.getElementById('pick').value;
      // end = document.getElementById('pick2').value;
      // '<?php $start= '+start';?>';

      // google.charts.load("current", {packages:["corechart"]});
      //  google.charts.setOnLoadCallback(drawChart1);
      //  function drawChart1() {
      //   var data = google.visualization.arrayToDataTable([
      //     ['Year', 'Sales'],
      //     <?php
      //     $get_week_sales = mysqli_query($connection,"SELECT * FROM kp_sales GROUP BY shift ORDER BY shift DESC LIMIT 7 ") or die("Error fetching sales.:".mysqli_error($connection));
      //     while ($row = mysqli_fetch_assoc($get_week_sales)) {
      //         $day = $row['shift']; 
      //         $get_total_sales = mysqli_query($connection,"SELECT SUM(amount) AS total FROM kp_sales WHERE shift='$day'") or die(mysqli_error($connection));
      //         $tt = mysqli_fetch_assoc($get_total_sales);
      //         $total = $tt['total'];

      //         $your_date = date("d M Y", strtotime($day));

      //         echo "['".$your_date."', ".$total."],";
      //     }
      //     ?>
          
      //   ]);

      //   var options = {
      //     title: 'SALES ANALYTICS (Last 7 Days)',
      //     curveType: 'function',
      //     legend: { position: 'bottom' }
      //   };

      //   var chart = new google.visualization.LineChart(document.getElementById('filter_result'));
      //   console.log(data);
      //   chart.draw(data, options);
      // }
    
    }
  </script>