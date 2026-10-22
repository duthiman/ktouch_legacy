<?php

require_once __DIR__ . '/repo_boot.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$action = repo_text($_REQUEST['action'] ?? '');
repo_log($action, $user_id);

switch ($action) {
    case 'user_sales':
        $result = repo_service_user_sales($connection, repo_int($_REQUEST['user_id'] ?? 0), $_REQUEST['from'] ?? '', $_REQUEST['to'] ?? '', repo_int($_REQUEST['counter_id'] ?? $_REQUEST['counter'] ?? 0));
        repo_json($result['ok'], $result['message'], $result, $result['status'] ?? 200);
        break;
    case 'sale_items':
        $result = repo_service_sale_items($connection, repo_int($_REQUEST['sale_id'] ?? 0));
        repo_json($result['ok'], $result['message'], $result, $result['status'] ?? 200);
        break;

    case 'item_summary':
        $result = repo_service_item_summary($connection, repo_int($_REQUEST['item_id'] ?? $_REQUEST['item'] ?? 0), $_REQUEST['from'] ?? '', $_REQUEST['to'] ?? '');
        repo_json($result['ok'], $result['message'], $result, $result['status'] ?? 200);
        break;

    case 'counter_summary':
        $result = repo_service_counter_summary($connection, repo_int($_REQUEST['counter_id'] ?? $_REQUEST['counter'] ?? 0), $_REQUEST['from'] ?? '', $_REQUEST['to'] ?? '');
        repo_json($result['ok'], $result['message'], $result, $result['status'] ?? 200);
        break;

    case 'item_orders':
        $result = repo_service_item_orders($connection, repo_int($_REQUEST['user_id'] ?? 0), repo_int($_REQUEST['item_id'] ?? $_REQUEST['item'] ?? 0), $_REQUEST['from'] ?? '', $_REQUEST['to'] ?? '', repo_int($_REQUEST['counter_id'] ?? $_REQUEST['counter'] ?? 0));
        repo_json($result['ok'], $result['message'], $result, $result['status'] ?? 200);
        break;

    case 'user_returns':
        $result = repo_service_user_returns($connection, repo_int($_REQUEST['user_id'] ?? 0), $_REQUEST['from'] ?? '', $_REQUEST['to'] ?? '');
        repo_json($result['ok'], $result['message'], $result, $result['status'] ?? 200);
        break;
    default:
        repo_json(false, 'Unknown reports API action.', ['action'=>$action], 404);
}
