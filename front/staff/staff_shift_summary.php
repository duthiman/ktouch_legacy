<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
page_restrict(); 
$staff_id = $_SESSION['user_id'];
$shift = $_SESSION['shift'];
$status=staff_ord_status($staff_id);
if($status>=1){
  header("Location:../landing/landing.php?msg=4");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=true">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>View Orders</title>
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /*body {
           background: url("../assets/img/bg/5.jpg")no-repeat;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
          background-size: cover;
        }*/
        html { 
  background: url(../assets/img/bg/5.jpg) no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
       
    </style>
 </head>

<body class="fixed-sn deep-orange-skin">
  <header>
    <nav class="navbar navbar-fixed-top scrolling-navbar double-nav">
      <ul class="nav navbar-nav navbar-left">
        <li class="nav-item ">
          <a  href="../landing/landing.php" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 70px; width:70px; background-color: #FFF;border: 5px solid #02550E;" class="btn btn-sm btn-success m-b-10">Home</a>
        </li>
      </ul>
      <ul class="float-xs-right">
        <a  href="../log_out.php" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 90px; width:90px; background-color: #FFF;border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
      </ul>
    </nav>
  </header>
  <!--Main layout-->
  <main class="container-fluid" >
    <div class="container" style="">
    <div class="row" style=" margin-top: 70px;">
      <div class="col-md-6" style=" background-color: #FFFFFFE6;margin-left: 0%; position: absolute; width: 45% !important;  height: 90%; overflow-y:scroll;padding-right: 2px;">
        <?php
          $get_orders = "SELECT order_no,split FROM orders WHERE staff_id='$staff_id' AND pay_status=2  ORDER BY order_no DESC";
          $get_orders_r = mysqli_query($connection,$get_orders) or die("Could not get the orders");
             if (mysqli_num_rows($get_orders_r) < 1) {
                echo "You have not placed any orders today";
             }else{
        ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th colspan="4" style="text-align: center;">YOUR UNCLEARED ORDERS</th> 
            </tr>
            <tr>
               <th>Order Number</th> 
               <th>Total Amount</th>
               <th>Tokens</th>
               <!-- <th>CLEAR</th> -->
               <th>RETURN</th>
              
            </tr>
          </thead>
          <tbody>
              <?php
              while ($or = mysqli_fetch_assoc($get_orders_r)) {
                  $order_no = $or['order_no']; 
                  $split = $or['split'];
                  $total=total($order_no);
                  ?>
              <tr style="padding: 0px 0px 0px 0px;">
                <td><?php echo $order_no; ?></td> 
                <td><?php echo $total; ?></td>
                <td><?php echo order_tokens($order_no); ?></td>
                <!-- <td>
                  <a class="btn btn-success" href="../order/delete_order.php?oid=<?php echo $order_no; ?>" style="padding: 0px 0px 0px 0px; width:60px; font-weight: bold; border-radius: 10px; background-color: #A7171C;border: 3px solid #fff;">void</a>
                </td>
                <td>
                  <a class="btn btn-success" href="ret_page.php?id=<?php echo $order_no; ?>"  style="padding: 0px 0px 0px 0px; width:100px; font-weight: bold; border-radius: 10px; background-color: #A7171C;border: 3px solid #fff; ">CLEAR</a>
                </td>-->
                <td>
                  <a class="btn btn-success" href="../order/ret_page.php?id=<?php echo $order_no; ?>" style="padding: 0px 0px 0px 0px; width:100px; font-weight: bold; border-radius: 10px; background-color: #A7171C;border: 3px solid #fff; ">RETURN</a>
                </td> 
              </tr>
                  <?php  
              }
              ?>
            </tbody>
          </table>
          <?php
            }
          ?>
      </div>
      <div class="col-md-6" style=" background-color: #FFFFFFE6;margin-left: 50%; position: absolute; width: 35% !important; padding-right: 2px;">
        <div class="panel">
          <div class="panel-body">
            <table class="table table-striped ">
          <thead>
            <tr>
               <th>TODAY'S TOTAL</th> 
               <th><?php echo staff_shift_sum($staff_id,$shift); ?></th>
             </tr>
             <tr>
               <th>TODAY'S CLEARED</th> 
               <th><?php echo staff_cleared_sum($staff_id,$shift); ?></th>
             </tr>
             <tr>
               <th>TODAY'S UNCLEARED</th> 
               <th><?php echo staff_uncleared_sum($staff_id,$shift); ?></th>
             </tr>
             <tr>
               <th>PREVIOUS UNCLEARED</th> 
               <th><?php echo staff_previous_sum($staff_id,$shift); ?></th>
             </tr>
             <tr>
               <th>TOKENS</th>
               <th><?php echo staff_tokens($staff_id,$shift); ?></th>
             </tr>
           </thead> 
          </table>
          
          <div >
  <table class="table table-bordered">
            <thead>
              <tr>
               <th colspan="3"><h5>PAYMENT SUMMARY</h5></th>
             </tr>
            </thead>
            <tbody>
               <aside>
             
  <tr>
              <td><h6>SHIFT</h6></td>
               <td>CASH</td> 
               <td>MPESA</td>
             </tr> 
              <tr>
                <?php 
                $array =staff_shift_payments($staff_id,$shift);
                $parray =staff_previous_payments($staff_id,$shift)
                ?>
                <td><h6>TODAY</h6> </td>
                <td><?php echo $array[0]; ?></td> 
               <td><?php echo $array[1]; ?></td>
             </tr>
               <tr>
              <td><h6>PREVIOUS</h6> </td>  
                <td><?php echo $parray[0]; ?></td> 
               <td><?php echo $parray[1]; ?></td>
             </tr>
             <tr>
              <td colspan="2" style="text-align: center;"><h4>TOTAL</h4> </td>  
                <td><h4><?php echo shift_staff_payments($staff_id,$shift); ?></h4></td>  
             </tr>
            </tbody>
          </table>


    
    </div>
          </div>
          <div class="panel-footer"> 
            <a class="btn btn-primary" href="staff_close.php">CLOSE SHIFT</a>
              <a class="btn btn-success" href="../printing/print_staff_shift.php?id=<?php echo $staff_id; ?>">PRINT SUMMARY</a>
          </div>
        </div>
        
      </div>
    </div> 
        
        </div>
  
      </main>
  <script type="text/javascript" src="../assets/js/jquery-3.1.1.min.js"></script>
 <!--  <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script> -->

   
     <script type="text/javascript">
  
   $(document).ready(function(){
    $(document).on('click','#ret', function(e){
        e.preventDefault();
        var uid = $(this).data('id'); 
        $('#dynamic-content').html(''); // leave this diff blank
        $('#modal-loader').show(); // load ajax loader button cliked
        $.ajax({
          url: 'ret_modal.php',
          typr: 'POST',
          data: {id:uid},
          dataType: 'html'
        })
        .done(function(data){
          console.log(data);
          $('#dynamic-content').html(''); // blank before load.
          $('#dynamic-content').html(data);
        })
        .fail(function(){
          $('#dynamic-content').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
          $('#modal-loader').hide();
        });
      });
   });
</script>
  </body>
</html>