<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
  
$sale_id = $_GET['id'];  

$delp = "UPDATE kp_sale_print SET k_print=1 WHERE sale_id='$sale_id' AND dpt=2 AND status=3";
$up_r = mysqli_query($connection,$delp) or die("Could not update the print items".mysqli_error($connection));

$delp = "DELETE FROM kp_sale_print WHERE sale_id='$sale_id' AND dpt=2 AND status=1";
$delp_r = mysqli_query($connection,$delp) or die("Could not delete product".mysqli_error($connection));

$action="FINISHED|-|-PRINTING KITCHEN TICKET FOR ORDER NO|".$sale_id;
logs($action,'../logs');

if (!$delp_r && !$up_r ) {
	die("Failed to place order");
}else{ 
	$print_mode = get_print_mode();
	if ($print_mode != 1) {
		header('Location:../index.php');
	} else {
		header('Location:riko_print.php');
	}
}
?>
