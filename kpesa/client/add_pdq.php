<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

// Constants
const STATUS_SUCCESS = 1;
const STATUS_FAILURE = 0;
const LOG_BASE_DIR = __DIR__ . '/../../logs';

// Log setup
function getLogFilePath() {
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $logDir = LOG_BASE_DIR . "/$year/$month/$day";
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    return "$logDir/pdq_add_errors.log";
}

function logError($message) {
    $logFile = getLogFilePath();
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] ERROR: $message\n", FILE_APPEND | LOCK_EX);
}

// Initialize response
$response = ["status" => STATUS_FAILURE, "message" => "PDQ submission failed"];

// Collect and validate inputs
$user_id = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : null;
$ref = isset($_REQUEST['ref_number']) ? trim($_REQUEST['ref_number']) : null;
$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : null;
$terminal_id = isset($_REQUEST['terminal_id']) ? (int)$_REQUEST['terminal_id'] : 0;
$terminal_code = isset($_REQUEST['terminal_code']) ? trim($_REQUEST['terminal_code']) : '';
$datime = todatime();

if (!$user_id || !is_numeric($user_id)) {
    $response["message"] = "User ID missing or invalid. Please log out and log in again.";
    echo json_encode($response);
    exit;
}
if (empty($ref)) {
    $response["message"] = "Reference number is missing. Please add the reference.";
    echo json_encode($response);
    exit;
}
if (empty($amount) || !is_numeric($amount)) {
    $response["message"] = "Amount is missing or invalid.";
    echo json_encode($response);
    exit;
}

//$connection = connectidentitiDB();
if (check_duplicate($connection,'kp_user_pdq', 'refno', $ref)) {
    $response["message"] = "Reference already exists! Contact a supervisor to raise a dispute.";
    echo json_encode($response);
    exit;
}

try {
    if (!$connection) throw new Exception("Database connection failed: " . mysqli_connect_error());

    // Resolve terminal by code if terminal_id not provided by older app clients.
    if ($terminal_id <= 0 && $terminal_code !== '') {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_pdq_terminals (
            terminal_id INT NOT NULL AUTO_INCREMENT,
            bank_name VARCHAR(120) NOT NULL,
            card_label VARCHAR(120) NOT NULL,
            terminal_code VARCHAR(80) NOT NULL DEFAULT '',
            status INT NOT NULL DEFAULT 2,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (terminal_id),
            KEY idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $codeEsc = mysqli_real_escape_string($connection, $terminal_code);
        $tr = mysqli_fetch_assoc(mysqli_query($connection, "SELECT terminal_id FROM kp_pdq_terminals WHERE terminal_code='$codeEsc' AND status=2 LIMIT 1"));
        if ($tr) $terminal_id = (int)$tr['terminal_id'];
    }

    $query = "INSERT INTO kp_user_pdq (user_id, amount, refno, shift) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("idss", $user_id, $amount, $ref, $datime);

    if ($stmt->execute()) {
        $pdq_id = (int)$stmt->insert_id;

        if ($terminal_id > 0) {
            mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_user_pdq_terminals_map (
                id INT NOT NULL AUTO_INCREMENT,
                pdq_id INT NOT NULL,
                terminal_id INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_pdq (pdq_id),
                KEY idx_terminal (terminal_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $mapStmt = $connection->prepare("INSERT INTO kp_user_pdq_terminals_map (pdq_id, terminal_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE terminal_id=VALUES(terminal_id)");
            $mapStmt->bind_param('ii', $pdq_id, $terminal_id);
            $mapStmt->execute();
            $mapStmt->close();
        }

        $response["status"] = STATUS_SUCCESS;
        $response["message"] = "PDQ added successfully.";
    } else {
        throw new Exception("Insert failed: " . $stmt->error);
    }

    $stmt->close();
    $connection->close();
} catch (Exception $e) {
    logError("User ID: $user_id - " . $e->getMessage());
    $response["message"] = "An error occurred while saving PDQ. Please contact support.";
}

echo json_encode($response);
