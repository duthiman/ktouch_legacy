<?php
session_start();

header('Content-Type: application/json');

include('../../../inc/connection.php');
include_once('../../../inc/gen_funcs.php');
require_once __DIR__ . '/take_schema.php';
require_once __DIR__ . '/../../counter/api/counter_inventory_schema.php';
require_once __DIR__ . '/take_helpers.php';
require_once __DIR__ . '/take_repository.php';
require_once __DIR__ . '/take_service.php';

take_ensure_item_stock_take_system();
counter_inventory_ensure_schema();

$action = take_clean_text($_REQUEST['action'] ?? '');
$session = take_check_session();

if (!$session['ok']) {
    take_json_response(false, $session['message'], ['user_id' => 0], 401);
}

$user_id = (int)$session['user_id'];

switch ($action) {
    case '':
    case 'boot':
        $result = take_service_boot($user_id);
        take_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'items_html' => $result['items_html'] ?? '',
            'list_html' => $result['list_html'] ?? '',
            'metrics' => $result['metrics'] ?? [],
        ], 200);
        break;

    case 'fetch_items':
        $result = take_service_fetch_items($user_id);
        take_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
            'remaining_count' => $result['remaining_count'] ?? 0,
            'has_items' => $result['has_items'] ?? false,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'list':
        $result = take_service_list_rows($user_id);
        take_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
            'counted_count' => $result['counted_count'] ?? 0,
            'variance_count' => $result['variance_count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'add_item':
        $result = take_service_add_item($user_id, take_clean_int($_REQUEST['item_id'] ?? 0), $_REQUEST['qty'] ?? '', $_REQUEST['take_entry'] ?? '');
        take_json_response($result['ok'], $result['message'], [
            'item_name' => $result['item_name'] ?? '',
            'counted' => $result['counted'] ?? 0,
            'counted_label' => $result['counted_label'] ?? '',
            'list_html' => $result['list_html'] ?? '',
            'items_html' => $result['items_html'] ?? '',
            'remaining_count' => $result['remaining_count'] ?? 0,
            'has_items' => $result['has_items'] ?? false,
            'variance_count' => $result['variance_count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'delete_item':
        $result = take_service_delete_item($user_id, take_clean_int($_REQUEST['id'] ?? 0));
        take_json_response($result['ok'], $result['message'], [
            'list_html' => $result['list_html'] ?? '',
            'items_html' => $result['items_html'] ?? '',
            'remaining_count' => $result['remaining_count'] ?? 0,
            'has_items' => $result['has_items'] ?? false,
            'variance_count' => $result['variance_count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'variance_modal':
        $result = take_service_variance_modal($user_id);
        take_json_response($result['ok'], $result['message'], ['html' => $result['html'] ?? ''], $result['ok'] ? 200 : 400);
        break;

    case 'confirm_modal':
        $result = take_service_confirm_modal($user_id);
        take_json_response($result['ok'], $result['message'], ['html' => $result['html'] ?? ''], $result['ok'] ? 200 : 400);
        break;

    case 'confirm_take':
        $result = take_service_confirm_take($user_id);
        take_json_response($result['ok'], $result['message'], [
            'status' => $result['status'] ?? ($result['ok'] ? 'success' : 'error'),
            'redirect' => $result['redirect'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        take_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
