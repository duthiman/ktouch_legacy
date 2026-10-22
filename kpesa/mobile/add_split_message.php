<?php

// Step 1: Setup
require_once __DIR__ . '/../../inc/connection.php';
//$connection = connectidentitiDB();

function ensure_special_split_tables($connection, $development = 0) {
    static $ensured = false;
    if ($ensured) {
        return true;
    }

    $tables = [
        'kp_split_withdrawal' => "CREATE TABLE IF NOT EXISTS `kp_split_withdrawal` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `phone` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `amount` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `account` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `balance` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `cost` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `transaction_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'WITHDRAWAL',
            `till_name` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `mpesa_message` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `date` date NOT NULL,
            `time` time NOT NULL,
            `datime` datetime NOT NULL,
            `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `split_status` int NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        'kp_split_reversal' => "CREATE TABLE IF NOT EXISTS `kp_split_reversal` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `phone` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `reversed_transaction_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `amount` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `account` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `balance` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `cost` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `transaction_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'REVERSAL',
            `till_name` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `mpesa_message` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `date` date NOT NULL,
            `time` time NOT NULL,
            `datime` datetime NOT NULL,
            `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `split_status` int NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`),
            KEY `reversed_transaction_code` (`reversed_transaction_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    ];

    foreach ($tables as $table_name => $query) {
        if ($development) {
            logQuery($query);
        }

        if (!$connection->query($query)) {
            logError("Failed creating table {$table_name}: " . $connection->error, $development);
            return false;
        }
    }

    $ensured = true;
    return true;
}

function legacy_balance_table_exists($connection) {
    static $has_balance_table = null;
    if ($has_balance_table !== null) {
        return $has_balance_table;
    }

    $result = $connection->query("SHOW TABLES LIKE 'balance'");
    $has_balance_table = $result && $result->num_rows > 0;
    if ($result instanceof mysqli_result) {
        $result->close();
    }

    return $has_balance_table;
}

// Config
$development = 0;  // Set to 1 for development (enables query and error logs)

// Logging paths
$log_dir = '../../logs/splits/' . date('Y') . '/' . date('F') . '/' . date('d') . '/';
$log_file = $log_dir . 'log_' . date('Y-m-d') . '.json';

// Ensure log directory exists
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}

// Load or initialize log
$log_data = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];


// Step 2: Get and log input
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);
$request_id = uniqid('req_', true);

// Log incoming payload
$log_entry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_id' => $request_id,
    'payload' => $data,
    'raw_input' => $raw_input
];
$log_data[] = $log_entry;

// Save log immediately after receiving
file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));


// Step 3: Basic validation
$response = [];
$status = 0;
$message = "";
$has_balance_table = legacy_balance_table_exists($connection);

if (empty($data)) {
    $message = "Empty Payload";
    logError("Empty Payload", $development);
} else {
    $amount = $data['amount'] ?? "0";

    // Step 4: Amount check
    if (empty($amount) || $amount == "0") {
        $message = "Transaction skipped due to zero amount";
        logError($message, $development);
    } else {
        // Step 5: Split or Unsplit flow
        $split_status = $data['split_status'] ?? 0;
        $mpesa_message = cleanString($connection, $data['mpesa_message'] ?? '');

        if ($split_status == 1) {
            // --- Split message fields ---
            $name = cleanString($connection, $data['name'] ?? '');
            $phone = cleanString($connection, $data['phone'] ?? '');
            $code = cleanString($connection, $data['code'] ?? '');
            $account = cleanString($connection, $data['account'] ?? '');
            $balance = cleanString($connection, $data['balance'] ?? '');
            $cost = cleanString($connection, $data['cost'] ?? '');
            $date = cleanString($connection, $data['date'] ?? '');
            $rawTime = (string)($data['time'] ?? '00:00');
            $time = date('h:i A', strtotime($rawTime));
            $datime = date("Y-m-d H:i:s", strtotime($date . ' ' . $rawTime));
            $transaction_type = strtoupper(cleanString($connection, $data['transaction_type'] ?? ''));
            $reversed_transaction_code = cleanString($connection, $data['reversed_transaction_code'] ?? '');
            $till_name = cleanString($connection, $data['till_name'] ?? '');

            // Step 6: Insert split message
            if ($transaction_type === 'WITHDRAWAL' || $transaction_type === 'REVERSAL') {
                if (!ensure_special_split_tables($connection, $development)) {
                    $status = 0;
                    $message = "Error preparing transaction tables: " . $connection->error;
                    logError($message, $development);

                    $response = [
                        'request_id' => $request_id,
                        'status' => $status,
                        'message' => $message
                    ];

                    $log_data[] = [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'response' => $response
                    ];
                    file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));

                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }

                if ($transaction_type === 'WITHDRAWAL') {
                    $query1 = "INSERT IGNORE INTO kp_split_withdrawal(name, phone, code, amount, account, balance, cost, transaction_type, till_name, mpesa_message, date, time, datime, split_status)
                        VALUES ('$name', '$phone', '$code', '$amount', '$account', '$balance', '$cost', '$transaction_type', '$till_name', '$mpesa_message', '$date', '$time', '$datime', '1')";
                } else {
                    $query1 = "INSERT IGNORE INTO kp_split_reversal(name, phone, code, reversed_transaction_code, amount, account, balance, cost, transaction_type, till_name, mpesa_message, date, time, datime, split_status)
                        VALUES ('$name', '$phone', '$code', '$reversed_transaction_code', '$amount', '$account', '$balance', '$cost', '$transaction_type', '$till_name', '$mpesa_message', '$date', '$time', '$datime', '1')";
                }
            } else {
                $query1 = "INSERT IGNORE INTO kp_split_message(name, phone, code, amount, account, balance, cost, date, time, datime) 
                    VALUES ('$name', '$phone', '$code', '$amount', '$account', '$balance', '$cost', '$date', '$time', '$datime')";
            }

            $query2 = "INSERT INTO balance(id, balance) VALUES(1, '$balance') 
                ON DUPLICATE KEY UPDATE balance='$balance'";
            $query3 = "INSERT INTO mpesa_message(message, split_status) VALUES('$mpesa_message', '1')";

            if ($development) {
                logQuery($query1);
                logQuery($query2);
                logQuery($query3);
            }

            $insert_split = $connection->query($query1);
            if ($has_balance_table) {
                $connection->query($query2);
            }
            $insert_message = $connection->query($query3);

            if ($insert_split && $insert_message) {
                $status = 1;
                $message = "Split payment details recorded.";
            } else {
                $status = 0;
                $message = "Error recording split message: " . $connection->error;
                logError($message, $development);
            }

        } else {
            // Step 7: Handle unsplit
            $query_unsplit = "INSERT INTO mpesa_message(message, split_status) VALUES('$mpesa_message', '0')";

            if ($development) {
                logQuery($query_unsplit);
            }

            $insert_unsplit = $connection->query($query_unsplit);

            if ($insert_unsplit) {
                $status = 1;
                $message = "Unsplit message recorded.";
            } else {
                $status = 0;
                $message = "Error recording unsplit message: " . $connection->error;
                logError($message, $development);
            }
        }
    }
}


// Step 8: Final response & log
$response = [
    'request_id' => $request_id,
    'status' => $status,
    'message' => $message
];

// Log response
$log_data[] = [
    'timestamp' => date('Y-m-d H:i:s'),
    'response' => $response
];
file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));

// Output response
header('Content-Type: application/json');
echo json_encode($response);


// ===== Helper Functions =====

function cleanString($conn, $value) {
    return $conn->real_escape_string(str_replace("'", '', trim($value)));
}

function logError($error_message, $enabled) {
    if (!$enabled) return;

    $path = '../../logs/errors/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
    if (!file_exists($path)) mkdir($path, 0777, true);
    $file = $path . 'error_log_' . date('Y-m-d') . '.json';

    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $data[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error_message' => $error_message
    ];
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function logQuery($query) {
    $path = '../../logs/queries/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
    if (!file_exists($path)) mkdir($path, 0777, true);
    $file = $path . 'query_log_' . date('Y-m-d') . '.json';

    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $data[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'query' => $query
    ];
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}
