<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

// Show errors during development (comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$connection = connectidentitiDB();

// Constants
const STATUS_SUCCESS = 1;
const STATUS_FAILURE = 0;
const LOG_BASE_DIR = __DIR__ . '/../../logs';

// Function to get the log file path
function getLogFilePath() {
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $logDir = LOG_BASE_DIR . "/$year/$month/$day";
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    return "$logDir/pdq_fetch_errors.log";
}

// Function to log errors
function logError($message) {
    $logFile = getLogFilePath();
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] ERROR: $message\n", FILE_APPEND | LOCK_EX);
}
 

// Initialize response
$response = ['status' => STATUS_FAILURE, 'message' => 'Failed to fetch PDQ records', 'messages' => []];

// Check DB connection
if (!$connection) {
    $response['message'] = 'Database connection failed';
    logError('Database connection failed: ' . mysqli_connect_error());
    echo json_encode($response);
    exit;
}

try {
    $query = "SELECT p.id, p.refno, p.amount, p.shift, p.user_id,
                     COALESCE(t.bank_name,'Unassigned') AS bank_name,
                     COALESCE(t.card_label,'Unknown Card') AS card_label,
                     COALESCE(t.terminal_code,'') AS terminal_code
              FROM kp_user_pdq p
              LEFT JOIN kp_user_pdq_terminals_map pm ON pm.pdq_id = p.id
              LEFT JOIN kp_pdq_terminals t ON t.terminal_id = pm.terminal_id
              WHERE p.approve_status = 2
              ORDER BY p.id ASC LIMIT 150";
    $result = $connection->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $connection->error);
    }

    $return_arr = [];

    while ($row = $result->fetch_assoc()) {
        $claim_user = $row['user_id'];
        logError("Processing PDQ ID: " . $row['id'] . " | user_id: " . $claim_user); // For debugging

        // Safely fetch username
        $username = get_string($connection, "kp_users", "username", "user_id = '$claim_user'");
        if ($username === null) {
            logError("Username not found for user_id: $claim_user");
        }

        $return_arr[] = [
            "id" => $row['id'],
            "ref" => $row['refno'],
            "amount" => $row['amount'], 
            "date" => $row['shift'],
            "bank_name" => $row['bank_name'],
            "card_label" => $row['card_label'],
            "terminal_code" => $row['terminal_code'],
            "claim_staff_id" => $claim_user,
            "claim_staff_username" => $username ?: "Unknown",
        ];
    }

    $response['status'] = STATUS_SUCCESS;
    $response['message'] = "Claimed PDQ Transactions fetched successfully";
    $response['messages'] = $return_arr;

} catch (Exception $e) {
    logError("Fetch error: " . $e->getMessage());
    logError("Line: " . $e->getLine());
    $response['message'] = "An error occurred. Contact support.";
}

$connection->close();
echo json_encode($response);
