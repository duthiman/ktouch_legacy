<?php
require_once __DIR__ . '/_bootstrap.php';

$data = chaba_api_request_data();
$queueId = (int)($data['id'] ?? $data['queue_id'] ?? $_GET['id'] ?? $_GET['queue_id'] ?? 0);
$maxAttempts = max(1, (int)($data['max_attempts'] ?? $_GET['max_attempts'] ?? 5));
$message = trim((string)($data['message'] ?? $data['error'] ?? $_GET['message'] ?? $_GET['error'] ?? 'Print job failed'));
if ($queueId < 1) {
    chaba_api_json(['ok' => false, 'message' => 'Invalid queue id'], 400);
}

$escaped = mysqli_real_escape_string($connection, mb_substr($message, 0, 240));
mysqli_query(
    $connection,
    "UPDATE kp_print_queue
     SET status = IF(print_attempts + 1 >= '$maxAttempts', 2, 0),
         print_attempts = print_attempts + 1,
         last_error = '$escaped'
     WHERE queue_id = '$queueId' AND status IN (0,3)"
);

chaba_api_json(['ok' => true, 'message' => 'Job marked as failed']);
