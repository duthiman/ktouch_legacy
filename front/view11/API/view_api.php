<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';

$viewFuncBridge = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'funcs.php';
if (is_file($viewFuncBridge)) {
    require_once $viewFuncBridge;
}

require_once __DIR__ . '/view_schema.php';
require_once __DIR__ . '/view_helper.php';
require_once __DIR__ . '/view_logger.php';
require_once __DIR__ . '/view_repository.php';
require_once __DIR__ . '/view_service.php';

view_schema_boot($connection);

$action = view_clean_text($_REQUEST['action'] ?? 'health');
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$sale_id = view_clean_int($_REQUEST['sale_id'] ?? 0);
$request_id = view_clean_int($_REQUEST['request_id'] ?? 0);
$my_root_sale_id = view_clean_int($_REQUEST['my_root_sale_id'] ?? 0);
$target_bill_no = view_clean_int($_REQUEST['target_bill_no'] ?? 0);
$merge_id = view_clean_int($_REQUEST['merge_id'] ?? 0);
$item_name = view_clean_text($_REQUEST['item_name'] ?? '');
$pass = view_clean_text($_REQUEST['pass'] ?? '');
$sale_ids = $_REQUEST['sale_ids'] ?? [];
$page = view_clean_int($_REQUEST['page'] ?? 1);
$search = view_clean_text($_REQUEST['search'] ?? '');

switch ($action) {
    case 'bootstrap':
        $result = view_service_bootstrap($connection, $user_id, $page, $search);
        view_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'counts' => $result['counts'] ?? [],
            'sales_rows' => $result['sales_rows'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'sold_rows' => $result['sold_rows'] ?? [],
            'items' => $result['items'] ?? ($result['sold_rows'] ?? []),
            'merge' => $result['merge'] ?? [],
            'merge_cards' => $result['merge_cards'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'health':
        $result = view_service_health($user_id);
        view_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_add_sale':
        $result = view_service_merge_add_sale($connection, $user_id, $sale_id);
        view_json_response($result['ok'], $result['message'], [
            'merge_id' => $result['merge_id'] ?? 0,
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_remove_sale':
        $result = view_service_merge_remove_sale($connection, $user_id, $sale_id);
        view_json_response($result['ok'], $result['message'], [], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_request':
        $result = view_service_merge_request($connection, $user_id, $my_root_sale_id, $target_bill_no);
        view_json_response($result['ok'], $result['message'], [
            'merge_id' => $result['merge_id'] ?? 0,
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_create_from_sales':
        $result = view_service_merge_create_from_sales($connection, $user_id, $sale_ids);
        view_json_response($result['ok'], $result['message'], [
            'merge_id' => $result['merge_id'] ?? 0,
            'merge_no' => $result['merge_no'] ?? '',
            'total_amount' => $result['total_amount'] ?? '0.00',
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_finalize':
        $result = view_service_merge_finalize($connection, $user_id);
        view_json_response($result['ok'], $result['message'], [
            'merge_no' => $result['merge_no'] ?? '',
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_accept':
        $result = view_service_merge_accept($connection, $user_id, $request_id);
        view_json_response($result['ok'], $result['message'], [
            'request_id' => $result['request_id'] ?? 0,
        ], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_reject':
        $result = view_service_merge_reject($connection, $user_id, $request_id);
        view_json_response($result['ok'], $result['message'], [], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'merge_close_request':
        $result = view_service_merge_close_request($connection, $user_id, $request_id);
        view_json_response($result['ok'], $result['message'], [], $result['status'] ?? ($result['ok'] ? 200 : 400));
        break;

    case 'bill_items_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_bill_items_modal($connection, $user_id, $sale_id);
        echo $result['html'] ?? '';
        exit;

    case 'merge_bill_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_merge_bill_modal($connection, $user_id, $sale_id);
        echo $result['html'] ?? '';
        exit;

    case 'merge_review_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_merge_review_modal($connection, $user_id, $merge_id);
        echo $result['html'] ?? '';
        exit;

    case 'merge_requests_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_merge_requests_modal(
            $connection,
            $user_id,
            view_clean_int($_REQUEST['incoming'] ?? 0) === 1,
            view_clean_int($_REQUEST['pending'] ?? 0) === 1
        );
        echo $result['html'] ?? '';
        exit;

    case 'item_sales_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_item_sales_modal($connection, $user_id, $item_name);
        echo $result['html'] ?? '';
        exit;

    case 'reprint_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_reprint_modal($connection, $user_id, $sale_id);
        echo $result['html'] ?? '';
        exit;

    case 'reprint_authorize':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_reprint_authorize($connection, $user_id, $sale_id, $pass);
        echo $result['html'] ?? '';
        exit;

    case 'void_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_void_modal($connection, $user_id, $sale_id);
        echo $result['html'] ?? '';
        exit;

    case 'void_authorize':
        header('Content-Type: text/html; charset=UTF-8');
        $result = view_service_void_authorize($connection, $user_id, $sale_id, $pass);
        echo $result['html'] ?? '';
        exit;

    default:
        view_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
