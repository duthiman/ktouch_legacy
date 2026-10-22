<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php'); 

$date = $_REQUEST['date'];
if (empty($date)) {
    $date = date('Y-m-d');
}

$start_date =$date.' 0900';
$end_date   = date('Y-m-d', strtotime("+1 day", strtotime($date))).' 0900';
$user = array();
$query = "SELECT COUNT(id) AS cnt,user_id,SUM(amount) AS amt FROM split_message WHERE claim_status = 1 AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start_date."' AND '".$end_date."'  GROUP BY user_id"; 
$result = mysqli_query($connection, $query);
$claims_list = array();
while ($pow = mysqli_fetch_array($result)) {
    $user_id=$pow['user_id'];
    $user['user_id'] = $user_id;
    if ($user_id==0) {
        $username='NO NAME';
    }else{
        $username=get_column('kp_users','username',"user_id='$user_id'");
    }
    $user['total_amount'] = $pow['amt'];
    $user['claim_count'] = $pow['cnt'];
    $user['username'] = $username;
    $user['date'] = $date;
    $claims_list[] = $user;
}    

$response = array();
$response['claims'] = $claims_list;
$response["status"] = 1;
$response["message"] = "Claims Report";

echo json_encode($response);
