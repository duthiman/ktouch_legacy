<?php
session_start();

header('Content-Type: application/json');

include('../../../inc/connection.php');
require_once('funcs.php');
require_once('bill_helpers.php');
require_once('bill_logger.php');
require_once('bill_repository.php');
require_once('bill_service.php');

$action = trim((string)($_REQUEST['action'] ?? ''));
if ($action === '' && isset($_REQUEST['counter'], $_REQUEST['st'])) {
    $action = 'initialise';
}

// Explicit continuation aliases. The legacy services already contain the
// parent/child and print-queue rules; these aliases expose the same clear
// actions used by the newer sales flow.
if ($action === 'continue.open.last') {
    $action = 'open_order';
    $_REQUEST['open_state'] = 'continue';
    $_REQUEST['open_mode'] = 'open';
    $_REQUEST['bill_mode'] = 'last';
}
if ($action === 'continue.open.each') {
    $action = 'open_order';
    $_REQUEST['open_state'] = 'continue';
    $_REQUEST['open_mode'] = 'open';
    $_REQUEST['bill_mode'] = 'each';
}
if ($action === 'continue.open.close') {
    $action = 'close_order';
    $_REQUEST['open_state'] = 'last';
    $_REQUEST['open_mode'] = 'open';
    $_REQUEST['bill_mode'] = 'last';
    if (!isset($_REQUEST['close_parent_sale_id']) && isset($_REQUEST['sale_id'])) {
        $_REQUEST['close_parent_sale_id'] = (int)$_REQUEST['sale_id'];
    }
}

$read_only_actions = [
    'page_bootstrap',
    'fetch_catalog',
    'get_items',
    'get_categories',
    'get_subcategories',
    'get_order_details',
    'get_current_cart',
    'get_scenario',
    'get_live_context',
    'load_submit_confirm_modal',
    'load_open_bill_mode_modal',
    'load_open_bill_modal',
    'load_open_continue_type_modal',
    'load_open_counter_modal',
    'load_open_identifier_modal',
    'load_open_sales_list',
    'load_open_page',
    'load_counter_print_modal',
];
if (!in_array($action, $read_only_actions, true)) {
    $schema_cache_file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . 'mvure_kwedia_schema_' . md5(__DIR__) . '.stamp';
    $schema_cache_time = @filemtime($schema_cache_file);
    if ($schema_cache_time === false || (time() - $schema_cache_time) >= 300) {
        kw_ensure_bill_schema();
        @touch($schema_cache_file);
    }
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // These modal reads only consume the authenticated user id; do not hold
    // the session file lock while their database work is running.
    session_write_close();
}

/*
|--------------------------------------------------------------------------
| BILL API
|--------------------------------------------------------------------------
| What belongs here:
| - Start session
| - Load required files
| - Read request values
| - Call service functions
| - Return JSON response
|
| What does NOT belong here:
| - Raw SQL queries
| - Large business workflows
| - Logging implementation details
*/

$session = bill_check_session();

if (!$session['ok']) {
    bill_json_response(false, $session['message'], ['user_id' => 0], 401);
}

$user_id = (int)$session['user_id'];

bill_log('BILL_API_REQUEST', 'user_id=' . $user_id . '|action=' . $action);

switch ($action) {
    case '':
        bill_json_response(true, 'Bill API is ready.', [
            'user_id' => $user_id,
            'next_step' => 'Add one action at a time.',
        ]);
        break;

    case 'initialise':
        $counter = (int)($_REQUEST['counter'] ?? 0);
        $status = (int)($_REQUEST['st'] ?? 0);
        $sale_id = (int)($_REQUEST['sale_id'] ?? 0);
        $result = bill_service_initialise($user_id, $counter, $status, $sale_id);
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'counter' => (int)($result['counter'] ?? 0),
            'status' => (int)($result['status'] ?? 0),
            'redirect' => (string)($result['redirect'] ?? ''),
            'error' => (string)($result['error'] ?? ''),
            'ongoing_sale' => !empty($result['ongoing_sale']),
            'counter_name' => (string)($result['counter_name'] ?? ''),
            'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
        ], $result['ok'] ? 200 : ((int)($result['status'] ?? 400) ?: 400));
        break;

    case 'add_item':
        try {
            $item_id = (int)($_REQUEST['id'] ?? 0);
            $qty = isset($_REQUEST['qty']) ? (float)$_REQUEST['qty'] : 0;
            $amount_override = isset($_REQUEST['amount_override']) && $_REQUEST['amount_override'] !== ''
                ? (float)$_REQUEST['amount_override'] : null;
            $attr_id = isset($_REQUEST['attr_id']) && $_REQUEST['attr_id'] !== '' ? (int)$_REQUEST['attr_id'] : null;
            $modifier = isset($_REQUEST['price_modifier']) ? (float)$_REQUEST['price_modifier'] : 0;
            $accompaniments = [];
            if (!empty($_REQUEST['accompaniment_json'])) {
                $decoded = json_decode((string)$_REQUEST['accompaniment_json'], true);
                if (is_array($decoded)) {
                    $accompaniments = $decoded;
                }
            }

            $cart = bill_service_add_item($user_id, $item_id, $qty, $attr_id, $modifier, $accompaniments, $amount_override);
            bill_json_response(true, 'Item added to cart.', [
                'cart' => $cart,
            ]);
        } catch (RuntimeException $exception) {
            bill_json_response(false, $exception->getMessage(), [], 400);
        }
        break;

    case 'fetch_catalog':
        $result = bill_service_fetch_catalog($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'categories' => $result['categories'],
            'subcategories' => $result['subcategories'],
            'items' => $result['items'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_items':
        $subcategory_id = (int)($_REQUEST['subcategory_id'] ?? 0);
        $category_id = (int)($_REQUEST['category_id'] ?? 0);
        $result = bill_service_get_items($user_id, $category_id, $subcategory_id);
        bill_json_response($result['ok'], $result['message'], [
            'items' => $result['items'],
            'category_id' => $category_id,
            'subcategory_id' => $subcategory_id,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_categories':
        $result = bill_service_get_categories($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'categories' => $result['categories'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_subcategories':
        $category_id = (int)($_REQUEST['category_id'] ?? 0);
        $result = bill_service_get_subcategories($category_id);
        bill_json_response($result['ok'], $result['message'], [
            'category_id' => $result['category_id'],
            'subcategories' => $result['subcategories'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_order_details':
        $result = bill_service_get_order_details($user_id);
        $cart = bill_service_get_current_cart($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'user_id' => $result['user_id'],
            'user_name' => $result['user_name'],
            'counter_id' => $result['counter_id'],
            'counter_name' => $result['counter_name'],
            'sale_id' => $result['sale_id'],
            'items' => $result['items'],
            'amount' => $result['amount'],
            'cart' => [
                'items' => is_array($cart['items'] ?? null) ? $cart['items'] : [],
                'rows' => is_array($cart['rows'] ?? null) ? $cart['rows'] : [],
                'total' => (float)($cart['total'] ?? 0),
                'sale_id' => (int)($cart['sale_id'] ?? 0),
            ],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'page_bootstrap':
        $result = bill_service_page_bootstrap($user_id, $_REQUEST);
        bill_json_response($result['ok'], $result['message'], [
            'user_id' => (int)($result['user_id'] ?? $user_id),
            'user_name' => (string)($result['user_name'] ?? ''),
            'counter_id' => (int)($result['counter_id'] ?? 0),
            'counter_name' => (string)($result['counter_name'] ?? ''),
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'status' => (int)($result['status'] ?? 0),
            'cart' => is_array($result['cart'] ?? null) ? $result['cart'] : [],
            'redirect' => (string)($result['redirect'] ?? ''),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_current_cart':
        $result = bill_service_get_current_cart($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'items' => $result['items'],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'sale_id' => $result['sale_id'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_scenario':
        $result = bill_service_get_scenario($user_id, ['require_status' => false]);
        bill_json_response($result['ok'], $result['message'], [
            'scenario' => $result['scenario'],
            'sale_id' => $result['sale_id'],
            'counter_id' => $result['counter_id'],
            'status_row' => $result['status_row'],
            'thesion' => $result['thesion'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'get_live_context':
        $result = bill_service_get_live_context($user_id, ['require_status' => false]);
        bill_json_response($result['ok'], $result['message'], [
            'user_id' => (int)($result['user_id'] ?? 0),
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'counter_id' => (int)($result['counter_id'] ?? 0),
            'scenario' => (string)($result['scenario'] ?? ''),
            'status_row' => $result['status_row'] ?? null,
            'thesion' => $result['thesion'] ?? null,
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_submit_confirm_modal':
        $result = bill_service_load_submit_confirm_modal($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'total' => (float)($result['total'] ?? 0),
            'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
            'parent_bill' => (int)($result['parent_bill'] ?? 0),
            'parent_bill_mode' => (string)($result['parent_bill_mode'] ?? 'each'),
            'has_parent' => !empty($result['has_parent']),
            'is_empty_session' => !empty($result['is_empty_session']),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_bill_mode_modal':
        $result = bill_service_load_open_bill_mode_modal($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'title' => (string)($result['title'] ?? ''),
            'copy' => (string)($result['copy'] ?? ''),
            'parent_bill' => (int)($result['parent_bill'] ?? 0),
            'existing_parent_mode' => (string)($result['existing_parent_mode'] ?? ''),
            'options' => is_array($result['options'] ?? null) ? $result['options'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_bill_modal':
        $result = bill_service_open_load_bill_modal($user_id, (int)($_REQUEST['sale_id'] ?? 0));
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'owner_name' => (string)($result['owner_name'] ?? ''),
            'counter_name' => (string)($result['counter_name'] ?? ''),
            'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
            'grand_total' => (float)($result['grand_total'] ?? 0),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_continue_type_modal':
        $result = bill_service_open_load_continue_type_modal($user_id, (int)($_REQUEST['sale_id'] ?? 0));
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'amount' => (float)($result['amount'] ?? 0),
            'options' => is_array($result['options'] ?? null) ? $result['options'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_counter_modal':
        $result = bill_service_open_load_counter_modal($user_id, (int)($_REQUEST['sale_id'] ?? 0));
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'target_sale_id' => (int)($result['target_sale_id'] ?? 0),
            'is_open_bill_mode' => !empty($result['is_open_bill_mode']),
            'bill_mode' => (string)($result['bill_mode'] ?? 'each'),
            'family_total' => (float)($result['family_total'] ?? 0),
            'identifier_label' => (string)($result['identifier_label'] ?? ''),
            'bill_items' => is_array($result['bill_items'] ?? null) ? $result['bill_items'] : [],
            'child_bills' => is_array($result['child_bills'] ?? null) ? $result['child_bills'] : [],
            'family_type' => (string)($result['family_type'] ?? ''),
            'active_child_sale_id' => (int)($result['active_child_sale_id'] ?? 0),
            'family_views' => is_array($result['family_views'] ?? null) ? $result['family_views'] : [],
            'is_parent_locked' => !empty($result['is_parent_locked']),
            'counters' => is_array($result['counters'] ?? null) ? $result['counters'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_identifier_modal':
        $result = bill_service_open_load_identifier_modal(
            $user_id,
            (int)($_REQUEST['sale_id'] ?? 0),
            (int)($_REQUEST['counter_id'] ?? 0),
            (string)($_REQUEST['bill_mode'] ?? 'each'),
            !empty($_REQUEST['fresh_open'])
        );
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'counter_id' => (int)($result['counter_id'] ?? 0),
            'bill_mode' => (string)($result['bill_mode'] ?? 'each'),
            'fresh_open' => !empty($result['fresh_open']),
            'colors' => is_array($result['colors'] ?? null) ? $result['colors'] : [],
            'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
            'items_total' => (float)($result['items_total'] ?? 0),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'save_open_identifier':
        $result = bill_service_open_save_identifier($user_id, $_REQUEST);
        bill_json_response($result['ok'], $result['message'], [
            'identifier' => $result['identifier'] ?? null,
            'redirect' => (string)($result['redirect'] ?? ''),
            'mode' => (string)($result['mode'] ?? ''),
            'fresh_open' => !empty($result['fresh_open']),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_sales_list':
        $result = bill_service_open_list_sales($user_id, (string)($_REQUEST['tab'] ?? 'new'), (string)($_REQUEST['q'] ?? ''));
        bill_json_response($result['ok'], $result['message'], [
            'tab' => (string)($result['tab'] ?? 'new'),
            'sales' => is_array($result['sales'] ?? null) ? $result['sales'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_page':
        $result = bill_service_open_page_bootstrap($user_id, (string)($_REQUEST['tab'] ?? 'open'));
        bill_json_response($result['ok'], $result['message'], [
            'username' => (string)($result['username'] ?? ''),
            'recent_count' => (int)($result['recent_count'] ?? 0),
            'default_tab' => (string)($result['default_tab'] ?? 'open'),
            'page_title' => (string)($result['page_title'] ?? 'Open Orders'),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_counter_print_modal':
        $result = bill_service_load_counter_print_modal($user_id, $_REQUEST);
        bill_json_response($result['ok'], $result['message'], [
            'title' => $result['title'],
            'copy' => $result['copy'],
            'scenario' => $result['scenario'],
            'selected_counter' => $result['selected_counter'],
            'open_state' => $result['open_state'],
            'open_mode' => $result['open_mode'],
            'bill_mode' => $result['bill_mode'],
            'final_verdict' => $result['final_verdict'],
            'close_current_bill' => $result['close_current_bill'],
            'close_parent_sale_id' => $result['close_parent_sale_id'],
            'print_final' => $result['print_final'],
            'counters' => $result['counters'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'open_order':
        $result = bill_service_open_order($user_id, $_REQUEST);
        bill_json_response($result['ok'], $result['message'], [
            'scenario' => (string)($result['scenario'] ?? ''),
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'queued_jobs' => is_array($result['queued_jobs'] ?? null) ? $result['queued_jobs'] : [],
            'redirect' => (string)($result['redirect'] ?? ''),
            'error' => (string)($result['error'] ?? ''),
            'ongoing_sale' => !empty($result['ongoing_sale']),
            'counter' => (int)($result['counter'] ?? 0),
            'counter_name' => (string)($result['counter_name'] ?? ''),
            'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
        ], $result['ok'] ? 200 : ((int)($result['status'] ?? 400) ?: 400));
        break;

    case 'place_closed_order':
        $result = bill_service_place_closed_order($user_id, $_REQUEST);
        bill_json_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'queued_jobs' => is_array($result['queued_jobs'] ?? null) ? $result['queued_jobs'] : [],
            'redirect' => (string)($result['redirect'] ?? ''),
            'error' => (string)($result['error'] ?? ''),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'close_order':
        $result = bill_service_close_order($user_id, $_REQUEST);
        bill_json_response($result['ok'], $result['message'], [
            'scenario' => (string)($result['scenario'] ?? ''),
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'amount' => (float)($result['amount'] ?? 0),
            'queued_jobs' => is_array($result['queued_jobs'] ?? null) ? $result['queued_jobs'] : [],
            'redirect' => (string)($result['redirect'] ?? ''),
            'error' => (string)($result['error'] ?? ''),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'delete_item':
    case 'remove_item':
        $item_id = (int)($_REQUEST['item_id'] ?? 0);
        $hour = (int)($_REQUEST['hhr'] ?? 0);
        $attr_id = isset($_REQUEST['attr_id']) && $_REQUEST['attr_id'] !== '' ? (int)$_REQUEST['attr_id'] : null;
        $accompaniment_json = isset($_REQUEST['accompaniment_json']) ? bill_clean_text($_REQUEST['accompaniment_json']) : null;
        $result = bill_service_delete_item($user_id, $item_id, $hour, $attr_id, $accompaniment_json);
        bill_json_response($result['ok'], $result['message'], [
            'item_id' => $result['item_id'],
            'sale_id' => $result['sale_id'],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'cancel_order':
        $result = bill_service_cancel_order($user_id);
        bill_json_response($result['ok'], $result['message'], [
            'user_id' => $result['user_id'],
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        bill_json_response(false, 'Unknown action.', [
            'action' => $action,
        ], 400);
        break;
}

?>
