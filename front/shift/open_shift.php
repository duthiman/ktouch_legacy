<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id=$_SESSION['user_id'];

$place = "INSERT INTO kp_shift(shift) VALUES(CURRENT_DATE)";
$place_r = mysqli_query($connection,$place) or die("Could not open shift");

$get_order = "SELECT sale_id FROM kp_sale_id";
$get_order_r = mysqli_query($connection,$get_order) or die("Could not get the last order numbers");
$row = mysqli_fetch_array($get_order_r);
$last_id = $row['sale_id'];

$shift= mysqli_query($connection,"INSERT INTO kp_day_shift(shift,opening,open_by,open_ord,status) VALUES(CURRENT_DATE,CURRENT_TIMESTAMP,'$user_id','$last_id',2) ");
if ($shift) {
	$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=2,counter_status=2");
	if ($update_waiter) {
		$update_counter= mysqli_query($connection,"UPDATE kp_counters SET take_status=2,counter_status=2");
		if ($update_counter) {
			$action="OPENED|-|-|SHIFT|".date('Y-m-d');
			logs($action,'../logs');
			?>
			<script type="text/javascript"> 
				window.setTimeout(function(){
					window.location.href = '../log_out.php';
				}, 100);
			</script>
			<?php 
		}else{
			echo "COULD NOT UPDATE WAITERS DETAILS";
		}
	}else{
	echo "COULD NOT UPDATE WAITERS DETAILS";
}
}else{
	echo "COULD NOT ADD DAY SHIFT DETAILS";
}

 

?>
 