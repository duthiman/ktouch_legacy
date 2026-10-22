<?php
session_start();
include('inc/connection.php');
$user_id = $_SESSION['user_id'];
$update_loggin_status = mysqli_query($connection,"UPDATE kp_users SET log_status=2 WHERE user_id='$user_id'") or die(mysqli_error($connection));
if (!$update_loggin_status) {
	echo "Logg out error";
}else{
	$_SESSION =array();
	if(isset($_COOKIE[session_name()])){
		setcookie(session_name(),'',time()-42000,'/');
	}
	session_destroy();
	header('Location:index.php');
}
?>