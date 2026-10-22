<?php
session_start();
include('../../inc/connection.php');

$query = "SELECT * FROM split_message WHERE claim_status=1 AND assign_status=2 ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) DESC LIMIT 150";

$result = mysqli_query($connection, $query);
$no = 0;
$return_arr = array();
while ($pow = mysqli_fetch_array($result)) {
   $id = $pow['id'];
   $code = $pow['code'];
   $name = $pow['name'];
   $amount = $pow['amount'];
   $time = $pow['time'];
   $date = $pow['date'];
   $claim_staff_id = $pow['claim_staff'];

   $query_username = "SELECT username FROM kp_users WHERE user_id='$claim_staff_id' LIMIT 1";
   $query_result = mysqli_query($connection, $query_username);

   $row = mysqli_fetch_assoc($query_result);
   $username = $row['username'];

   $return_arr[] = array(
      "id" => $id,
      "name" => $name,
      "code" => $code,
      "amount" => $amount,
      "time" => $time,
      "date" => $date,
      "claim_staff_id" => $claim_staff_id,
      "claim_staff_username" => $username,
   );
}
$response = array();
$response['messages'] = $return_arr;
$response["status"] = 1;
$response["message"] = "Claimed Messages";

echo json_encode($response);
