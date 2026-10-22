<?php
session_start();
include('../../inc/connection.php'); 
include('funcs.php');
$user_id = $_SESSION['user_id'];
$shift = $_SESSION['shift'];
$kit_id=$_SESSION['kit'];
 

$create_po = mysqli_query($connection,"INSERT INTO kp_kit(kit_id,user_id,shift) VALUES('$kit_id','$user_id','$shift')") or die(mysqli_error($connection));
$check = mysqli_query($connection,"SELECT * FROM  kp_kit_items WHERE  kit_id='$kit_id'");
while ($chk = mysqli_fetch_assoc($check)) {
  $item_id = $chk['item_id'];
  $qty = $chk['qty'];

  $track_stock = mysqli_query($connection,"INSERT INTO kp_stock_track(item_id,kit_id,user_id,qty,status,shift) VALUES('$item_id','$kit_id','$user_id','$qty','IN','$shift')");
  $update_stock = mysqli_query($connection,"UPDATE kp_items SET qty =qty +'$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));
}

$clear_status = mysqli_query($connection,"DELETE FROM kp_kit_status WHERE kit_id='$kit_id'") or die("Could clear status");

if (!$create_po AND !$clear_status) {
	echo "Failed";
}else{
	$action="PLACED|-|-| KITCHEN RECEIVE NO|".$kit_id;
    logs($action,'../');
header('Location:../dash/dash.php');
}
 

?> 