<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
if (isset($_GET['req_id']) AND isset($_GET['item_id']) AND isset($_GET['qty'])) {
	$req_id = $_GET['req_id'];
	$item_id = $_GET['item_id'];
	$qty = $_GET['qty'];
	$user_id = $_SESSION['user_id'];
	
	$shift = get_column('kp_shift','shift',"1");  
	$counter=get_column('kp_counter_req','counter',"req_id='$req_id'"); 
	$column_qty="cntr".$counter."_qty";
	$rec_created_col = mysqli_query($connection,"SHOW COLUMNS FROM kp_counter_req LIKE 'rec_created_at'") or die(mysqli_error($connection));
	if ($rec_created_col && mysqli_num_rows($rec_created_col) < 1) {
		mysqli_query($connection,"ALTER TABLE kp_counter_req ADD COLUMN rec_created_at TIMESTAMP NULL DEFAULT NULL") or die(mysqli_error($connection));
	}
	$check_stock = mysqli_query($connection,"SELECT dpt FROM kp_items WHERE item_id='$item_id'") or die(mysqli_error($connection));
	$stock_row = mysqli_fetch_assoc($check_stock);
	$shop = isset($stock_row['dpt']) ? $stock_row['dpt'] : 0;
 
	
	$update = mysqli_query($connection,"UPDATE kp_counter_req_items SET s2s=1 WHERE req_id='$req_id' AND item_id='$item_id'");

	$track_stock = mysqli_query($connection,"INSERT INTO kp_stock_track(item_id,req_id,user_id,qty,status,stock_counter,shift,dpt) VALUES('$item_id','$req_id','$user_id','$qty','IN','$counter','$shift','$shop')") or die(mysqli_error($connection));

	$update_stock = mysqli_query($connection,"UPDATE kp_items SET ".$column_qty." =".$column_qty." +'$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));
	
	if (!$update && !$update_stock ) {
		echo "Failed";
	}else{
		$action="RECEIVED|".$qty."|".get_item($item_id)."|REQUISITION NO|".$req_id;
		logs($action,'../logs');
		$check = mysqli_query($connection,"SELECT * FROM  kp_counter_req_items WHERE  s2s=2 AND req_id='$req_id'");
		if (mysqli_num_rows($check) < 1) {
			 mysqli_query($connection,"UPDATE  kp_counter_req SET rec_status=1 ,s2s=1,rec_created_at=CURRENT_TIMESTAMP WHERE req_id='$req_id'") or die("error updating requistion".mysqli_error($connection));
			header("Location:rec_pending.php?msg=2");
		}else{
			header("Location:req_rec.php?req=$req_id&msg=1");
		}
		
	}
}

?>
