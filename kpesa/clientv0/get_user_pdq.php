<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php'); 

$user_id = $_REQUEST['user_id'];
if (empty($user_id)) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else { 
    $claims_list = array();
    $query = "SELECT  * FROM kp_user_pdq WHERE clear_status=2 AND user_id='$user_id'";
    $result = mysqli_query($connection, $query);
    $claims = array();
    if (mysqli_num_rows($result) > 0) { 
        while ($pow = mysqli_fetch_array($result)) {
            $refno = $pow['refno']; 
            $amount = $pow['amount']; 
            $date = $pow['datime'];
            $claims[] = array("user_id" => $user_id, "refno" => $refno, "amount" => $amount, "date" => $date);
        }
    }
    $response = array();
    $response['pdqs'] = $claims;
    $response["status"] = 1;
    $response["message"] = "Use PDQ Report";

    echo json_encode($response);
}
