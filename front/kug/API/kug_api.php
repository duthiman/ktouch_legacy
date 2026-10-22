<?php
session_start();

header('Content-Type: application/json');

include('../../../inc/connection.php');
include_once('../../../inc/gen_funcs.php');
include_once('../funcs.php');
require_once __DIR__ . '/kug_helpers.php';
require_once __DIR__ . '/kug_repository.php';
require_once __DIR__ . '/kug_service.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id < 1) {
    kug_json_response(false, 'Session expired. Please login again.', [], 401);
}

$action = kug_clean_text($_REQUEST['action'] ?? '');

switch ($action) {
    case 'init':
        $result = kug_service_init($user_id);
        kug_json_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    case 'bootstrap':
        $result = kug_service_bootstrap($user_id);
        kug_json_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    case 'get_subcategories':
        $result = kug_service_get_subcategories($user_id, kug_clean_int($_REQUEST['id'] ?? 0));
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'get_category_items':
        $result = kug_service_get_items($user_id, kug_clean_int($_REQUEST['id'] ?? 0), 0);
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'get_subcategory_items':
        $result = kug_service_get_items($user_id, 0, kug_clean_int($_REQUEST['id'] ?? 0));
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'get_list':
        $result = kug_service_get_list($user_id);
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'add_item':
        $result = kug_service_add_item($user_id, $_REQUEST);
        kug_json_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    case 'delete_item':
        $result = kug_service_delete_item($user_id, kug_clean_int($_REQUEST['id'] ?? 0));
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'toggle_tax':
        $result = kug_service_toggle_tax($user_id, kug_clean_int($_REQUEST['tax'] ?? 0));
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'supplier_modal':
        $result = kug_service_supplier_modal($user_id);
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'supplier_new_modal':
        $html = is_file(__DIR__ . '/../supplier_new_modal.php') ? file_get_contents(__DIR__ . '/../supplier_new_modal.php') : '';
        kug_json_response($html !== '', $html !== '' ? 'Supplier form loaded.' : 'Could not load supplier form.', ['html' => $html]);
        break;

    case 'cancel_modal':
        $result = kug_service_cancel_modal($user_id);
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'submit_modal':
        $result = kug_service_submit_modal($user_id);
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'save_supplier':
        $result = kug_service_save_supplier($user_id, kug_clean_int($_REQUEST['sup_id'] ?? 0));
        kug_json_response($result['ok'], $result['message'], $result);
        break;

    case 'create_supplier':
        $result = kug_service_create_supplier($user_id, $_REQUEST);
        kug_json_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    case 'cancel':
        $result = kug_service_cancel($user_id);
        kug_json_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    case 'submit':
        $result = kug_service_submit($user_id);
        kug_json_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    default:
        kug_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
