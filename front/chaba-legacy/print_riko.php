<?php
session_start();
include('../../inc/connection.php');
include('../riko/funcs.php');
 
$sale_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id']; 

// Routed server modes handle KOT automatically – skip browser dialog
if (get_print_mode() != 1) {
    header('Location: ../riko/riko_confirm.php?id=' . $sale_id);
    exit;
}

$action="PRINTED|-|-|KITCHEN TICKET FOR ORDER NO|".$sale_id;
logs($action,'../logs');
 
  ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
  <head>
      <meta http-equiv="refresh" content="5" />
    <meta http-equiv="content-type" content="text/html; charset=<charset>" />
    <title>Receipt</title>
  </head> 
<style type="text/css">
*{
  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;

}
 #receipt{ 
  padding:2mm;
  margin: 0 auto;
  width: 79mm;
  background: #25e25e;
  }
}
#bord{
  border: 2px solid #000 !important
 }
 table{ 
  padding:2mm;
  margin: 0 auto;
  width: 96%;
  background: #25e25e;
   border-collapse: collapse;
}
td,th{
  font-size: 11px;
  border: 1px solid #000 !important
}
th{
  font-size: 11px;
  padding: 2px;
  margin: 2px;
}

.text-center {
  text-align:center!important
}
.text-left {
  text-align:left!important
}
.text-right {
  text-align:right!important
}
.pull-left {
  float:left!important
}
.pull-right {
  float:right!important
}
h1.title{
  font-size: 26px;
  margin-bottom: 0px; 
}
p{
  font-size: 11px;
}
span{
  font-size: 12px; 
}
@media print {
  p.break {
    page-break-after: always;
  }
  body{
    margin: 2mm;
  }
  *{
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
  }
  #receipt{ 
    padding:1mm;
    margin: 0 auto;
    width: 79mm;
    background: #25e25e;
  }
  #border{
   border: 2px solid #000 !important
  }
  #bord{
    border: 2px solid #000 !important
  }
  table{ 
    padding:2mm;
    margin: 0 auto;
    width: 96%;
    background: #25e25e;
    border-collapse: collapse;
  }
  td,th{
    font-size: 11px;
    border: 1.5px solid #000 !important
  }
  th{
    font-size: 11px;
    padding: 2px;
    margin: 2px;
  }
   
  span{
    font-size: 12px;
    padding: 5px;
  }
}
 </style>
 <script type="text/javascript">
   function kit_print(){
    var id='<?php echo $sale_id; ?>'
  window.location = '../riko/riko_confirm.php?id='+id;
}
</script>
<!-- -->
<body  onload="window.print();kit_print();">
<!-- START RECEIPT -->
<div id="receipt">
  <?php
  $get_sale = "SELECT counter,user_id FROM kp_sale_print WHERE sale_id='$sale_id' AND dpt=2 AND k_print=2 GROUP BY item_id";
  $get_sale_r = mysqli_query($connection,$get_sale) or die("Could not fetch the order");
  $quantity = mysqli_num_rows($get_sale_r);
  if ($quantity<1) {
    header('Location:../riko/riko_confirm.php?id='.$sale_id);
  }else{ 
    $so = mysqli_fetch_assoc($get_sale_r);
    $waiter=$so['user_id']; 
    ?>
    <div id="receipt-bar"><br>
      <div id="bord">
        <div id="receipt-header" class="text-center">
          <h1 style="margin: 0px 0px 0px 0px;">KITCHEN ORDER TICKET</h1> 
          <h2 style="margin: 0px 0px 0px 0px;"><U> WAITER:  <?php echo strtoupper(get_column("kp_users","username","user_id='$waiter'")); ?></U></h2> 
          <h2 style="margin: 0px 0px 0px 0px;">Order No: <?php echo $sale_id; ?></h2>
          <span class="text-center"><strong>SHIFT: <?php  echo get_column("kp_sales","shift","sale_id='$sale_id'"); ?></strong></span> </br>
          <span class="text-center"><strong>PRINT TIME: <?php echo today(); ?></strong></span>
          <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        </div>
        <div id="receipt-body">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $get_order = "SELECT SUM(qty) as sqty,price,item_id FROM kp_sale_print WHERE sale_id='$sale_id' AND dpt=2  AND k_print=2  GROUP BY item_id";
              $get_order_r = mysqli_query($connection,$get_order)or die("Could not fetch the order");
              while ($o = mysqli_fetch_assoc($get_order_r)) {
                $item_id = $o['item_id'];
                $price = $o['price'];
                $qty=$o['sqty'];
                ?>
                <tr>
                  <td><?php echo get_item($item_id); ?></td>
                  <td><?php echo $qty; ?></td>
                  <td><?php echo $price; ?></td>
                  <td id="myStyle"><?php echo number_format($qty * $price,2); ?></td>
                </tr>
                <?php
              }
              ?>
            </tbody> 
          <tfoot>
            <tr> 
              <?php  
              $get_orders = mysqli_query($connection,"SELECT SUM(total) AS order_total FROM kp_sale_print WHERE sale_id='$sale_id' AND dpt=2  AND k_print=2");
              $or = mysqli_fetch_assoc($get_orders);
              $sum = $or['order_total'];
              ?>
               <th colspan="4" class="text-center"><B style=" font-size: 22px;">TOTAL: <?php  echo number_format($sum,2); ?>&nbsp;</B></th> 
             </tr>
             <tr style="height:40px;">
               <th><h3>COOKED BY:</h3></th>
               <th colspan="3"></th>
             </tr>
             <tr style="height:40px;">
               <th><h3>SERVED BY:</h3></th>
               <th colspan="3"></th>
             </tr>
             <tr style="height:30px;">
               <th>SERVED TO CUSTOMER:</th>
               <th colspan="3"></th>
             </tr>
           </tfoot>
               </table>
    </div>
   <br>   
</div>
</div> 
 
    </div>

<?php } ?>
  </body>
</html>
 
