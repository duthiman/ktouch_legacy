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

// Keep compatibility with older callers that do not send the current cashier.
if ($prev_user < 1) {
    $counter_user=mysqli_query($connection,"SELECT user_id FROM kp_counters WHERE cntr_id='$counter'");
    $st=$counter_user ? mysqli_fetch_assoc($counter_user) : null;
    $prev_user=(int)($st['user_id'] ?? 0);
}

$transaction="CHANGED CASHIER FROM ".strtoupper($cashier=get_column('kp_counters','user_id',"cntr_id='$counter'"))." TO ".strtoupper(get_user($user_id));

$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$user_id'"); 
$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=2,counter_status=2,counter=0 WHERE user_id='$prev_user'"); 

$update_counter= mysqli_query($connection,"UPDATE kp_counters SET user_id='$user_id' WHERE cntr_id='$counter'") or die(mysqli_error($connection));

$shift= mysqli_query($connection,"INSERT INTO kp_counter_history(cntr_id,transaction,reason,user_id,shift) VALUES('$counter','$transaction','$reason','$trans_by','$shift') ") or die(mysqli_error($connection));
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Assigned Successfully", "success");
    $('#modal').modal('toggle');
    window.location.reload(); 


</script>
