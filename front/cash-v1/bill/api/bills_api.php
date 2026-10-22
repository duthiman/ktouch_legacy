<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include('../../../../inc/connection.php');
require_once __DIR__ . '/bills_helpers.php';
require_once __DIR__ . '/bills_schema.php';
require_once __DIR__ . '/bills_logger.php';
require_once __DIR__ . '/bills_repository.php';
require_once __DIR__ . '/bills_service.php';

bills_schema_boot($connection);

$action = trim((string)($_REQUEST['action'] ?? ''));
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

bills_log_event('bills_api_request', [
    'action' => $action,
    'user_id' => $user_id,
]);

switch ($action) {
    case '':
    case 'health':
        bills_json([
            'ok' => true,
            'message' => 'Bills API is ready.',
            'data' => [
                'user_id' => $user_id,
                'next_step' => 'Add bills actions here later.',
            ],
        ]);
        break;

    case 'get_unpaid_users':
    case 'users_unpaid_list':
        $shift = trim((string)($_REQUEST['shift'] ?? ''));
        $result = bills_service_get_unpaid_users($connection, $user_id, $shift);
        bills_json([
            'ok' => true,
            'message' => $result['message'],
            'data' => [
                'shift' => $result['shift'],
                'summary' => $result['summary'],
                'users' => $result['users'],
                'unpaid_sales' => $result['unpaid_sales'],
            ],
        ]);
        break;

    case 'get_unpaid_user':
    case 'user_unpaid_bills':
        $shift = trim((string)($_REQUEST['shift'] ?? ''));
        $targetUserId = isset($_REQUEST['target_user_id']) ? (int)$_REQUEST['target_user_id'] : (int)($_REQUEST['user_target_id'] ?? 0);
        $result = bills_service_get_unpaid_user($connection, $user_id, $shift, $targetUserId);
        bills_json([
            'ok' => (bool)$result['ok'],
            'message' => $result['message'],
            'data' => [
                'shift' => $result['shift'],
                'user' => $result['user'],
                'sales' => $result['sales'],
            ],
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        bills_json([
            'ok' => false,
            'message' => 'Unknown action.',
            'data' => [
                'action' => $action,
            ],
        ], 400);
        break;
}