<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

// Constants for status codes
const STATUS_SUCCESS = 1;
const STATUS_FAILURE = 0;

// Base log directory
const LOG_BASE_DIR = __DIR__ . '/../../logs';

// Function to set up log directory and file path
function getLogFilePath() {
    $year = date('Y');  // e.g., 2025
    $month = date('m'); // e.g., 04
    $day = date('d');   // e.g., 14

    // Construct the directory path: logs/YYYY/MM/DD
    $logDir = LOG_BASE_DIR . "/$year/$month/$day";

    // Create directories if they don't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true); // Recursive directory creation
    }

    // Full path to the log file
    return "$logDir/claim_message_errors.log";
}

// Function to log errors to a file
function logError($message) {
    $logFile = getLogFilePath();
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Initialize response array
$response = ['status' => STATUS_FAILURE, 'message' => 'Failed to claim message'];

// Validate inputs
$user_id = isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : null;
$msg_id = isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) && $_REQUEST['id'] != 0 ? (int)$_REQUEST['id'] : null;

if (!$user_id) {
    $response['message'] = 'Invalid or missing user ID';
    echo json_encode($response);
    exit;
}

if (!$msg_id) {
    $response['message'] = 'You have to select a message!';
    echo json_encode($response);
    exit;
}

// Connect to database
//$connection = connectidentitiDB();
if (!$connection) {
    $response['message'] = 'Database connection failed';
    logError('Database connection failed: ' . mysqli_connect_error());
    echo json_encode($response);
    exit;
}

// Use a transaction for atomic operations
mysqli_begin_transaction($connection);

try {
    // Insert into message_claim
    $stmt = $connection->prepare('INSERT INTO message_claim (user_id, msg_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $user_id, $msg_id);
    $stmt->execute();
    $stmt->close();

    // Update kp_split_message
    $stmt = $connection->prepare('UPDATE kp_split_message SET claim_status = 1, claim_staff = ? WHERE id = ?');
    $stmt->bind_param('ii', $user_id, $msg_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    // Check if the update affected any rows (i.e., the message ID exists)
    if ($affected_rows === 0) {
        throw new Exception('Message ID not found or already claimed');
    }

    // Commit the transaction
    mysqli_commit($connection);

    // Success response
    $response['status'] = STATUS_SUCCESS;
    $response['message'] = 'Message claimed successfully!';

} catch (Exception $e) {
    // Rollback transaction on failure
    mysqli_rollback($connection);

    // Log the detailed error
    $error_message = $e->getMessage();
    logError("User ID: $user_id, Message ID: $msg_id - $error_message");

    // Set user-friendly message
    $response['message'] = str_contains($error_message, 'Message ID not found')
        ? 'Message not found or already claimed. Ask admin to check.'
        : 'Error tracking claim. Contact cashier.';
} finally {
    $connection->close();
    echo json_encode($response);
}
?>