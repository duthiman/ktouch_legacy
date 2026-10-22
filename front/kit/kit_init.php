<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

 $user_id = $_SESSION['user_id'];
//check if there is any active poing process
$check_order=mysqli_query($connection,"SELECT kit_id FROM kp_kit_status WHERE  user_id='$user_id' AND status=2") or die("Could not check req".mysqli_error($connection));
if (mysqli_num_rows($check_order) > 0) {
$on = mysqli_fetch_assoc($check_order);
$new_kit = $on['kit_id'];
$_SESSION["kit"]=$new_kit;  
echo "no status but kit has number"; 
}else{ 
$get_id = mysqli_query($connection,"SELECT kit_id FROM kp_kit_id") or die("Could not get the last order numbers");
if (mysqli_num_rows($get_id) > 0) {
$row = mysqli_fetch_array($get_id);
$last_kit = $row['kit_id'];
$new_kit = $last_kit + 1;

$create_po_status = "INSERT INTO kp_kit_status(kit_id,user_id) VALUES('$new_kit','$user_id')";
$create_po_status_r = mysqli_query($connection,$create_po_status) or die("Could not create a new req status".mysqli_error($connection));

$create_last_po = "UPDATE kp_kit_id SET kit_id='$new_kit'";
$create_last_po_r = mysqli_query($connection,$create_last_po) or die("Could not create a last po id");

$_SESSION["kit"]=$new_kit; 
}else{ 
    $create_po_id = mysqli_query($connection,"INSERT INTO kp_kit_id(kit_id) VALUES(1)") or die("Could not insert a new po id");
    $new_po =1;
    $create_po_status = "INSERT INTO kp_kit_status(kit_id,user_id) VALUES('$new_kit','$user_id')";
    $create_po_status_r = mysqli_query($connection,$create_po_status) or die("Could not create a new po number".mysqli_error($connection));

    $_SESSION["kit"]=$new_req; 

}

}
 
$action="STARTED|-|-|Kitchen Receive|";
logs($action,'../');

 header("Location:index.php");
 

?>