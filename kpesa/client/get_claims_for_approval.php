<?php
session_start();
include('../../inc/connection.php');

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

//$connection = connectidentitiDB();
if (!$connection) {
    $response['message'] = 'Database connection failed';
    logError('Database connection failed: ' . mysqli_connect_error());
    echo json_encode($response);
    exit;
}
$orderField = splitMessageHasDatime($connection)
    ? 'sm.datime'
    : "concat(sm.date,' ',str_to_date(sm.time,'%l:%i %p'))";
$hasAssignStatus = splitMessageHasColumn($connection, 'assign_status');
$query = "SELECT sm.*, u.username AS claim_staff_username
          FROM kp_split_message sm
          LEFT JOIN kp_users u ON u.user_id = sm.claim_staff
          WHERE sm.claim_status=1 AND sm.approve_status = 2
          ORDER BY $orderField DESC
          LIMIT 150";

$result = mysqli_query($connection, $query);
$no = 0;
$return_arr = array();
while ($pow = mysqli_fetch_array($result)) {
   $id = $pow['id'];
   $code = $pow['code'];
   $name = $pow['name'];
   $amount = $pow['amount'];
   $time = $pow['time'] ?? date('h:i A', strtotime((string)($pow['datime'] ?? 'now')));
   $date = $pow['date'] ?? date('Y-m-d', strtotime((string)($pow['datime'] ?? 'now')));
   $claim_staff_id = $pow['claim_staff'];
   $username = $pow['claim_staff_username'] ?? '';

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
