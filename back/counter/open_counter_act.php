<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
$counter=(int)($_REQUEST['counter'] ?? 0);
$user_id=(int)($_REQUEST['user_id'] ?? 0);
$take_status=(int)($_REQUEST['take_status'] ?? 3);
$take_status=in_array($take_status,[1,2,3],true) ? $take_status : 3;

if ($counter < 1 || $user_id < 1) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Missing counter or cashier.']);
    exit;
}
 
$column="cntr".$counter."_take";

$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$user_id'");

$update_waiter= mysqli_query($connection,"UPDATE kp_items SET ".$column."=2");

$update_counter= mysqli_query($connection,"UPDATE kp_counters SET user_id='$user_id',take_status='$take_status',counter_status=1,close_status=3 WHERE cntr_id='$counter'") or die(mysqli_error($connection));
echo json_encode(['ok' => true, 'message' => 'Counter opened successfully.']);


