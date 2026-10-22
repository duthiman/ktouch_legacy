<?php
session_start();
include('../../inc/connection.php');
require_once('../../inc/gen_funcs.php');  



//$connection = connectidentitiDB();

$response = array();
$user_id=$_REQUEST['user_id'] ;

$start=shift();//last_day($connection);
$end=date('Y-m-d h:ia');

$response["mpesa_total"] = column_total('split_message','amount',"claim_staff='$user_id' AND claim_status=1 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start."' AND NOW()");
$response["message_count"] = column_count('split_message','amount',"claim_staff='$user_id' AND claim_status=1 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start."' AND NOW()");

$response["approved"] = column_count('split_message','user_id',"assign_status=1 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start."' AND NOW()");

$response["claimed_not_approved"] = column_count('split_message','user_id',"claim_staff='$user_id' AND claim_status=1 AND assign_status=2 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start."' AND NOW()");

$response["new_messages"] = column_count('split_message','amount',"claim_status=2 AND  assign_status=2 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start."' AND NOW()"); 

$response["pdq_count"] = 0;//column_count('kp_user_pdq','id',"user_id='$user_id' AND  datime BETWEEN '".$start."' AND NOW()"); 
$response["pdq_amount"] = 0;//column_total('kp_user_pdq','amount',"user_id='$user_id' AND  datime BETWEEN '".$start."' AND NOW()");
$response["status"] = 1; 
$response["message"] = "Dashboard details"; 

 echo json_encode($response);

 
?> 