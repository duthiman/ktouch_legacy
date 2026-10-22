<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../inc/connection.php');
include('funcs.php');
$user_id = $_SESSION["user_id"];
$req_id = $_SESSION["req"]; 
$shift = get_column('kp_shift','shift',"1");
$item_id = $_POST['item_id'];
$qty = $_POST['qty'];
if (!isset($_SESSION['user_id']) || $_SESSION['user_id']==0) {
  header("Location:../log_out.php");
}
$counter=user_counter($user_id);  
$djob = (int)get_column('kp_users', 'job', "user_id='$user_id'");
if (empty($item_id) OR empty($qty)) {
	?> 
     <div class="alert alert-danger">
			<h5><b>Please fill all the required fields.!!!</b></h5>
		</div>
	<?php
}else{
	$check = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE item_id='$item_id' AND req_id='$req_id'");
	if (mysqli_num_rows($check) > 0) {
		?>
		<div class="alert alert-danger">
          <h5><b>Item already in the list!!!</b></h5>
        </div> 
		<?php
	}else{
		if ($djob==3 && $counter==0) {
			$create = mysqli_query($connection,"INSERT INTO kp_counter_req_items(req_id,req_user,item_id,req_qty,req_shift,counter) VALUES('$req_id','$user_id','$item_id','$qty','$shift','kit')") or die(mysqli_error($connection));
			if (!$create) {
				?> 
				<div class="row">
					<div class="alert alert-warning"> 
						<p><i class="fa fa-lg fa-warning"></i>&nbsp;Failed to add the item.Please try again.</p>
					</div>
				</div>
				<?php
			}else{
				$action="ADDED|".$qty."|". get_item($item_id)."|TO REQUISITION NO|".$req_id;
				logs($action,'../logs');
			} 
		}else{
			$create = mysqli_query($connection,"INSERT INTO kp_counter_req_items(req_id,req_user,item_id,req_qty,req_shift,counter) VALUES('$req_id','$user_id','$item_id','$qty','$shift','$counter')") or die(mysqli_error($connection));
			if (!$create) {
				?> 
				<div class="row">
					<div class="alert alert-warning"> 
						<p><i class="fa fa-lg fa-warning"></i>&nbsp;Failed to add the item.Please try again.</p>
					</div>
				</div>
				<?php
			}else{
				$action="ADDED|".$qty."|". get_item($item_id)."|TO REQUISITION NO|".$req_id;
				logs($action,'../logs');
			}
		}
	}
}
?>
	
	
