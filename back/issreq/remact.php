<?php
session_start();
include('../../inc/connection.php'); 
include("funcs.php");
$user_id = $_SESSION['user_id']; 
$item_id = $_REQUEST['item_id'];
$req = $_REQUEST['req'];
$reason = $_REQUEST['reason']; 
$trans = "REMOVE REQUEST ITEM";
if (empty($reason)) {
?>
<div class="panel-body">
	<div class="alert alert-warning">
		<strong>You Must input reason.</strong>
	</div>
</div>
<?php
}else{
	$counter=get_column('kp_counter_req','counter',"req_id='$req'");
	$shift = get_column('kp_shift','shift',"1"); 
	$track= mysqli_query($connection, "INSERT INTO kp_counter_req_del_items(item_id,user_id,req_id,reason,shift,counter) VALUES('$item_id','$user_id','$req','$reason','$shift','$counter')") or die(mysqli_error($connection));
	if ($track) {
		$del = mysqli_query($connection,"DELETE FROM kp_counter_req_items WHERE item_id='$item_id' AND req_id='$req'");
		if ($del) {
			?>
			<div class="alert alert-success">
				<strong>SUCCESS!!!REMOVED SUCCESSFULLY.</strong>
			</div>
			<script type="text/javascript">
				window.setTimeout(function(){
					window.location.reload();
				}, 200);
			</script>
			<?php
		}else{
			$del = mysqli_query($connection,"DELETE FROM kp_counter_req_del_items WHERE item_id='$item_id' AND req_id='$req_id'");
			?>
			<div class="alert alert-success">
				<strong>COULD NOT REMOVE THE ITEM.</strong>
			</div> 
			<?php
			}
	}else{
		?>
		<div class="alert alert-danger">
			<strong>COULD NOT TRACK THE ITEMS DELETED.</strong>
		</div> 
		<?php
	} 
}
?>
		

 