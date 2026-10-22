<?php
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
$user_id = $_REQUEST['user_id'];
$ref = $_REQUEST['ref_number'];
$amount = $_REQUEST['amount'];
$datime=todatime();
if (empty($user_id)) {
  $response["status"] = 0;
  $response["message"] = "User id missing!! Logou and loggin again";
  echo json_encode($response);
} elseif (empty($ref)) {
  $response["status"] = 0;
  $response["message"] = "Ref missing!! Make seure you add the ref";
  echo json_encode($response);
} elseif (empty($amount)) {
  $response["status"] = 0;
  $response["message"] = "Amount missing!! Make seure you add the ref";
  echo json_encode($response);
} elseif (check_duplicate('kp_user_pdq','refno',$ref)) {
  $response["status"] = 0;
  $response["message"] = "Ref already in the system!! cONTACT MANAGER OR SUPERVISOR TO RAISE DISPUTE";
  echo json_encode($response);
} else  {
  $username = $_REQUEST['username'];
  $pin = $_REQUEST['pin'];
  $role_id = $_REQUEST['role_id'];
  $add_user = mysqli_query($connection, "INSERT INTO kp_user_pdq(user_id,amount,refno,shift) VALUES('$user_id','$amount','$ref','$datime')");
  if ($add_user) {
    $response["status"] = 1;
    $response["message"] = "PDQ added successfully";
    echo json_encode($response);
  } else {
    $response["status"] = 0;
    $response["message"] = "A database error occured. Please contact support";
    echo json_encode($response);
  }
}

