<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');  

$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
$po_id = $row['po_id']; 
$item_id = mysqli_real_escape_string($connection,$_POST['item_id']);
$price  = mysqli_real_escape_string($connection,$_POST['price']);
 
 
 
if (empty($price)) {
?>
	<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Price cannot be empty", "danger");

</script>
	<?php
}elseif ($price<=0) {
?>
	<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Price cannot be Zero / Less than Zero", "danger");

</script>
	<?php
}else{ 
	$total = $qty * $price;

	$update_order = "UPDATE kp_po_items SET price='$price',total=price*qty WHERE item_id='$item_id' AND po_id='$po_id'";
    $update_order_r = mysqli_query($connection,$update_order) or die("Could not create".mysqli_error($connection)); 
 
   
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
	hulla.send("Price updated Successfully", "success");

</script>

<?php

				
}
 
 
?>