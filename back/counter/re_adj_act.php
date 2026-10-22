<?php
// Compatibility endpoint for the legacy back/counter close dialog.
// The actual close rules remain in the transactional counter service.
require_once __DIR__ . '/../../front/counterolder/api/cntr_boot.php';

$counter_id = (int)($_POST['cntrid'] ?? 0);
$session_user_id = (int)($_SESSION['user_id'] ?? 0);

if ($counter_id < 1) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid counter selected.']);
    exit;
}

if ($session_user_id < 1) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Your session has expired. Please sign in again.']);
    exit;
}

$assigned_counter = (int)get_column('kp_users', 'counter', "user_id='$session_user_id'");
if ($assigned_counter !== $counter_id) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'message' => 'This counter is assigned to a different cashier.']);
    exit;
}

$result = cntr_service_close_counter($connection, $session_user_id);
http_response_code((int)($result['status'] ?? ($result['ok'] ? 200 : 500)));
echo json_encode($result);
