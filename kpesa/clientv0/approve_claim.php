<?php
session_start();
include('../../inc/connection.php');

$user_id = $_REQUEST['user_id'];
$message_id = $_REQUEST['message_id'];
if (empty($user_id) or $user_id == 0) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else {
    $query_assign = "UPDATE split_message SET assign_status = 1, user_id = claim_staff, assign_by = '$user_id' WHERE id = '$message_id'";
    $update_message = mysqli_query($connection, $query_assign);

    if (!$update_message) {
        $response["status"] = 0;
        $response["message"] = "Could not update order status";
        echo json_encode($response);
    } else {
        $query_claim = "UPDATE message_claim SET app_status=1 WHERE msg_id='$message_id'";
        $update_claim = mysqli_query($connection, $query_claim) or die(mysqli_error($connection) . "Could not update Claim status");
        if ($update_claim) {
            $response["status"] = 1;
            $response["message"] = "Claim Assigned Successfully";
            echo json_encode($response);
        } else {
            $response["status"] = 0;
            $response["message"] = "Could not update order status";
            echo json_encode($response);
        }
    }
}
