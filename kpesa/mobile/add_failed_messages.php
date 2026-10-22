<?php

// Step 1: Setup
require_once __DIR__ . '/../../inc/connection.php';

header('Content-Type: application/json; charset=utf-8');

//$connection = connectidentitiDB();
$log_queries = 0; // Set to 1 to enable query logging for debugging

// Helper: Write structured log entry
function write_log($type, $entry) {
    $base_dir = '../../logs/' . $type . '/' . date('Y') . '/' . date('F') . '/' . date('d') . '/';
    if (!file_exists($base_dir)) {
        mkdir($base_dir, 0777, true);
    }

    $log_file = $base_dir . 'log_' . date('Y-m-d') . '.json';
    $log_data = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];
    if (!is_array($log_data)) {
        $log_data = [];
    }
    $log_data[] = $entry;
    file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));
}

function write_trace_log($entry) {
    write_log('trace', $entry);
}

// Helper: Sanitize input safely
function sanitize($conn, $value) {
    return $conn->real_escape_string(trim(str_replace("'", '', $value ?? '')));
}

function split_message_has_datime($connection) {
    static $has_datime = null;
    if ($has_datime !== null) {
        return $has_datime;
    }

    $result = $connection->query("SHOW COLUMNS FROM kp_split_message LIKE 'datime'");
    $has_datime = $result && $result->num_rows > 0;
    if ($result instanceof mysqli_result) {
        $result->close();
    }

    return $has_datime;
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

// Step 2: Read and decode payload
$raw_input = file_get_contents('php://input');
$array = json_decode($raw_input, true);

$response = [];
$status = 0;
$messages = [];
$request_id = uniqid('req_', true);
$has_datime = split_message_has_datime($connection);
$has_balance_table = legacy_balance_table_exists($connection);

write_trace_log([
    'timestamp' => date('Y-m-d H:i:s'),
    'request_id' => $request_id,
    'endpoint' => 'kpesa/mobile/add_failed_messages.php',
    'raw_input' => $raw_input,
    'decoded_request' => $array
]);

if (empty($array) || !isset($array['list']) || !is_array($array['list'])) {
    $messages[] = 'Empty or invalid payload';
    write_log('fail', [
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => $request_id,
        'error' => 'Invalid or missing JSON payload',
        'raw_input' => $raw_input
    ]);
} else {
    $list = $array['list'];
    $any_success = false;

    foreach ($list as $index => $data) {
        $amount = $data['amount'] ?? '0';

        // Skip entries with empty or zero amount
        if (empty($amount) || $amount == "0") {
            continue;
        }

        $split_status = $data['split_status'] ?? 0;
        $mpesa_message = sanitize($connection, $data['mpesa_message'] ?? '');
        $code = sanitize($connection, $data['code'] ?? '');

         

        // Process split message
        if ($split_status == 1) {
            $name = sanitize($connection, $data['name'] ?? '');
            $phone = sanitize($connection, $data['phone'] ?? '');
            $account = sanitize($connection, $data['account'] ?? '');
            $balance = sanitize($connection, $data['balance'] ?? '');
            $cost = sanitize($connection, $data['cost'] ?? '');
            $date = sanitize($connection, $data['date'] ?? '');
            $rawTime = (string)($data['time'] ?? '');
            $time = date('h:i A', strtotime($rawTime));
            $time = sanitize($connection, $time);
            $datime = date("Y-m-d H:i:s", strtotime($date . ' ' . $rawTime));

            if ($has_datime) {
                $insert_split = $connection->query("INSERT IGNORE INTO kp_split_message(name, phone, code, amount, account, balance, cost, date, time, datime)
                    VALUES ('$name', '$phone', '$code', '$amount', '$account', '$balance', '$cost', '$date', '$time', '$datime')");
            } else {
                $insert_split = $connection->query("INSERT IGNORE INTO kp_split_message(name, phone, code, amount, account, balance, cost, date, time)
                    VALUES ('$name', '$phone', '$code', '$amount', '$account', '$balance', '$cost', '$date', '$time')");
            }

            if ($log_queries) error_log("Insert Split: " . $connection->error);

            if ($has_balance_table) {
                try {
                    $connection->query("INSERT INTO balance(id, balance) VALUES (1, '$balance') ON DUPLICATE KEY UPDATE balance='$balance'");
                } catch (Throwable $e) {
                    $messages[] = "[$index] Balance sync skipped.";
                    write_log('fail', [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'request_id' => $request_id,
                        'warning' => 'Legacy balance table update failed',
                        'error' => $e->getMessage(),
                        'data' => $data
                    ]);
                }
            }

            $insert_message = $connection->query("INSERT INTO mpesa_message(message, split_status) VALUES('$mpesa_message', '1')");

            if ($insert_message) {
                $messages[] = "[$index] Split message recorded.";
                $any_success = true;
                write_log('success', [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'request_id' => $request_id,
                    'type' => 'split',
                    'code' => $code,
                    'data' => $data
                ]);
            } else {
                $messages[] = "[$index] Failed to insert split message.";
                write_log('fail', [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'request_id' => $request_id,
                    'error' => $connection->error,
                    'data' => $data
                ]);
            }

        } else {
            // Process unsplit message
            $insert_message = $connection->query("INSERT INTO mpesa_message(message, split_status) VALUES('$mpesa_message', '0')");

            if ($insert_message) {
                $messages[] = "[$index] Unsplit message recorded.";
                $any_success = true;
                write_log('success', [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'request_id' => $request_id,
                    'type' => 'unsplit',
                    'data' => $data
                ]);
            } else {
                $messages[] = "[$index] Failed to insert unsplit message.";
                write_log('fail', [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'request_id' => $request_id,
                    'error' => $connection->error,
                    'data' => $data
                ]);
            }
        }
    }

    $status = $any_success ? 1 : 0;
}

// Step 3: Return response
$response = [
    'request_id' => $request_id,
    'status' => $status,
    'message' => implode(" | ", $messages)
];

// Log response
write_log($status ? 'success' : 'fail', [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_id' => $request_id,
    'response' => $response
]);

write_trace_log([
    'timestamp' => date('Y-m-d H:i:s'),
    'request_id' => $request_id,
    'endpoint' => 'kpesa/mobile/add_failed_messages.php',
    'response' => $response
]);

echo json_encode($response, JSON_UNESCAPED_SLASHES);
