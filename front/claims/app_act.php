<?php
session_start();
include('../../inc/connection.php');
include("funcs.php");
$con=$connection;
$user_id=$_SESSION['user_id'];
$id=$_REQUEST['id']; 
if ( empty($id) or $id==0) {
	?>
	<script type="text/javascript">
		var hulla = new hullabaloo();
		hulla.send("You have to select message!!!!", "warning");
	</script>
	<?php
}else{   
	$upord = "UPDATE split_message SET assign_status=1, user_id=claim_staff,app_user='$user_id'  WHERE id='$id'";
	$upord_r = mysqli_query($con,$upord) or die(mysqli_error($con)."Could not update order status");

	$upclaim = "UPDATE message_claim SET app_status=1 WHERE msg_id='$id'";
	$upclaim_r = mysqli_query($con,$upclaim) or die(mysqli_error($con)."Could not update Claim status");
	?>
	<script type="text/javascript">
	 	var hulla = new hullabaloo();
		hulla.send("ASSIGNED SUCCESSFULLY!!!!", "success");
		setTimeout(() => { 
			location.reload(); }, 300); 
	</script>
	<?php
}
?>