<?php
session_start();

header('Content-Type: application/json');

include('../../../inc/connection.php');
include_once('../../../inc/gen_funcs.php');
require_once __DIR__ . '/tots_schema.php';
require_once __DIR__ . '/tots_helpers.php';
require_once __DIR__ . '/tots_repository.php';
require_once __DIR__ . '/tots_service.php';
require_once __DIR__ . '/tots_logger.php';

$action = tots_clean_text($_REQUEST['action'] ?? '');
$session = tots_check_session();

if (!$session['ok']) {
    tots_json_response(false, $session['message'], ['user_id' => 0], 401);
}

$user_id = (int)$session['user_id'];

switch ($action) {
    case '':
    case 'boot':
        $result = tots_service_boot($user_id);
        tots_log_event('boot', ['user_id' => $user_id]);
        tots_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
            'actions' => $result['actions'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'health':
        $result = tots_service_health($user_id);
        tots_log_event('health', ['user_id' => $user_id]);
        tots_json_response($result['ok'], $result['message'], [
            'context' => $result['context'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'init':
        $result = tots_service_initialize($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'code' => $result['code'] ?? '',
            'redirect' => $result['redirect'] ?? '',
            'context' => $result['context'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'bootstrap':
        $result = tots_service_bootstrap($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'categories_desktop_html' => $result['categories_desktop_html'] ?? '',
            'categories_mobile_html' => $result['categories_mobile_html'] ?? '',
            'items_html' => $result['items_html'] ?? '',
            'live_list_html' => $result['live_list_html'] ?? '',
            'live_count' => $result['live_count'] ?? 0,
            'context' => $result['context'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_categories':
        $result = tots_service_get_categories($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'desktop_html' => $result['desktop_html'] ?? '',
            'mobile_html' => $result['mobile_html'] ?? '',
            'count' => $result['count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_initial_items':
        $result = tots_service_get_initial_items($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
            'count' => $result['count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_category_items':
        $result = tots_service_get_category_items($user_id, tots_clean_int($_REQUEST['id'] ?? 0));
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_subcategory_items':
        $result = tots_service_get_subcategory_items($user_id, tots_clean_int($_REQUEST['id'] ?? 0));
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_subcategories':
        $result = tots_service_get_subcategories($user_id, tots_clean_int($_REQUEST['id'] ?? 0));
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_live_list':
        $result = tots_service_get_live_list($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
            'count' => $result['count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'add_item':
        $result = tots_service_add_item($user_id, tots_clean_int($_REQUEST['item_id'] ?? 0));
        tots_json_response($result['ok'], $result['message'], [
            'code' => $result['code'] ?? '',
            'live_list_html' => $result['live_list_html'] ?? '',
            'live_count' => $result['live_count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'delete_item':
        $result = tots_service_delete_item($user_id, tots_clean_int($_REQUEST['id'] ?? 0));
        tots_json_response($result['ok'], $result['message'], [
            'code' => $result['code'] ?? '',
            'live_list_html' => $result['live_list_html'] ?? '',
            'live_count' => $result['live_count'] ?? 0,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_summary_modal':
        $result = tots_service_get_summary_modal($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_print_modal':
        $result = tots_service_get_print_modal($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_cancel_modal':
        $result = tots_service_get_cancel_modal($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'submit_print':
        $result = tots_service_submit_print($user_id, tots_clean_int($_REQUEST['cntr_id'] ?? 0));
        tots_json_response($result['ok'], $result['message'], [
            'redirect' => $result['redirect'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'cancel':
        $result = tots_service_cancel($user_id);
        tots_json_response($result['ok'], $result['message'], [
            'code' => $result['code'] ?? '',
            'redirect' => $result['redirect'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        tots_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
