<?php
session_start();
include('../../inc/connection.php');

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
$user_id = $_REQUEST['user_id'];
$message_id = $_REQUEST['message_id'];
if (empty($user_id) or $user_id == 0) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else {
    $updates = [];
    if (splitMessageHasColumn($connection, 'assign_status')) {
        $updates[] = "assign_status = 1";
    }
    if (splitMessageHasColumn($connection, 'user_id')) {
        $updates[] = "user_id = claim_staff";
    }
    if (splitMessageHasColumn($connection, 'assign_by')) {
        $updates[] = "assign_by = '" . mysqli_real_escape_string($connection, $user_id) . "'";
    }
    if (splitMessageHasColumn($connection, 'approve_status')) {
        $updates[] = "approve_status = 1";
    }
    if (splitMessageHasColumn($connection, 'approve_user')) {
        $updates[] = "approve_user = '" . mysqli_real_escape_string($connection, $user_id) . "'";
    }
    if (splitMessageHasColumn($connection, 'approve_datime')) {
        $updates[] = "approve_datime = NOW()";
    }

    if (empty($updates)) {
        $response["status"] = 0;
        $response["message"] = "No supported approval columns found on kp_split_message";
        echo json_encode($response);
        exit;
    }

    $query_assign = "UPDATE kp_split_message SET " . implode(', ', $updates) . " WHERE id = '" . mysqli_real_escape_string($connection, $message_id) . "'";
    $update_message = mysqli_query($connection, $query_assign);

    if (!$update_message) {
        $response["status"] = 0;
        $response["message"] = "Could not update order status";
        echo json_encode($response);
    } else {
        $query_claim = "UPDATE message_claim SET app_status=1 WHERE msg_id='$message_id'";
        $update_claim = mysqli_query($connection, $query_claim) or die(mysqli_error($connection) . "Could not update Claim status");
        if ($update_claim) {
            $response["status"] = 1;
            $response["message"] = "Claim Assigned Successfully";
            echo json_encode($response);
        } else {
            $response["status"] = 0;
            $response["message"] = "Could not update order status";
            echo json_encode($response);
        }
    }
}
