<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php'); 
$response=array();
if (empty($_REQUEST['pin'])) { 
     $response["user_id"] ='0';
     $response["username"] ='';
     $response["status"] =0;
     $response["message"] ="Empty Pin"; 
     echo json_encode($response);
}else{
    $kihingo = mysqli_real_escape_string($connection,$_REQUEST['pin']); 
    $verify = mysqli_query($connection,"SELECT * FROM kp_users WHERE pin='$kihingo'") or die("Could not verify the password");
    if (mysqli_num_rows($verify) < 1) { 
        $response["user_id"] ='0';
         $response["username"] ='';
         $response["status"] =0;
         $response["message"] ="WRONG PIN"; 
         echo json_encode($response);
    }else{
        $row = mysqli_fetch_assoc($verify);
        $login_status=2; 
        $work_status=$row['work_status']; 
        $username=$row['username'];
        $user_id=$row['user_id'];   
        $role = $row['role_id']; //1 represents cashier/supervisor, demo purposes
        if ($login_status != 2) {
            $response["user_id"] = $user_id;
            $response["username"] = $username;
            $response["status"] = 0;
            $response["message"] = "Contact Cashier!!!You are logged in another app";
            echo json_encode($response);
        } else {
            if ($work_status != 2) {
                $response["user_id"] = $user_id;
                $response["username"] = $username;
                $response["status"] = 0;
                $response["message"] = "Contact Cashier!!!You are Suspended";
                echo json_encode($response);
            } else {
                $response["user_id"] = $user_id;
                $response["username"] = $username;
                $response["status"] = 1;
                $response["user_role"] = $role; 
                $response["message"] = "Login Successful";
                echo json_encode($response);
            }
        }
    }
}
 
?>