<?php
session_start();
include('../../inc/connection.php'); 
include('funcs.php');
$user_id = $_SESSION['user_id'];
$st = $_REQUEST['st'];

if ($st=='NEW') { 
	$check_status=mysqli_query($connection,"SELECT req_id FROM kp_req_status WHERE  user_id='$user_id'");
	if (mysqli_num_rows($check_status) > 0) {
		$on = mysqli_fetch_assoc($check_status);
		$req_id = $on['req_id']; 
	}else{ 
		$check_id=mysqli_query($connection,"SELECT req_id FROM kp_req_id");
		if (mysqli_num_rows($check_id) > 0) {	
			$update_petty = mysqli_query($connection,"UPDATE kp_req_id SET req_id=req_id+1") or die(mysqli_error($connection));
		}else{
			$create_quote_id = "INSERT INTO kp_req_id(req_id) VALUES(1)";
			$create_quote_id_r = mysqli_query($connection,$create_quote_id) or die("Could not create a new req_id");	
		}

		$get_qt_r = mysqli_query($connection,"SELECT req_id FROM kp_req_id") or die("Could not get the last qt_id");
		$row = mysqli_fetch_array($get_qt_r);
		
		$req_id = $row['req_id'];

		$create_qt_status = "INSERT INTO kp_req_status(req_id,user_id) VALUES('$req_id','$user_id')";
		$create_qt_status_r = mysqli_query($connection,$create_qt_status) or die("Could not create a quote status");

	}
}elseif ($st="CONT") {
	$req_id=$_REQUEST['id']; 
}else{

}
	if (empty($req_id) OR $req_id==0) {
		echo "ERROR";
	}else{
 
		$column='req_id';
		$value=$req_id;
		$folder='front/kuot/';
		$page= $folder.basename($_SERVER['PHP_SELF']);
		update_thesion($column,$value,$page);
 

	 	?>
	 	 <script type="text/javascript">
         window.location = "req_page.php";
    </script>	 	
	 	<?php
	}
?>