<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id=$_GET['user_id'];
$shift=$_SESSION['shift'];
$cashier= mysqli_query($connection,"UPDATE kp_users SET job=2,shift_status=2,counter=0,cleared=1 WHERE user_id='$user_id'");
 
$shift_hist= mysqli_query($connection,"UPDATE kp_staff_shift_hist SET closing=CURRENT_TIME,closing_datime=CURRENT_TIMESTAMP WHERE shift='$shift' AND user_id='$user_id'");

if (!$shift_hist) {
	echo "test";
}else{
	echo $staff_id;
	echo $shift;
}

$action="CLOSED|-|-|SHIFT|".$shift;
logs($action,"../");

$_SESSION =array();
if(isset($_COOKIE[session_name()])){
	setcookie(session_name(),'',time()-42000,'/');
}
session_destroy();
header('Location:../index.php?msg=2');
?>