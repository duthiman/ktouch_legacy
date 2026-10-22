<?php

session_start();

include('../../../inc/connection.php');
include('../../../inc/gen_funcs.php');

require_once __DIR__ . '/lpo_helpers.php';
require_once __DIR__ . '/lpo_schema.php';
require_once __DIR__ . '/lpo_repo.php';
require_once __DIR__ . '/lpo_service.php';

lpo_api_ensure_schema($connection);

$action = isset($_REQUEST['action']) ? strtolower(trim((string)$_REQUEST['action'])) : '';

if ($action === 'dashboard') {
    header('Content-Type: application/json');

    $session = lpo_api_check_session();
    if (empty($session['ok'])) {
        echo json_encode([
            'success' => false,
            'message' => $session['message'],
        ]);
        exit;
    }

    echo json_encode(lpo_service_get_dashboard_data());
    exit;
}

if ($action === 'init_data') {
    header('Content-Type: application/json');
    $session = lpo_api_check_session();
    if (empty($session['ok'])) {
        echo json_encode(['success' => false, 'message' => $session['message']]);
        exit;
    }
    echo json_encode([
        'success' => true,
        'message' => 'Purchase initialization data loaded.',
        'held_orders' => lpo_repo_init_held_orders()
    ]);
    exit;
}

if ($action === 'initialize' || $action === 'continue') {
    header('Content-Type: application/json');
    $session = lpo_api_check_session();
    if (empty($session['ok'])) {
        echo json_encode(['success' => false, 'message' => $session['message']]);
        exit;
    }
    if ($action === 'continue') {
        $result = lpo_service_continue($_REQUEST['po_id'] ?? 0, $_REQUEST['sup_id'] ?? 0);
    } else {
        $result = lpo_service_initialize($session['user_id'], $_REQUEST['sup_id'] ?? 0, $_REQUEST['type'] ?? 2, $_REQUEST['poby'] ?? 5);
    }
    if (!empty($result['ok'])) {
        if (function_exists('trans_thesion')) trans_thesion((int)$result['po_id'], 'PURCHASE');
        echo json_encode(['success' => true, 'message' => 'Purchase order ready.', 'po_id' => (int)$result['po_id'], 'continued' => !empty($result['continued'])]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Could not initialize purchase order.']);
    }
    exit;
}
