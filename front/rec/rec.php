<?php
session_start();
include('../../inc/connection.php');include('funcs.php');
$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1"); 
$req_id = $_POST['req'];
$item_id = $_POST['id'];
$qty = $_POST['qty'];

$update = mysqli_query($connection,"UPDATE kp_counter_req_items SET rec_status=1,rec_qty='$qty',rec_shift='$shift',rec_user='$user_id' WHERE req_id='$req_id' AND item_id='$item_id'");

$update = mysqli_query($connection,"UPDATE kp_counter_req_items SET rec_status=1,rec_qty='$qty',rec_shift='$shift',rec_user='$user_id' WHERE req_id='$req_id' AND item_id='$item_id'");


if (!$update) {
	echo "Failed";
}

?>