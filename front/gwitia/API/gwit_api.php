<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/gwit_schema.php';
require_once __DIR__ . '/../../counter/api/counter_inventory_schema.php';
require_once __DIR__ . '/gwit_helper.php';
require_once __DIR__ . '/gwit_logger.php';
require_once __DIR__ . '/gwit_repository.php';
require_once __DIR__ . '/gwit_service.php';

$action = gwit_clean_text($_REQUEST['action'] ?? '');
// Keep the requisition tables and columns available before every request action.
gwit_schema_boot($connection);
$user_id = (int)($_SESSION['user_id'] ?? 0);
$req_id = (int)($_SESSION['req'] ?? 0);
$cat_id = gwit_clean_int($_REQUEST['cat_id'] ?? 0);
$sub_id = gwit_clean_int($_REQUEST['sub_id'] ?? 0);
$item_id = gwit_clean_int($_REQUEST['item_id'] ?? $_REQUEST['id'] ?? 0);
$qty = gwit_clean_float($_REQUEST['qty'] ?? 0);
$cntr_id = gwit_clean_int($_REQUEST['cntr_id'] ?? 0);

switch ($action) {
    case 'bootstrap':
        $result = gwit_service_bootstrap($connection, $user_id, $req_id);
        gwit_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'categories' => $result['categories'] ?? [],
            'subcategories' => $result['subcategories'] ?? [],
            'items' => $result['items'] ?? [],
            'req_items' => $result['req_items'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'category_items':
        $result = gwit_service_get_items($connection, $user_id, $cat_id, 0);
        gwit_json_response($result['ok'], $result['message'], [
            'subcategories' => $result['subcategories'] ?? [],
            'items' => $result['items'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'subcategory_items':
        $result = gwit_service_get_items($connection, $user_id, 0, $sub_id);
        gwit_json_response($result['ok'], $result['message'], [
            'items' => $result['items'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'req_items':
        $result = gwit_service_get_req_items($connection, $user_id, $req_id);
        gwit_json_response($result['ok'], $result['message'], [
            'req_items' => $result['req_items'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'item_modal':
        $result = gwit_service_item_modal($connection, $user_id, $item_id);
        gwit_json_response($result['ok'], $result['message'], [
            'item' => $result['item'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'add_item':
        $result = gwit_service_add_item($connection, $user_id, $req_id, $item_id, $qty);
        gwit_json_response($result['ok'], $result['message'], [
            'req_items' => $result['req_items'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'delete_item':
        $result = gwit_service_delete_item($connection, $user_id, $req_id, $item_id);
        gwit_json_response($result['ok'], $result['message'], [
            'req_items' => $result['req_items'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'submit_modal':
        $result = gwit_service_submit_modal($connection, $user_id, $req_id);
        gwit_json_response($result['ok'], $result['message'], [
            'modal' => $result['modal'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'cancel_modal':
        $result = gwit_service_cancel_modal($user_id, $req_id);
        gwit_json_response($result['ok'], $result['message'], [], $result['status'] ?? 200);
        break;
    case 'submit_request':
        $result = gwit_service_submit_request($connection, $user_id, $req_id, $cntr_id);
        gwit_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['status'] ?? 200);
        break;
    case 'cancel_request':
        $result = gwit_service_cancel_request($connection, $user_id, $req_id);
        gwit_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['status'] ?? 200);
        break;
    case 'start_modal':
        $result = gwit_service_start_modal($connection, $user_id);
        gwit_json_response($result['ok'], $result['message'], [
            'modal' => $result['modal'] ?? [],
        ], $result['status'] ?? 200);
        break;
    case 'init_request':
        $result = gwit_service_init_request($connection, $user_id);
        gwit_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['status'] ?? 200);
        break;
    default:
        gwit_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
