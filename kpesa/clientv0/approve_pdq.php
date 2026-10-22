<?php
session_start();
include('../../inc/connection.php');

$user_id = $_REQUEST['user_id'];
$message_id = $_REQUEST['id'];
if (empty($user_id) or $user_id == 0) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else {
    $query_assign = "UPDATE kp_user_pdq SET app_status = 1, app_user = '$user_id' WHERE id = '$message_id'";
    $update_message = mysqli_query($connection, $query_assign);

    if (!$update_message) {
        $response["status"] = 0;
        $response["message"] = "Could not update order status";
        echo json_encode($response);
    } else {
        $response["status"] = 1;
        $response["message"] = "Claim Assigned Successfully";
        echo json_encode($response);
        
    }
}
