<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
$counter=(int)($_REQUEST['counter'] ?? 0);
$stat=(int)($_REQUEST['stat'] ?? 0);
$reason=mysqli_real_escape_string($connection, $_REQUEST['reason'] ?? '');
$trans_by=(int)($_SESSION['user_id'] ?? 0);
$shift=shift();
if ($counter < 1 || !in_array($stat,[1,2,3],true)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'message'=>'Please select a valid counter take status.']);
    exit;
} else {
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
echo json_encode(['ok'=>true,'message'=>'Counter take status updated successfully.']);
}
?>
