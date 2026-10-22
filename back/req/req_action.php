<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details); 
$req_id = $row['req_id']; 
$item_id = $_POST['item_id'];
$quantity = $_POST['quantity']; 
 
if (empty($item_id) OR empty($quantity)) {
	?>
<div class="panel-body">
	<div class="row">
		<div class="alert alert-warning">
			<p><i class="fa fa-lg fa-warning"></i>&nbsp;Please fill all the required fields.</p>
		</div>
	</div>
</div>
<?php
}else{
		$results = mysqli_query($connection ,"SELECT * FROM kp_req_items WHERE item_id ='$item_id' AND req_id='$req_id'") or die(mysqli_error($connection));
		if(mysqli_num_rows($results) < 1) {
			$create = mysqli_query($connection,"INSERT INTO kp_req_items(req_id,item_id,req_qty) VALUES('$req_id','$item_id','$quantity')") or die(mysqli_error($connection));
			if (!$create) {
?>
<div class="panel-body">
	<div class="row">
        <div class="alert alert-warning">        
           <p><i class="fa fa-lg fa-warning"></i>&nbsp;Failed to add the item.Please try again.</p>
        </div>
    </div>
</div>
<?php
	}
}else{
	?>
<div class="alert alert-warning">
	<a href="#" class="close" data-dismiss="alert"> &times; </a>
	<strong>Oops!</strong>Item already captured update price and quantity in approve section.
</div>
	<?php
		}
		}
?>
	
