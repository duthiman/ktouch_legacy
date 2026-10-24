<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/ret_schema.php';
require_once __DIR__ . '/ret_helper.php';
require_once __DIR__ . '/ret_logger.php';
require_once __DIR__ . '/ret_repository.php';
require_once __DIR__ . '/ret_service.php';

ret_schema_boot($connection);

$action = ret_clean_text($_REQUEST['action'] ?? '');
$sale_id = ret_clean_int($_REQUEST['sale_id'] ?? 0);
$item_id = ret_clean_int($_REQUEST['item_id'] ?? $_REQUEST['id'] ?? 0);
$counter = ret_clean_int($_REQUEST['counter'] ?? 0);
$return_id = ret_clean_int($_REQUEST['return_id'] ?? $_REQUEST['id'] ?? 0);
$qty = (float)($_REQUEST['qty'] ?? 0);
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

switch ($action) {
    case 'bootstrap':
        $result = ret_service_bootstrap($connection, $sale_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'sale' => $result['sale'] ?? [],
            'pending_returns' => $result['pending_returns'] ?? [],
            'single_item_void' => $result['single_item_void'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'return_modal':
        $result = ret_service_return_modal($connection, $sale_id, $item_id, $counter, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'item' => $result['item'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'add_return':
        $result = ret_service_add_return($connection, $sale_id, $item_id, $counter, $qty, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'sale' => $result['sale'] ?? [],
            'pending_returns' => $result['pending_returns'] ?? [],
            'single_item_void' => $result['single_item_void'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'delete_return':
        $result = ret_service_delete_return($connection, $return_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'sale' => $result['sale'] ?? [],
            'pending_returns' => $result['pending_returns'] ?? [],
            'single_item_void' => $result['single_item_void'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'finish_return':
        $result = ret_service_finish_return($connection, $sale_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'health':
        $result = ret_service_health($sale_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'approval_list':
        $result = ret_service_approval_list($connection, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'rows' => $result['rows'] ?? [],
            'requires_cashier_approval' => $result['requires_cashier_approval'] ?? false,
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'approval_modal':
        $mode = ret_clean_text($_REQUEST['mode'] ?? 'confirm');
        $result = ret_service_approval_modal($connection, $return_id, $user_id, $mode);
        ret_json_response($result['ok'], $result['message'], [
            'modal' => $result['modal'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'cashier_approve':
        $result = ret_service_cashier_approve($connection, $return_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'rows' => $result['rows'] ?? [],
            'requires_cashier_approval' => $result['requires_cashier_approval'] ?? false,
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'confirm_return':
        $result = ret_service_confirm_return($connection, $return_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'print_sale_id' => $result['print_sale_id'] ?? 0,
            'remaining_items' => $result['remaining_items'] ?? 0,
            'queued_prints' => $result['queued_prints'] ?? [],
            'approvals' => $result['approvals'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'reject_return':
        $result = ret_service_reject_return($connection, $return_id, $user_id);
        ret_json_response($result['ok'], $result['message'], [
            'rows' => $result['rows'] ?? [],
            'requires_cashier_approval' => $result['requires_cashier_approval'] ?? false,
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    default:
        ret_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
