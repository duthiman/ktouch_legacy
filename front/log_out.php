<?php
session_start();
include('../inc/connection.php');
include('funcs.php');
$user_id= $_SESSION['user_id'];
$update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=2 WHERE user_id='$user_id'") or die(mysqli_error($connection));

$action="LOGGED OUT";
logs($action,'logs');
clear_thesion($user_id);
$_SESSION =array();
if(isset($_COOKIE[session_name()])){
	setcookie(session_name(),'',time()-42000,'/');
}
session_destroy();
mysqli_close($connection);
header('Location:index.php');
?>