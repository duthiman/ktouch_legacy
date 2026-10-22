<?php
session_start();
include('../../inc/connection.php'); 
include('funcs.php');
$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");
$req_id=$_SESSION['req'];  
$counter=user_counter($user_id);

$created_col = mysqli_query($connection,"SHOW COLUMNS FROM kp_counter_req LIKE 'created_at'") or die(mysqli_error($connection));
if ($created_col && mysqli_num_rows($created_col) < 1) {
	mysqli_query($connection,"ALTER TABLE kp_counter_req ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP") or die(mysqli_error($connection));
}
$issue_created_col = mysqli_query($connection,"SHOW COLUMNS FROM kp_counter_req LIKE 'issue_created_at'") or die(mysqli_error($connection));
if ($issue_created_col && mysqli_num_rows($issue_created_col) < 1) {
	mysqli_query($connection,"ALTER TABLE kp_counter_req ADD COLUMN issue_created_at TIMESTAMP NULL DEFAULT NULL") or die(mysqli_error($connection));
}
$rec_created_col = mysqli_query($connection,"SHOW COLUMNS FROM kp_counter_req LIKE 'rec_created_at'") or die(mysqli_error($connection));
if ($rec_created_col && mysqli_num_rows($rec_created_col) < 1) {
	mysqli_query($connection,"ALTER TABLE kp_counter_req ADD COLUMN rec_created_at TIMESTAMP NULL DEFAULT NULL") or die(mysqli_error($connection));
}
 
$create_po = mysqli_query($connection,"INSERT INTO kp_counter_req(req_id,req_user,req_shift,counter) VALUES('$req_id','$user_id','$shift','$counter')") or die(mysqli_error($connection));
$clear_status = mysqli_query($connection,"DELETE FROM kp_counter_req_status WHERE req_id='$req_id'") or die("Could clear status");

if (!$create_po AND !$clear_status) {
	echo "Failed";
}else{
	$action="PLACED|-|-| REQUISITION NO|".$req_id;
    logs($action,'../logs');
	header('Location:../dash/dash.php');
}
 

?> 
