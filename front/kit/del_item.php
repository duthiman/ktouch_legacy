<?php 
session_start();
include('../../inc/connection.php');
include('funcs.php');

$id = $_GET['id']; 
$kit_id=$_SESSION['kit'];

$delete = mysqli_query($connection,"DELETE  FROM  kp_kit_items WHERE item_id='$id' AND kit_id='$kit_id'") or die(mysqli_error($connection));
if (!$delete) {
	echo "Failed to delete";
}else{
	$action="DELETED|-|". get_item($id)."|FROM KITCHEN RECEIVE NO |".$kit_id;
logs($action,'../'); 
	header("Location:index.php");
}



?>