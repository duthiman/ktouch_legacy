 
<?php
session_start();
include('../../inc/connection.php');
include("funcs.php"); 
$user_id = $_SESSION['user_id'];
page_restrict(); 
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
$po = $row['po_id'];  
$grn_id = $row['grn_id']; 

$id = $_REQUEST['id']; 

$check_grn=mysqli_query($connection,"SELECT * FROM kp_grn_items WHERE id='$id'");
$dd=mysqli_fetch_assoc($check_grn);
$item_id=$dd['item_id'];
$grn_id =$dd['grn_id']; 
$qty =$dd['qty'];

$update = mysqli_query($connection,"UPDATE kp_po_items SET rec_qty=rec_qty-'$qty' WHERE po_id='$po' AND item_id='$item_id'");
if ($update) {
	$create_grn= mysqli_query($connection,"DELETE FROM kp_grn_items WHERE  id='$id'") or die(mysqli_error($connection));
	if ($create_grn) {
		?>
		<div class="alert alert-success">
		  	<strong><?php echo $qty." ". get_item($item_id); ?> REMOVED SUCCSSFULLY!!!</strong>
		</div>
		<script type="text/javascript"> 
		    window.location.reload(); 
		</script>
		<?php
	}else{
	?>
		<p style="color: red;">COULD NOT INSERT ITEMS INTO GRN</p>
	<?php
	}
}else{
	?>
	<p style="color: red;">COULD NOT UPDATE PO ITEMS </p>
	<?php
} 
 
?>