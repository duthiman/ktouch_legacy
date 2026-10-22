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
    return "$logDir/approved_messages_errors.log";
}

// Function to log errors to a file
function logError($message) {
    $logFile = getLogFilePath();
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Initialize response array
$response = ['status' => STATUS_FAILURE, 'message' => 'Failed to fetch approved messages', 'messages' => []];

// Validate inputs
$user_id = isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : null;
if (!$user_id) {
    $response['message'] = 'Invalid or missing user ID';
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
// Get start datetime
$start = last_day($connection);
if (!$start) {
    $response['message'] = 'Invalid start date';
    logError('Invalid start date returned by last_day()');
    echo json_encode($response);
    exit;
}

// Get current datetime (equivalent to NOW() in SQL)
$end = date('Y-m-d H:i:s');


try {
    // Prepare the query using datime for the date range
    $query = "SELECT code, name, amount, time, date 
              FROM kp_split_message 
              WHERE claim_status = 1 
              AND approve_status = 1
              AND claim_staff = ? 
              AND datime BETWEEN ? AND ? 
              ORDER BY datime DESC";

    $stmt = $connection->prepare($query);
    $stmt->bind_param('iss', $user_id, $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'code' => $row['code'],
            'name' => $row['name'],
            'amount' => $row['amount'],
            'time' => $row['time'],
            'date' => $row['date']
        ];
    }

    $stmt->close();

    // Success response
    $response['status'] = STATUS_SUCCESS;
    $response['message'] = 'Approved messages fetched successfully';
    $response['messages'] = $messages;

} catch (Exception $e) {
    $error_message = $e->getMessage();
    logError("User ID: $user_id - Query failed: $error_message");
    $response['message'] = 'Error fetching approved messages. Contact support.';
} finally {
    $connection->close();
    echo json_encode($response);
}
?>