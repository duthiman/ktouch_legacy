<?php
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
$counter=$_REQUEST['counter']; 
$stat=$_REQUEST['stat'];
$reason=mysqli_real_escape_string($connection, $_REQUEST['reason'] ?? '');
$trans_by=$_SESSION['user_id'];
$shift=shift();
 if ($stat==0) {
	?>
	<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Make sure you select Status", "success"); 


</script><?php
}else{
if ($stat==1) {
	$stock="FINISHED";
}elseif ($stat==3) {
	$stock="CONTINUING";
}else{
	$stock="NOT STARTED";
} 

$transaction="CHANGED COUNTER STATUS  TO ".strtoupper($stock);

// $update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$user_id'"); 
// $update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=2,counter_status=2,counter=0 WHERE user_id='$prev_user'"); 

$update_counter= mysqli_query($connection,"UPDATE kp_counters SET take_status='$stat' WHERE cntr_id='$counter'") or die(mysqli_error($connection));

$shift= mysqli_query($connection,"INSERT INTO kp_counter_history(cntr_id,transaction,reason,user_id,shift) VALUES('$counter','$transaction','$reason','$trans_by','$shift') ") or die(mysqli_error($connection));
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Assigned Successfully", "success");
    $('#modal').modal('toggle');
    window.location.reload(); 


</script>
<?php
}
?>
