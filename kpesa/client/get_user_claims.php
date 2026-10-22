<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

function splitMessageHasDatime($connection) {
    static $hasDatime = null;
    if ($hasDatime !== null) {
        return $hasDatime;
    }

    $result = $connection->query("SHOW COLUMNS FROM kp_split_message LIKE 'datime'");
    $hasDatime = $result && $result->num_rows > 0;
    if ($result instanceof mysqli_result) {
        $result->close();
    }

    return $hasDatime;
}

function splitMessageHasColumn($connection, $column) {
    static $columns = [];
    if (array_key_exists($column, $columns)) {
        return $columns[$column];
    }

    $result = $connection->query("SHOW COLUMNS FROM kp_split_message LIKE '" . $connection->real_escape_string($column) . "'");
    $columns[$column] = $result && $result->num_rows > 0;
    if ($result instanceof mysqli_result) {
        $result->close();
    }

    return $columns[$column];
}

//Claim status from assign_status column in split messages table
// 1 - Approved
// 2 - Default - pending approval
// 3 - 
//$connection = connectidentitiDB();
if (!$connection) {
    $response['message'] = 'Database connection failed';
    logError('Database connection failed: ' . mysqli_connect_error());
    echo json_encode($response);
    exit;
}
$user_id = $_REQUEST['user_id'];
if (empty($user_id)) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else {
    $date = $_REQUEST['date'] ?? '';
    if (empty($date)) {
        $date = date('Y-m-d');
    }

    $start_date =$date.' 0900';
$end_date   = date('Y-m-d', strtotime("+1 day", strtotime($date))).' 0900';

    $claims_list = array();

    $filterField = splitMessageHasDatime($connection)
        ? 'datime'
        : "concat(date,' ',str_to_date(time,'%l:%i %p'))";
    $hasAssignStatus = splitMessageHasColumn($connection, 'assign_status');
    $query = "SELECT * FROM kp_split_message WHERE claim_status = 1 AND claim_staff = '$user_id' AND $filterField BETWEEN '".$start_date."' AND '".$end_date."' ORDER BY $filterField LIMIT 150";

    $result = mysqli_query($connection, $query);
    $claims = array();
    if (mysqli_num_rows($result) > 0) {
        $user['claim_count'] = mysqli_num_rows($result);
        while ($pow = mysqli_fetch_array($result)) {
            $code = $pow['code'];
            $assign_status = $hasAssignStatus ? ($pow['assign_status'] ?? 2) : 2;
            $amount = $pow['amount'];
            $time = $pow['time'] ?? date('h:i A', strtotime((string)($pow['datime'] ?? 'now')));
            $date = $pow['date'] ?? date('Y-m-d', strtotime((string)($pow['datime'] ?? 'now')));

            $claims[] = array("user_id" => $user_id, "code" => $code, "amount" => $amount, "time" => $time, "date" => $date, "assign_status" => $assign_status);
        }
    }


    $response = array();
    $response['claims'] = $claims;
    $response["status"] = 1;
    $response["message"] = "Claims Report";

    echo json_encode($response);
}
