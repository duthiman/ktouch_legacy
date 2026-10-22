<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

//Claim status from assign_status column in split messages table
// 1 - Approved
// 2 - Default - pending approval
// 3 - 

$user_id = $_REQUEST['user_id'];
if (empty($user_id)) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else {
    $date = $_REQUEST['date'];
    if (empty($date)) {
        $date = date('Y-m-d');
    }

    $start_date =$date.' 0900';
$end_date   = date('Y-m-d', strtotime("+1 day", strtotime($date))).' 0900';

    $claims_list = array();

    $query = "SELECT  * FROM split_message WHERE claim_status = 1 AND claim_staff = '$user_id' AND concat(date,' ',str_to_date(time,'%l:%i %p')) BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) LIMIT 150";

    $result = mysqli_query($connection, $query);
    $claims = array();
    if (mysqli_num_rows($result) > 0) {
        $user['claim_count'] = mysqli_num_rows($result);
        while ($pow = mysqli_fetch_array($result)) {
            $code = $pow['code'];
            $assign_status = $pow['assign_status'];
            $amount = $pow['amount'];
            $time = $pow['time'];
            $date = $pow['date'];

            $claims[] = array("user_id" => $user_id, "code" => $code, "amount" => $amount, "time" => $time, "date" => $date, "assign_status" => $assign_status);
        }
    }


    $response = array();
    $response['claims'] = $claims;
    $response["status"] = 1;
    $response["message"] = "Claims Report";

    echo json_encode($response);
}
