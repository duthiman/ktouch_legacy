<?php
session_start();
include('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
require_once __DIR__ . '/API/take_schema.php';
$user_id=$_SESSION['user_id'];
$counter=user_counter($user_id); 

if (!isset($_SESSION['user_id'])  OR !isset($counter) ) {
  header("Location:../log_out.php");
}  
if ($counter==0) {
	echo "CALL ADMINISTRATOR AND REPORT ERROR STOCK TAKE IN EMPTY COUNTER";
}else{
	$counter_status=take_user_counter_status($user_id);
	if ($counter_status!=1) {
		$_SESSION['counter']=$counter; 
		header("Location:stock_take.php");
	}else{
		header("Location:../dash/dash.php");
	}
}
?> 
