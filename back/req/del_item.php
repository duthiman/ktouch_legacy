<?php
session_start();
include('../../inc/connection.php'); 
include('../clude/funcs.php'); 

$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
$req_id = $row['req_id']; 
$item_id = $_REQUEST['item_id']; 

$delete = mysqli_query($connection,"DELETE FROM kp_req_items WHERE item_id='$item_id' AND req_id='$req_id' ") or die("could not delete sale item". mysqli_error($connection));
 
if(!$delete){
	echo "did not delete";
}else{
	$action="DELETED|".$qty."|".get_item($item_id)."|REQUEST|".$req_id;
	logs($action,"../../"); 
}

?>
