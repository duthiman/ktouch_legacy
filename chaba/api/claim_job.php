<?php
require_once __DIR__ . '/_bootstrap.php';

$data = chaba_api_request_data();
$queueId = (int)($data['id'] ?? $data['queue_id'] ?? $_GET['id'] ?? $_GET['queue_id'] ?? 0);
if ($queueId < 1) {
    chaba_api_json(['ok' => false, 'message' => 'Invalid queue id'], 400);
}

mysqli_begin_transaction($connection);
$claimQ = mysqli_query(
    $connection,
    "SELECT queue_id FROM kp_print_queue WHERE queue_id = '$queueId' AND status = 0 LIMIT 1 FOR UPDATE"
);
if (!$claimQ || mysqli_num_rows($claimQ) < 1) {
    mysqli_rollback($connection);
    chaba_api_json(['ok' => false, 'message' => 'Job already claimed or processed']);
}

mysqli_query($connection, "UPDATE kp_print_queue SET status = 3 WHERE queue_id = '$queueId' AND status = 0");
$claimed = mysqli_affected_rows($connection) > 0;
if ($claimed) {
    mysqli_commit($connection);
} else {
    mysqli_rollback($connection);
}

chaba_api_json(['ok' => $claimed]);
