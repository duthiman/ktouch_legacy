<?php
include('funcs.php');
$user_id = $_SESSION['mwene']; 
$day=today();
if ($user_id==0) {
	?>
	<script type="text/javascript">
		var hulla = new hullabaloo();
        hulla.send('SESSION ERROR!!LOGOUT AND TRY TO LOGIN AGAIN', "danger");
	</script>
	<?php
}else{
	if (isset($_GET['cont'])) {
		$po_id = $_GET['id'];
		$sup_id = $_GET['sup'];
		$page= $folder.basename($_SERVER['PHP_SELF']);
		$column="po_id";
		trans_thesion($po_id,'PURCHASE') ;
		$action="CONTINUED LPO NO| ".$po_id;
		$url="../../";
		logs($url,'back/#kugura/','PURCHASE',$po_id,'-','-',$action);
		logs("../",'back/#kugura/','PURCHASE',$po_id,'-','-',$action);
		echo '<span id="kug-po-ready">'.$po_id.'</span>';
	}else{
		$sup_id = $_REQUEST['sup'];
		$type = $_REQUEST['type'];
		$poby = isset($_REQUEST['poby']) ? $_REQUEST['poby'] : 5;
		$check_id=mysqli_query($connection,"SELECT po_id FROM kp_po_status WHERE  po_user_id='$user_id' AND sup_id='$sup_id' AND po_status=2");
		if (mysqli_num_rows($check_id) > 0) {
			$on = mysqli_fetch_assoc($check_id);
			$po_id = $on['po_id'];
		}else{
			$get_id=mysqli_query($connection,"SELECT po_id FROM kp_po_id");
			if (mysqli_num_rows($get_id) > 0) {
				$update_po = mysqli_query($connection,"UPDATE kp_po_id SET po_id=po_id+1") or die("Could not create a new purchase order number");
			}else{
				$create_id = "INSERT INTO kp_po_id(po_id) VALUES(1)";
				$create_id_r = mysqli_query($connection,$create_id) or die("Could not create a new purchase number");
			}

			$get_po_r = mysqli_query($connection,"SELECT po_id FROM kp_po_id") or die("Could not get the last purchase id");
			$row = mysqli_fetch_array($get_po_r);

			$po_id = $row['po_id'];

			$create_po_status = "INSERT INTO kp_po_status(po_id,po_user_id,sup_id,potype,poby,po_day) VALUES('$po_id','$user_id','$sup_id','$type','$poby','$day')";
			$create_po_status_r = mysqli_query($connection,$create_po_status) or die(mysqli_error($connection)." Could not create a new purchase number");
		}

		trans_thesion($po_id,'PURCHASE') ;
		$action="INITIALISED LPO NO| ".$po_id;
		$url="../../";
		logs($url,'back/kugura/','PURCHASE',$po_id,'-','-',$action);
		logs("../",'back/kugura/','PURCHASE',$po_id,'-','-',$action);
		echo '<span id="kug-po-ready">'.$po_id.'</span>';
	}

}
?>