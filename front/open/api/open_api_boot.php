<?php
// Shared bootstrap for the open-orders API.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$open_root = dirname(__DIR__, 3);
require_once $open_root . '/inc/connection.php';
require_once __DIR__ . '/open_schema.php';
require_once __DIR__ . '/open_repo.php';
require_once __DIR__ . '/open_service.php';

$schema_action = strtolower(trim((string)($_REQUEST['action'] ?? 'bootstrap')));
$read_only_modal_actions = [
    'continue_type',
    'bill_modal',
    'counter',
    'load_open_identifier_modal',
    'load_counter_print_modal',
];

// These actions are the hot path after a waiter selects a counter. The
// required tables are healed during bootstrap; repeating all CREATE/ALTER
// checks here makes submitting an otherwise small sale noticeably slower.
$hot_order_actions = [
    'open_order',
    'close_order',
    'continue.open.last',
    'continue.open.each',
    'continue.open.close',
];

// Modal reads do not mutate schema. Avoid running the expensive schema/index
// healing checks on every modal request; bootstrap and writes still heal fully.
if (!in_array($schema_action, $read_only_modal_actions, true)
    && !in_array($schema_action, $hot_order_actions, true)) {
    open_schema_boot($connection);
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // API handlers use the captured user id and do not mutate the PHP
    // session. Releasing the lock lets the submit/redirect finish without
    // waiting behind another request from the same waiter.
    session_write_close();
}

function open_api_response($ok, $message, $data = [], $status = 200)
{
    http_response_code((int)$status);
    echo json_encode([
        'ok' => (bool)$ok,
        'message' => (string)$message,
        'data' => is_array($data) ? $data : [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function open_api_user_id()
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function open_api_session()
{
    $user_id = open_api_user_id();
    return $user_id > 0
        ? ['ok' => true, 'user_id' => $user_id]
        : ['ok' => false, 'user_id' => 0, 'message' => 'Session expired. Please login again.'];
}
