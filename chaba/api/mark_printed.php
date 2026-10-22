<?php
require_once __DIR__ . '/_bootstrap.php';

$data = chaba_api_request_data();
$queueId = (int)($data['id'] ?? $data['queue_id'] ?? $_GET['id'] ?? $_GET['queue_id'] ?? 0);
if ($queueId < 1) {
    chaba_api_json(['ok' => false, 'message' => 'Invalid queue id'], 400);
}

mysqli_begin_transaction($connection);
$marked = mysqli_query(
    $connection,
    "UPDATE kp_print_queue
     SET status = 1, printed_at = NOW(), last_error = ''
     WHERE queue_id = '$queueId' AND status IN (0,3)"
);
if (!$marked || mysqli_affected_rows($connection) !== 1) {
    mysqli_rollback($connection);
    chaba_api_json(['ok' => false, 'message' => 'Printed job was not found or could not be marked'], 409);
}

$archive = mysqli_query(
    $connection,
    "INSERT INTO kp_print_queue_printed
     (queue_id,sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,created_at,shift,payload_json,print_attempts,last_error,printed_at,items_json)
     SELECT queue_id,sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,created_at,shift,payload_json,print_attempts,last_error,printed_at,items_json
     FROM kp_print_queue
     WHERE queue_id = '$queueId' AND status = 1"
);

if (!$archive || mysqli_affected_rows($connection) !== 1) {
    mysqli_rollback($connection);
    chaba_api_json(['ok' => false, 'message' => 'Printed job was not found or could not be archived'], 409);
}

$delete = mysqli_query($connection, "DELETE FROM kp_print_queue WHERE queue_id = '$queueId' AND status = 1");
if (!$delete || mysqli_affected_rows($connection) !== 1) {
    mysqli_rollback($connection);
    chaba_api_json(['ok' => false, 'message' => 'Printed job could not be removed from the live queue'], 500);
}

mysqli_commit($connection);
chaba_api_json(['ok' => true, 'message' => 'Job archived as printed']);
