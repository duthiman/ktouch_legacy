<?php
include('../../inc/connection.php'); 


//$connection = connectidentitiDB();

$username = $_REQUEST['username'];
if (empty($username)) {
  $response["status"] = 0;
  $response["message"] = "Username missing";
  echo json_encode($response);
} else {
  $username = $_REQUEST['username'];
  $pin = $_REQUEST['pin'];
  $role_id = $_REQUEST['role_id'];
  $add_user = mysqli_query($connection, "INSERT INTO kp_users(username, cabi,role_id) VALUES('$username','$pin', '$role_id')");
  if ($add_user) {
    $response["status"] = 1;
    $response["message"] = "New staff added successfully";
    echo json_encode($response);
  } else {
    $response["status"] = 0;
    $response["message"] = "A database error occured. Please contact support";
    echo json_encode($response);
  }
}
