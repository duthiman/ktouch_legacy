<?php
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
$counter=$_REQUEST['counter'];
$user_id=$_REQUEST['user_id'];
$current_user_id=(int)($_REQUEST['current_user_id'] ?? 0);
$reason=$_REQUEST['reason'] ?? '';
$trans_by=$_SESSION['user_id'];
$shift=shift();

$counter=(int)$counter;
$user_id=(int)$user_id;
$prev_user=$current_user_id;

if ($counter < 1 || $user_id < 1) {
    http_response_code(422);
    exit('Missing counter or selected cashier.');
}

$transaction="CHANGED CASHIER FROM ".strtoupper($cashier=get_column('kp_counters','user_id',"cntr_id='$counter'"))." TO ".strtoupper(get_user($user_id));

$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$user_id'"); 
if ($prev_user > 0 && $prev_user !== $user_id) {
    $update_previous_waiter= mysqli_query($connection,"UPDATE kp_users SET job=2,counter_status=2,counter=0 WHERE user_id='$prev_user'");
}

$update_counter= mysqli_query($connection,"UPDATE kp_counters SET user_id='$user_id' WHERE cntr_id='$counter'") or die(mysqli_error($connection));

$shift= mysqli_query($connection,"INSERT INTO kp_counter_history(cntr_id,transaction,reason,user_id,shift) VALUES('$counter','$transaction','$reason','$trans_by','$shift') ") or die(mysqli_error($connection));
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Assigned Successfully", "success");
    $('#modal').modal('toggle');
    window.location.reload(); 


</script>
