<?php
// Open-page orchestration. Bill-entry and printing operations are isolated
// behind open_bill_bridge.php while open-specific workflows are migrated.
require_once __DIR__ . '/open_bill_bridge.php';
require_once dirname(__DIR__) . '/funcs.php';

function open_service_bootstrap($user_id, $tab = 'open', $query = '')
{
    $tab = strtolower(trim((string)$tab)) === 'new' ? 'new' : 'open';
    $page = bill_service_open_page_bootstrap((int)$user_id, $tab);
    $list = bill_service_open_list_sales((int)$user_id, $tab, (string)$query);

    return [
        'ok' => !empty($page['ok']) && !empty($list['ok']),
        'message' => !empty($page['ok']) && !empty($list['ok'])
            ? 'Open orders loaded.'
            : ((string)($list['message'] ?? $page['message'] ?? 'Unable to load open orders.')),
        'username' => (string)($page['username'] ?? ''),
        'recent_count' => (int)($page['recent_count'] ?? 0),
        'default_tab' => (string)($page['default_tab'] ?? $tab),
        'page_title' => (string)($page['page_title'] ?? 'Open Orders'),
        'tab' => $tab,
        'sales' => is_array($list['sales'] ?? null) ? $list['sales'] : [],
    ];
}

function open_service_sales($user_id, $tab, $query = '')
{
    return bill_service_open_list_sales((int)$user_id, (string)$tab, (string)$query);
}

function open_service_continue_type($user_id, $sale_id)
{
    return bill_service_open_load_continue_type_modal((int)$user_id, (int)$sale_id);
}

function open_service_bill_modal($user_id, $sale_id)
{
    return bill_service_open_load_bill_modal((int)$user_id, (int)$sale_id);
}

function open_service_counter($user_id, $sale_id)
{
    return bill_service_open_load_counter_modal((int)$user_id, (int)$sale_id);
}

function open_service_initialise($user_id, $params)
{
    $params = is_array($params) ? $params : [];
    return bill_service_initialise(
        (int)$user_id,
        (int)($params['counter'] ?? 0),
        (int)($params['st'] ?? 0),
        (int)($params['sale_id'] ?? 0)
    );
}

function open_service_identifier($user_id, $params)
{
    $params = is_array($params) ? $params : [];
    return bill_service_open_load_identifier_modal(
        (int)$user_id,
        (int)($params['sale_id'] ?? 0),
        (int)($params['counter_id'] ?? 0),
        (string)($params['bill_mode'] ?? 'each'),
        !empty($params['fresh_open'])
    );
}

function open_service_save_identifier($user_id, $params)
{
    return bill_service_open_save_identifier((int)$user_id, is_array($params) ? $params : []);
}

function open_service_order($user_id, $params)
{
    return bill_service_open_order((int)$user_id, is_array($params) ? $params : []);
}

function open_service_counter_print($user_id, $params)
{
    return bill_service_load_counter_print_modal((int)$user_id, is_array($params) ? $params : []);
}

function open_service_close($user_id, $params)
{
    return bill_service_close_order((int)$user_id, is_array($params) ? $params : []);
}
