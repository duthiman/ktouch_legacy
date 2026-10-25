<?php
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php'); 
$counter=$_REQUEST['counter'];
$user_id=$_REQUEST['user_id'];
$take_status=(int)($_REQUEST['take_status'] ?? 3);
$take_status=in_array($take_status,[1,2,3],true) ? $take_status : 3;
$reason=mysqli_real_escape_string($connection, $_REQUEST['reason'] ?? '');
$trans_by=$_SESSION['user_id'];
$shift=shift();

$get_order = "SELECT sale_id FROM kp_sale_id";
$get_order_r = mysqli_query($connection,$get_order) or die("Could not get the last order numbers");
$row = mysqli_fetch_array($get_order_r);
$last_id = $row['sale_id'];
 
$column="cntr".$counter."_take";

$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$user_id'");

$update_waiter= mysqli_query($connection,"UPDATE kp_items SET ".$column."=2");

$update_counter= mysqli_query($connection,"UPDATE kp_counters SET user_id='$user_id',take_status='$take_status',counter_status=1,close_status=3 WHERE cntr_id='$counter'") or die(mysqli_error($connection));

$counter_hist_result= mysqli_query($connection,"INSERT INTO kp_counter_hist(cntr_id,shift,opening,open_by,open_ord,status) VALUES('$counter',CURRENT_DATE,CURRENT_TIMESTAMP,'$user_id','$last_id',2) ") or die(mysqli_error($connection));

$transaction="OPENED  ".strtoupper(get_counter($counter))." COUNTER";
$counter_history_result= mysqli_query($connection,"INSERT INTO kp_counter_history(cntr_id,transaction,reason,user_id,shift) VALUES('$counter','$transaction','$reason','$trans_by','$shift') ") or die(mysqli_error($connection));

// $action="OPENED ".get_counter($counter)." COUNTER |-|-|SHIFT|".date('Y-m-d');
// logs($action);
 
?>
<!-- <script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Counter Opened Successfully", "success");
    $('#modal').modal('toggle');
    window.location.reload(); 


</script> -->


