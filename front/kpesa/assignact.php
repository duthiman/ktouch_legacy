<?php
session_start();
include('../../inc/connection.php');
include("funcs.php");

$user_id = $_GET['id']; 
$code = $_GET['code'];
$shift = get_column('kp_shift','shift',"1");

$upord = "UPDATE kp_split_message SET approve_status=1, claim_staff='$user_id', shift='$shift' WHERE code='$code'";
$upord_r = mysqli_query($connection,$upord) or die(mysqli_error($connection)."Could not update order status");


header('Location:mpesas.php?msg=2');
	 ?>