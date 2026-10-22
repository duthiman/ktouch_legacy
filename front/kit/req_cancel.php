<?php 
session_start();
include('../../inc/connection.php');
include('funcs.php'); 
$req_id = $_SESSION["req"];

$delete = mysqli_query($connection,"DELETE  FROM kp_counter_req_items WHERE  req_id = '$req_id'") or die(mysqli_error($connection));
if (!$delete) {
	echo "Failed to delete";
}else{ 
	$action="CANCELLED|-|-|REQUISITION NO|".$req_id;
	    logs($action);
	header("Location:../stock/store_dash.php?msg=2");
}



?>