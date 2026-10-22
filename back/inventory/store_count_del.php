<?php
session_start();
include("../../inc/connection.php");
$id = $_GET['id'];
$shift = $_GET['shift'];

$closing_stock = mysqli_query($connection,"SELECT closing FROM kp_store_stock_take WHERE item_id='$item_id' AND shift='$shift'");
$open = mysqli_fetch_assoc($closing_stock);
$closing =round($open['closing'],2); 

$delete = mysqli_query($connection,"DELETE FROM kp_store_stock_take WHERE item_id='$id' AND shift='$shift'") or die(mysqli_error($connection));;

$update_items = mysqli_query($connection,"UPDATE kp_items SET store_take=2,store_qty='$closing' WHERE item_id='$id'") or die(mysqli_error($connection));;

if (!$delete OR !$update_items) {
	echo "Failed to delete";
}else{
	header("Location:take.php?shift=".$shift);
}

?>