<?php
session_start();
include('../../inc/connection.php');
require_once('../kwedia/funcs.php'); 
$user_id = $_SESSION['user_id']; 
$sale_id = get_column('kp_thesion_front','sale_id',"user_id='$user_id'");
$counter= get_column('kp_thesion_front','cntr_id',"user_id='$user_id'");
$shift = get_column('kp_shift','shift',"1");
$status = $_GET['status']; 
$action="PRINTED|-|-|OPEN ORDER NO|".$sale_id;
logs($action,"../logs"); 
$get_order_r = mysqli_query($connection,"SELECT * FROM kp_sale_print WHERE sale_id='$sale_id' AND status='$status'  GROUP BY item_id")or die("Could not fetch the order 1");
 if (mysqli_num_rows($get_order_r) < 1) {
    header('Location:../dash/dash.php?msg=2');
 }else{
  ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<charset>" />
    <title>Receipt</title>
  </head>
  <link href="../assets/css/thermal_print.css" rel="stylesheet" />
  <script type="text/javascript">
    function open(){
      window.location = '../kwedia/place_open.php';
    }
  </script>
  <!--  -->
  <body onload="open();window.print()">
<!-- START RECEIPT -->
  <div id="receipt">
    <?php
    $get_order = "SELECT sale_id FROM kp_sale_print WHERE sale_id='$sale_id' AND b_print=2 AND status=2 GROUP BY item_id";
    $get_order_r = mysqli_query($connection,$get_order) or die("Could not fetch the order");
    $quantity = mysqli_num_rows($get_order_r);
    if ($quantity<1) {}    else{ 
  ?>
  <div id="receipt-bar"><br>
    <div id="bord"> 
      <div id="receipt-header" class="text-center">
        <h2 style="margin: 0px 0px 0px 0px;font-size:23px;"><?php echo get_counter($counter) ?></br> BAR ORDER TICKET(BOT)</h2> 
        <strong class="text-center" style="margin-right: 5px;font-size:20px;">Order No: <?php echo $sale_id; ?></strong></br>
        <strong class="text-center" style="font-size:20px;">Waiter: <?php echo strtoupper( get_column('kp_users','username',"user_id='$user_id'")); ?></strong><br>
        <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
        <span class="text-center" style="font-size:16px;"><strong>Shift: <?php echo $shift; ?></strong></span></br>
        <span class="text-center"><strong>Print Date: <?php  echo today(); ?></strong></span><br>
        <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
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
            $get_order = "SELECT SUM(qty) AS the_qty,price,item_id FROM kp_sale_print WHERE sale_id='$sale_id'  AND b_print=2 AND status=2  GROUP BY item_id";
            $get_order_r = mysqli_query($connection,$get_order)or die("Could not fetch the order"); 
            while ($o = mysqli_fetch_assoc($get_order_r)) {
              $item_id = $o['item_id'];
              $price = $o['price']; 
              $quantity =$o['the_qty'];
              ?>
              <tr>
                <td><?php echo get_column("kp_items","name","item_id='$item_id'"); ?></td>
                <td><?php echo $quantity; ?></td>
                <td><?php echo $price; ?></td>
                <td id="myStyle"><?php echo number_format($quantity * $price,2); ?></td>
              </tr>
              <?php
                 }
              ?>
            </tbody> 
            <tfoot> 
              <tr>
                <td class="text-center" colspan="4" style=" font-size: 22px;">
                  <b>TOTAL : <?php echo number_format(column_total('kp_sale_print','total',"sale_id='$sale_id' AND b_print=2 AND status=2"),2); ?> </b>
                </td> 
              </tr>
            </tfoot>
          </table>
        </div>
          <br> 
        <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
        <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
       
      </div>
    </div>
      <?php
    }
    ?>
  </div>
</body>
</html>
<?php
}
?>