<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
include_once('../../inc/gen_funcs.php');
// page_restrict();
$user_id = $_GET['id'];
$status=waiter_open_status($user_id);
if($status>=1){
  header("Location:../dash/dash.php?msg=6");
} 

$get_job = mysqli_query($connection,"SELECT job FROM kp_users");
$us=mysqli_fetch_assoc($get_job);
$job=$us['job'];
$shift = get_column('kp_shift','shift',"1"); 
$print_mode = (int)get_print_mode();
$staff_print_href = $print_mode == 1
  ? '../chaba/render_staff_summary.php?id='.$user_id.'&auto_print=1'
  : '../chaba/receipt_counter.php?type=staff_sum&id='.$user_id.'&user_id='.$user_id.'&return_to='.urlencode('../staff/staff_shift.php?id='.$user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags always come first -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Landing</title>
  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- ================== BEGIN PAGE LEVEL JS ================== -->
  <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
  <!-- ================== END PAGE LEVEL JS ================== -->
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
        <a href="clear_staff.php"  name="open" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;">BACK</a> 
      </ul>
      <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
        <li  class="nav-item">
          <a  href="../log_out.php" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
        </li>   
      </ul>
    </nav> 
    <!--/.Navbar-->
  </header>
  <!-- begin #content -->
<div id="content" class="content" style="margin-top: 75px; margin-left: 10%;">
  <div class="panel panel-default col-md-8">
    <div class="row"> 
      <div class="panel-body col-md-12"> 
        <table class="table table-striped ">
          <thead>
            <tr>
              <th colspan="2"><h5>SHIFT'S SUMMARY</h5></th>
            </tr>
          </thead>  
          <tbody>
            <tr>
              <th>CLEARED SALES</th>  
              <th><?php echo column_total("kp_sales","amount","user_id='$user_id' AND shift='$shift' AND pay_status=1"); ?></th>
              <?php 
                $get_stores = mysqli_query($connection,"SELECT * FROM kp_counters");
                foreach ($get_stores  as $cow) {
                  $counter=$cow['cntr_id'];
                  $dpt=1;
                  // $counter_amount=paid_counter_amount($user_id,$counter,$dpt,$shift);
                  // echo "<td>".$counter_amount."</td>";
                }
                ?>  
              <th><?php //echo staff_cleared_dpt_sum($user_id,$shift,2); ?></th>
            </tr>
            <tr>
              <th>UNCLEARED SALES</th> 
              <th><?php //echo  staff_uncleared_sum($user_id,$shift); ?></th>
              <?php 
                $get_stores = mysqli_query($connection,"SELECT * FROM kp_counters");
                foreach ($get_stores  as $cow) {
                  $counter=$cow['cntr_id'];
                  $dpt=1;
                  // $counter_amount=unpaid_counter_amount($user_id,$counter,$dpt,$shift)+unpaid_counter_amount($user_id,$counter,2,$shift);
                  // echo "<td>".$counter_amount."</td>";
                }
                ?> 
              <th><?php //echo staff_dpt_uncleared_sum($user_id,$shift,2); ?></th>
            </tr>
          </tbody>
        </table>
        <table class="table table-bordered">
          <thead>
            <tr>
             <th colspan="4"><h5>PAYMENT SUMMARY</h5></th>
           </tr>
          </thead>
          <tbody>
            <tr>
              <td><h6>SHIFT</h6></td>
              <td>CASH</td> 
              <td>MPESA</td>
              <td>VISA</td>
            </tr> 
                <?php 
                //$array =staff_shift_payments($user_id,$shift);
               // $parray =staff_previous_payments($user_id,$shift)
                ?>
             <!--  <tr>
                <td><h6>TODAY</h6> </td>
                <td><?php echo $array[0]; ?></td> 
               <td><?php echo $array[1]; ?></td>
               <td><?php echo $array[2]; ?></td>
             </tr>
               <tr>
              <td><h6>PREVIOUS</h6> </td>  
                <td><?php echo $parray[0]; ?></td> 
               <td><?php echo $parray[1]; ?></td>
               <td><?php echo $parray[2]; ?></td>
             </tr> -->
             <tr>
              <td colspan="3" style="text-align: center;"><h5><b>TOTAL</b></h5> </td>  
                <td><h5><b><?php //echo shift_staff_payments($user_id,$shift); ?></b></h5></td>  
             </tr>
            </tbody>
            <tfoot>
              <tr>
                <?php if ($job==1) {
                  # code...
                }else{
                  ?>
               
               <!--  <th><a  data-toggle="modal" data-target="#modal" data-id="<?php echo $sale_id; ?>" class="btn btn-primary">CLOSE SHIFT</a>
               </th> -->
             <?php  }?>
                <th><a class="btn btn-success" href="<?php echo htmlspecialchars($staff_print_href); ?>">PRINT SHIFT SUMMARY</a>
               </th>
              </tr>
            </tfoot>
          </table>
        </div> 
      </div>
    </div>
  </div>
    <!-- end #content -->
<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" > 
    <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">WARNING</h4>
            </div>
            <div class="modal-body">
              <div class="container-fluid">
                <div class="row">
                      <div class="alert alert-warning">
                        <p>WARNING!! This action is irreversible and will CLOSE THE WAITERS SHIFT COMPLETELY.</p>
                      </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <a class="btn btn-danger"  href="staff_close.php?user_id=<?php echo $user_id; ?>">CONTINUE</a>
            </div>
          </div>
    <!-- <div class="modal-content" id="dynamic-content">
    <div class="modal-header">
    <h3 style="float:left;">CLEAR <?php echo get_user($user_id);?></h3>
    </div> 
    <div class="modal-body"> 
      <?php 
      $array =staff_shift_payments($user_id,$shift);
      $parray =staff_previous_payments($user_id,$shift);
     ?>
      <div class="form-group">
        <form>
        <table class="table">
           <tbody>
                <tr> 
               <td>CASH</td> 
               <td>MPESA</td>
               <td>VISA</td>
             </tr> 
              <tr>
                <?php 
                $array =staff_shift_payments($user_id,$shift);
                $parray =staff_previous_payments($user_id,$shift)
                ?> 
                <td><?php echo $array[0]+$parray[0]; ?></td> 
               <td><?php echo $array[1]+$parray[1]; ?></td>
               <td><?php echo $array[2]+$parray[2]; ?></td>
             </tr> 
             <tr>
              <td colspan="2" style="text-align: center;"><h5><b>TOTAL</b></h5> </td>  
                <td><h5><b><?php echo shift_staff_payments($user_id,$shift); ?></b></h5></td>  
             </tr>
            </tbody>
            </table>
        </div>
        <div class="form-group">
          <input type="text" id="cash" class="form-control" name="cash" placeholder="CASH">
        </div>
        <div class="form-group"> 
          <input type="text" id="mpesa" class="form-control" name="mpesa" placeholder="MPESA"> 
        </div> 
        <div class="form-group"> 
          <input type="text" id="visa" class="form-control" name="visa" placeholder="VISA"> 
        </div> 
        <div class="form-group"> 
          <input type="text" id="short" class="form-control" name="short" placeholder="SHORTAGE"> 
        </div>  
    </div> 
    <div class="modal-footer" style="padding: 10px;">
               <button type="button" data-dismiss="modal" class="btn btn-danger">CLOSE</button>
               <button type="submit" onclick="clear_bill('MPESA')" name="place_order" id="pay" disabled="true" class="btn btn-warning">CLEAR</button>
               
            </div>
            </form> -->
    </div> 
  </div> 
</div>  
</body>
</html>
