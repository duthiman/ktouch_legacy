<?php
session_start();
include('../../inc/connection.php'); 
include('../../inc/gen_funcs.php');  
$user_id=$_SESSION['user_id'];
$id     =$_REQUEST['id'];
$code   =$_REQUEST['code'];
$name   =$_REQUEST['name'];
$amount =str_replace( ',', '',$_REQUEST['amount']);
$date   =$_REQUEST['date'];
$time   =$_REQUEST['time'];
$phone  =$_REQUEST['phone']; 
if ( empty($id) or $id==0) {
	?>
	<script type="text/javascript">
		var hulla = new hullabaloo();
		hulla.send("You have to select message!!!!", "warning");
	</script>
	<?php
}else{ 
	$track=mysqli_query($con,"INSERT IGNORE INTO split_message(name, phone, code, amount, date, time) VALUES ('$name', '$phone', '$code', '$amount', '$date', '$time')") or die("Could not track the item".mysqli_error($con));
	if ($track) {
		$edit_item = "UPDATE mpesa_message SET split_status=1,manual_split =1 WHERE id='$id' " or die("Error edition the file");
		$edit_item_r = mysqli_query($con,$edit_item) or die("error on 18".mysqli_error($con));
		if (!$edit_item_r) {
			?>
			<script type="text/javascript">
				var hulla = new hullabaloo();
				hulla.send("Failed to update Cotact Administrator!!!!", "danger");
			</script>
		<?php
		}else{
			?>
			<script type="text/javascript">
				var hulla = new hullabaloo();
				hulla.send("Updated Successfully!!!!", "success"); 
				$('#modal').modal('toggle');
				location.reload();
			</script>
			<?php
		}
	}else{
		?>
		<script type="text/javascript">
			var hulla = new hullabaloo();
			hulla.send("COULD NOT TRACK CLAIMED MESSAGES!!!!", "success"); 
		</script>
		<?php
	}
}
?>