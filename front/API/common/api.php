<?php
session_start();

header('Content-Type: application/json');

include('../../../inc/connection.php');
include_once('../../../inc/gen_funcs.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/repository.php');
require_once(__DIR__ . '/service.php');

$session = common_check_session();
if (!$session['ok']) {
    common_json_response(false, $session['message'], ['user_id' => 0], 401);
}

$action = common_clean_text($_REQUEST['action'] ?? '');
$filters = common_service_filters_from_request($_REQUEST);

try {
    switch ($action) {
        case '':
            common_json_response(true, 'Common API is ready.', [
                'user_id' => (int)$session['user_id'],
                'actions' => [
                    'fetch_sale_details',
                    'fetch_sale_items',
                    'fetch_staff_list',
                    'fetch_item_list',
                    'fetch_catalog',
                    'fetch_user_list',
                    'fetch_role_list',
                    'fetch_purchases',
                    'fetch_purchase_items',
                    'fetch_stock_track',
                ],
            ]);
            break;

        case 'fetch_sale_details':
            $result = common_service_fetch_sale_details($filters);
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'fetch_sale_items':
            $result = common_service_fetch_sale_items($filters);
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'fetch_staff_list':
            $result = common_service_fetch_staff_list();
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
            ]);
            break;

        case 'fetch_item_list':
            $result = common_service_fetch_item_list($filters);
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'fetch_catalog':
            $result = common_service_fetch_catalog($session['user_id'], $filters);
            common_json_response($result['ok'], $result['message'], [
                'categories' => $result['categories'],
                'subcategories' => $result['subcategories'],
                'items' => $result['items'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'fetch_user_list':
            $result = common_service_fetch_user_list();
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
            ]);
            break;

        case 'fetch_role_list':
            $result = common_service_fetch_role_list();
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
            ]);
            break;

        case 'fetch_purchases':
            $result = common_service_fetch_purchases($filters);
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'fetch_purchase_items':
            $result = common_service_fetch_purchase_items($filters);
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'fetch_stock_track':
            $result = common_service_fetch_stock_track($filters);
            common_json_response($result['ok'], $result['message'], [
                'rows' => $result['rows'],
                'filters' => $result['filters'],
            ]);
            break;

        case 'log_button':
            // Button telemetry is sent with sendBeacon while navigating away.
            // Accept it as a non-blocking event so it cannot produce an API
            // error during open-bill continuation.
            common_json_response(true, 'Button event accepted.', [
                'button' => common_clean_text($_REQUEST['button'] ?? ''),
                'page' => common_clean_text($_REQUEST['page'] ?? ''),
            ]);
            break;

        default:
            common_json_response(false, 'Unknown action.', [
                'action' => $action,
            ], 400);
            break;
    }
} catch (Throwable $exception) {
    common_json_response(false, 'Common API request failed.', [
        'action' => $action,
        'error' => $exception->getMessage(),
    ], 500);
}

?>
