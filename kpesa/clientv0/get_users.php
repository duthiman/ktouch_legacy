<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
//$connection = connectidentitiDB();
$query_users = "SELECT user_id, username, work_status FROM kp_users WHERE role_id=2 ORDER BY username ASC";
$user_result = mysqli_query($connection, $query_users);

if ($query_users) {
    $users_list = array();
    while ($user = mysqli_fetch_array($user_result)) {
        $users_list[] = array("user_id" => $user['user_id'], "username" => $user['username'], "work_status" => $user['work_status']);
    }

    $response = array();
    $response['users'] = $users_list;
    $response["status"] = 1;
    $response["message"] = "Users List";

    echo json_encode($response);
} else {
    $response = array();
    $response["status"] = 2;
    $response["message"] = "A database error occured. Please contact support";

    echo json_encode($response);
}
