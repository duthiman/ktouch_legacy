<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id = $_SESSION["user_id"];
$kit_id = $_SESSION["kit"];
$shift = get_column('kp_shift','shift',"1");
$item_id = $_POST['item_id'];
$qty = $_POST['qty'];
$djob = $_SESSION['djob'];


if (empty($item_id) OR empty($qty)) {
	?> 
     <div class="alert alert-danger">
			<h5><b>Please fill all the required fields.!!!</b></h5>
		</div>
	<?php
}else{
	$check = mysqli_query($connection,"SELECT * FROM kp_kit_items WHERE item_id='$item_id' AND kit_id='$kit_id'");
	if (mysqli_num_rows($check) > 0) {
		?>
		<div class="alert alert-danger">
          <h5><b>Item already in the list!!!</b></h5>
        </div> 
		<?php
	}else{
			$create = mysqli_query($connection,"INSERT INTO kp_kit_items(kit_id,user_id,item_id,qty,shift) VALUES('$kit_id','$user_id','$item_id','$qty','$shift')") or die(mysqli_error($connection));
			if (!$create) {
				?> 
				<div class="row">
					<div class="alert alert-warning"> 
						<p><i class="fa fa-lg fa-warning"></i>&nbsp;Failed to add the item.Please try again.</p>
					</div>
				</div>
				<?php
			}else{
				$action="ADDED|".$qty."|". get_item($item_id)."|TO KITCHEN RECEIVE NO|".$kit_id;
				logs($action);
			} 
	
	}
}
?>
	
	
