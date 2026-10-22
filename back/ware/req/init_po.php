<?php
session_start();
include('../../inc/connection.php'); 
include('funcs.php');
$user_id = $_SESSION['user_id'];
$folder="front/kugura/";
$sup_id = $_REQUEST['sup'];
$st = $_REQUEST['st'];
$po_day=date("Y-m-d");
if ($st=="3") {
	$po_id = $_GET['id'];
	$sup_id = $_GET['sup'];
	$page= $folder.basename($_SERVER['PHP_SELF']);
	$column="po_id"; 
	update_thesion($column,$po_id,$page);
	update_thesion("sup_id",$sup_id,$page);
	$action="CONTINUED LPO NO| ".$po_id;
	$url="../";
	header("Location: new_po.php");
	?>
	<script type="text/javascript">
		window.location = 'new_po.php';  
	</script>
	<?php
}else{
	$check_id=mysqli_query($connection,"SELECT po_id FROM kp_po_status WHERE  user_id='$user_id' AND sup_id='$sup_id' AND po_status=2");
	if (mysqli_num_rows($check_id) > 0) {
		$on = mysqli_fetch_assoc($check_id);
		$po_id = $on['po_id']; 
	}else{  
		$po_id = get_id('kp_po_id','po_id');

		$create_po_status = "INSERT INTO kp_po_status(po_id,user_id,sup_id) VALUES('$po_id','$user_id','$sup_id')";
		$create_po_status_r = mysqli_query($connection,$create_po_status) or die("Could not create a new purchase number"); 
	}  
	$page= $folder.basename($_SERVER['PHP_SELF']);
	$column="po_id"; 
	update_thesion($column,$po_id,$page);
	update_thesion("sup_id",$sup_id,$page);
	update_thesion("day",$po_day,$page);
	$action="INITIALISED LPO NO| ".$po_id;
	$url="../";
	?>
	<script type="text/javascript">
		window.location = 'new_po.php';  
	</script>
	<?php
}
?>