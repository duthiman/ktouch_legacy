<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

$query = "SELECT * FROM kp_user_pdq WHERE app_status = 2 ORDER BY id ASC LIMIT 150";

$result = mysqli_query($connection, $query);
$return_arr = array();

while ($pow = mysqli_fetch_array($result)) {
   $id = $pow['id'];
   $ref = $pow['refno']; 
   $amount = $pow['amount'];
   $time = $pow['time'];
   $date = $pow['shift'];
   $claim_user = $pow['user_id']; 

   $query_username = "SELECT username FROM kp_users WHERE user_id='$claim_user' LIMIT 1";
   $query_result = mysqli_query($connection, $query_username);

   $row = mysqli_fetch_assoc($query_result);
   $username = $row['username'];

   $return_arr[] = array(
      "id" => $id,
      "ref" => $ref,
      "amount" => $amount,
      "time" => $time,
      "date" => $date,
      "claim_staff_id" => $claim_user,
      "claim_staff_username" => $username,
   );
}

$response = array();
$response['messages'] = $return_arr;
$response["status"] = 1;
$response["message"] = "Claimed PDQ Transaction";

echo json_encode($response);
