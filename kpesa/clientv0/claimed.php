<?php
session_start();
include('../../inc/connection.php'); 
include('../../inc/gen_funcs.php'); 

$start = shift();//last_day();
$user_id = $_REQUEST['user_id'];
$query = "SELECT  * FROM split_message WHERE claim_status=1 AND claim_staff='$user_id' AND assign_status=2 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start."' AND NOW() ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) DESC ";

$result = mysqli_query($connection, $query);
$no = 0;
$return_arr = array();
while ($pow = mysqli_fetch_array($result)) {
   $code = $pow['code'];
   $name = $pow['name'];
   $amount = $pow['amount'];
   $time = $pow['time'];
   $date = $pow['date'];

   $return_arr[] = array("code" => $code,"name" => $name, "amount" => $amount, "time" => $time, "date" => $date);
}
$response = array();
$response['messages'] = $return_arr;
$response["status"] = 1;
$response["message"] = "Approved Messages";

echo json_encode($response);
