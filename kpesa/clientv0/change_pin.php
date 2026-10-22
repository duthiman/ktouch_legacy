<?php
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
$user_id = $_REQUEST['user_id'];
if (empty($user_id)) {
  $response["status"] = 0;
  $response["message"] = "User ID missing";
  echo json_encode($response);
} else {
  $user_id = $_REQUEST['user_id'];
  $pin = $_REQUEST['pin'];
  $change_pin = mysqli_query($connection, "UPDATE kp_users SET cabi = '$pin' WHERE user_id = '$user_id'");
  if ($change_pin) {
    $response["status"] = 1;
    $response["message"] = "PIN changed successfully";
    echo json_encode($response);
  } else {
    $response["status"] = 0;
    $response["message"] = "A database error occured. Please contact support";
    echo json_encode($response);
  }
}
