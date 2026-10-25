<?php
session_start();
include_once('../../inc/connection.php');

$operation_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_GET['operation_id'] ?? $_POST['operation_id'] ?? ''));
header('Content-Type: application/json');
if ($operation_id === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Missing close operation id.']);
    exit;
}

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_close_progress (operation_id varchar(80) NOT NULL, step_no tinyint unsigned NOT NULL DEFAULT 1, state varchar(16) NOT NULL DEFAULT 'active', message varchar(255) NOT NULL DEFAULT '', updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (operation_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$id = mysqli_real_escape_string($connection, $operation_id);
$result = mysqli_query($connection, "SELECT operation_id, step_no, state, message, updated_at FROM kp_counter_close_progress WHERE operation_id='$id' LIMIT 1");
$row = $result ? mysqli_fetch_assoc($result) : null;
if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Close progress is not available yet.']);
    exit;
}
echo json_encode(['ok' => true, 'data' => $row]);
