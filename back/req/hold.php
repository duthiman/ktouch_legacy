<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php'); 

$user_id = $_SESSION['user_id']; 
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
$req_id = $row['req_id'];  

$sql1 = "UPDATE kp_req_status SET status=3 WHERE user_id='$user_id' AND req_id='$req_id'";
$result = mysqli_query($connection , $sql1) or die('could suspend make sale');
 
 if (!$result) {
 	 
 }else{ 
 	clear_thesion_ids($user_id);
	$action="HELD|-|-|-|REQUEST|".$req_id;
	logs($action,"../../");  
	header("Location:../dash/".$dash);
 }
?>