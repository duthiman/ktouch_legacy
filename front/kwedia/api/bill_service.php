<?php

if (!function_exists('bill_service_health')) {
    function bill_service_health($user_id) {
        $user_id = (int)$user_id;

        return [
            'ok' => true,
            'message' => 'Bill service is ready.',
            'user_id' => $user_id,
            'db_ready' => bill_repo_ping(),
        ];
    }
}

if (!function_exists('bill_service_get_live_context')) {
    function bill_service_get_live_context($user_id, $options = []) {
        $user_id = (int)$user_id;
        $options = is_array($options) ? $options : [];
        $require_status = !empty($options['require_status']);
        $requested_sale_id = (int)($options['sale_id'] ?? 0);
        $thesion = repo_get_thesion($user_id); // tracing only; never used to select the sale

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired. Please login again.',
                'user_id' => 0,
                'sale_id' => 0,
                'counter_id' => 0,
                'thesion' => null,
                'status_row' => null,
                'scenario' => '',
            ];
        }

        $status_row = $requested_sale_id > 0
            ? repo_get_sale_status_for_sale($user_id, $requested_sale_id)
            : repo_get_sale_status_by_user($user_id);
        $sale_id = (int)($status_row['sale_id'] ?? 0);
        $session_status = (int)($status_row['status'] ?? 0);
        $counter_id = (int)($status_row['counter_id'] ?? 0);
        if ($sale_id < 1 || $session_status < 1) {
            return [
                'ok' => false,
                'message' => 'Order session expired. Please reopen the bill.',
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'counter_id' => $counter_id,
                'thesion' => $thesion,
                'status_row' => null,
                'scenario' => '',
            ];
        }

        if (!$thesion) {
            $thesion = ['sale_id' => $sale_id, 'status' => $session_status, 'cntr_id' => $counter_id];
        }

        $normalized_status = $status_row ? bill_normalize_sale_status_row($status_row) : null;
        $status_sale_id = (int)($normalized_status['sale_id'] ?? 0);
        $status_parent_sale_id = (int)($normalized_status['parent_sale_id'] ?? 0);
        $scenario_sale_id = $status_sale_id > 0 ? $status_sale_id : $sale_id;
        $scenario_parent_sale_id = $status_parent_sale_id > 0 ? $status_parent_sale_id : $scenario_sale_id;
        $sales_open_row = bill_repo_get_sales_open_registry($scenario_sale_id, $user_id);
        if (!$sales_open_row && $scenario_parent_sale_id > 0 && $scenario_parent_sale_id !== $scenario_sale_id) {
            $sales_open_row = bill_repo_get_sales_open_registry($scenario_parent_sale_id, $user_id);
        }
        $closed_open_row = repo_get_closed_opened_parent($scenario_parent_sale_id);
        if (!$closed_open_row && $scenario_sale_id > 0 && $scenario_sale_id !== $scenario_parent_sale_id) {
            $closed_open_row = repo_get_closed_opened_parent($scenario_sale_id);
        }

        if ($require_status && !$status_row && !$closed_open_row) {
            return [
                'ok' => false,
                'message' => 'Live bill status was not found. Please reopen the bill.',
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'counter_id' => $counter_id,
                'thesion' => $thesion,
                'status_row' => null,
                'scenario' => '',
            ];
        }

        // Scenario selection is driven by kp_sale_status. Thesion is returned
        // only for compatibility/debugging and must not influence the flow.
        $scenario = bill_service_map_scenario($normalized_status, null, $sales_open_row, $closed_open_row);

        return [
            'ok' => true,
            'message' => 'Live bill context loaded.',
            'user_id' => $user_id,
            'sale_id' => $sale_id,
            'counter_id' => $counter_id,
            'thesion' => $thesion,
            'status_row' => $normalized_status,
            'sales_open_row' => $sales_open_row,
            'closed_open_row' => $closed_open_row,
            'scenario' => $scenario,
        ];
    }
}

if (!function_exists('bill_service_map_scenario')) {
    function bill_service_map_scenario($status_row, $thesion = null, $sales_open_row = null, $closed_open_row = null) {
        $status_row = is_array($status_row) ? bill_normalize_sale_status_row($status_row) : null;
        $thesion = is_array($thesion) ? $thesion : [];
        $sales_open_row = is_array($sales_open_row) ? $sales_open_row : null;
        $closed_open_row = is_array($closed_open_row) ? $closed_open_row : null;

        $status_sale_id = (int)($status_row['sale_id'] ?? 0);
        $status_value = (int)($status_row['status'] ?? 0);

        if ($status_sale_id < 1 || $status_value < 1) {
            return '';
        }

        if (!$status_row) {
            $closed_mode = bill_clean_bill_mode($closed_open_row['bill_mode'] ?? $closed_open_row['mode'] ?? '');
            if ($closed_open_row && $closed_mode === 'each') {
                return 'closed_open_each_parent';
            }
            if ($closed_open_row && $closed_mode === 'last') {
                return 'closed_open_last_parent';
            }
            return '';
        }

        $sale_id = (int)($status_row['sale_id'] ?? 0);
        $status = (int)($status_row['status'] ?? 0);
        $mode = bill_clean_bill_mode($status_row['mode'] ?? 'fresh');
        $parent_status = (int)($status_row['parent_status'] ?? 2);
        $parent_sale_id = (int)($status_row['parent_sale_id'] ?? 0);
        $has_sales_open = !empty($sales_open_row);
        $has_closed_open = !empty($closed_open_row);

        if ($sale_id < 1) {
            return '';
        }

        if ($status === 2 && $mode === 'fresh' && $parent_sale_id === 0) {
            return 'fresh_active';
        }

        if ($status === 3 && $mode === 'last') {
            return 'open_active_last';
        }

        if ($status === 3 && $mode === 'each' && $parent_sale_id > 0) {
            return 'open_active_each';
        }

        if ($status === 2 && $mode === 'each' && $parent_sale_id > 0) {
            return 'open_active_each';
        }

        if ($has_closed_open && $parent_sale_id === 0) {
            if ($mode === 'each') {
                return 'closed_open_each_parent';
            }
            if ($mode === 'last') {
                return 'closed_open_last_parent';
            }
        }

        return '';
    }
}

if (!function_exists('bill_service_get_scenario')) {
    function bill_service_get_scenario($user_id, $options = []) {
        $context = bill_service_get_live_context($user_id, $options);

        return [
            'ok' => (bool)($context['ok'] ?? false),
            'message' => (string)($context['message'] ?? ''),
            'scenario' => (string)($context['scenario'] ?? ''),
            'sale_id' => (int)($context['sale_id'] ?? 0),
            'counter_id' => (int)($context['counter_id'] ?? 0),
            'status_row' => $context['status_row'] ?? null,
            'thesion' => $context['thesion'] ?? null,
            'sales_open_row' => $context['sales_open_row'] ?? null,
            'closed_open_row' => $context['closed_open_row'] ?? null,
        ];
    }
}

if (!function_exists('bill_service_load_counter_print_modal')) {
    function bill_service_load_counter_print_modal($user_id, $params = []) {
        $user_id = (int)$user_id;
        $params = is_array($params) ? $params : [];

        $live_context = bill_service_get_live_context($user_id);
        $active_status = $live_context['status_row'] ?? null;
        $active_sale_id = (int)($live_context['sale_id'] ?? 0);
        $active_scenario = (string)($live_context['scenario'] ?? '');
        $my_cntr = (int)($live_context['counter_id'] ?? repo_get_user_counter($user_id));

        $selected_cntr = (int)($params['bill_cntr'] ?? $my_cntr);
        if ($selected_cntr < 1) {
            $selected_cntr = $my_cntr;
        }

        $open_mode = trim((string)($params['open_mode'] ?? ''));
        if ($open_mode !== 'open') {
            $open_mode = 'closed';
        }

        $bill_mode = trim((string)($params['bill_mode'] ?? ''));
        if ($bill_mode !== 'last') {
            $bill_mode = 'each';
        }

        $open_state = trim((string)($params['open_state'] ?? ''));
        if (!in_array($open_state, ['last', 'continue', 'open', 'closed'], true)) {
            $open_state = '';
        }

        $final_verdict = trim((string)($params['final_verdict'] ?? ''));
        if (!in_array($final_verdict, ['open', 'close'], true)) {
            $final_verdict = '';
        }

        $close_parent_sale_id = (int)($params['close_parent_sale_id'] ?? 0);
        $close_current_bill = (int)($params['close_current_bill'] ?? 0) === 1;
        $print_final = (int)($params['print_final'] ?? 0) === 1;

        if ($final_verdict === 'close' && $close_parent_sale_id < 1 && !$close_current_bill) {
            $close_current_bill = true;
        }

        $is_explicit_close_flow = $final_verdict === 'close' || $close_current_bill || $print_final;

        if ($is_explicit_close_flow && $close_parent_sale_id < 1 && in_array($active_scenario, ['open_active_last', 'open_active_each'], true)) {
            $close_parent_sale_id = (int)($active_status['parent_sale_id'] ?? 0);
        }
        if ($is_explicit_close_flow && $close_parent_sale_id < 1 && $open_mode === 'open' && $open_state === 'last') {
            $close_parent_sale_id = (int)($active_status['parent_sale_id'] ?? 0);
            if ($close_parent_sale_id < 1) {
                $close_parent_sale_id = $active_sale_id;
            }
        }

        $modal_title = 'Select Counter';
        $modal_copy = 'Choose the counter where this bill should print before sending the order.';

        if ($final_verdict === 'close' || $close_current_bill || $close_parent_sale_id > 0) {
            $modal_title = 'Close Order Counter';
            $modal_copy = 'Choose the counter where this closing bill should print.';
        } elseif ($open_state === 'last') {
            $modal_title = 'Close Order Counter';
            $modal_copy = 'Choose the counter where the closing bill should print.';
        } elseif ($active_scenario === 'open_active_each' || $open_state === 'continue') {
            $modal_title = 'Still Open Counter';
            $modal_copy = 'Choose the counter where this bill should print while keeping the order open.';
        } elseif ($open_mode === 'open') {
            $modal_title = $bill_mode === 'last' ? 'Open Bill Counter - Bill At Last' : 'Open Bill Counter - Bill Every Time';
            $modal_copy = $bill_mode === 'last'
                ? 'Choose the counter. This order will print BOT/KOT now and wait to print the customer bill on the last bill.'
                : 'Choose the counter. This open bill will print the customer bill now and keep running totals for later bills.';
        }

        $counter_buttons = [];
        foreach (bill_repo_fetch_active_counters() as $counter) {
            $counter_id = (int)($counter['cntr_id'] ?? 0);
            $counter_name = (string)($counter['name'] ?? '');
            $counter_dpt = (int)($counter['dpt'] ?? 0);
            $counter_label = $counter_name;
            if ($counter_dpt > 0) {
                $counter_label .= ' - DPT ' . $counter_dpt;
            }

            $button_action = 'submit_bill';
            if ($final_verdict === 'close') {
                $button_action = $close_current_bill ? 'close_current_bill' : 'close_open_bill';
            } elseif ($close_current_bill) {
                $button_action = 'close_current_bill';
            } elseif ($close_parent_sale_id > 0) {
                $button_action = 'close_open_bill';
            }

            $counter_buttons[] = [
                'id' => $counter_id,
                'label' => $counter_label,
                'active' => $counter_id === $selected_cntr,
                'action' => $button_action,
                'close_parent_sale_id' => $close_parent_sale_id,
                'print_final' => $print_final,
                'open_state' => $open_state,
                'open_mode' => $open_mode,
                'bill_mode' => $bill_mode,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Counter print modal loaded.',
            'title' => $modal_title,
            'copy' => $modal_copy,
            'scenario' => $active_scenario,
            'selected_counter' => $selected_cntr,
            'open_state' => $open_state,
            'open_mode' => $open_mode,
            'bill_mode' => $bill_mode,
            'final_verdict' => $final_verdict,
            'close_current_bill' => $close_current_bill,
            'close_parent_sale_id' => $close_parent_sale_id,
            'print_final' => $print_final,
            'counters' => $counter_buttons,
        ];
    }
}

if (!function_exists('bill_service_load_submit_confirm_modal')) {
    function bill_service_load_submit_confirm_modal($user_id) {
        $user_id = (int)$user_id;
        $live_context = bill_service_get_live_context($user_id, ['require_status' => true]);
        if (empty($live_context['ok'])) {
            return [
                'ok' => false,
                'message' => (string)($live_context['message'] ?? 'Order session expired. Please reopen the bill.'),
            ];
        }

        $sale_id = (int)($live_context['sale_id'] ?? 0);
        $status_row = $live_context['status_row'] ?? [];
        $parent_bill = (int)($status_row['parent_sale_id'] ?? 0);
        $sales_open_row = $live_context['sales_open_row'] ?? [];
        // Some open orders intentionally have no parent_sale_id in
        // kp_sale_status. Their open registry is the parent reference, so use
        // it for fresh LAST orders and EACH child continuations alike. This
        // keeps the submit buttons in the open-order flow instead of falling
        // back to place_closed_order.
        if ($parent_bill < 1 && !empty($sales_open_row)) {
            $parent_bill = (int)($sales_open_row['parent_sale_id'] ?? $sales_open_row['sale_id'] ?? $sale_id);
        }
        if ($parent_bill < 1 && !empty($live_context['closed_open_row'])) {
            $parent_bill = (int)($live_context['closed_open_row']['sale_id'] ?? 0);
        }
        $open_parent = $parent_bill > 0 ? repo_get_closed_opened_parent($parent_bill) : null;
        $parent_bill_mode = $parent_bill > 0
            ? bill_clean_bill_mode($open_parent['bill_mode'] ?? $sales_open_row['bill_mode'] ?? $status_row['mode'] ?? 'each')
            : 'each';
        $items = repo_fetch_sale_print_grouped($sale_id, 2, 'kp_sale_items_live');
        $parent_sale_id = (int)($status_row['parent_sale_id'] ?? $parent_bill);
        $open_items = $parent_sale_id > 0
            ? repo_fetch_sale_print_grouped($parent_sale_id, 1, 'kp_sale_print')
            : [];
        $open_total = 0.0;
        foreach ($open_items as $open_item) {
            $open_total += (float)($open_item['total'] ?? 0);
        }
        $normalized_items = [];

        foreach ($items as $item) {
            $normalized_items[] = [
                'name' => (string)($item['name'] ?? ''),
                'qty' => (float)($item['qty'] ?? 0),
                'total' => (float)($item['total'] ?? 0),
                'attributes' => is_array($item['attributes'] ?? null) ? $item['attributes'] : [],
            ];
        }

        $total = 0.0;
        foreach ($normalized_items as $normalized_item) {
            $total += (float)$normalized_item['total'];
        }

        return [
            'ok' => true,
            'message' => 'Submit confirm modal loaded.',
            'sale_id' => $sale_id,
            'total' => (float)$total,
            'items' => $normalized_items,
            'parent_bill' => $parent_bill,
            'parent_bill_mode' => $parent_bill_mode,
            'has_parent' => $parent_bill > 0,
            'is_empty_session' => $parent_bill > 0 && (float)$total <= 0,
        ];
    }
}

if (!function_exists('bill_service_load_open_bill_mode_modal')) {
    function bill_service_load_open_bill_mode_modal($user_id) {
        $user_id = (int)$user_id;
        $context = bill_service_get_live_context($user_id, ['require_status' => true]);
        if (empty($context['ok'])) {
            return [
                'ok' => false,
                'message' => (string)($context['message'] ?? 'Order session expired. Please reopen the bill.'),
            ];
        }

        $status_row = $context['status_row'] ?? [];
        $parent_bill = (int)($status_row['parent_sale_id'] ?? 0);
        $existing_parent = $parent_bill > 0 ? repo_get_closed_opened_parent($parent_bill) : null;
        $existing_parent_mode = $parent_bill > 0
            ? bill_clean_bill_mode($existing_parent['bill_mode'] ?? $status_row['mode'] ?? '')
            : '';

        $title = 'Choose Open Bill Style';
        $copy = $existing_parent_mode !== ''
            ? 'This open order already has a saved mode, so the system will keep using it.'
            : 'Choose whether this should stay as one open bill until close, or print as a continuation bill every round.';

        $options = [];
        if ($existing_parent_mode === 'last') {
            $options[] = [
                'style' => 'last',
                'title' => 'Continue Open Bill',
                'copy' => 'This bill is already in Open Bill mode. BOT and KOT tickets will print now while the bill stays open.',
                'action_type' => 'direct_submit',
                'open_mode' => 'open',
                'bill_mode' => 'last',
                'open_state' => 'continue',
            ];
        } elseif ($existing_parent_mode === 'each') {
            $options[] = [
                'style' => 'each',
                'title' => 'Continue Continuation Bill',
                'copy' => 'This bill is already in Continuation mode. The next round will keep using the same saved mode.',
                'action_type' => 'counter_modal',
                'open_mode' => 'open',
                'bill_mode' => 'each',
                'open_state' => 'continue',
            ];
        } else {
            $options[] = [
                'style' => 'each',
                'title' => 'Continuation Bill',
                'copy' => 'Print a customer bill every time. Each new round becomes a child bill under the same parent.',
                'action_type' => 'counter_modal',
                'open_mode' => 'open',
                'bill_mode' => 'each',
                'open_state' => 'open',
            ];
            $options[] = [
                'style' => 'last',
                'title' => 'Open Bill',
                'copy' => 'Keep one sale id open. Print BOT and KOT tickets now, then one customer bill only at final close.',
                'action_type' => 'direct_submit',
                'open_mode' => 'open',
                'bill_mode' => 'last',
                'open_state' => '',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Open bill mode modal loaded.',
            'title' => $title,
            'copy' => $copy,
            'parent_bill' => $parent_bill,
            'existing_parent_mode' => $existing_parent_mode,
            'options' => $options,
        ];
    }
}

if (!function_exists('bill_service_open_load_bill_modal')) {
    function bill_service_open_load_bill_modal($user_id, $sale_id) {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $sale = repo_get_sales_header($sale_id);
        if ($sale_id < 1 || !$sale || (int)($sale['user_id'] ?? 0) !== $user_id) {
            return ['ok' => false, 'message' => 'Invalid bill selected.'];
        }

        $items = [];
        global $connection;
        $items_q = mysqli_query(
            $connection,
            "SELECT item_id, attr_id, SUM(qty) AS qty, price, SUM(total) AS total
             FROM kp_sale_items
             WHERE sale_id='$sale_id'
             GROUP BY item_id, attr_id, price
             ORDER BY item_id ASC"
        );
        if ($items_q) {
            while ($row = mysqli_fetch_assoc($items_q)) {
                $item_name = bill_repo_get_item_name((int)$row['item_id']);
                if ((int)$row['attr_id'] > 0) {
                    $attr_name = bill_repo_get_attribute_name((int)$row['attr_id']);
                    if ($attr_name !== '') {
                        $item_name .= ' (' . $attr_name . ')';
                    }
                }
                $items[] = [
                    'name' => $item_name,
                    'qty' => (float)($row['qty'] ?? 0),
                    'price' => (float)($row['price'] ?? 0),
                    'total' => (float)($row['total'] ?? 0),
                ];
            }
        }

        return [
            'ok' => true,
            'message' => 'Bill modal loaded.',
            'sale_id' => $sale_id,
            'owner_name' => function_exists('get_column') ? (string)get_column('kp_users', 'username', "user_id='" . (int)$sale['user_id'] . "'") : '',
            'counter_name' => function_exists('get_counter') ? (string)get_counter((int)($sale['counter'] ?? 0)) : '',
            'items' => $items,
            'grand_total' => array_sum(array_map(function ($item) { return (float)$item['total']; }, $items)),
        ];
    }
}

if (!function_exists('bill_service_open_load_continue_type_modal')) {
    function bill_service_open_load_continue_type_modal($user_id, $sale_id) {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $sale = repo_get_sales_header($sale_id);
        if ($sale_id < 1 || !$sale || (int)($sale['user_id'] ?? 0) !== $user_id) {
            return ['ok' => false, 'message' => 'Invalid bill selected.'];
        }

        return [
            'ok' => true,
            'message' => 'Continue type modal loaded.',
            'sale_id' => $sale_id,
            'amount' => (float)($sale['amount'] ?? 0),
            'options' => [
                [
                    'title' => 'Continuation Bill',
                    'copy' => 'Every next round prints a customer bill. Bill 2 starts the continuation chain from this first bill.',
                    'bill_mode' => 'each',
                ],
                [
                    'title' => 'Open Bill',
                    'copy' => 'Bill 2 stays open and prints only at final close. BOT and KOT tickets will print during adds.',
                    'bill_mode' => 'last',
                ],
            ],
        ];
    }
}

if (!function_exists('bill_service_open_load_counter_modal')) {
    function bill_service_open_load_counter_modal($user_id, $sale_id) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $sale = repo_get_sales_header($sale_id);
        $closed_parent = repo_get_closed_opened_parent($sale_id);
        $sales_open_row = bill_repo_get_sales_open_registry($sale_id, $user_id);
        $open_registry = $sales_open_row ?: $closed_parent;
        $owner_id = $sale ? (int)$sale['user_id'] : (int)($open_registry['user_id'] ?? 0);
        if ($sale_id < 1 || ((!$sale && !$open_registry) || $owner_id !== $user_id)) {
            return ['ok' => false, 'message' => 'Invalid parent bill selected.'];
        }

        $bill_mode = bill_clean_bill_mode($open_registry['bill_mode'] ?? $open_registry['mode'] ?? 'each');
        $is_open_bill_mode = (bool)$sales_open_row;
        $last_bill_id = (int)($open_registry['last_bill'] ?? 0);
        $target_bill_id = $last_bill_id > 0 ? $last_bill_id : ((int)($open_registry['sale_id'] ?? 0) > 0 ? (int)$open_registry['sale_id'] : $sale_id);
        $target_bill_exists = bill_repo_sale_exists($target_bill_id);
        $parent_finalized = bill_repo_get_sale_amount($sale_id);
        $children_finalized = bill_repo_get_children_sales_total($sale_id);
        $active_draft = ($is_open_bill_mode && !$target_bill_exists) ? bill_repo_get_sale_print_total($target_bill_id) : 0;
        $family_total = round($parent_finalized + $children_finalized + $active_draft, 2);
        $identifier_label = trim((string)($open_registry['identifier_label'] ?? ''));
        $bill_items = bill_repo_fetch_bill_items_summary($target_bill_id, $target_bill_exists);
        $child_bills = bill_repo_fetch_family_bills($sale_id);
        $family_views = [];
        $active_child_sale_id = 0;
        $resolved_family_type = 'fresh_open';

        $parent_items = [];
        if ($sale) {
            $parent_items = bill_repo_fetch_bill_items_summary($sale_id, true);
            if (count($parent_items) === 0) {
                $parent_items = bill_repo_fetch_bill_items_summary($sale_id, false);
            }
        } elseif ($is_open_bill_mode) {
            // A fresh open-LAST bill is stored in the open registry and its
            // accumulated items live in kp_shift_sale_open_items, not in
            // kp_sale_print. Show that open snapshot in the counter modal.
            $open_rows = function_exists('bill_service_fetch_shift_open_items')
                ? bill_service_fetch_shift_open_items($sale_id)
                : [];
            $open_grouped = [];
            foreach ($open_rows as $open_row) {
                $item_id = (int)($open_row['item_id'] ?? 0);
                $attr_id = (int)($open_row['attr_id'] ?? 0);
                $key = $item_id . ':' . $attr_id;
                if (!isset($open_grouped[$key])) {
                    $name = function_exists('bill_repo_get_item_name')
                        ? bill_repo_get_item_name($item_id)
                        : '';
                    $open_grouped[$key] = [
                        'item_id' => $item_id,
                        'name' => $name !== '' ? $name : ('Item ' . $item_id),
                        'qty' => 0,
                        'total' => 0,
                    ];
                }
                $open_grouped[$key]['qty'] += (float)($open_row['qty'] ?? 0);
                $open_grouped[$key]['total'] += (float)($open_row['total'] ?? 0);
            }
            $parent_items = array_values($open_grouped);
            if (count($parent_items) === 0) {
                $parent_items = bill_repo_fetch_bill_items_summary($sale_id, false);
            }
        }

        $root_view_items = count($parent_items) > 0 ? $parent_items : $bill_items;
        $root_view_kind = $is_open_bill_mode && !$sale ? 'fresh_open' : 'parent';
        $root_view_label = $root_view_kind === 'fresh_open' ? 'Bill #' . $sale_id : 'Parent #' . $sale_id;
        $family_views[] = [
            'sale_id' => $sale_id,
            'label' => $root_view_label,
            'kind' => $root_view_kind,
            'is_open' => $is_open_bill_mode && !$sale,
            'is_active' => $target_bill_id === $sale_id,
            'items' => $root_view_items,
            'amount' => $sale ? (float)($sale['amount'] ?? 0) : bill_repo_get_sale_print_total($sale_id),
        ];

        if ($last_bill_id > 0 && $is_open_bill_mode && !bill_repo_sale_exists($last_bill_id)) {
            $child_bills[] = [
                'sale_id' => $last_bill_id,
                'amount' => bill_repo_get_sale_print_total($last_bill_id),
                'is_draft' => true,
            ];
        }

        if ($bill_mode === 'last' && $is_open_bill_mode && $target_bill_id > 0 && $target_bill_id !== $sale_id) {
            $already_added_target = false;
            foreach ($child_bills as $child_bill) {
                if ((int)($child_bill['sale_id'] ?? 0) === $target_bill_id) {
                    $already_added_target = true;
                    break;
                }
            }
            if (!$already_added_target) {
                $child_bills[] = [
                    'sale_id' => $target_bill_id,
                    'amount' => $target_bill_exists ? bill_repo_get_sale_amount($target_bill_id) : bill_repo_get_sale_print_total($target_bill_id),
                    'is_draft' => !$target_bill_exists,
                ];
            }
        }

        foreach ($child_bills as $child_bill) {
            $child_sale_id = (int)($child_bill['sale_id'] ?? 0);
            if ($child_sale_id < 1 || $child_sale_id === $sale_id) {
                continue;
            }

            $child_exists = bill_repo_sale_exists($child_sale_id);
            $child_is_open = !$child_exists;
            $child_items = bill_repo_fetch_bill_items_summary($child_sale_id, $child_exists);
            $child_kind = $bill_mode === 'last' ? 'last_child' : 'each_child';
            if ($child_is_open) {
                $active_child_sale_id = $child_sale_id;
                $child_kind = 'open_child';
            }

            $family_views[] = [
                'sale_id' => $child_sale_id,
                'label' => ($child_is_open ? 'Open Child #' : 'Child #') . $child_sale_id,
                'kind' => $child_kind,
                'is_open' => $child_is_open,
                'is_active' => $target_bill_id === $child_sale_id,
                'items' => $child_items,
                'amount' => (float)($child_bill['amount'] ?? ($child_is_open ? bill_repo_get_sale_print_total($child_sale_id) : 0)),
            ];
        }

        if (count($family_views) === 1 && count($family_views[0]['items']) === 0 && $target_bill_id > 0 && $target_bill_id !== $sale_id) {
            $target_items = bill_repo_fetch_bill_items_summary($target_bill_id, $target_bill_exists);
            if (count($target_items) > 0) {
                $family_views[] = [
                    'sale_id' => $target_bill_id,
                    'label' => ($target_bill_exists ? 'Child #' : 'Open Child #') . $target_bill_id,
                    'kind' => $target_bill_exists ? 'child' : 'open_child',
                    'is_open' => !$target_bill_exists,
                    'is_active' => true,
                    'items' => $target_items,
                    'amount' => $target_bill_exists ? bill_repo_get_sale_amount($target_bill_id) : bill_repo_get_sale_print_total($target_bill_id),
                ];
                if (!$target_bill_exists) {
                    $active_child_sale_id = $target_bill_id;
                }
            }
        }

        if ($sale && $bill_mode === 'each') {
            $resolved_family_type = count($family_views) > 1 ? 'open_parent_each' : 'open_parent_each_no_child';
        } elseif ($is_open_bill_mode) {
            $resolved_family_type = $active_child_sale_id > 0 ? 'open_parent_last_with_child' : ($sale ? 'open_parent_last' : 'fresh_open');
        } elseif ($closed_parent) {
            $resolved_family_type = $bill_mode === 'last' ? 'closed_parent_last' : 'closed_parent_each';
        }

        return [
            'ok' => true,
            'message' => 'Counter modal loaded.',
            'sale_id' => $sale_id,
            'target_sale_id' => $target_bill_id,
            'is_open_bill_mode' => $is_open_bill_mode,
            'bill_mode' => $bill_mode,
            'family_total' => $family_total,
            'identifier_label' => $identifier_label,
            'bill_items' => $bill_items,
            'child_bills' => $child_bills,
            'family_type' => $resolved_family_type,
            'active_child_sale_id' => $active_child_sale_id,
            'family_views' => $family_views,
            'is_parent_locked' => bill_repo_is_parent_locked($sale_id),
            'counters' => bill_repo_fetch_active_counters(),
        ];
    }
}

if (!function_exists('bill_service_open_list_sales')) {
    function bill_service_open_list_sales($user_id, $tab = 'new', $q = '') {
        global $connection;

        $user_id = (int)$user_id;
        $tab = trim((string)$tab) === 'open' ? 'open' : 'new';
        $q = trim((string)$q);
        $shift = function_exists('get_column') ? (string)get_column('kp_shift', 'shift', "1") : '';
        $safe_shift = mysqli_real_escape_string($connection, $shift);
        $safe_q = mysqli_real_escape_string($connection, $q);
        $sales = [];

        if ($tab === 'open') {
            $where = [
                "sco.user_id='$user_id'",
                "sco.status=1",
                "(COALESCE(sco.shift, '')='' OR sco.shift='$safe_shift')"
            ];
            if ($q !== '') {
                $where[] = "(sco.sale_id LIKE '%$safe_q%' OR sco.identifier_label LIKE '%$safe_q%' OR sco.identifier_value LIKE '%$safe_q%')";
            }
            $sql = "SELECT sco.sale_id, sco.user_id, sco.identifier_label, sco.identifier_type, sco.identifier_hex,
                           sco.bill_mode, sco.counter_id, sco.print_cntr_id,
                           COALESCE(parent_sale.amount, 0) AS parent_amount,
                           COUNT(child.sale_id) AS child_count,
                           COALESCE(SUM(child.amount), 0) AS child_total,
                           GROUP_CONCAT(DISTINCT child.sale_id ORDER BY child.sale_id DESC SEPARATOR ',') AS child_ids,
                           COALESCE(MAX(active_child.sale_id), 0) AS current_open_child
                    FROM kp_shift_sales_open sco
                    LEFT JOIN kp_sales parent_sale ON parent_sale.sale_id = sco.sale_id
                    LEFT JOIN kp_sales child ON child.parent = sco.sale_id AND child.sale_id <> sco.sale_id
                    LEFT JOIN kp_shift_sales_open active_child ON active_child.parent_sale_id = sco.sale_id AND active_child.user_id = sco.user_id AND active_child.status = 1
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY sco.sale_id, sco.user_id, sco.identifier_label, sco.identifier_type, sco.identifier_hex,
                             sco.bill_mode, sco.counter_id, sco.print_cntr_id, parent_sale.amount
                    ORDER BY sco.sale_id DESC";
            $query = mysqli_query($connection, $sql);
            if ($query) {
                while ($row = mysqli_fetch_assoc($query)) {
                    $child_bills = [];
                    $childQuery = mysqli_query($connection, "SELECT sale_id, amount, counter FROM kp_sales WHERE parent='" . (int)$row['sale_id'] . "' AND sale_id<>" . (int)$row['sale_id'] . " ORDER BY sale_id DESC");
                    if ($childQuery) while ($child = mysqli_fetch_assoc($childQuery)) {
                        $childCounter = (int)($child['counter'] ?? 0);
                        $child_bills[] = [
                            'sale_id' => (int)($child['sale_id'] ?? 0),
                            'amount' => (float)($child['amount'] ?? 0),
                            'counter_id' => $childCounter,
                            'counter_name' => function_exists('get_counter') ? (string)get_counter($childCounter) : '',
                        ];
                    }
                    $sales[] = [
                        'sale_id' => (int)$row['sale_id'],
                        'identifier_label' => (string)($row['identifier_label'] ?? ''),
                        'identifier_type' => (string)($row['identifier_type'] ?? ''),
                        'identifier_hex' => (string)($row['identifier_hex'] ?? ''),
                        'bill_mode' => bill_clean_bill_mode($row['bill_mode'] ?? 'each'),
                        'counter_name' => function_exists('get_counter') ? (string)get_counter((int)($row['counter_id'] ?? 0)) : '',
                        'child_count' => (int)($row['child_count'] ?? 0),
                        'children_total' => (float)($row['child_total'] ?? 0),
                        'parent_amount' => (float)($row['parent_amount'] ?? 0),
                        'family_total' => (float)($row['parent_amount'] ?? 0) + (float)($row['child_total'] ?? 0),
                        'open_items_total' => repo_sum_shift_open_items((int)$row['sale_id']),
                        'child_ids' => trim((string)($row['child_ids'] ?? '')),
                        'child_bills' => $child_bills,
                        'current_open_child' => (int)($row['current_open_child'] ?? 0),
                        'need_identifier' => trim((string)($row['identifier_label'] ?? '')) === '',
                    ];
                }
            }

            $open_where = [
                "so.user_id='$user_id'",
                "so.status=1",
                "(COALESCE(so.shift, '')='' OR so.shift='$safe_shift')",
                "so.bill_mode='last'"
            ];
            if ($q !== '') {
                $open_where[] = "(so.sale_id LIKE '%$safe_q%' OR so.identifier_label LIKE '%$safe_q%' OR so.identifier_value LIKE '%$safe_q%')";
            }

            $open_sql = "SELECT so.sale_id, so.user_id, so.identifier_label, so.identifier_type, so.identifier_hex,
                                so.bill_mode, so.counter_id, so.print_cntr_id
                         FROM kp_shift_sales_open so
                         WHERE " . implode(' AND ', $open_where) . "
                         ORDER BY so.sale_id DESC";
            $open_query = mysqli_query($connection, $open_sql);
            if ($open_query) {
                while ($row = mysqli_fetch_assoc($open_query)) {
                    $sale_id = (int)($row['sale_id'] ?? 0);
                    $already_loaded = false;
                    foreach ($sales as $existing_sale) {
                        if ((int)($existing_sale['sale_id'] ?? 0) === $sale_id) {
                            $already_loaded = true;
                            break;
                        }
                    }
                    if ($already_loaded) {
                        continue;
                    }

                    $parent_amount = bill_repo_get_sale_print_total($sale_id);
                    if ($parent_amount <= 0) {
                        $parent_amount = bill_repo_get_sale_amount($sale_id);
                    }

                    $sales[] = [
                        'sale_id' => $sale_id,
                        'identifier_label' => (string)($row['identifier_label'] ?? ''),
                        'identifier_type' => (string)($row['identifier_type'] ?? ''),
                        'identifier_hex' => (string)($row['identifier_hex'] ?? ''),
                        'bill_mode' => bill_clean_bill_mode($row['bill_mode'] ?? 'last'),
                        'counter_name' => function_exists('get_counter') ? (string)get_counter((int)($row['counter_id'] ?? 0)) : '',
                        'child_count' => 0,
                        'children_total' => 0,
                        'parent_amount' => (float)$parent_amount,
                        'family_total' => (float)$parent_amount,
                        'open_items_total' => repo_sum_shift_open_items($sale_id),
                        'child_ids' => '',
                        'child_bills' => [],
                        'current_open_child' => 0,
                        'need_identifier' => trim((string)($row['identifier_label'] ?? '')) === '',
                    ];
                }
            }
        } else {
            $where = ["s.user_id='$user_id'", "s.shift='$safe_shift'"];
            if ($q !== '') {
                $where[] = "s.sale_id LIKE '%$safe_q%'";
            }
            $sql = "SELECT s.sale_id, s.amount, s.counter, s.bill_role,
                           COUNT(c.sale_id) AS child_count,
                           COALESCE(SUM(c.amount), 0) AS child_total
                    FROM kp_sales s
                    LEFT JOIN kp_sales c ON c.parent = s.sale_id AND c.sale_id <> s.sale_id
                    LEFT JOIN kp_shift_sales_open sco ON sco.sale_id = s.sale_id
                    WHERE " . implode(' AND ', $where) . " AND sco.sale_id IS NULL AND s.parent = 0
                    GROUP BY s.sale_id, s.amount, s.counter, s.bill_role
                    ORDER BY s.sale_id DESC";
            $query = mysqli_query($connection, $sql);
            if ($query) {
                while ($row = mysqli_fetch_assoc($query)) {
                    $existing_tag = repo_get_closed_opened_parent((int)$row['sale_id']);
                    $sales[] = [
                        'sale_id' => (int)$row['sale_id'],
                        'amount' => (float)($row['amount'] ?? 0),
                        'counter_name' => function_exists('get_counter') ? (string)get_counter((int)($row['counter'] ?? 0)) : '',
                        'is_parent' => ((int)($row['child_count'] ?? 0) > 0),
                        'child_count' => (int)($row['child_count'] ?? 0),
                        'family_total' => (float)($row['amount'] ?? 0) + (float)($row['child_total'] ?? 0),
                        'bill_role' => (string)($row['bill_role'] ?? 'closed'),
                        'need_identifier' => trim((string)($existing_tag['identifier_label'] ?? '')) === '',
                    ];
                }
            }
        }

        return [
            'ok' => true,
            'message' => 'Sales list loaded.',
            'tab' => $tab,
            'sales' => $sales,
        ];
    }
}

if (!function_exists('bill_service_open_page_bootstrap')) {
    function bill_service_open_page_bootstrap($user_id, $tab = 'open') {
        $user_id = (int)$user_id;
        $tab = trim((string)$tab) === 'new' ? 'new' : 'open';
        $username = function_exists('get_column') ? (string)get_column('kp_users', 'username', "user_id='$user_id'") : '';
        $shift = function_exists('get_column') ? (string)get_column('kp_shift', 'shift', "1") : '';
        $recent_count_where = "shift='$shift' AND user_id='$user_id'";
        $recent_count = function_exists('column_count') ? (int)column_count('kp_sales', 'sale_id', $recent_count_where) : 0;

        return [
            'ok' => true,
            'message' => 'Open page loaded.',
            'username' => $username,
            'recent_count' => $recent_count,
            'default_tab' => $tab,
            'page_title' => 'Open Orders',
        ];
    }
}

if (!function_exists('bill_service_open_load_identifier_modal')) {
    function bill_service_open_load_identifier_modal($user_id, $sale_id, $counter_id = 0, $bill_mode = 'each', $fresh_open = false) {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $counter_id = (int)$counter_id;
        $bill_mode = bill_clean_bill_mode($bill_mode);
        $fresh_open = (bool)$fresh_open;

        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected for identifier setup.'];
        }

        $sale = repo_get_sales_header($sale_id);
        $open_parent = repo_get_closed_opened_parent($sale_id);
        $sales_open_row = bill_repo_get_sales_open_registry($sale_id, $user_id);
        $owner_id = $sale ? (int)$sale['user_id'] : (int)($open_parent['user_id'] ?? ($sales_open_row['user_id'] ?? 0));
        if ((!$sale && !$open_parent && !$sales_open_row) || ($owner_id > 0 && $owner_id !== $user_id)) {
            return ['ok' => false, 'message' => 'You can only continue your own bills.'];
        }

        $colors = bill_repo_fetch_open_colors();
        $target_sale_id = $sale_id;
        $target_sale_exists = bill_repo_sale_exists($target_sale_id);
        $items = [];
        global $connection;
        $items_q = mysqli_query(
            $connection,
            "SELECT item_id, attr_id, SUM(qty) AS qty, SUM(total) AS total
             FROM " . ($target_sale_exists ? 'kp_sale_items' : 'kp_sale_print') . "
             WHERE sale_id='$target_sale_id'
             GROUP BY item_id, attr_id
             HAVING SUM(qty) > 0
             ORDER BY item_id ASC"
        );
        if ($items_q) {
            while ($row = mysqli_fetch_assoc($items_q)) {
                $item_name = bill_repo_get_item_name((int)$row['item_id']);
                if ((int)$row['attr_id'] > 0) {
                    $attr_name = bill_repo_get_attribute_name((int)$row['attr_id']);
                    if ($attr_name !== '') {
                        $item_name .= ' (' . $attr_name . ')';
                    }
                }
                $items[] = [
                    'name' => $item_name,
                    'qty' => (float)($row['qty'] ?? 0),
                    'total' => (float)($row['total'] ?? 0),
                ];
            }
        }

        return [
            'ok' => true,
            'message' => 'Identifier modal loaded.',
            'sale_id' => $sale_id,
            'counter_id' => $counter_id,
            'bill_mode' => $bill_mode,
            'fresh_open' => $fresh_open,
            'colors' => $colors,
            'items' => $items,
            'items_total' => array_sum(array_map(function ($item) { return (float)$item['total']; }, $items)),
        ];
    }
}

if (!function_exists('bill_service_open_save_identifier')) {
    function bill_service_open_save_identifier($user_id, $params = []) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)($params['sale_id'] ?? 0);
        $bill_mode = bill_clean_bill_mode($params['bill_mode'] ?? 'each');
        $fresh_open = !empty($params['fresh_open']);
        $type = trim((string)($params['type'] ?? 'name'));
        $value = trim((string)($params['value'] ?? ''));
        $label = trim((string)($params['label'] ?? ''));
        $hex = trim((string)($params['hex'] ?? ''));
        $save_preset = !empty($params['save_preset']);

        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.'];
        }

        $identifier = [
            'type' => $type === 'color' ? 'color' : 'name',
            'value' => $value,
            'label' => $type === 'color' ? ($label !== '' ? $label : $value) : $value,
            'hex' => $type === 'color' ? $hex : '',
        ];
        if (trim((string)$identifier['label']) === '') {
            return ['ok' => false, 'message' => 'Add an identifier before continuing.'];
        }

        if ($fresh_open) {
            return [
                'ok' => true,
                'message' => 'Identifier ready.',
                'identifier' => $identifier,
                'redirect' => '',
                'mode' => $bill_mode,
                'fresh_open' => true,
            ];
        }

        $parent_open = repo_get_closed_opened_parent($sale_id);
        $sales_open_row = bill_repo_get_sales_open_registry($sale_id, $user_id);
        $sale_header = repo_get_sales_header($sale_id);
        if (!$parent_open && !$sales_open_row && !$sale_header) {
            return ['ok' => false, 'message' => 'The selected bill could not be identified right now.'];
        }

        if ($sales_open_row) {
            if (!repo_sync_sales_open_identifier($sale_id, $user_id, [
                'identifier_type' => $identifier['type'],
                'identifier_value' => $identifier['value'],
                'identifier_label' => $identifier['label'],
                'identifier_hex' => $identifier['hex'],
            ], $bill_mode)) {
                return ['ok' => false, 'message' => 'Unable to update open bill identifier right now.'];
            }
        } else {
            if (!repo_register_parent_closed_opened($sale_id, $bill_mode, [
                'identifier_type' => $identifier['type'],
                'identifier_value' => $identifier['value'],
                'identifier_label' => $identifier['label'],
                'identifier_hex' => $identifier['hex'],
            ], $user_id)) {
                return ['ok' => false, 'message' => 'Unable to update closed bill identifier right now.'];
            }
        }

        if ($save_preset && $identifier['type'] === 'name' && $identifier['label'] !== '') {
            $preset_name = mysqli_real_escape_string($connection, $identifier['label']);
            mysqli_query(
                $connection,
                "INSERT INTO kp_open_identifier_preset(user_id, preset_name, last_used_at)
                 VALUES('$user_id', '$preset_name', NOW())
                 ON DUPLICATE KEY UPDATE last_used_at=NOW()"
            );
        }

        $_SESSION['open_continue_draft'] = array_merge($_SESSION['open_continue_draft'] ?? [], [
            'parent_sale_id' => $sale_id,
            'type' => $identifier['type'],
            'value' => $identifier['value'],
            'label' => $identifier['label'],
            'hex' => $identifier['hex'],
            'bill_mode' => $bill_mode,
            'fresh_open' => $fresh_open ? 1 : 0,
            'saved_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'ok' => true,
            'message' => 'Identifier saved.',
            'identifier' => $identifier,
            'redirect' => 'index.php?tab=open',
            'mode' => $bill_mode,
            'fresh_open' => false,
        ];
    }
}

if (!function_exists('bill_service_resolve_counter_id')) {
    function bill_service_resolve_counter_id($context, $params = []) {
        $params = is_array($params) ? $params : [];
        $context = is_array($context) ? $context : [];
        $counter_id = (int)($params['bill_cntr'] ?? 0);
        if ($counter_id < 1) $counter_id = (int)($params['counter'] ?? 0);
        if ($counter_id < 1) $counter_id = (int)($context['counter_id'] ?? 0);
        if ($counter_id < 1) $counter_id = (int)($context['status_row']['counter_id'] ?? $context['status_row']['counter'] ?? 0);
        if ($counter_id < 1 && function_exists('repo_get_sale_status_for_sale')) {
            $status = repo_get_sale_status_for_sale((int)($context['user_id'] ?? 0), (int)($context['sale_id'] ?? 0));
            $counter_id = (int)($status['counter_id'] ?? $status['counter'] ?? 0);
        }
        return $counter_id > 0 ? $counter_id : 0;
    }
}

if (!function_exists('bill_service_queue_print_job')) {
    function bill_service_queue_print_job($sale_id, $user_id, $counter_id, $print_type, $label, $route_path, $route_params = []) {
        global $connection;

        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $counter_id = (int)$counter_id;
        $print_type = trim((string)$print_type);
        $label = trim((string)$label);
        $route_path = trim((string)$route_path);
        $route_params = is_array($route_params) ? $route_params : [];
        $payload_header = is_array($route_params['_payload_header'] ?? null) ? $route_params['_payload_header'] : [];
        unset($route_params['_payload_header']);

        if ($sale_id < 1 || $user_id < 1 || $counter_id < 1 || $print_type === '' || $route_path === '') {
            bill_log(
                'PRINT_QUEUE_SKIP',
                'sale_id=' . $sale_id
                . '|user_id=' . $user_id
                . '|counter_id=' . $counter_id
                . '|print_type=' . $print_type
                . '|route_path=' . $route_path
            );
            return 0;
        }

        $route_query = '';
        if (!empty($route_params)) {
            $route_query = http_build_query($route_params);
        }

        $queue_id = repo_print_queue([
            'sale_id' => $sale_id,
            'ref_id' => $sale_id,
            'print_type' => $print_type,
            'label' => $label,
            'cntr_id' => $counter_id,
            'route_path' => $route_path,
            'route_query' => $route_query,
            'payload_header' => $payload_header,
            'requested_by' => $user_id,
        ]);

        if ($queue_id > 0) {
            bill_log(
                'PRINT_QUEUE_OK',
                'queue_id=' . $queue_id
                . '|sale_id=' . $sale_id
                . '|user_id=' . $user_id
                . '|counter_id=' . $counter_id
                . '|print_type=' . $print_type
                . '|route_path=' . $route_path
                . '|route_query=' . $route_query
            );
        } else {
            bill_log(
                'PRINT_QUEUE_ERROR',
                'sale_id=' . $sale_id
                . '|user_id=' . $user_id
                . '|counter_id=' . $counter_id
                . '|print_type=' . $print_type
                . '|route_path=' . $route_path
                . '|route_query=' . $route_query
                . '|db_error=' . mysqli_error($connection)
            );
        }

        return (int)$queue_id;
    }
}

if (!function_exists('bill_service_detect_print_departments_from_items')) {
    function bill_service_detect_print_departments_from_items($items) {
        $items = is_array($items) ? $items : [];
        $departments = [
            'bot' => false,
            'kot' => false,
            'got' => false,
        ];

        foreach ($items as $item) {
            $dpt = (int)($item['dpt'] ?? 0);
            if ($dpt === 1) {
                $departments['bot'] = true;
            } elseif ($dpt === 2) {
                $departments['kot'] = true;
            } elseif ($dpt === 3) {
                $departments['got'] = true;
            }
        }

        return $departments;
    }
}

if (!function_exists('bill_service_detect_print_departments')) {
    function bill_service_detect_print_departments($sale_id, $session_flag = null) {
        return bill_service_detect_print_departments_from_items(repo_fetch_sale_print($sale_id, $session_flag));
    }
}

if (!function_exists('bill_service_queue_unprinted_tickets')) {
    function bill_service_queue_snapshot_job($sale_id, $user_id, $counter_id, $type, $items, $sale_counter_id = 0) {
        global $connection;
        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $counter_id = (int)$counter_id;
        $type = strtoupper(trim((string)$type));
        $items = is_array($items) ? array_values($items) : [];
        if ($sale_id < 1 || $user_id < 1 || $counter_id < 1 || !$items) return 0;
        $amount = 0.0;
        foreach ($items as &$item) {
            $item = is_array($item) ? $item : [];
            $item['name'] = (string)($item['name'] ?? $item['item_name'] ?? ('Item ' . (int)($item['item_id'] ?? 0)));
            $item['qty'] = (float)($item['qty'] ?? $item['quantity'] ?? 0);
            $item['price'] = (float)($item['price'] ?? $item['unit_price'] ?? 0);
            $item['total'] = (float)($item['total'] ?? $item['amount'] ?? ($item['qty'] * $item['price']));
            $amount += $item['total'];
        }
        unset($item);
        $sale = function_exists('repo_get_sales_header') ? (repo_get_sales_header($sale_id) ?: []) : [];
        $waiter_id = (int)($sale['user_id'] ?? $sale['waiter_id'] ?? $user_id);
        $header = [
            'sale_id' => $sale_id,
            'counter_id' => $sale_counter_id > 0 ? $sale_counter_id : $counter_id,
            'counter_name' => function_exists('get_counter') ? (string)get_counter($sale_counter_id > 0 ? $sale_counter_id : $counter_id) : '',
            'waiter_id' => $waiter_id,
            'waiter_name' => function_exists('get_user') ? (string)get_user($waiter_id) : '',
            'served_by' => function_exists('get_user') ? (string)get_user($waiter_id) : '',
            'shift' => (string)($sale['shift'] ?? repo_get_shift()),
            'shift_date' => (string)($sale['shift'] ?? repo_get_shift()),
            'order_time' => date('Y-m-d H:i:s'),
            'print_time' => date('Y-m-d H:i:s'),
        ];
        $company = mysqli_query($connection, "SELECT company_name,company_tag,address,phone1,phone2,image_path,thermal_logo_path FROM kp_settings_gen LIMIT 1");
        if ($company && ($company_row = mysqli_fetch_assoc($company))) {
            $header['company_name'] = (string)($company_row['company_name'] ?? '');
            $header['company_tagline'] = (string)($company_row['company_tag'] ?? '');
            $header['company_address'] = (string)($company_row['address'] ?? '');
            $header['company_phone'] = trim((string)($company_row['phone1'] ?? '') . ' ' . (string)($company_row['phone2'] ?? ''));
            $header['logo_src'] = (string)($company_row['thermal_logo_path'] ?? $company_row['image_path'] ?? '');
        }
        $payload = json_encode([
            'payload_version' => 1,
            'document_type' => $type,
            'title' => $type,
            'items' => $items,
            'summary' => ['item_count' => count($items), 'amount' => $amount, 'total' => $amount],
            'header' => $header,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) return 0;
        $shift = mysqli_real_escape_string($connection, (string)repo_get_shift());
        $payload = mysqli_real_escape_string($connection, $payload);
        $label = mysqli_real_escape_string($connection, $type);
        $sql = "INSERT INTO kp_print_queue
            (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,print_attempts,last_error,shift,payload_json,items_json)
            VALUES ('$sale_id','$sale_id','$type','$label','$counter_id','', '', '$user_id',0,0,0,'','$shift','$payload','')";
        return mysqli_query($connection, $sql) ? (int)mysqli_insert_id($connection) : 0;
    }

    function bill_service_queue_unprinted_tickets($sale_id, $user_id, $print_counter_id, $options = []) {
        $options = is_array($options) ? $options : [];

        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $print_counter_id = (int)$print_counter_id;
        $sale_counter_id = (int)($options['sale_counter_id'] ?? $print_counter_id);
        $include_cashier_order = !array_key_exists('include_cashier_order', $options) || !empty($options['include_cashier_order']);
        $delta_only = !empty($options['delta_only']);
        $session_flag = array_key_exists('session_flag', $options) ? $options['session_flag'] : null;
        $live_source = !empty($options['live_source']);
        $open_source = !empty($options['open_source']);
        $fallback_bot = !empty($options['fallback_bot']);
        $delta_rows = $delta_only
            ? ($live_source ? repo_fetch_sale_print_grouped($sale_id, $session_flag, 'kp_sale_items_live') : ($open_source ? bill_service_fetch_shift_open_items($sale_id, $session_flag) : repo_fetch_sale_print($sale_id, $session_flag)))
            : [];
        $departments = $options['departments'] ?? bill_service_detect_print_departments($sale_id, $session_flag);

        if ($sale_id < 1 || $user_id < 1 || $print_counter_id < 1) {
            return [];
        }

        if ($delta_only && count($delta_rows) === 0) {
            bill_log(
                'PRINT_QUEUE_DELTA_EMPTY',
                'sale_id=' . $sale_id
                . '|user_id=' . $user_id
                . '|print_counter_id=' . $print_counter_id
                . '|session_flag=' . (string)$session_flag
            );
            return [];
        }

        // V1 ticket jobs carry their complete immutable item snapshot in
        // kp_print_queue.payload_json. No secondary ticket-batch table is
        // needed by Chaba.

        $jobs_to_queue = [];
        if ($include_cashier_order) {
            $jobs_to_queue[] = 'cashier_order';
        }
        if (!empty($departments['bot'])) {
            $jobs_to_queue[] = 'bot';
        }
        if (!empty($departments['kot'])) {
            $jobs_to_queue[] = 'kot';
        }
        if (!empty($departments['got'])) {
            $jobs_to_queue[] = 'got';
        }

        $queued_jobs = [];
        foreach ($jobs_to_queue as $type) {
            $counter_id = $print_counter_id;
            if ($type === 'bot' && $sale_counter_id > 0) {
                $counter_id = $sale_counter_id;
            } elseif ($type === 'kot') {
                $kitchen_counter = (int)get_column('kp_counters', 'cntr_id', "dpt=2 AND kit_default=1");
                if ($kitchen_counter < 1) {
                    $kitchen_counter = (int)get_column('kp_counters', 'cntr_id', "dpt=2");
                }
                if ($kitchen_counter > 0) {
                    $counter_id = $kitchen_counter;
                }
            } elseif ($type === 'got') {
                $grill_counter = (int)get_column('kp_counters', 'cntr_id', "dpt=3");
                if ($grill_counter > 0) {
                    $counter_id = $grill_counter;
                }
            }

            $route_map = [
                'cashier_order' => 'render_cashier_order.php',
                'bot' => 'render_bot.php',
                'kot' => 'render_kot.php',
                'got' => 'render_got.php',
                'bill' => 'render_bill.php',
            ];
            $route_path = $route_map[$type] ?? '';
            if ($route_path === '') {
                continue;
            }

            $route_params = ['sale_id' => $sale_id];
            if ($delta_only && $type !== 'bill') {
                $route_params['delta_only'] = 1;
            }

            $queue_id = 0;
            if ($delta_only && $type !== 'cashier_order' && $delta_rows) {
                $queue_items = [];
                foreach ($delta_rows as $row) {
                    $row_dpt = (int)($row['dpt'] ?? 0);
                    $expected_dpt = (int)($type === 'bot' ? 1 : ($type === 'kot' ? 2 : 3));
                    if (!($fallback_bot && $type === 'bot' && $row_dpt === 0) && $row_dpt !== $expected_dpt) continue;
                    if ($fallback_bot && $type === 'bot' && $row_dpt === 0) {
                        $row['dpt'] = 1;
                    }
                    $queue_items[] = $row;
                }
                $queue_id = bill_service_queue_snapshot_job($sale_id, $user_id, $counter_id, strtoupper($type), $queue_items, $sale_counter_id);
            } else {
                $queue_id = bill_service_queue_print_job(
                    $sale_id,
                    $user_id,
                    $counter_id,
                    $type,
                    strtoupper(str_replace('_', ' ', $type)),
                    $route_path,
                    $route_params
                );
            }

            if ($queue_id > 0) {
                $queued_jobs[] = [
                    'queue_id' => $queue_id,
                    'type' => $type,
                    'counter_id' => $counter_id,
                ];
            }
        }

        return $queued_jobs;
    }
}

if (!function_exists('bill_service_queue_full_bill')) {
    function bill_service_queue_full_bill($sale_id, $user_id, $print_counter_id, $options = []) {
        $options = is_array($options) ? $options : [];

        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $print_counter_id = (int)$print_counter_id;
        $delta_only = !empty($options['delta_only']);

        if ($sale_id < 1 || $user_id < 1 || $print_counter_id < 1) {
            return [];
        }

        $route_params = ['sale_id' => $sale_id];
        if (is_array($options['payload_header'] ?? null) && $options['payload_header']) {
            $route_params['_payload_header'] = $options['payload_header'];
        }
        if ($delta_only) {
            $route_params['delta_only'] = 1;
        }

        $queue_id = bill_service_queue_print_job(
            $sale_id,
            $user_id,
            $print_counter_id,
            'bill',
            'BILL',
            'render_bill.php',
            $route_params
        );

        if ($queue_id < 1) {
            return [];
        }

        return [[
            'queue_id' => $queue_id,
            'type' => 'bill',
            'counter_id' => $print_counter_id,
        ]];
    }
}

if (!function_exists('bill_service_queue_order_tickets')) {
    function bill_service_queue_order_tickets($sale_id, $user_id, $print_counter_id, $options = []) {
        return bill_service_queue_unprinted_tickets($sale_id, $user_id, $print_counter_id, $options);
    }
}

if (!function_exists('bill_service_queue_customer_bill')) {
    function bill_service_queue_customer_bill($sale_id, $user_id, $print_counter_id, $options = []) {
        return bill_service_queue_full_bill($sale_id, $user_id, $print_counter_id, $options);
    }
}

if (!function_exists('bill_service_queue_open_prints')) {
    function bill_service_mark_shift_open_items_sent($sale_id) {
        global $connection;
        $sale_id = (int)$sale_id;
        return $sale_id > 0
            && mysqli_query($connection, "UPDATE kp_shift_sale_open_items SET session_flag=1 WHERE sale_id='$sale_id' AND session_flag=2") !== false;
    }

    function bill_service_fetch_shift_open_items($sale_id, $session_flag = null) {
        global $connection;
        $sale_id = (int)$sale_id;
        $where = $session_flag === null ? '' : " AND session_flag='" . (int)$session_flag . "'";
        $rows = [];
        $result = mysqli_query($connection, "SELECT item_id,qty,price,total,dpt,counter,stock_counter,attr_id,accompaniment_json FROM kp_shift_sale_open_items WHERE sale_id='$sale_id'$where ORDER BY id ASC");
        if ($result) while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
        return $rows;
    }

    function bill_service_snapshot_shift_open_items($sale_id, $user_id) {
        global $connection;
        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        if ($sale_id < 1 || $user_id < 1) return false;

        $openId = (int)get_column('kp_shift_sales_open', 'id', "sale_id='$sale_id' AND user_id='$user_id' AND status=1 ORDER BY id DESC LIMIT 1");
        if ($openId < 1) $openId = 0;
        mysqli_query($connection, "UPDATE kp_shift_sale_open_items o
            INNER JOIN kp_sale_items_live l ON l.id=o.source_live_id
            SET o.open_id='$openId', o.qty=l.qty, o.price=l.price, o.total=l.total,
                o.dpt=l.dpt, o.shift=l.shift, o.counter=l.counter, o.stock_counter=l.stock_counter,
                o.attr_id=l.attr_id, o.accompaniment_json=l.accompaniment_json
            WHERE l.sale_id='$sale_id' AND l.user_id='$user_id'");
        $sql = "INSERT INTO kp_shift_sale_open_items
            (source_live_id,sale_id,open_id,item_id,user_id,qty,price,total,dpt,shift,counter,stock_counter,attr_id,accompaniment_json,session_flag)
            SELECT l.id,l.sale_id,'$openId',l.item_id,l.user_id,l.qty,l.price,l.total,l.dpt,l.shift,l.counter,l.stock_counter,l.attr_id,l.accompaniment_json,2
            FROM kp_sale_items_live l
            WHERE l.sale_id='$sale_id' AND l.user_id='$user_id' AND l.session_flag=2
              AND NOT EXISTS (
                SELECT 1 FROM kp_shift_sale_open_items o
                WHERE o.sale_id=l.sale_id AND o.source_live_id=l.id
              )";
        return mysqli_query($connection, $sql) !== false;
    }

    function bill_service_queue_open_prints($context, $params = [], $scenario = '') {
        global $connection;
        $params = is_array($params) ? $params : [];
        $sale_id = (int)($context['sale_id'] ?? 0);
        $user_id = (int)($context['user_id'] ?? 0);
        $counter_id = bill_service_resolve_counter_id($context, $params);
        // Both fresh and continued LAST orders queue only the current
        // session-flagged delta rows. Previously sent rows are session_flag=1.
        $session_flag = 2;

        if (in_array($scenario, ['fresh_open_last', 'open_active_last', 'open_active_each'], true)
            && !bill_service_snapshot_shift_open_items($sale_id, $user_id)) {
            throw new RuntimeException('Could not create the shift open-order item snapshot.');
        }

        $live_items = repo_fetch_sale_print_grouped($sale_id, 2, 'kp_sale_items_live');
        $departments = bill_service_detect_print_departments_from_items($live_items);
        $open_bot_fallback = in_array($scenario, ['fresh_open_last', 'open_active_last', 'fresh_open_each'], true);
        if ($open_bot_fallback && !$departments['bot'] && !$departments['kot'] && !$departments['got'] && $live_items) {
            $departments['bot'] = true;
        }

        $queued_jobs = bill_service_queue_unprinted_tickets($sale_id, $user_id, $counter_id, [
            'include_cashier_order' => false,
            'delta_only' => true,
            'session_flag' => $session_flag,
            'sale_counter_id' => (int)($context['counter_id'] ?? 0),
            'live_source' => true,
            'departments' => $departments,
            'fallback_bot' => $open_bot_fallback,
        ]);

        if ($scenario !== 'fresh_open_each') {
            if (!bill_service_snapshot_shift_open_items($sale_id, $user_id)) {
                throw new RuntimeException('Could not move live items to shift open items.');
            }
            if (!mysqli_query($connection, "DELETE FROM kp_sale_items_live WHERE sale_id='" . (int)$sale_id . "' AND session_flag=2")) {
                throw new RuntimeException('Could not remove moved live open items.');
            }
        }

        if (in_array($scenario, ['fresh_open_each', 'open_active_each'], true)) {
            $queued_jobs = array_merge($queued_jobs, bill_service_queue_full_bill($sale_id, $user_id, $counter_id, [
                'payload_header' => bill_service_open_each_bill_header($context),
            ]));
        }

        return $queued_jobs;
    }
}

if (!function_exists('bill_service_queue_close_prints')) {
    function bill_service_queue_close_prints($context, $params = [], $scenario = '') {
        $params = is_array($params) ? $params : [];
        $sale_id = (int)($context['sale_id'] ?? 0);
        $user_id = (int)($context['user_id'] ?? 0);
        $counter_id = bill_service_resolve_counter_id($context, $params);
        $is_final_close = !empty($params['close_current_bill'])
            || !empty($params['print_final'])
            || (($params['final_verdict'] ?? '') === 'close')
            || !in_array($scenario, ['open_active_last'], true);

        $queued_jobs = bill_service_queue_unprinted_tickets($sale_id, $user_id, $counter_id, [
            'include_cashier_order' => false,
            'delta_only' => in_array($scenario, ['open_active_last'], true),
            'session_flag' => in_array($scenario, ['open_active_last'], true) ? 2 : null,
            'sale_counter_id' => (int)($context['counter_id'] ?? 0),
        ]);

        if (!$is_final_close) {
            return $queued_jobs;
        }

        return array_merge($queued_jobs, bill_service_queue_full_bill($sale_id, $user_id, $counter_id, [
            'delta_only' => false,
            'payload_header' => bill_service_open_each_bill_header($context),
        ]));
    }
}

if (!function_exists('bill_service_open_each_bill_header')) {
    function bill_service_open_each_bill_header($context) {
        global $connection;
        $context = is_array($context) ? $context : [];
        $status = is_array($context['status_row'] ?? null) ? $context['status_row'] : [];
        $sale_id = (int)($context['sale_id'] ?? 0);
        // The context may have been created before initialization updated the
        // position. Always use the persisted status for the printable header.
        $fresh_status = repo_get_sale_status_for_sale((int)($context['user_id'] ?? 0), $sale_id);
        if (is_array($fresh_status)) {
            $status = array_replace($status, $fresh_status);
        }
        $parent_id = (int)($status['parent_sale_id'] ?? 0);
        $mode = bill_clean_bill_mode($status['mode'] ?? $status['bill_mode'] ?? '');
        if ($sale_id < 1 || $parent_id < 1 || $mode !== 'each' || $sale_id === $parent_id) return [];

        $previous_total = (float)($status['previous_balance'] ?? 0);
        $query = mysqli_query($connection, "SELECT COALESCE(SUM(amount),0) total FROM kp_sales WHERE (sale_id='" . $parent_id . "' OR parent='" . $parent_id . "') AND sale_id<>'" . $sale_id . "'");
        if ($previous_total <= 0 && $query && ($row = mysqli_fetch_assoc($query))) $previous_total = (float)($row['total'] ?? 0);

        return [
            'bill_mode' => 'each',
            'parent_sale_id' => $parent_id,
            'previous_bill_sale_id' => (int)($status['previous_sale_id'] ?? $parent_id),
            'previous_total' => $previous_total,
            'bill_position' => (int)($status['bill_position'] ?? 0),
            'bill_count' => (int)($status['bill_count'] ?? $status['bill_position'] ?? 0),
        ];
    }
}

if (!function_exists('bill_service_finalize_sale_steps')) {
    function bill_service_finalize_sale_steps($context, $params = [], $options = []) {
        $params = is_array($params) ? $params : [];
        $options = is_array($options) ? $options : [];

        $user_id = (int)($context['user_id'] ?? 0);
        $sale_id = (int)($context['sale_id'] ?? 0);
        $status_row = $context['status_row'] ?? [];
        $parent_sale_id = (int)($options['parent_sale_id'] ?? ($status_row['parent_sale_id'] ?? 0));
        $counter_id = bill_service_resolve_counter_id($context, $params);
        $shift = repo_get_shift();
        $source_open = !empty($options['source_open_items']);
        $amount = $source_open ? repo_sum_shift_open_items($sale_id) : repo_sum_sale_print($sale_id);

        if ($sale_id < 1) {
            throw new RuntimeException('No active sale selected.');
        }
        if ($counter_id < 1) {
            throw new RuntimeException('Counter not found for current user.');
        }
        if ($shift === '') {
            throw new RuntimeException('Shift not found.');
        }
        if ($amount <= 0) {
            throw new RuntimeException('Cart is empty.');
        }

        if (!repo_insert_sale([
            'sale_id' => $sale_id,
            'user_id' => $user_id,
            'shift' => $shift,
            'amount' => $amount,
            'counter' => (int)($context['counter_id'] ?? 0),
            'bill_counter' => $counter_id,
            'parent_sale_id' => $parent_sale_id,
        ])) {
            throw new RuntimeException('Could not insert sale.');
        }

        $items_saved = $source_open
            ? repo_insert_sale_items_from_shift_open($sale_id)
            : repo_insert_sale_items($sale_id);
        if (!$items_saved) {
            throw new RuntimeException('Could not insert sale items.');
        }

        if (!repo_update_sale_family_context($sale_id, $parent_sale_id)) {
            throw new RuntimeException('Could not update sale family context.');
        }

        if (!empty($options['clear_print'])) {
            $print_rows_before_clear = count(repo_fetch_sale_print($sale_id));
            bill_log(
                'CLEAR_SALE_PRINT_START',
                'sale_id=' . $sale_id
                . '|rows=' . $print_rows_before_clear
                . '|user_id=' . $user_id
            );

            if (!repo_clear_sale_print($sale_id)) {
                bill_log(
                    'CLEAR_SALE_PRINT_ERROR',
                    'sale_id=' . $sale_id
                    . '|rows=' . $print_rows_before_clear
                    . '|user_id=' . $user_id
                );
                throw new RuntimeException('Could not clear sale print.');
            }

            $print_rows_after_clear = count(repo_fetch_sale_print($sale_id));
            bill_log(
                'CLEAR_SALE_PRINT_OK',
                'sale_id=' . $sale_id
                . '|rows_before=' . $print_rows_before_clear
                . '|rows_after=' . $print_rows_after_clear
                . '|user_id=' . $user_id
            );
        }

        if (!empty($options['clear_status']) && !repo_clear_sale_status($user_id, $sale_id)) {
            throw new RuntimeException('Could not clear sale status.');
        }

        if (!empty($options['clear_sale_open'])) {
            $clear_parent_key = $parent_sale_id > 0 ? $parent_sale_id : $sale_id;
            if (!repo_clear_sale_open($sale_id, $clear_parent_key)) {
                throw new RuntimeException('Could not clear sales open registry.');
            }
        }

        if (!empty($options['clear_sale_closed_open']) && $parent_sale_id > 0 && !repo_clear_sale_closed_open($parent_sale_id)) {
            throw new RuntimeException('Could not clear closed-open registry.');
        }

        if (!empty($options['reset_thesion']) && !repo_reset_thesion_front($user_id)) {
            throw new RuntimeException('Could not reset active session.');
        }

        return [
            'sale_id' => $sale_id,
            'amount' => (float)$amount,
            'counter_id' => $counter_id,
            'parent_sale_id' => $parent_sale_id,
        ];
    }
}

if (!function_exists('bill_service_resolve_closed_parent_child')) {
    function bill_service_resolve_closed_parent_child($user_id, $parent_sale_id, $counter_id, $params = [], $options = []) {
        global $connection;

        $params = is_array($params) ? $params : [];
        $options = is_array($options) ? $options : [];
        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        $counter_id = (int)$counter_id;

        $closed_parent = $options['closed_parent'] ?? repo_get_closed_opened_parent($parent_sale_id);
        if (!$closed_parent) {
            throw new RuntimeException('Open parent registration was not found.');
        }
        if ($user_id < 1 || $parent_sale_id < 1 || $counter_id < 1) {
            throw new RuntimeException('Invalid closed-open parent continuation data.');
        }

        $mode = bill_clean_bill_mode($closed_parent['bill_mode'] ?? $closed_parent['mode'] ?? 'last');
        if (!in_array($mode, ['each', 'last'], true)) {
            throw new RuntimeException('Saved parent mode is invalid.');
        }

        $sales_open_row = bill_repo_get_sales_open_registry($parent_sale_id, $user_id);
        $child_sale_id = 0;
        $created = false;
        $child_position = 0;

        if ($sales_open_row) {
            $child_sale_id = (int)($sales_open_row['child_sale_id'] ?? 0);
            if ($child_sale_id < 1) {
                $child_sale_id = (int)($sales_open_row['sale_id'] ?? 0);
            }
        }

        // Reopening a historical closed bill must always create a new sale.
        // Never reuse the closed parent itself, even if an older registry row
        // incorrectly points its sale_id back to that parent.
        if ($child_sale_id === $parent_sale_id) {
            $child_sale_id = 0;
        }

        if ($child_sale_id < 1) {
            $child_sale_id = bill_repo_last_id($user_id);
            if ($child_sale_id < 1) {
                throw new RuntimeException('Could not create child sale session.');
            }
            $created = true;
        }

        // Every closed-open EACH continuation is an independent bill. Allocate
        // its round number before activating the child so the status row can be
        // used as the authoritative family state.
        if ($mode === 'each') {
            $position_result = mysqli_query(
                $connection,
                "SELECT COALESCE(MAX(bill_position), 0) AS last_position
                 FROM kp_sale_status
                 WHERE user_id='" . (int)$user_id . "'
                   AND parent_sale_id='" . (int)$parent_sale_id . "'"
            );
            $position_row = $position_result ? mysqli_fetch_assoc($position_result) : null;
            $child_position = ((int)($position_row['last_position'] ?? 0)) + 1;
        }

        // Every continuing child is an active open bill while it is being
        // edited. The bill mode remains EACH/LAST; status 3 is the active
        // open-session marker for both modes.
        $child_status = 3;
        if (!repo_set_sale_status($user_id, $child_sale_id, $child_status, $mode, 1, $parent_sale_id)) {
            throw new RuntimeException('Could not start child session: ' . mysqli_error($connection));
        }
        if ($mode === 'each' && !repo_set_sale_position($user_id, $child_sale_id, $child_position)) {
            throw new RuntimeException('Could not assign child bill position: ' . mysqli_error($connection));
        }
        if ($mode === 'each') {
            $previous_sale_id = 0;
            $previous_query = mysqli_query($connection, "SELECT sale_id FROM kp_sales WHERE sale_id='" . $parent_sale_id . "' OR parent='" . $parent_sale_id . "' ORDER BY sale_id DESC");
            while ($previous_query && ($previous_row = mysqli_fetch_assoc($previous_query))) {
                if ((int)$previous_row['sale_id'] === $child_sale_id) continue;
                $previous_sale_id = (int)$previous_row['sale_id'];
                break;
            }
            $previous_balance = (float)bill_repo_get_sale_amount($parent_sale_id);
            $previous_balance += (float)column_total('kp_sales', 'amount', "parent='" . $parent_sale_id . "' AND sale_id<'" . $child_sale_id . "'");
            if (!repo_set_sale_lineage($user_id, $child_sale_id, $child_position, $previous_sale_id, $previous_balance, $child_position)) {
                throw new RuntimeException('Could not preserve EACH bill lineage: ' . mysqli_error($connection));
            }
        }

        if ($mode === 'last') {
            $print_counter_id = (int)($options['print_counter_id'] ?? $counter_id);
            if ($print_counter_id < 1) {
                $print_counter_id = $counter_id;
            }

            if (!repo_upsert_sales_open([
                'sale_id' => $child_sale_id,
                'child_sale_id' => $child_sale_id,
                'parent_sale_id' => $parent_sale_id,
                'user_id' => $user_id,
                'bill_mode' => 'last',
                'identifier_type' => (string)($closed_parent['identifier_type'] ?? ''),
                'identifier_value' => (string)($closed_parent['identifier_value'] ?? ''),
                'identifier_label' => (string)($closed_parent['identifier_label'] ?? ''),
                'identifier_hex' => (string)($closed_parent['identifier_hex'] ?? ''),
                'counter_id' => $counter_id,
                'print_cntr_id' => $print_counter_id,
                'shift' => repo_get_shift(),
                'status' => 1,
            ])) {
                throw new RuntimeException('Could not register active LAST child: ' . mysqli_error($connection));
            }
        }

        if (!empty($options['activate_session'])) {
            if (!repo_set_active_sale_id($user_id, $child_sale_id, $child_status)) {
                throw new RuntimeException('Could not activate child sale session.');
            }

            if (function_exists('sales_thesion')) {
                sales_thesion('front/kwedia/', $child_sale_id, $counter_id, $child_status);
            }
        }

        bill_log(
            $created ? 'CLOSED_PARENT_NEW_CHILD' : 'CLOSED_PARENT_CONTINUE_CHILD',
            'user_id=' . $user_id
            . '|parent_sale_id=' . $parent_sale_id
            . '|child_sale_id=' . $child_sale_id
            . '|mode=' . $mode
            . '|counter=' . $counter_id
        );

        return [
            'child_sale_id' => $child_sale_id,
            'parent_sale_id' => $parent_sale_id,
            'mode' => $mode,
            'status' => $child_status,
            'position' => $child_position,
            'created' => $created,
            'closed_parent' => $closed_parent,
        ];
    }
}

if (!function_exists('bill_service_continue_closed_parent')) {
    function bill_service_continue_closed_parent($context, $params = []) {
        $params = is_array($params) ? $params : [];
        $user_id = (int)($context['user_id'] ?? 0);
        $counter_id = (int)($context['counter_id'] ?? repo_get_user_counter($user_id));
        $closed_parent = $context['closed_open_row'] ?? null;
        $parent_sale_id = (int)($closed_parent['sale_id'] ?? $context['sale_id'] ?? 0);
        return bill_service_resolve_closed_parent_child($user_id, $parent_sale_id, $counter_id, $params, [
            'closed_parent' => $closed_parent,
            'print_counter_id' => bill_service_resolve_counter_id($context, $params) ?: $counter_id,
            'activate_session' => true,
        ]);
    }
}

if (!function_exists('bill_service_close_closed_parent')) {
    function bill_service_close_closed_parent($context, $params = []) {
        $params = is_array($params) ? $params : [];
        $user_id = (int)($context['user_id'] ?? 0);
        $counter_id = bill_service_resolve_counter_id($context, $params);
        $closed_parent = $context['closed_open_row'] ?? null;
        $parent_sale_id = (int)($closed_parent['sale_id'] ?? $context['sale_id'] ?? 0);
        $mode = bill_clean_bill_mode($closed_parent['bill_mode'] ?? $closed_parent['mode'] ?? 'last');

        if ($parent_sale_id < 1) {
            throw new RuntimeException('Open parent was not found.');
        }

        $child_sale_id = $mode === 'last'
            ? repo_find_active_last_child($parent_sale_id, $user_id)
            : repo_find_active_each_child($parent_sale_id, $user_id);

        if ($child_sale_id < 1) {
            // An EACH parent may be closed directly from the Open page before
            // a new child is initialised. In that case there is no child bill
            // to finalize or print; close only the open-family registry.
            if ($mode === 'each') {
                if (!repo_clear_sale_closed_open($parent_sale_id)) {
                    throw new RuntimeException('Could not close the open EACH parent.');
                }
                if (function_exists('repo_clear_open_family_status')) {
                    repo_clear_open_family_status($user_id, $parent_sale_id);
                }
                if (function_exists('repo_reset_thesion_front')) {
                    repo_reset_thesion_front($user_id);
                }
                return [
                    'sale_id' => $parent_sale_id,
                    'amount' => 0,
                    'queued_jobs' => [],
                    'parent_sale_id' => $parent_sale_id,
                    'mode' => $mode,
                    'redirect' => '../log_out.php',
                ];
            }
            throw new RuntimeException('No active child was found for this parent.');
        }

        $child_context = [
            'user_id' => $user_id,
            'sale_id' => $child_sale_id,
            'counter_id' => (int)($context['counter_id'] ?? 0),
            'status_row' => [
                'sale_id' => $child_sale_id,
                'parent_sale_id' => $parent_sale_id,
            ],
        ];

        $queued_jobs = bill_service_queue_close_prints(
            $child_context,
            $params,
            $mode === 'last' ? 'open_active_last' : 'open_active_each'
        );

        $result = bill_service_finalize_sale_steps($child_context, $params, [
            'parent_sale_id' => $parent_sale_id,
            'source_open_items' => true,
            'clear_print' => true,
            'clear_status' => true,
            'clear_sale_open' => $mode === 'last',
            'clear_sale_closed_open' => true,
            'reset_thesion' => true,
        ]);

        return [
            'sale_id' => (int)$result['sale_id'],
            'amount' => (float)$result['amount'],
            'queued_jobs' => $queued_jobs,
            'parent_sale_id' => $parent_sale_id,
            'mode' => $mode,
            'redirect' => '../log_out.php',
        ];
    }
}

if (!function_exists('bill_service_finalize_each_round')) {
    function bill_service_finalize_each_round($context, $params = [], $clear_parent_registry = false) {
        global $connection;

        $params = is_array($params) ? $params : [];
        $user_id = (int)($context['user_id'] ?? 0);
        $sale_id = (int)($context['sale_id'] ?? 0);
        $parent_sale_id = (int)($context['status_row']['parent_sale_id'] ?? 0);

        if ($sale_id < 1 || $parent_sale_id < 1) {
            throw new RuntimeException('Invalid EACH child session.');
        }

        $queued_jobs = bill_service_queue_open_prints($context, $params, 'open_active_each');
        $result = bill_service_finalize_sale_steps($context, $params, [
            'parent_sale_id' => $parent_sale_id,
            'clear_print' => true,
            'clear_status' => true,
            'clear_sale_open' => false,
            'clear_sale_closed_open' => $clear_parent_registry,
            'reset_thesion' => true,
        ]);

        return [
            'sale_id' => (int)$result['sale_id'],
            'parent_sale_id' => $parent_sale_id,
            'amount' => (float)$result['amount'],
            'queued_jobs' => $queued_jobs,
        ];
    }
}

if (!function_exists('bill_service_open_fresh_each')) {
    function bill_service_open_fresh_each($context, $params = []) {
        global $connection;

        $params = is_array($params) ? $params : [];
        $user_id = (int)($context['user_id'] ?? 0);
        $sale_id = (int)($context['sale_id'] ?? 0);

        if ($sale_id < 1) {
            throw new RuntimeException('Invalid sale selected.');
        }

        if (function_exists('repo_set_sale_lineage')) {
            repo_set_sale_lineage($user_id, $sale_id, 1, 0, 0, 1);
        }

        // EACH closes the current round as a child bill. Keep the parent
        // reference-only: use a temporary print snapshot for this child,
        // never kp_shift_sale_open_items.
        if (!mysqli_query($connection, "DELETE FROM kp_sale_print WHERE sale_id='" . $sale_id . "' AND session_flag=2")) {
            throw new RuntimeException('Could not prepare fresh EACH print rows.');
        }
        if (!mysqli_query(
            $connection,
            "INSERT INTO kp_sale_print
                (sale_id,item_id,user_id,qty,dpt,price,total,shift,counter,hour,stock_counter,attr_id,accompaniment_json,session_flag)
             SELECT sale_id,item_id,user_id,qty,dpt,price,total,shift,counter,hour,stock_counter,attr_id,accompaniment_json,2
             FROM kp_sale_items_live
             WHERE sale_id='" . $sale_id . "' AND session_flag=2"
        )) {
            throw new RuntimeException('Could not create fresh EACH print rows.');
        }

        $identifier = repo_normalize_open_identifier($params);
        $queued_jobs = bill_service_queue_open_prints($context, $params, 'fresh_open_each');
        $result = bill_service_finalize_sale_steps($context, $params, [
            'parent_sale_id' => 0,
            'clear_print' => true,
            'clear_status' => true,
            'reset_thesion' => true,
        ]);

        // EACH creates a closed child bill. Its live rows are consumed by the
        // finalized sale and must not remain as a second pending order. The
        // parent itself is represented only by kp_shift_sales_open.
        $snapshot_sale_id = (int)$sale_id;
        if (!mysqli_query(
            $connection,
            "DELETE FROM kp_sale_items_live WHERE sale_id='" . $snapshot_sale_id . "' AND session_flag=2"
        )) {
            throw new RuntimeException('Could not clear fresh EACH live items.');
        }
        bill_log(
            'OPEN_FRESH_EACH_FINALIZED_CHILD',
            'sale_id=' . $snapshot_sale_id . '|user_id=' . $user_id
        );

        if (!repo_register_parent_closed_opened($sale_id, 'each', $identifier, $user_id)) {
            throw new RuntimeException('Could not register open EACH parent.');
        }
        if (!repo_set_sales_open_bill_position($sale_id, $user_id, 1, $sale_id)) {
            throw new RuntimeException('Could not initialize open EACH bill position.');
        }

        return [
            'sale_id' => $sale_id,
            'amount' => (float)$result['amount'],
            'queued_jobs' => $queued_jobs,
            'redirect' => 'index.php',
        ];
    }
}

if (!function_exists('bill_service_open_fresh_last')) {
    function bill_service_open_fresh_last($context, $params = []) {
        $params = is_array($params) ? $params : [];
        $user_id = (int)($context['user_id'] ?? 0);
        $sale_id = (int)($context['sale_id'] ?? 0);
        $counter_id = (int)($context['counter_id'] ?? 0);
        $print_counter_id = bill_service_resolve_counter_id($context, $params);
        $shift = repo_get_shift();

        if ($sale_id < 1) {
            throw new RuntimeException('Invalid sale selected.');
        }
        if ($counter_id < 1) {
            throw new RuntimeException('Counter not found for current user.');
        }
        if ($print_counter_id < 1) {
            $print_counter_id = $counter_id;
        }

        // A brand-new open order must hand off every item currently in the
        // live draft. Older clients could create the first rows with a
        // non-pending session flag, which made the initial snapshot empty.
        // Normalize only this fresh sale; later continuations continue to use
        // session_flag=2 as the delta marker.
        if (!mysqli_query(
            $GLOBALS['connection'],
            "UPDATE kp_sale_items_live SET session_flag=2
             WHERE sale_id='" . $sale_id . "' AND user_id='" . $user_id . "'"
        )) {
            throw new RuntimeException('Could not prepare fresh open items.');
        }

        if (!repo_update_sale_status($user_id, $sale_id, 3, 'last', 2, 0)) {
            throw new RuntimeException('Could not update sale status.');
        }

        if (!repo_upsert_sales_open([
            'sale_id' => $sale_id,
            'child_sale_id' => $sale_id,
            'parent_sale_id' => $sale_id,
            'user_id' => $user_id,
            'bill_mode' => 'last',
            'counter_id' => $counter_id,
            'print_cntr_id' => $print_counter_id,
            'shift' => $shift,
            'status' => 1,
        ])) {
            throw new RuntimeException('Could not register active LAST bill.');
        }

        $queued_jobs = bill_service_queue_open_prints($context, $params, 'fresh_open_last');
        if (!repo_update_sale_print_session_flag_to_one($sale_id)) {
            throw new RuntimeException('Could not mark open bill items as sent.');
        }
        bill_log('OPEN_LAST_SESSION_FLAG_UPDATED', 'scenario=fresh_open_last|sale_id=' . $sale_id . '|user_id=' . $user_id);

        return [
            'sale_id' => $sale_id,
            'queued_jobs' => $queued_jobs,
            //'redirect' => '../kwedia/index.php',
            
            'redirect' => 'index.php',
        ];
    }
}

if (!function_exists('bill_service_resolve_close_context')) {
    function bill_service_resolve_close_context($user_id, $params = []) {
        $user_id = (int)$user_id;
        $params = is_array($params) ? $params : [];

        $close_parent_sale_id = (int)($params['close_parent_sale_id'] ?? 0);
        $requested_sale_id = (int)($params['sale_id'] ?? 0);
        $target_sale_id = $close_parent_sale_id > 0 ? $close_parent_sale_id : $requested_sale_id;
        if ($target_sale_id < 1) {
            return bill_service_get_live_context($user_id, ['require_status' => true]);
        }

        $closed_open_row = repo_get_closed_opened_parent($target_sale_id);
        $status_row = repo_get_sale_status_for_sale($user_id, $target_sale_id);
        $normalized_status = $status_row ? bill_normalize_sale_status_row($status_row) : null;
        $parent_sale_id = (int)($normalized_status['parent_sale_id'] ?? 0);
        $family_parent_sale_id = $close_parent_sale_id > 0
            ? $close_parent_sale_id
            : ($parent_sale_id > 0 ? $parent_sale_id : $target_sale_id);

        if (!$closed_open_row && $family_parent_sale_id > 0 && $family_parent_sale_id !== $target_sale_id) {
            $closed_open_row = repo_get_closed_opened_parent($family_parent_sale_id);
        }

        $sales_open_row = bill_repo_get_sales_open_registry($target_sale_id, $user_id);
        if (!$sales_open_row && $family_parent_sale_id > 0 && $family_parent_sale_id !== $target_sale_id) {
            $sales_open_row = bill_repo_get_sales_open_registry($family_parent_sale_id, $user_id);
        }
        $mode = bill_clean_bill_mode(
            $params['bill_mode']
            ?? ($closed_open_row['bill_mode'] ?? $closed_open_row['mode'] ?? $sales_open_row['bill_mode'] ?? $normalized_status['mode'] ?? 'last')
        );

        $child_sale_id = 0;
        if ($family_parent_sale_id > 0 && $closed_open_row) {
            if ($mode === 'last') {
                $child_sale_id = repo_find_active_last_child($family_parent_sale_id, $user_id);
            } else {
                $child_sale_id = repo_find_active_each_child($family_parent_sale_id, $user_id);
            }
        }

        $scenario = '';
        $sale_id = $target_sale_id;
        $counter_id = repo_get_user_counter($user_id);

        if ($child_sale_id > 0) {
            $status_row = repo_get_sale_status_for_sale($user_id, $child_sale_id);
            $sale_id = $child_sale_id;
        } elseif (!$status_row && $family_parent_sale_id > 0 && $family_parent_sale_id !== $target_sale_id) {
            $status_row = repo_get_sale_status_for_sale($user_id, $family_parent_sale_id);
            if ($status_row) {
                $sale_id = $family_parent_sale_id;
            }
        }

        if ($status_row) {
            $status_row = bill_normalize_sale_status_row($status_row);
            $counter_id = (int)($status_row['counter_id'] ?? $status_row['counter'] ?? $counter_id);
            $scenario = bill_service_map_scenario($status_row, null, $sales_open_row, $closed_open_row);

            if ($closed_open_row && $mode === 'last') {
                $status_parent_sale_id = (int)($status_row['parent_sale_id'] ?? 0);
                $status_mode = bill_clean_bill_mode($status_row['mode'] ?? '');
                if ($status_mode === 'last' && ($status_parent_sale_id === $family_parent_sale_id || $sale_id === $family_parent_sale_id)) {
                    $scenario = 'closed_open_last_parent';
                }
            }
        } elseif ($closed_open_row) {
            $scenario = $mode === 'each' ? 'closed_open_each_parent' : 'closed_open_last_parent';
        } elseif ($sales_open_row) {
            $scenario = $mode === 'last' ? 'open_active_last' : 'open_active_each';
        }

        if ($scenario === '' || ($mode === 'last' && $scenario === 'fresh_active')) {
            if ($closed_open_row) {
                $scenario = $mode === 'each' ? 'closed_open_each_parent' : 'closed_open_last_parent';
            } elseif ($sales_open_row) {
                $scenario = $mode === 'last' ? 'open_active_last' : 'open_active_each';
            }
        }

        if ($scenario === '') {
            return bill_service_get_live_context($user_id, ['require_status' => true]);
        }

        return [
            'ok' => true,
            'message' => 'Close bill context loaded.',
            'user_id' => $user_id,
            'sale_id' => $sale_id,
            'counter_id' => $counter_id,
            'thesion' => null,
            'status_row' => $status_row,
            'sales_open_row' => $sales_open_row,
            'closed_open_row' => $closed_open_row,
            'scenario' => $scenario,
        ];
    }
}

if (!function_exists('bill_service_close_order')) {
    function bill_service_close_order($user_id, $params = []) {
        global $connection;

        $params = is_array($params) ? $params : [];
        $context = bill_service_resolve_close_context($user_id, $params);
        if (empty($context['ok'])) {
            return $context;
        }

        $scenario = (string)($context['scenario'] ?? '');
        mysqli_begin_transaction($connection);

        try {
            switch ($scenario) {
                case 'fresh_active':
                    $queued_jobs = bill_service_queue_close_prints($context, $params, $scenario);
                    $result = bill_service_finalize_sale_steps($context, $params, [
                        'clear_print' => true,
                        'clear_status' => true,
                        'reset_thesion' => true,
                    ]);
                    bill_log('CLOSE_FRESH_ACTIVE', 'sale_id=' . $result['sale_id'] . '|amount=' . $result['amount'] . '|user_id=' . (int)$user_id);
                    break;

                case 'open_active_last':
                    $queued_jobs = bill_service_queue_close_prints($context, $params, $scenario);
                    $result = bill_service_finalize_sale_steps($context, $params, [
                        'clear_print' => true,
                        'clear_status' => true,
                        'clear_sale_open' => true,
                        'reset_thesion' => true,
                    ]);
                    bill_log('CLOSE_OPEN_ACTIVE_LAST', 'scenario=' . $scenario . '|sale_id=' . $result['sale_id'] . '|amount=' . $result['amount'] . '|user_id=' . (int)$user_id);
                    break;

                case 'open_active_each':
                    $queued_jobs = bill_service_queue_close_prints($context, $params, $scenario);
                    $result = bill_service_finalize_sale_steps($context, $params, [
                        'parent_sale_id' => (int)($context['status_row']['parent_sale_id'] ?? 0),
                        'clear_print' => true,
                        'clear_status' => true,
                        'clear_sale_open' => true,
                        'clear_sale_closed_open' => true,
                        'reset_thesion' => true,
                    ]);
                    bill_log('CLOSE_OPEN_ACTIVE_EACH', 'scenario=' . $scenario . '|sale_id=' . $result['sale_id'] . '|parent_sale_id=' . $result['parent_sale_id'] . '|amount=' . $result['amount'] . '|user_id=' . (int)$user_id);
                    break;

                case 'closed_open_each_parent':
                case 'closed_open_last_parent':
                    $result = bill_service_close_closed_parent($context, $params);
                    $queued_jobs = $result['queued_jobs'] ?? [];
                    bill_log('CLOSE_CLOSED_PARENT', 'scenario=' . $scenario . '|sale_id=' . $result['sale_id'] . '|parent_sale_id=' . $result['parent_sale_id'] . '|amount=' . $result['amount'] . '|user_id=' . (int)$user_id);
                    break;

                default:
                    throw new RuntimeException('Unsupported close scenario.');
            }

            mysqli_commit($connection);
            return [
                'ok' => true,
                'message' => 'Bill closed successfully.',
                'scenario' => $scenario,
                'sale_id' => (int)($result['sale_id'] ?? 0),
                'amount' => (float)($result['amount'] ?? 0),
                'queued_jobs' => $queued_jobs ?? [],
                'redirect' => '../log_out.php',
            ];
        } catch (Throwable $exception) {
            mysqli_rollback($connection);
            bill_log('CLOSE_ORDER_ERROR', 'scenario=' . $scenario . '|user_id=' . (int)$user_id . '|error=' . $exception->getMessage());
            return [
                'ok' => false,
                'message' => 'Could not close the bill right now.',
                'scenario' => $scenario,
                'error' => $exception->getMessage(),
            ];
        }
    }
}

if (!function_exists('bill_service_open_order')) {
    function bill_service_place_closed_order($user_id, $params = []) {
        global $connection;

        $user_id = (int)$user_id;
        $status_row = repo_get_sale_status_by_user($user_id);
        $sale_id = (int)($status_row['sale_id'] ?? 0);
        $counter_id = (int)($status_row['counter_id'] ?? 0);
        if ($sale_id < 1 || (int)($status_row['status'] ?? 0) !== 2 || $counter_id < 1) {
            return ['ok' => false, 'message' => 'No valid ongoing closed sale was found.'];
        }

        $live_items = repo_fetch_sale_print_grouped($sale_id, 2, 'kp_sale_items_live');
        if (!$live_items) {
            return ['ok' => false, 'message' => 'The sale has no items to place.'];
        }

        mysqli_begin_transaction($connection);
        try {
            $shift = repo_get_shift();
            $total = 0.0;
            foreach ($live_items as $live_item) {
                $total += (float)($live_item['total'] ?? 0);
            }
            if (!repo_insert_sales_header([
                'sale_id' => $sale_id,
                'user_id' => $user_id,
                'shift' => $shift,
                'amount' => $total,
                'counter' => $counter_id,
                'bill_counter' => $counter_id,
            ])) {
                throw new RuntimeException('Could not save the sale header.');
            }

            $copy_sql = "INSERT INTO kp_sale_print
                (sale_id,item_id,user_id,qty,dpt,price,total,shift,counter,hour,stock_counter,attr_id,accompaniment_json,session_flag)
                SELECT sale_id,item_id,user_id,qty,dpt,price,total,shift,counter,hour,stock_counter,attr_id,accompaniment_json,2
                FROM kp_sale_items_live WHERE sale_id='$sale_id' AND session_flag=2";
            if (!mysqli_query($connection, $copy_sql)) {
                throw new RuntimeException('Could not create the print item snapshot.');
            }
            if (!repo_insert_sale_items_from_print($sale_id)) {
                throw new RuntimeException('Could not finalize the sale items.');
            }

            $check = mysqli_query($connection, "SELECT
                (SELECT COALESCE(SUM(qty),0) FROM kp_sale_items_live WHERE sale_id='$sale_id' AND session_flag=2) live_qty,
                (SELECT COALESCE(SUM(qty),0) FROM kp_sale_print WHERE sale_id='$sale_id' AND session_flag=2) print_qty,
                (SELECT COALESCE(SUM(total),0) FROM kp_sale_items_live WHERE sale_id='$sale_id' AND session_flag=2) live_total,
                (SELECT COALESCE(SUM(total),0) FROM kp_sale_print WHERE sale_id='$sale_id' AND session_flag=2) print_total");
            $check_row = $check ? mysqli_fetch_assoc($check) : null;
            if (!$check_row || (float)$check_row['live_qty'] !== (float)$check_row['print_qty'] || abs((float)$check_row['live_total'] - (float)$check_row['print_total']) > 0.001) {
                throw new RuntimeException('Live sale items do not match print items.');
            }

            $queued_jobs = array_merge(
                bill_service_queue_unprinted_tickets($sale_id, $user_id, $counter_id, ['include_cashier_order' => false, 'session_flag' => 2, 'sale_counter_id' => $counter_id]),
                bill_service_queue_full_bill($sale_id, $user_id, $counter_id)
            );
            if (!$queued_jobs) {
                throw new RuntimeException('No print jobs were created.');
            }
            if (!repo_clear_sale_status($user_id, $sale_id)) {
                throw new RuntimeException('Could not clear the placed sale status.');
            }
            mysqli_commit($connection);
            return ['ok' => true, 'message' => 'Order placed and queued for printing.', 'sale_id' => $sale_id, 'queued_jobs' => $queued_jobs, 'redirect' => '../log_out.php'];
        } catch (Throwable $exception) {
            mysqli_rollback($connection);
            return ['ok' => false, 'message' => 'Could not place the order.', 'error' => $exception->getMessage()];
        }
    }

    function bill_service_open_order($user_id, $params = []) {
        global $connection;

        $params = is_array($params) ? $params : [];
        $requested_sale_id = (int)($params['sale_id'] ?? 0);
        $context = bill_service_get_live_context($user_id, [
            'require_status' => true,
            'sale_id' => $requested_sale_id,
        ]);
        if (empty($context['ok'])) {
            return $context;
        }

        $selected_counter = (int)($params['bill_cntr'] ?? $params['counter'] ?? 0);
        $status_row = repo_get_sale_status_for_sale($user_id, (int)($context['sale_id'] ?? 0));
        $status_counter = (int)($status_row['counter_id'] ?? 0);
        $draft_items = repo_fetch_sale_print_grouped((int)($context['sale_id'] ?? 0), 2);
        if ($selected_counter > 0 && $status_counter > 0 && $selected_counter !== $status_counter && count($draft_items) > 0) {
            return [
                'ok' => false,
                'message' => 'This draft sale belongs to another counter.',
                'status' => 409,
                'ongoing_sale' => true,
                'sale_id' => (int)($context['sale_id'] ?? 0),
                'counter' => $status_counter,
                'counter_name' => get_counter($status_counter),
                'items' => $draft_items,
            ];
        }

        $scenario = (string)($context['scenario'] ?? '');
        $bill_mode = bill_clean_bill_mode($params['bill_mode'] ?? 'each');
        mysqli_begin_transaction($connection);

        try {
            switch ($scenario) {
                case 'fresh_active':
                    if ($bill_mode === 'last') {
                        $result = bill_service_open_fresh_last($context, $params);
                        $queued_jobs = $result['queued_jobs'] ?? [];
                    } else {
                        $result = bill_service_open_fresh_each($context, $params);
                        $queued_jobs = $result['queued_jobs'] ?? [];
                    }
                    bill_log('OPEN_FRESH_ACTIVE', 'sale_id=' . (int)($result['sale_id'] ?? $context['sale_id'] ?? 0) . '|mode=' . $bill_mode . '|user_id=' . (int)$user_id);
                    break;

                case 'open_active_last':
                    $queued_jobs = bill_service_queue_open_prints($context, $params, $scenario);
                    if (!repo_update_sale_print_session_flag_to_one((int)($context['sale_id'] ?? 0))) {
                        throw new RuntimeException('Could not mark open last items as sent.');
                    }
                    bill_log('OPEN_LAST_SESSION_FLAG_UPDATED', 'scenario=' . $scenario . '|sale_id=' . (int)($context['sale_id'] ?? 0) . '|user_id=' . (int)$user_id);
                    bill_log('OPEN_ACTIVE_LAST', 'scenario=' . $scenario . '|sale_id=' . (int)$context['sale_id'] . '|user_id=' . (int)$user_id);
                    break;

                case 'open_active_each':
                    $result = bill_service_finalize_each_round($context, $params, false);
                    $queued_jobs = $result['queued_jobs'] ?? [];
                    bill_log('OPEN_ACTIVE_EACH', 'sale_id=' . (int)($result['sale_id'] ?? $context['sale_id'] ?? 0) . '|parent_sale_id=' . (int)($result['parent_sale_id'] ?? 0) . '|user_id=' . (int)$user_id);
                    break;

                case 'closed_open_each_parent':
                case 'closed_open_last_parent':
                    $result = bill_service_continue_closed_parent($context, $params);
                    if (($result['mode'] ?? '') === 'last') {
                        $queued_jobs = bill_service_queue_unprinted_tickets(
                            (int)($result['child_sale_id'] ?? 0),
                            (int)$user_id,
                            bill_service_resolve_counter_id($context, $params) ?: (int)($context['counter_id'] ?? 0),
                            [
                                'include_cashier_order' => false,
                                'delta_only' => true,
                                'session_flag' => 2,
                                'sale_counter_id' => (int)($context['counter_id'] ?? 0),
                            ]
                        );
                        if (!repo_update_sale_print_session_flag_to_one((int)($result['child_sale_id'] ?? 0))) {
                            throw new RuntimeException('Could not mark continued open last items as sent.');
                        }
                        bill_log('OPEN_LAST_SESSION_FLAG_UPDATED', 'scenario=' . $scenario . '|sale_id=' . (int)($result['child_sale_id'] ?? 0) . '|user_id=' . (int)$user_id);
                    } else {
                        $queued_jobs = bill_service_queue_unprinted_tickets(
                            (int)($result['child_sale_id'] ?? 0),
                            (int)$user_id,
                            bill_service_resolve_counter_id($context, $params) ?: (int)($context['counter_id'] ?? 0),
                            [
                                'include_cashier_order' => false,
                                'delta_only' => false,
                                'sale_counter_id' => (int)($context['counter_id'] ?? 0),
                            ]
                        );
                    }
                    bill_log('OPEN_CLOSED_PARENT', 'scenario=' . $scenario . '|parent_sale_id=' . (int)($result['parent_sale_id'] ?? 0) . '|child_sale_id=' . (int)($result['child_sale_id'] ?? 0) . '|mode=' . (string)($result['mode'] ?? '') . '|user_id=' . (int)$user_id);
                    break;

                default:
                    throw new RuntimeException('Unsupported open scenario.');
            }

            if (!bill_service_mark_shift_open_items_sent((int)($context['sale_id'] ?? 0))) {
                throw new RuntimeException('Could not mark shift open items as sent.');
            }

            // Once an open order has been handed off to the open-sale
            // registry and its print snapshot, it must no longer block a new
            // K-wedia session through kp_sale_status. The open-sale tables
            // remain the source of truth for reopening it.
            $statusSaleIds = [
                (int)($context['sale_id'] ?? 0),
                (int)($result['sale_id'] ?? 0),
                (int)($result['child_sale_id'] ?? 0),
                (int)($result['parent_sale_id'] ?? 0),
            ];
            foreach (array_unique($statusSaleIds) as $statusSaleId) {
                if ($statusSaleId > 0 && !repo_clear_sale_status($user_id, $statusSaleId)) {
                    throw new RuntimeException('Could not clear the placed open-sale status.');
                }
            }

            mysqli_commit($connection);
            return [
                'ok' => true,
                'message' => 'Bill kept open successfully.',
                'scenario' => $scenario,
                'sale_id' => (int)($result['child_sale_id'] ?? $result['sale_id'] ?? $context['sale_id'] ?? 0),
                'queued_jobs' => $queued_jobs ?? [],
                'redirect' => (string)($result['redirect'] ?? (!empty($result['child_sale_id']) ? '../kwedia/index.php?sale_id=' . (int)$result['child_sale_id'] : '../open/index.php')),
            ];
        } catch (Throwable $exception) {
            mysqli_rollback($connection);
            bill_log('OPEN_ORDER_ERROR', 'scenario=' . $scenario . '|user_id=' . (int)$user_id . '|error=' . $exception->getMessage());
            return [
                'ok' => false,
                'message' => 'Could not keep the bill open right now.',
                'scenario' => $scenario,
                'error' => $exception->getMessage(),
            ];
        }
    }
}

if (!function_exists('bill_service_get_order_details')) {
    function bill_service_page_bootstrap($user_id, $params = []) {
        $user_id = (int)$user_id;
        $params = is_array($params) ? $params : [];
        $requested_sale_id = (int)($params['sale_id'] ?? 0);
        $requested_counter = (int)($params['sale_counter'] ?? 0);

        if ($requested_sale_id > 0) {
            $status_row = repo_get_sale_status_for_sale($user_id, $requested_sale_id);
            $open_row = bill_repo_get_sales_open_registry($requested_sale_id, $user_id);
            if ($status_row) {
                $counter_id = $requested_counter > 0
                    ? $requested_counter
                    : (int)($open_row['counter_id'] ?? repo_get_user_counter($user_id));
                if ($counter_id > 0 && function_exists('sales_thesion')) {
                    sales_thesion('front/kwedia/', $requested_sale_id, $counter_id, 2);
                }
            }
        }

        $truth = $requested_sale_id > 0
            ? repo_get_sale_status_for_sale($user_id, $requested_sale_id)
            : repo_get_sale_status_by_user($user_id);
        $sale_id = (int)($truth['sale_id'] ?? 0);
        $status = (int)($truth['status'] ?? 0);
        if ($sale_id < 1 || $status < 1) {
            return [
                'ok' => false,
                'message' => 'Order session was not found. Please reopen the bill.',
                'redirect' => '../dash/index.php?redir=order_lost',
                'debug_session' => [
                    'session_user_id' => $user_id,
                    'requested_sale_id' => $requested_sale_id,
                    'requested_counter' => $requested_counter,
                    'status_row' => $truth ?: null,
                    'resolved_sale_id' => $sale_id,
                    'resolved_status' => $status,
                ],
            ];
        }

        $details = bill_service_get_order_details($user_id);
        $cart = bill_service_get_current_cart($user_id);
        return [
            'ok' => true,
            'message' => 'Kwedia page loaded.',
            'user_id' => $user_id,
            'user_name' => (string)($details['user_name'] ?? ''),
            'counter_id' => (int)($details['counter_id'] ?? 0),
            'counter_name' => (string)($details['counter_name'] ?? ''),
            'sale_id' => (int)($details['sale_id'] ?? $sale_id),
            'status' => $status,
            'status_row' => $truth ?: [],
            'cart' => $cart,
        ];
    }

    function bill_service_get_order_details($user_id) {
        $user_id = (int)$user_id;
        $counter_id = repo_get_user_counter($user_id);
        $sale_id = bill_repo_get_active_sale_id($user_id);

        return [
            'ok' => true,
            'message' => 'Order details loaded.',
            'user_id' => $user_id,
            'user_name' => function_exists('get_user') ? (string)get_user($user_id) : '',
            'counter_id' => $counter_id,
            'counter_name' => ($counter_id > 0 && function_exists('get_counter')) ? (string)get_counter($counter_id) : '',
            'sale_id' => $sale_id,
            'items' => $sale_id > 0 ? repo_fetch_sale_print($sale_id) : [],
            'amount' => $sale_id > 0 ? repo_sum_sale_print($sale_id) : 0,
        ];
    }
}

if (!function_exists('bill_service_add_item')) {
    function bill_service_add_item($user_id, $item_id, $qty, $attr_id = null, $modifier = 0, $accompaniments = [], $amount_override = null) {
        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $modifier = (float)$modifier;
        $amount_override = $amount_override !== null ? (float)$amount_override : null;
        $sale_status = repo_get_sale_status_by_user($user_id);
        if (!$sale_status || (int)($sale_status['sale_id'] ?? 0) < 1 || !in_array((int)($sale_status['status'] ?? 0), [2, 3], true)) {
            throw new RuntimeException('Invalid sale session. Please start a new order.');
        }

        $sale_id = (int)$sale_status['sale_id'];
        $item = repo_get_item($item_id);
        if (!$item) {
            throw new RuntimeException('Item not found.');
        }

        // kp_items.deci=2 means whole-unit quantities only; deci=1 permits
        // fractional quantities. This server-side check cannot be bypassed.
        $decimal_setting = (int)($item['deci'] ?? 2);
        if ($amount_override === null && $decimal_setting === 2 && abs($qty - round($qty)) > 0.000001) {
            throw new RuntimeException('This item only allows whole-number quantities. Please enter a whole number.');
        }

        $hour = function_exists('hhr') ? (int)hhr() : 1;
        $base_price = $hour === 2 ? (float)($item['hhr_price'] ?? 0) : (float)($item['price'] ?? 0);
        $price = $base_price + $modifier;
        $total = $amount_override !== null && $amount_override >= 0
            ? $amount_override
            : $price * $qty;
        $counter_id = (int)($sale_status['counter_id'] ?? 0);
        if ($counter_id < 1) {
            throw new RuntimeException('The active sale has no counter assigned. Please restart the order.');
        }
        $item_dpt = (int)($item['dpt'] ?? 0);
        $stock_counter = function_exists('kw_api_item_counter_id') ? (int)kw_api_item_counter_id($counter_id, $item_dpt) : $counter_id;
        $shift = repo_get_shift();
        $accompaniment_json = !empty($accompaniments) ? json_encode($accompaniments) : null;

        $GLOBALS['kw_recipe_tracking_context'] = [
            'user_id' => $user_id,
            'sale_id' => $sale_id,
            'shift' => $shift,
            'hour' => $hour,
        ];
        $stock_result = repo_deduct_item_stock_from_counter($item_id, $stock_counter, $qty);
        unset($GLOBALS['kw_recipe_tracking_context']);
        if (empty($stock_result['ok'])) {
            throw new RuntimeException((string)($stock_result['message'] ?? 'Could not deduct counter stock.'));
        }

        $data = [
            'item_id' => $item_id,
            'sale_id' => $sale_id,
            'user_id' => $user_id,
            'qty' => $qty,
            'price' => $price,
            'total' => $total,
            'hour' => $hour,
            'dpt' => $item_dpt,
            'shift' => $shift,
            'counter' => $counter_id,
            'stock_counter' => $stock_counter,
            'attr_id' => $attr_id,
            'accompaniment_json' => $accompaniment_json,
            'session_flag' => 2,
        ];

        if (!repo_upsert_sale_item($data, false, 'kp_sale_items_live')) {
            throw new RuntimeException('Could not save the live sale item.');
        }
        // Resumed open bills use kp_sale_print for delta tickets and final
        // totals. Keep the new live line in that snapshot as well.
        if ((int)($sale_status['status'] ?? 0) === 3
            && !repo_upsert_sale_item($data, false, 'kp_sale_print')) {
            throw new RuntimeException('Could not save the continuation print item.');
        }
        repo_insert_stock_track([
            'item_id' => $item_id,
            'qty' => $qty,
            'user_id' => $user_id,
            'sale_id' => $sale_id,
            'status' => 'added',
            'sale_counter' => $counter_id,
            'stock_counter' => $stock_counter,
            'shift' => $shift,
            'hour' => $hour,
            'dpt' => $item_dpt,
        ]);

        return bill_service_get_current_cart($user_id);
    }
}

if (!function_exists('bill_service_get_current_cart')) {
    function bill_service_get_current_cart($user_id) {
        $user_id = (int)$user_id;
        $sale_status = repo_get_sale_status_by_user($user_id);
        $sale_id = (int)($sale_status['sale_id'] ?? 0);

        if ($sale_id < 1) {
            return [
                'ok' => true,
                'message' => 'Cart loaded.',
                'items' => [],
                'rows' => [],
                'total' => 0,
                'sale_id' => 0,
                'live_items' => [],
                'open_items' => [],
                'open_total' => 0,
            ];
        }

        $items = repo_fetch_sale_print_grouped($sale_id, 2, 'kp_sale_items_live');
        $total = 0;
        // Open-bill values are not available for every active-sale mode.
        // Initialize them so the page bootstrap never emits PHP warnings.
        $open_items = [];
        $open_total = 0;
        foreach ($items as $item) {
            $total += (float)($item['total'] ?? 0);
        }

        return [
            'ok' => true,
            'message' => 'Cart loaded.',
            'items' => $items,
            'rows' => $items,
            'total' => round($total, 2),
            'sale_id' => $sale_id,
            'live_items' => $items,
            'open_items' => $open_items,
            'open_total' => round($open_total, 2),
        ];
    }
}

if (!function_exists('bill_service_initialise')) {
    function bill_service_initialise($user_id, $counter, $status, $sale_id = 0) {
        global $connection;

        $user_id = (int)$user_id;
        $counter = (int)$counter;
        $status = (int)$status;
        $sale_id = (int)$sale_id;

        if (function_exists('kw_ensure_sale_status_schema')) {
            kw_ensure_sale_status_schema($connection);
        }

        if ($counter < 1) {
            return [
                'ok' => false,
                'message' => 'Invalid counter',
            ];
        }

        if ($status !== 1 && $status !== 2) {
            return [
                'ok' => false,
                'message' => 'Invalid init state',
            ];
        }

        // Before enforcing the single-status rule, inspect both possible
        // draft stores. A draft may still be in the live table or may already
        // be an open-order parent snapshot.
        $active_status_before = repo_get_sale_status_by_user($user_id);
        $active_sale_before = (int)($active_status_before['sale_id'] ?? 0);
        $active_counter_before = (int)($active_status_before['counter_id'] ?? 0);
        $active_live_items = $active_sale_before > 0
            ? repo_fetch_sale_print_grouped($active_sale_before, 2, 'kp_sale_items_live')
            : [];
        $active_open_items = $active_sale_before > 0 && function_exists('bill_service_fetch_shift_open_items')
            ? bill_service_fetch_shift_open_items($active_sale_before)
            : [];
        $active_draft_items = $active_live_items ?: $active_open_items;
        $active_items_source = $active_live_items ? 'live' : ($active_open_items ? 'open' : '');

        if ($status === 1 && $sale_id < 1 && $active_sale_before > 0
            && (int)($active_status_before['status'] ?? 0) === 2
            && $active_draft_items) {
            return [
                'ok' => false,
                'message' => 'An existing sale has items and must be continued or cancelled.',
                'status' => 409,
                'ongoing_sale' => true,
                'sale_id' => $active_sale_before,
                'counter' => $active_counter_before,
                'counter_name' => $active_counter_before > 0 ? get_counter($active_counter_before) : 'Unknown counter',
                'items_source' => $active_items_source,
                'items' => $active_draft_items,
            ];
        }

        $single_status = repo_enforce_single_sale_status($user_id, $sale_id);
        if (empty($single_status['ok'])) {
            return [
                'ok' => false,
                'message' => (string)($single_status['message'] ?? 'Another sale is already active.'),
                'status' => 409,
                'ongoing_sale' => true,
                'sale_id' => (int)($single_status['sale_id'] ?? 0),
                'items' => $active_draft_items,
                'items_source' => $active_items_source,
            ];
        }

        // kp_sale_status is the only source of truth for an existing draft.
        if ($status === 1 && $sale_id < 1) {
            $active_status = $active_status_before ?: repo_get_sale_status_by_user($user_id);
            $active_sale_id = (int)($active_status['sale_id'] ?? $active_sale_before);
            $active_counter = (int)($active_status['counter_id'] ?? $active_counter_before);
            $draft_items = $active_sale_id === $active_sale_before ? $active_draft_items : repo_fetch_sale_print_grouped($active_sale_id, 2);

            // Repair legacy drafts created before counter_id was stored in sale status.
            // After this one-time repair, sale status remains the source of truth.
            if ($active_sale_id > 0 && $active_counter < 1 && count($draft_items) > 0) {
                $inferred_counter = repo_infer_sale_counter_from_print($active_sale_id);
                if ($inferred_counter > 0) {
                    mysqli_query($connection, "UPDATE kp_sale_status SET counter_id='$inferred_counter' WHERE user_id='$user_id' AND sale_id='$active_sale_id'");
                    $active_counter = $inferred_counter;
                }
            }

            if ($active_sale_id > 0 && (int)($active_status['status'] ?? 0) === 2) {
                if (count($draft_items) > 0) {
                    return [
                        'ok' => false,
                        'message' => 'An existing sale has items and must be continued or cancelled.',
                        'status' => 409,
                        'ongoing_sale' => true,
                        'sale_id' => $active_sale_id,
                        'counter' => $active_counter,
                        'counter_name' => $active_counter > 0 ? get_counter($active_counter) : 'Unknown counter',
                        'items' => $draft_items,
                    ];
                }

                // Empty draft: resume it and assign the newly selected counter.
                $sale_id = $active_sale_id;
                $status = 2;
            }
        }

        $folder = 'front/kwedia/';

        mysqli_begin_transaction($connection);

        try {
            bill_log(
                'INITIALISE_START',
                'user_id=' . $user_id
                . '|sale_id=' . $sale_id
                . '|counter=' . $counter
                . '|status=' . $status
            );

            if ($status === 2) {
                if ($sale_id < 1) {
                    throw new RuntimeException('Invalid sale ID');
                }

                $selected_parent_sale_id = $sale_id;
                $closed_parent = repo_get_closed_opened_parent($selected_parent_sale_id);
                $active_open_parent = bill_repo_get_sales_open_registry($selected_parent_sale_id, $user_id);
                // A fresh EACH parent is stored in kp_shift_sales_open, not
                // in the legacy closed-parent table. Continuing it must
                // allocate a new child instead of reinitialising the parent.
                if (!$closed_parent && $active_open_parent
                    && (int)($active_open_parent['sale_id'] ?? 0) === $selected_parent_sale_id
                    && (int)($active_open_parent['parent_sale_id'] ?? $selected_parent_sale_id) === $selected_parent_sale_id
                    && bill_clean_bill_mode($active_open_parent['bill_mode'] ?? '') === 'each'
                    && (int)($active_open_parent['child_sale_id'] ?? 0) < 1) {
                    $closed_parent = [
                        'sale_id' => $selected_parent_sale_id,
                        'user_id' => $user_id,
                        'bill_mode' => 'each',
                        'identifier_type' => (string)($active_open_parent['identifier_type'] ?? ''),
                        'identifier_value' => (string)($active_open_parent['identifier_value'] ?? ''),
                        'identifier_label' => (string)($active_open_parent['identifier_label'] ?? ''),
                        'identifier_hex' => (string)($active_open_parent['identifier_hex'] ?? ''),
                    ];
                }
                if ($closed_parent) {
                    $resolved_child = bill_service_resolve_closed_parent_child($user_id, $selected_parent_sale_id, $counter, [
                        'counter' => $counter,
                    ], [
                        'closed_parent' => $closed_parent,
                        'print_counter_id' => $counter,
                        'activate_session' => false,
                    ]);
                    $sale_id = (int)($resolved_child['child_sale_id'] ?? 0);
                    $status = (int)($resolved_child['status'] ?? $status);
                }

                $sales_open_row = bill_repo_get_sales_open_registry($sale_id, $user_id);
                $saved_status_row = repo_get_sale_status_for_sale($user_id, $sale_id);
                $saved_mode = bill_clean_bill_mode($saved_status_row['mode'] ?? '');
                $is_open_last_session = $sales_open_row && bill_clean_bill_mode($sales_open_row['bill_mode'] ?? '') === 'last';
                if (!$is_open_last_session && $saved_status_row && $saved_mode === 'last' && (int)($saved_status_row['status'] ?? 0) === 3) {
                    $is_open_last_session = true;
                }
                $is_open_each_session = $sales_open_row && bill_clean_bill_mode($sales_open_row['bill_mode'] ?? '') === 'each';
                if (
                    !$is_open_each_session
                    && $saved_status_row
                    && $saved_mode === 'each'
                    && (int)($saved_status_row['parent_sale_id'] ?? 0) > 0
                ) {
                    $is_open_each_session = true;
                }
                if ($is_open_last_session || $is_open_each_session) {
                    $status = 3;
                }

                $status_mode = 'fresh';
                $status_parent_sale_id = 0;
                $status_parent_status = 2;

                if ($is_open_last_session) {
                    $status_mode = 'last';
                    $status_parent_sale_id = (int)($saved_status_row['parent_sale_id'] ?? 0);
                    if ($status_parent_sale_id < 1) {
                        $status_parent_sale_id = $sale_id;
                    }
                    $status_parent_status = 1;
                } elseif ($is_open_each_session) {
                    $status_mode = 'each';
                    $status_parent_sale_id = (int)($saved_status_row['parent_sale_id'] ?? 0);
                    if ($status_parent_sale_id < 1) {
                        $status_parent_sale_id = (int)($sales_open_row['sale_id'] ?? 0);
                    }
                    $status_parent_status = $status_parent_sale_id > 0 ? 1 : 2;
                }

                // Keep the sale header in the same open state as its parent.
                // Child sessions can otherwise retain the default normal/
                // closed header metadata even though sale_status says EACH.
                if ($status_parent_sale_id > 0) {
                    if (function_exists('kw_schema_ensure_column')) {
                        kw_schema_ensure_column('kp_sales', 'bill_role', "VARCHAR(20) NOT NULL DEFAULT 'closed' AFTER is_child_bill");
                        kw_schema_ensure_column('kp_sales', 'bill_status', "TINYINT(1) NOT NULL DEFAULT 2 AFTER bill_role");
                    }
                    if (!mysqli_query(
                        $connection,
                        "UPDATE kp_sales
                         SET parent='" . $status_parent_sale_id . "',
                             bill_role='child',
                             bill_status=1
                         WHERE sale_id='" . $sale_id . "'"
                    )) {
                        throw new RuntimeException('Could not mark child sale as open: ' . mysqli_error($connection));
                    }
                }

                if (!repo_set_sale_status($user_id, $sale_id, $status, $status_mode, $status_parent_status, $status_parent_sale_id, $counter)) {
                    throw new RuntimeException('Could not save sale status for continued order: ' . mysqli_error($connection));
                }
                bill_log(
                    'INITIALISE_STATUS_SYNCED',
                    'type=continued|user_id=' . $user_id
                    . '|sale_id=' . $sale_id
                    . '|status=' . $status
                    . '|mode=' . $status_mode
                    . '|parent_sale_id=' . $status_parent_sale_id
                );

                if (!repo_set_active_sale_id($user_id, $sale_id, $status)) {
                    throw new RuntimeException('Could not sync live session for continued order: ' . mysqli_error($connection));
                }

                sales_thesion($folder, $sale_id, $counter, $status);
                bill_log('INITIALISE_THESION_SET', 'type=continued|user_id=' . $user_id . '|sale_id=' . $sale_id . '|counter=' . $counter);
                $saved_status = repo_get_sale_status_for_sale($user_id, $sale_id);
                if (!$saved_status) {
                    throw new RuntimeException('Sale status was not saved for continued order. user_id=' . $user_id . ' sale_id=' . $sale_id);
                }
                bill_log('CONTINUED', 'user_id=' . $user_id . '|sale_id=' . $sale_id . '|counter=' . $counter . '|status=' . $status);
            } else {
                $sale_id = bill_repo_last_id($user_id);
                if ($sale_id < 1) {
                    throw new RuntimeException('Could not generate sale ID');
                }
                bill_log('INITIALISE_SALE_ID_READY', 'type=new|user_id=' . $user_id . '|sale_id=' . $sale_id);

                if (!repo_set_sale_status($user_id, $sale_id, 2, 'fresh', 2, 0, $counter)) {
                    throw new RuntimeException('Could not create sale status for new order: ' . mysqli_error($connection));
                }
                bill_log('INITIALISE_STATUS_CREATED', 'type=new|user_id=' . $user_id . '|sale_id=' . $sale_id);

                if (!repo_set_active_sale_id($user_id, $sale_id, $status)) {
                    throw new RuntimeException('Could not sync live session for new order: ' . mysqli_error($connection));
                }

                sales_thesion($folder, $sale_id, $counter, $status);
                bill_log('INITIALISE_THESION_SET', 'type=new|user_id=' . $user_id . '|sale_id=' . $sale_id . '|counter=' . $counter);
                $saved_status = repo_get_sale_status_for_sale($user_id, $sale_id);
                if (!$saved_status) {
                    throw new RuntimeException('Sale status was not saved for new order. user_id=' . $user_id . ' sale_id=' . $sale_id);
                }
                bill_log('STARTED', 'user_id=' . $user_id . '|sale_id=' . $sale_id . '|counter=' . $counter . '|status=' . $status);
            }

            mysqli_commit($connection);
            bill_log('INITIALISE_OK', 'user_id=' . $user_id . '|sale_id=' . $sale_id . '|counter=' . $counter . '|status=' . $status);
        } catch (Throwable $exception) {
            mysqli_rollback($connection);
            bill_log(
                'INITIALISE_ERROR',
                'user_id=' . $user_id
                . '|sale_id=' . $sale_id
                . '|counter=' . $counter
                . '|status=' . $status
                . '|error=' . $exception->getMessage()
            );

            return [
                'ok' => false,
                'message' => 'Could not initialize the order right now.',
                'error' => $exception->getMessage(),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Order initialized',
            'sale_id' => $sale_id,
            'counter' => $counter,
            'status' => $status,
            'redirect' => '../kwedia/index.php?sale_id=' . (int)$sale_id . '&sale_counter=' . (int)$counter,
        ];
    }
}

if (!function_exists('bill_service_initialise_open')) {
    /**
     * Initialise an existing open-order parent using the V1 open flow.
     * EACH always receives a fresh child sale; LAST restores the parent as
     * the active working sale.
     */
    function bill_service_initialise_open($user_id, $params = []) {
        global $connection;

        $user_id = (int)$user_id;
        $params = is_array($params) ? $params : [];
        $parent_sale_id = (int)($params['sale_id'] ?? 0);
        $counter_id = (int)($params['counter'] ?? $params['counter_id'] ?? 0);
        if ($parent_sale_id < 1 || $counter_id < 1) {
            return ['ok' => false, 'message' => 'sale_id and counter are required.'];
        }

        $open_parent = bill_repo_get_sales_open_registry($parent_sale_id, $user_id);
        if (!$open_parent || (int)($open_parent['status'] ?? 0) !== 1) {
            return ['ok' => false, 'message' => 'Open parent bill was not found.'];
        }
        $mode = bill_clean_bill_mode($params['bill_mode'] ?? $open_parent['bill_mode'] ?? 'last');
        mysqli_begin_transaction($connection);

        try {
            if ($mode === 'each') {
                // V1 EACH always creates a new working sale. Never fall back
                // to the parent when the open registry has no child yet.
                $sale_id = bill_repo_last_id($user_id);
                if ($sale_id < 1) {
                    throw new RuntimeException('Could not allocate a new open child sale.');
                }
                $bill_position = ((int)($open_parent['bill_position'] ?? 0)) + 1;
                $previous_sale_id = (int)($open_parent['previous_sale_id'] ?? 0);
                if ($previous_sale_id < 1) {
                    $previous_sale_id = $parent_sale_id;
                }
                if (!repo_set_sale_status($user_id, $sale_id, 3, 'each', 1, $parent_sale_id, $counter_id)
                    || !repo_set_sale_position($user_id, $sale_id, $bill_position)) {
                    throw new RuntimeException('Could not create the open child sale status.');
                }
                if (!repo_set_sales_open_bill_position($parent_sale_id, $user_id, $bill_position, $sale_id)) {
                    throw new RuntimeException('Could not advance the open bill position.');
                }
            } else {
                $sale_id = $parent_sale_id;
                if (!repo_set_sale_status($user_id, $sale_id, 3, 'last', 1, $parent_sale_id, $counter_id)) {
                    throw new RuntimeException('Could not restore the open LAST sale status.');
                }
            }

            if ($sale_id < 1) {
                throw new RuntimeException('Could not allocate the open working sale.');
            }
            if (function_exists('kw_schema_ensure_column')) {
                kw_schema_ensure_column('kp_sales', 'bill_role', "VARCHAR(20) NOT NULL DEFAULT 'closed' AFTER is_child_bill");
                kw_schema_ensure_column('kp_sales', 'bill_status', "TINYINT(1) NOT NULL DEFAULT 2 AFTER bill_role");
            }
            if ($mode === 'each' && !mysqli_query($connection, "UPDATE kp_sales SET parent='$parent_sale_id', bill_role='child', bill_status=1 WHERE sale_id='$sale_id'")) {
                throw new RuntimeException('Could not mark the open child sale.');
            }
            if (!repo_set_sale_status($user_id, $sale_id, 3, $mode, 1, $parent_sale_id, $counter_id)) {
                throw new RuntimeException('Could not save the open working sale status.');
            }
            if ($mode === 'each') {
                $previous_balance = (float)bill_repo_get_sale_amount($parent_sale_id);
                $previous_balance += (float)column_total('kp_sales', 'amount', "parent='" . (int)$parent_sale_id . "' AND sale_id<'" . (int)$sale_id . "'");
                if (!repo_set_sale_lineage($user_id, $sale_id, $bill_position, $previous_sale_id, $previous_balance, $bill_position)) {
                    throw new RuntimeException('Could not save the open bill lineage.');
                }
            }
            if (!repo_set_active_sale_id($user_id, $sale_id, 3)) {
                throw new RuntimeException('Could not activate the open working sale.');
            }
            sales_thesion('front/kwedia/', $sale_id, $counter_id, 3);
            mysqli_commit($connection);
            return [
                'ok' => true,
                'message' => 'Open bill initialised.',
                'sale_id' => $sale_id,
                'parent_sale_id' => $parent_sale_id,
                'counter' => $counter_id,
                'status' => 3,
                'mode' => $mode,
                'redirect' => '../kwedia/index.php?sale_id=' . $sale_id,
            ];
        } catch (Throwable $exception) {
            mysqli_rollback($connection);
            return ['ok' => false, 'message' => 'Could not initialise the open bill.', 'error' => $exception->getMessage()];
        }
    }
}

if (!function_exists('bill_service_get_items')) {
    function bill_service_get_items($user_id, $category_id, $subcategory_id = 0) {
        $user_id = (int)$user_id;
        $category_id = (int)$category_id;
        $subcategory_id = (int)$subcategory_id;

        return [
            'ok' => true,
            'message' => 'Items loaded.',
            'items' => bill_repo_get_items($user_id, $category_id, $subcategory_id),
        ];
    }
}

if (!function_exists('bill_service_get_categories')) {
    function bill_service_get_categories($user_id) {
        return [
            'ok' => true,
            'message' => 'Categories loaded.',
            'categories' => bill_repo_get_categories($user_id),
        ];
    }
}

if (!function_exists('bill_service_fetch_catalog')) {
    function bill_service_fetch_catalog($user_id) {
        $categories = bill_repo_get_categories($user_id);
        $subcategories = [];
        $items = [];
        foreach ($categories as $category) {
            $category_id = (int)($category['cat_id'] ?? 0);
            foreach (bill_repo_get_subcategories($category_id) as $subcategory) {
                // Keep the parent category with each subcategory so the
                // browser can filter/render them after the catalog loads.
                $subcategory['cat_id'] = $category_id;
                $subcategories[] = $subcategory;
            }
            foreach (bill_repo_get_items($user_id, $category_id, 0) as $item) {
                $items[] = $item;
            }
        }
        return [
            'ok' => true,
            'message' => 'Catalog loaded.',
            'categories' => $categories,
            'subcategories' => $subcategories,
            'items' => $items,
        ];
    }
}

if (!function_exists('bill_service_get_subcategories')) {
    function bill_service_get_subcategories($category_id) {
        return [
            'ok' => true,
            'message' => 'Subcategories loaded.',
            'category_id' => (int)$category_id,
            'subcategories' => bill_repo_get_subcategories($category_id),
        ];
    }
}

if (!function_exists('bill_service_delete_item')) {
    function bill_service_delete_item($user_id, $item_id, $hour = 0, $attr_id = null, $accompaniment_json = null) {
        $deleted = bill_repo_delete_item($user_id, $item_id, $hour, $attr_id, $accompaniment_json);

        return [
            'ok' => (bool)$deleted,
            'message' => $deleted ? 'Item deleted.' : 'Item could not be deleted.',
            'item_id' => (int)$item_id,
            'sale_id' => bill_repo_get_active_sale_id($user_id),
        ];
    }
}

if (!function_exists('bill_service_cancel_order')) {
    function bill_service_cancel_order($user_id) {
        $cancelled = bill_repo_cancel_order($user_id);
        if ($cancelled) {
            $sale_id = bill_repo_get_active_sale_id($user_id);
            repo_clear_sale_status($user_id, $sale_id);
            repo_reset_thesion_front($user_id);
        }

        return [
            'ok' => (bool)$cancelled,
            'message' => $cancelled ? 'Order cancelled.' : 'Order could not be cancelled.',
            'user_id' => (int)$user_id,
        ];
    }
}

?>

