<?php
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
$username = $_REQUEST['username'];
if (empty($username)) {
  $response["status"] = 0;
  $response["message"] = "Username missing";
  echo json_encode($response);
} else {
  $user_id = $_REQUEST['user_id'];
  $username = $_REQUEST['username'];
  $edit_user = mysqli_query($connection, "UPDATE kp_users SET username = '$username' WHERE user_id = '$user_id'");
  if ($edit_user) {
    $response["status"] = 1;
    $response["message"] = $username . " edited successfully";
    echo json_encode($response);
  } else {
    $response["status"] = 0;
    $response["message"] = "A database error occured. Please contact support";
    echo json_encode($response);
  }
}
