<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details); 
$req_id = $row['req_id'];   
$shop = $row['shop'];   

$create_qt = mysqli_query($connection,"INSERT INTO kp_req(req_id,req_user,req_date,shop_id) VALUES('$req_id','$user_id',CURRENT_DATE,'$shop')") or die(mysqli_error($connection));

$update_items = mysqli_query($connection,"UPDATE kp_req_items SET req_status=1 WHERE req_id='$req_id'") or die(mysqli_error($connection));

$clear_qt_status = mysqli_query($connection,"DELETE FROM kp_req_status  WHERE req_id='$req_id'") or die("Error");

if (!$create_qt OR !$update_items) {
	echo "Failed";
}else{
	?>
	<script type="text/javascript">
		$('#qt-modal').modal('hide');	
		window.location.href = "req_print.php?id="+'<?php echo $req_id; ?>';
	</script>
	<?php
}