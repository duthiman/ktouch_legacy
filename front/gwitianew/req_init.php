<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $user_id==0 || empty($user_id)) {
    ?>        
    <script>
       window.location = "../log_out.php";
    </script>
    <?php
}else{
    //check if there is any active poing process
    $check_order=mysqli_query($connection,"SELECT req_id FROM kp_counter_req_status WHERE  req_user_id='$user_id' AND req_status=2") or die("Could not check req".mysqli_error($connection));
    if (mysqli_num_rows($check_order) > 0) {
        $on = mysqli_fetch_assoc($check_order);
        $new_req = $on['req_id'];
        $_SESSION["req"]=$new_req;   
    }else{ 
        $get_id = mysqli_query($connection,"SELECT req_id FROM kp_counter_req_id") or die("Could not get the last order numbers");
        if (mysqli_num_rows($get_id) > 0) {
            $row = mysqli_fetch_array($get_id);
            $last_req = $row['req_id'];
            $new_req = $last_req + 1;

            $create_po_status = "INSERT INTO kp_counter_req_status(req_id,req_user_id) VALUES('$new_req','$user_id')";
            $create_po_status_r = mysqli_query($connection,$create_po_status) or die("Could not create a new req status".mysqli_error($connection));

            $create_last_po = "UPDATE kp_counter_req_id SET req_id='$new_req'";
            $create_last_po_r = mysqli_query($connection,$create_last_po) or die("Could not create a last po id");

            $_SESSION["req"]=$new_req; 
        }else{ 
            $create_po_id = mysqli_query($connection,"INSERT INTO kp_counter_req_id(req_id) VALUES(1)") or die("Could not insert a new po id");
            $new_po =1;
            $create_po_status = "INSERT INTO kp_counter_req_status(req_id,req_user_id) VALUES('$new_req','$user_id')";
            $create_po_status_r = mysqli_query($connection,$create_po_status) or die("Could not create a new po number".mysqli_error($connection));
            $_SESSION["req"]=$new_req;
        }
    }
    $counter=user_counter($_SESSION['user_id']);

    $action="STARTED|-|-|REQUISTIO NO|".$_SESSION['req']."FOR".get_counter($counter)." COUNTER ";
    logs($action,'../logs');
    ?>        
    <script>
        window.location.replace("../gwitia");
    </script>
    <?php 
} 
?>