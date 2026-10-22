<?php
require_once __DIR__ . '/open_api_boot.php';

$session = open_api_session();
if (!$session['ok']) {
    open_api_response(false, $session['message'], ['user_id' => 0], 401);
}

$user_id = (int)$session['user_id'];
$action = trim((string)($_REQUEST['action'] ?? 'bootstrap'));
$tab = strtolower(trim((string)($_REQUEST['tab'] ?? 'open'))) === 'new' ? 'new' : 'open';
$query = trim((string)($_REQUEST['q'] ?? ''));

// Keep the open-orders screen compatible with the explicit continuation
// actions used by the sales API.
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

switch ($action) {
    case 'bootstrap':
        $result = open_service_bootstrap($user_id, $tab, $query);
        open_api_response($result['ok'], $result['message'], $result, $result['ok'] ? 200 : 400);
        break;

    case 'sales':
        $result = open_service_sales($user_id, $tab, $query);
        open_api_response($result['ok'], $result['message'], [
            'tab' => $tab,
            'sales' => is_array($result['sales'] ?? null) ? $result['sales'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'continue_type':
        $result = open_service_continue_type($user_id, (int)($_REQUEST['sale_id'] ?? 0));
        open_api_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'amount' => (float)($result['amount'] ?? 0),
            'options' => is_array($result['options'] ?? null) ? $result['options'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'bill_modal':
        $result = open_service_bill_modal($user_id, (int)($_REQUEST['sale_id'] ?? 0));
        open_api_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'owner_name' => (string)($result['owner_name'] ?? ''),
            'counter_name' => (string)($result['counter_name'] ?? ''),
            'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
            'grand_total' => (float)($result['grand_total'] ?? 0),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'counter':
        $result = open_service_counter($user_id, (int)($_REQUEST['sale_id'] ?? 0));
        open_api_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'target_sale_id' => (int)($result['target_sale_id'] ?? 0),
            'is_open_bill_mode' => !empty($result['is_open_bill_mode']),
            'bill_mode' => (string)($result['bill_mode'] ?? 'each'),
            'family_total' => (float)($result['family_total'] ?? 0),
            'identifier_label' => (string)($result['identifier_label'] ?? ''),
            'family_type' => (string)($result['family_type'] ?? ''),
            'active_child_sale_id' => (int)($result['active_child_sale_id'] ?? 0),
            'family_views' => is_array($result['family_views'] ?? null) ? $result['family_views'] : [],
            'counters' => is_array($result['counters'] ?? null) ? $result['counters'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'initialise':
        $result = open_service_initialise($user_id, $_REQUEST);
        open_api_response($result['ok'], $result['message'], [
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'counter' => (int)($result['counter'] ?? 0),
            'status' => (int)($result['status'] ?? 0),
            'redirect' => (string)($result['redirect'] ?? ''),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_open_identifier_modal':
        $result = open_service_identifier($user_id, $_REQUEST);
        open_api_response($result['ok'], $result['message'], [
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
        $result = open_service_save_identifier($user_id, $_REQUEST);
        open_api_response($result['ok'], $result['message'], [
            'identifier' => $result['identifier'] ?? null,
            'redirect' => (string)($result['redirect'] ?? ''),
            'mode' => (string)($result['mode'] ?? ''),
            'fresh_open' => !empty($result['fresh_open']),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'open_order':
        $result = open_service_order($user_id, $_REQUEST);
        open_api_response($result['ok'], $result['message'], [
            'scenario' => (string)($result['scenario'] ?? ''),
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'redirect' => (string)($result['redirect'] ?? ''),
        ], $result['ok'] ? 200 : 400);
        break;

    case 'load_counter_print_modal':
        $result = open_service_counter_print($user_id, $_REQUEST);
        open_api_response($result['ok'], $result['message'], [
            'title' => (string)($result['title'] ?? ''),
            'copy' => (string)($result['copy'] ?? ''),
            'scenario' => (string)($result['scenario'] ?? ''),
            'selected_counter' => (int)($result['selected_counter'] ?? 0),
            'open_state' => (string)($result['open_state'] ?? ''),
            'open_mode' => (string)($result['open_mode'] ?? ''),
            'bill_mode' => (string)($result['bill_mode'] ?? ''),
            'final_verdict' => (string)($result['final_verdict'] ?? ''),
            'close_current_bill' => !empty($result['close_current_bill']),
            'close_parent_sale_id' => (int)($result['close_parent_sale_id'] ?? 0),
            'print_final' => !empty($result['print_final']),
            'counters' => is_array($result['counters'] ?? null) ? $result['counters'] : [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'close_order':
        $result = open_service_close($user_id, $_REQUEST);
        open_api_response($result['ok'], $result['message'], [
            'scenario' => (string)($result['scenario'] ?? ''),
            'sale_id' => (int)($result['sale_id'] ?? 0),
            'amount' => (float)($result['amount'] ?? 0),
            'redirect' => (string)($result['redirect'] ?? ''),
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        open_api_response(false, 'Unknown open-orders action.', ['action' => $action], 400);
}
