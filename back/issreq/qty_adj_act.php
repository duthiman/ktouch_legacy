<?php
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
$user_id = $_SESSION['user_id'];

$item_id = $_REQUEST['item_id'];
$qty = $_REQUEST['qty'];
$reason = $_REQUEST['reason'];
$init = $_REQUEST['init'];
$trans = "STORE STOCK ADJUSTMENTS";
if (empty($qty) OR empty($reason)) {
?>
<div class="panel-body">
	<div class="alert alert-warning">
		<strong>You Must input reason and Quantity.</strong>
	</div>
</div>
<?php
}else{ 
$create_history = mysqli_query($connection, "INSERT INTO kp_store_hist(item_id,user_id,init,new,reason,trans) VALUES('$item_id','$user_id','$init','$qty','$reason','$trans')") or die(mysqli_error($connection));
if (!$create_history) {
	echo "Failed to create History";
}else{
	$adjust_quantity = mysqli_query($connection, "UPDATE kp_items SET ware_qty = '$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));
	?>
	<div class="alert alert-success">
		<strong>Quantity Adjusted Successfully.</strong>
	</div>
	<script type="text/javascript">
		window.setTimeout(function(){
			window.location.reload();
		}, 100);
	</script>

	<?php
}
}
?>
		

 