<?php
session_start();
include('../../inc/connection.php');

// Debug mode (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Constants
const STATUS_SUCCESS = 1;
const STATUS_FAILURE = 0;
const LOG_FILE = __DIR__ . '/../../logs/pdq_assignment_errors.log';

// Helper: Log error to file
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$timestamp] ERROR: $message\n", FILE_APPEND | LOCK_EX);
}

// Fallback for datime if not defined
if (!function_exists('todatime')) {
    function todatime() {
        return date('Y-m-d H:i:s');
    }
}

// Initialize response
$response = ['status' => STATUS_FAILURE, 'message' => ''];

// Get inputs
$user_id = $_REQUEST['user_id'] ?? 0;
$message_id = $_REQUEST['id'] ?? 0;
$datime = todatime();

// Validate input
if (empty($user_id) || $user_id == 0) {
    $response['message'] = "User ID missing";
    echo json_encode($response);
    exit;
}

if (empty($message_id) || $message_id == 0) {
    $response['message'] = "Message ID missing";
    echo json_encode($response);
    exit;
}

// Database connection
//$connection = connectidentitiDB();
if (!$connection) {
    $response['message'] = "Database connection failed";
    logError("DB connection failed: " . mysqli_connect_error());
    echo json_encode($response);
    exit;
}

try {
    // Prepare update
    $stmt = $connection->prepare("
        UPDATE kp_user_pdq 
        SET approve_status = 1, approve_user = ?, approve_datime = ? 
        WHERE id = ?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("isi", $user_id, $datime, $message_id);

    if ($stmt->execute()) {
        $response['status'] = STATUS_SUCCESS;
        $response['message'] = "Claim assigned successfully.";
    } else {
        $response['message'] = "Failed to update order status.";
        logError("Execution failed for ID $message_id | User $user_id: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = "An error occurred. Contact support.";
    logError("Exception: " . $e->getMessage());
}

$connection->close();
echo json_encode($response);
