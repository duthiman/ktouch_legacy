<?php
session_start();
include('../../inc/connection.php');
include('../take/funcs.php'); 
$user_id = $_SESSION['user_id']; 
$nos=59;
$shift = get_column('kp_shift','shift',"1"); 
$user_id=$_SESSION['user_id'];
$counter = get_column("kp_users","counter","user_id='$user_id'");
$table="kp_cntr".$counter."_stock_take";
$print_template = function_exists('get_print_template') ? (int)get_print_template() : 1;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<charset>" />
    <title>Receipt</title>
  </head>
  <script type="text/javascript">
    function log_out(){
        window.location = '../log_out.php';
      }
  </script>
  <link href="../assets/css/thermal_print.css" rel="stylesheet" />
  <style>
    <?php if ($print_template === 2): ?>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * { box-sizing: border-box; }
    body {
      margin: 4mm;
      padding: 0;
      background: #fff;
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      color: #000;
    }
    #receipt {
      margin: 0 auto;
      width: 76mm;
      max-width: 76mm;
      background: #fff;
      padding: 3mm 2mm;
    }
    table {
      width: 100%;
      max-width: 100%;
      margin: 2px 0;
      border-collapse: collapse;
      table-layout: fixed;
    }
    td, th {
      font-size: 12px;
      border: 1px solid #000 !important;
      padding: 2px 1px;
    }
    h1.title { font-size: 22px; margin: 4px 0 2px; }
    p, span { font-size: 12px; }
    @media print {
      body { margin: 1mm; }
      #receipt { padding: 1mm; }
    }
    <?php else: ?>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * { box-sizing: border-box; }
    body {
      margin: 2mm;
      padding: 0;
      background: #fff;
      color: #000;
      font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    }
    #receipt {
      padding: 1mm;
      margin: 0 auto;
      width: 76mm;
      max-width: 76mm;
      background: #fff;
    }
    table {
      width: 100%;
      max-width: 100%;
      padding: 1mm;
      margin: 0 auto;
      border-collapse: collapse;
      table-layout: fixed;
    }
    td, th {
      font-size: 12px;
      border: 1px solid #000 !important;
      padding: 2px;
      margin: 2px;
    }
    th { font-size: 13px; }
    p, span { font-size: 13px; }
    @media print {
      body { margin: 2mm; }
      #receipt { padding: 1mm; }
      td, th { font-size: 13px; border: 1.5px solid #000 !important; }
      th { font-size: 12px; padding: 2px; margin: 2px; }
      span { font-size: 12px; padding: 5px; }
    }
    <?php endif; ?>
  </style>
  <!--  -->
  <body onload=" window.print();log_out();">
    <!-- START RECEIPT -->
    <div id="receipt">
      <div id="receipt-header" class="text-center">
        <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
        <h2 class="title"style="margin: 0px 0px 0px 0px;"><b><u><?php echo strtoupper(get_column("kp_settings_gen","company_name","id=1")); ?></u></b></h2> 
        <h3 class="title" style="margin:5px;"><b><u><?PHP ECHO get_column("kp_counters","name","cntr_id='$counter'"); ?> STOCK TAKE VARIANCE</u></b></h3> 
        <span  style="font-size: 18px;"><b>SHIFT: <?php  echo $shift; ?></b></span> <BR>
        <span  style="font-size: 18px;"><b>TAKEN BY:<?php echo get_user($user_id); ?></b></span>   
        <BR>
        
        <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
        <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      </div>
      <div id="receipt-body">
        <table>
          <thead>
            <th>ITEM</th>
            <th>OPEN</th>
            <th>COUNT</th>
            <th>VARI</th>  
            <th>CORRECT</th> 
          </thead>
          <tbody>
            <?php
            $get_items = mysqli_query($connection,"SELECT * FROM $table WHERE shift='$shift' AND opening!=counted  ORDER BY take_id DESC") or die("Error fetching items count.:".mysqli_error($connection));

            while ($row = mysqli_fetch_assoc($get_items)) {
            $item_id = $row['item_id'];
            $closing=$row['opening'];
            $counted=$row['counted'];
            $var=$closing-$counted; 
            ?>
            <tr style="color: <?php echo $color;?>;">
              <td><?php echo get_item($item_id); ?></td>
              <td><?php echo $closing; ?></td>
              <td><?php echo $counted; ?></td>
              <td><?php echo $var; ?></td>
              <td></td> 
            </tr>
            <?php
            } 

            ?>                                 
          </tbody> 
        </table>
      </div>
         
    </div>
</body>
</html> 
