<?php
session_start();
include('../../inc/connection.php'); 

if (!isset($_SESSION['user_id'])) {
  header("Location:../log_out.php");
}
$user_id = $_SESSION['user_id'] ;

$get_shift = mysqli_query($connection,"SELECT * FROM kp_shift") or die("Could not get the shift");
$nus=mysqli_num_rows($get_shift);

if ($nus==0) {
	//header("Location:../dash/admin_dash.php?msg=1");
	$shift=date('Y-m-d');
}else{
$shi=mysqli_fetch_assoc($get_shift); 
$shift=date('Y-m-d');
}
 
$get_status = mysqli_query($connection,"SELECT * FROM kp_store_take_status") or die("Could not get the users");
$sh=mysqli_fetch_assoc($get_status);

$num=mysqli_num_rows($get_status);
if ($num==0) {
		$empty = "TRUNCATE TABLE kp_store_closing_stock";
	$empty_r = mysqli_query($connection,$empty)  or die("Error Truncating Counter Closing.:".mysqli_error($connection));
	 
		//INSERT THE CLOSING STOCK 
		$qty_column="store_qty";
		$closing_stock = mysqli_query($connection,"INSERT INTO kp_store_closing_stock(item_id,qty) SELECT item_id,$qty_column FROM kp_items") or die("Error Inserting to closing stock.:".mysqli_error($connection));

	$update_items = mysqli_query($connection,"UPDATE kp_items SET store_take=2") or die("Could not update items".mysqli_error($connection)); 
	 $update_bar_track = mysqli_query($connection,"INSERT INTO kp_store_take_status(shift,take_status,user_id) VALUES('$shift',3,'$user_id')") or die(mysqli_error($connection));
	 $status=3;
$shift=$shift;
}else{
$status=$sh['take_status'];
$shift=$sh['shift'];
}


if ($status=2) {
	$update_items = mysqli_query($connection,"UPDATE kp_items SET store_take=2") or die("Could not update items".mysqli_error($connection)); 
	$update_status = mysqli_query($connection,"UPDATE kp_store_take_status SET take_status=3") or die("Could not update store status"); 
	header("Location:take.php?shift=".$shift);
}elseif ($status==3) {

	header("Location:take.php?shift=".$shift);
}elseif ($status==1) {
	$sql = "TRUNCATE TABLE kp_store_take_status";
	$empty_s = mysqli_query($connection,$sql)  or die("Error Copy to store.:".mysqli_error($connection)); 
	header("Location:../dash/admin_dash.php?msg=1");
}

?> 