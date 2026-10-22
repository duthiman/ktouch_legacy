<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

//$connection = connectidentitiDB();
if (!$connection) {
    $response['message'] = 'Database connection failed';
    logError('Database connection failed: ' . mysqli_connect_error());
    echo json_encode($response);
    exit;
}

$query_username = "SELECT user_id,username FROM kp_users WHERE 1";
$query_result = mysqli_query($connection,$query_username);
$no = 0;
while ($pow = mysqli_fetch_array($query_result)) {
   $id = $pow['user_id'];
   $pdq_count=column_count($connection,'kp_user_pdq','id',"clear_status=2 AND user_id='$id'");
   $pdq_total=column_total($connection,'kp_user_pdq','amount',"clear_status=2 AND user_id='$id'");
   $mpesa_count=column_count($connection,'kp_split_message','id',"clear_status=2 AND claim_staff='$id'");
   $mpesa_total=column_total($connection,'kp_split_message','amount',"clear_status=2 AND claim_staff='$id'");
   $username = $pow['username']; 
   $return_arr[] = array(
      "id" => $id,
      "name" => $username,
      "pdq_count" => $pdq_count,
      "pdq_amount" => $pdq_total,
      "mpesa_count" => $mpesa_count,
      "mpesa_total" => $mpesa_total, 
      "claim_staff_username" => $username,
   );
}
$response = array();
$response['messages'] = $return_arr;
$response["status"] = 1;
$response["message"] = "Claimed Messages";

echo json_encode($response);
