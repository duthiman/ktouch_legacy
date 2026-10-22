<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags always come first -->
   <meta http-equiv="refresh" content="5" />
    <title>Landing</title>
 <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css"> 

<style type="text/css">
      body { 
  background: url(../assets/img/bg/5.jpg) no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
    </style>
 
</head>
<body>
  <header>
        <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
            
  <ul class="nav navbar-nav navbar-left">
     
      <li class="nav-item" style="margin-left: 10px; margin-top: 10px;">
        <a href="../dash/dash.php"  name="open" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;">HOME</a> 
   </ul>
  <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
        <li  class="nav-item">
          <a  href="../log_out.php" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
             
        </li>       
 </ul>

        </nav> 
    <!--/.Navbar-->
  </header>
  <!-- begin #content
  <div id="content" class="content" style="margin-top: 100px; margin-left: 50px;"> -->
    <!--  
     <div class="panel panel-default col-md-8  ">  
      <div class="panel-body" id="load"> --> 
        <?php
       $get_orders = "SELECT * FROM kp_sale_print  WHERE status=1 OR status=3 AND k_print=2 AND dpt=2 GROUP BY sale_id ORDER BY sale_id DESC";
          $get_orders_r = mysqli_query($connection,$get_orders) or die("Could not get the orders".mysqli_error($connection));
             if (mysqli_num_rows($get_orders_r) < 1) {
                echo "You have not placed any orders today";
             }else{

              while ($or = mysqli_fetch_assoc($get_orders_r)) {
                  $sale_id = $or['sale_id'];

                  header("Location:../chaba/render_riko.php?id=$sale_id&auto_print=1");
              }
              } 
          ?>   
 </body>    
</html>
