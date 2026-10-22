<?php 
session_start();
include('../../inc/connection.php');
include('../includes/functions.php');

$id = $_GET['id']; 
$req_id=$_SESSION['req'];

$delete = mysqli_query($connection,"DELETE  FROM  kp_counter_req_items WHERE item_id='$id' AND req_id='$req_id'") or die(mysqli_error($connection));
if (!$delete) {
	echo "Failed to delete";
}else{
	$action="DELETED|-|". get_item($id)."|FROM REQUISITION NO|".$req_id;
	logs($action,'../logs'); 
	//header("Location:index.php");
}



?>