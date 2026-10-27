<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/inventory_helpers.php';
require_once __DIR__ . '/inventory_repository.php';
require_once __DIR__ . '/inventory_schema.php';
require_once '../../../front/counter/api/counter_inventory_schema.php';
require_once __DIR__ . '/inventory_service.php';

inventory_ensure_counter_stock_schema();
counter_inventory_ensure_schema();

$action = inventory_clean_text($_REQUEST['action'] ?? '');

switch ($action) {
    case 'counter_stock_bootstrap':
        $result = inventory_service_counter_stock_bootstrap(
            inventory_clean_int($_REQUEST['counter_id'] ?? 0),
            inventory_clean_int($_REQUEST['category_id'] ?? 0),
            inventory_clean_int($_REQUEST['subcategory_id'] ?? 0),
            inventory_clean_int($_REQUEST['stock_type'] ?? 0),
            inventory_clean_int($_REQUEST['department_id'] ?? 0)
        );
        inventory_json_response($result['ok'], $result['message'], [
            'counters' => $result['counters'] ?? [],
            'rows' => $result['rows'] ?? [],
            'categories' => $result['categories'] ?? [],
            'sub_categories' => $result['sub_categories'] ?? [],
            'departments' => $result['departments'] ?? [],
            'stock_types' => $result['stock_types'] ?? [],
            'cards' => $result['cards'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'take_items':
        $result = inventory_service_take_items();
        inventory_json_response($result['ok'], $result['message'], [
            'items' => $result['items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'adjust_store_qty':
        $result = inventory_service_adjust_store_qty(
            inventory_clean_int($_REQUEST['item_id'] ?? 0),
            inventory_clean_float($_REQUEST['qty'] ?? 0),
            $_REQUEST['reason'] ?? '',
            inventory_clean_float($_REQUEST['init'] ?? 0),
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'reload' => !empty($result['reload']),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'adjust_reorder_qty':
        $result = inventory_service_adjust_reorder_qty(
            inventory_clean_int($_REQUEST['item_id'] ?? 0),
            inventory_clean_float($_REQUEST['qty'] ?? 0),
            $_REQUEST['reason'] ?? '',
            inventory_clean_float($_REQUEST['init'] ?? 0),
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'reload' => !empty($result['reload']),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'adjust_counter_reorder_qty':
        $result = inventory_service_adjust_counter_reorder_qty(
            inventory_clean_int($_REQUEST['item_id'] ?? 0),
            inventory_clean_int($_REQUEST['counter_id'] ?? 0),
            inventory_clean_float($_REQUEST['qty'] ?? 0),
            $_REQUEST['reason'] ?? '',
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [], $result['ok'] ? 200 : 400);
        break;

    case 'take_stock':
        $result = inventory_service_take_stock(
            inventory_clean_int($_REQUEST['id'] ?? 0),
            inventory_clean_float($_REQUEST['qty'] ?? 0),
            $_REQUEST['shift'] ?? '',
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'items' => $result['items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'issue_request_item':
        $result = inventory_service_issue_request_item(
            inventory_clean_int($_REQUEST['req_id'] ?? 0),
            inventory_clean_int($_REQUEST['item'] ?? 0),
            inventory_clean_float($_REQUEST['qty'] ?? 0),
            inventory_clean_float($_REQUEST['portions'] ?? $_REQUEST['qty'] ?? 0),
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'reload' => !empty($result['reload']),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'issue_all_request':
        $result = inventory_service_issue_all_request(
            inventory_clean_int($_REQUEST['req'] ?? 0),
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'cancel_request':
        $result = inventory_service_cancel_request(
            inventory_clean_int($_REQUEST['req'] ?? 0),
            $_REQUEST['reason'] ?? '',
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'remove_request_item':
        $result = inventory_service_remove_request_item(
            inventory_clean_int($_REQUEST['req'] ?? 0),
            inventory_clean_int($_REQUEST['item_id'] ?? 0),
            $_REQUEST['reason'] ?? '',
            inventory_current_user_id()
        );
        inventory_json_response($result['ok'], $result['message'], [
            'reload' => !empty($result['reload']),
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        inventory_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
