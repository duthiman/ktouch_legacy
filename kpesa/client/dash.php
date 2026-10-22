<?php
session_start();
include('../../inc/connection.php');
require_once('../../inc/gen_funcs.php');

// Initialize response array
$response = array("status" => 0, "message" => "Failed to fetch dashboard details");

// Validate user input
if (!isset($_REQUEST['user_id']) || !is_numeric($_REQUEST['user_id'])) {
    $response["message"] = "Invalid or missing user ID";
    echo json_encode($response);
    exit;
}

$user_id = (int)$_REQUEST['user_id']; // Cast to integer to sanitize

// Connect to database
//$connection = connectidentitiDB();
if (!$connection) {
    $response["message"] = "Database connection failed";
    echo json_encode($response);
    exit;
}

// Define time range
$start_datetime = last_day($connection);
$current_datetime = todatime();

if (!$start_datetime || !$current_datetime) {
    $response["message"] = "Invalid date range";
    echo json_encode($response);
    exit;
}

// Use prepared statements for security
try {
    // Query 1: M-Pesa totals and counts for claimed transactions
    $stmt = $connection->prepare("SELECT SUM(amount) as total, COUNT(*) as msg_count 
                                  FROM kp_split_message 
                                  WHERE claim_staff = ? AND claim_status = 1 
                                  AND datime BETWEEN ? AND ?");
    $stmt->bind_param("iss", $user_id, $start_datetime, $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $response["mpesa_total"] = $row['total'] ?? 0;
    $response["message_count"] = $row['msg_count'] ?? 0;
    $stmt->close();

    // Query 2: Approved messages count
    $stmt = $connection->prepare("SELECT COUNT(*) as approved_count 
                                  FROM kp_split_message 
                                  WHERE approve_status = 1 
                                  AND datime BETWEEN ? AND ?");
    $stmt->bind_param("ss", $start_datetime, $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    $response["approved"] = $result->fetch_assoc()['approved_count'] ?? 0;
    $stmt->close();

    // Query 3: Claimed but not approved messages
    $stmt = $connection->prepare("SELECT COUNT(*) as claimed_not_approved 
                                  FROM kp_split_message 
                                  WHERE claim_staff = ? AND claim_status = 1 
                                  AND approve_status = 2 
                                  AND datime BETWEEN ? AND ?");
    $stmt->bind_param("iss", $user_id, $start_datetime, $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    $response["claimed_not_approved"] = $result->fetch_assoc()['claimed_not_approved'] ?? 0;
    $stmt->close();

    // Query 4: New messages (unclaimed)
    $stmt = $connection->prepare("SELECT COUNT(*) as new_msgs 
                                  FROM kp_split_message 
                                  WHERE claim_status = 2 AND approve_status = 2 
                                  AND datime BETWEEN ? AND ?");
    $stmt->bind_param("ss", $start_datetime, $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    $response["new_messages"] = $result->fetch_assoc()['new_msgs'] ?? 0;
    $stmt->close();

    // Query 5: PDQ counts and totals
    $stmt = $connection->prepare("SELECT COUNT(*) as pdq_count, SUM(amount) as pdq_total 
                                  FROM kp_user_pdq 
                                  WHERE user_id = ? 
                                  AND datime BETWEEN ? AND ?");
    $stmt->bind_param("iss", $user_id, $start_datetime, $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $response["pdq_count"] = $row['pdq_count'] ?? 0;
    $response["pdq_amount"] = $row['pdq_total'] ?? 0;
    $stmt->close();

    // Success response
    $response["status"] = 1;
    $response["message"] = "Dashboard details fetched successfully";

} catch (Exception $e) {
    $response["message"] = "Query failed: " . $e->getMessage();
}

echo json_encode($response);
$connection->close();
?>