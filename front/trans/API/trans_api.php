<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once '../../../front/chaba/funcs.php';
require_once __DIR__ . '/../funcs.php';
require_once __DIR__ . '/trans_schema.php';
require_once __DIR__ . '/trans_helper.php';
require_once __DIR__ . '/trans_logger.php';
require_once __DIR__ . '/trans_repository.php';
require_once __DIR__ . '/trans_service.php';

trans_schema_boot($connection);

$action = trans_clean_text($_REQUEST['action'] ?? 'bootstrap');
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$item_id = trans_clean_int($_REQUEST['item_id'] ?? $_REQUEST['id'] ?? 0);
$qty = trans_clean_number($_REQUEST['qty'] ?? 0);
$counter = trans_clean_int($_REQUEST['counter'] ?? 0);
$cat_id = trans_clean_int($_REQUEST['cat_id'] ?? $_REQUEST['id'] ?? 0);
$sub_id = trans_clean_int($_REQUEST['sub_id'] ?? $_REQUEST['id'] ?? 0);
$cntr_id = trans_clean_int($_REQUEST['cntr_id'] ?? 0);
$trans_id = trans_clean_int($_REQUEST['trans_id'] ?? $_REQUEST['trans'] ?? 0);

switch ($action) {
    case 'bootstrap':
        $result = trans_service_bootstrap($connection, $user_id);
        trans_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'categories' => $result['categories'] ?? [],
            'transfer_items' => $result['transfer_items'] ?? [],
        ], $result['status'] ?? 200);
        break;

    case 'counter_picker_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_counter_picker_modal($connection, $user_id);
        echo $result['html'] ?? '';
        exit;

    case 'init_transfer':
        $result = trans_service_init_transfer($connection, $user_id, $counter);
        trans_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
            'trans_id' => $result['trans_id'] ?? 0,
        ], $result['status'] ?? 200);
        break;

    case 'subcategories_html':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_subcategories_html($connection, $cat_id);
        echo $result['html'] ?? '';
        exit;

    case 'items_by_cat_html':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_items_by_cat($connection, $cat_id);
        echo $result['html'] ?? '';
        exit;

    case 'items_by_sub_html':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_items_by_sub($connection, $sub_id);
        echo $result['html'] ?? '';
        exit;

    case 'list_html':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_list_html($connection, (int)($_SESSION['trans'] ?? 0));
        echo $result['html'] ?? '';
        exit;

    case 'item_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_item_modal($connection, $user_id, $item_id);
        echo $result['html'] ?? '';
        exit;

    case 'submit_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_submit_modal($connection, $user_id);
        echo $result['html'] ?? '';
        exit;

    case 'add_item':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_add_item($connection, $user_id, $item_id, $qty);
        echo $result['html'] ?? '';
        exit;

    case 'delete_item':
        $result = trans_service_delete_item($connection, $user_id, $item_id);
        trans_json_response($result['ok'], $result['message'], [], $result['status'] ?? 200);
        break;

    case 'cancel_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_cancel_modal();
        echo $result['html'] ?? '';
        exit;

    case 'cancel_transfer':
        $result = trans_service_cancel_transfer($connection);
        trans_json_response($result['ok'], $result['message'], ['redirect' => $result['redirect'] ?? ''], $result['status'] ?? 200);
        break;

    case 'submit_transfer':
        $result = trans_service_submit_transfer($connection, $user_id, $cntr_id);
        trans_json_response($result['ok'], $result['message'], ['redirect' => $result['redirect'] ?? ''], $result['status'] ?? 200);
        break;

    case 'receive_page':
        $result = trans_service_receive_page($connection, $user_id, $trans_id);
        trans_json_response($result['ok'], $result['message'], [
            'rows' => $result['rows'] ?? [],
            'redirect' => $result['redirect'] ?? '',
        ], $result['status'] ?? 200);
        break;

    case 'accept_modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = trans_service_accept_modal($connection, $trans_id, $item_id);
        echo $result['html'] ?? '';
        exit;

    case 'receive_item':
        $result = trans_service_receive_item($connection, $user_id, $trans_id, $item_id, $qty);
        trans_json_response($result['ok'], $result['message'], [], $result['status'] ?? 200);
        break;

    default:
        trans_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
