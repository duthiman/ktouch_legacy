<?php

if (!function_exists('tots_service_validate_actor')) {
    function tots_service_validate_actor($user_id, $options = []) {
        $user_id = (int)$user_id;
        $options = is_array($options) ? $options : [];
        $require_tot = !empty($options['require_tot']);

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired.',
                'code' => 'SESSION_EXPIRED',
                'context' => [],
            ];
        }

        $user = tots_repo_get_user_row($user_id);
        if (!$user) {
            return [
                'ok' => false,
                'message' => 'User account was not found.',
                'code' => 'USER_NOT_FOUND',
                'context' => [],
            ];
        }

        if (!tots_repo_user_is_active($user)) {
            return [
                'ok' => false,
                'message' => 'This user is not active.',
                'code' => 'USER_NOT_ACTIVE',
                'context' => [],
            ];
        }

        if (isset($user['job']) && (int)$user['job'] !== 1) {
            return [
                'ok' => false,
                'message' => 'Open a counter first before raising TOTs.',
                'code' => 'USER_NOT_ON_COUNTER',
                'context' => [],
            ];
        }

        if (isset($user['counter_status']) && (int)$user['counter_status'] !== 1) {
            return [
                'ok' => false,
                'message' => 'Your counter is not active.',
                'code' => 'COUNTER_NOT_ACTIVE',
                'context' => [],
            ];
        }

        if (isset($user['shift_status']) && (int)$user['shift_status'] !== 1) {
            return [
                'ok' => false,
                'message' => 'No active shift is assigned to this user.',
                'code' => 'USER_SHIFT_NOT_ACTIVE',
                'context' => [],
            ];
        }

        $shift = tots_repo_get_active_shift();
        if ($shift === '') {
            return [
                'ok' => false,
                'message' => 'No active shift found.',
                'code' => 'SHIFT_NOT_ACTIVE',
                'context' => [],
            ];
        }

        $counter = (int)($user['counter'] ?? 0);
        if ($counter < 1) {
            return [
                'ok' => false,
                'message' => 'Please select or open a counter first before generating TOTs.',
                'code' => 'NO_COUNTER_SELECTED',
                'context' => [],
            ];
        }

        $counter_row = tots_repo_get_counter_row($counter);
        if (!$counter_row) {
            return [
                'ok' => false,
                'message' => 'Assigned counter was not found.',
                'code' => 'COUNTER_NOT_FOUND',
                'context' => [],
            ];
        }

        if (!tots_repo_counter_is_active($counter_row)) {
            return [
                'ok' => false,
                'message' => 'Assigned counter is not open.',
                'code' => 'COUNTER_NOT_OPEN',
                'context' => [],
            ];
        }

        $context = [
            'user_id' => $user_id,
            'tot_id' => isset($_SESSION['tot']) ? (int)$_SESSION['tot'] : 0,
            'shift' => $shift,
            'dpt' => isset($counter_row['dpt']) ? (string)$counter_row['dpt'] : '',
            'counter' => $counter,
        ];

        if ($require_tot && (int)$context['tot_id'] < 1) {
            return [
                'ok' => false,
                'message' => 'No active TOT session found.',
                'code' => 'TOT_SESSION_MISSING',
                'context' => $context,
            ];
        }

        return [
            'ok' => true,
            'message' => 'TOTS actor validated.',
            'code' => 'ACTOR_VALID',
            'context' => $context,
            'user' => $user,
            'counter_row' => $counter_row,
        ];
    }
}

if (!function_exists('tots_service_boot')) {
    function tots_service_boot($user_id) {
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
                'context' => $actor['context'] ?? [],
                'actions' => [],
            ];
        }

        return [
            'ok' => true,
            'message' => 'TOTS API shell is ready.',
            'context' => $actor['context'],
            'actions' => [
                'boot',
                'health',
            ],
        ];
    }
}

if (!function_exists('tots_service_initialize')) {
    function tots_service_initialize($user_id) {
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
                'redirect' => '../stock/store_dash.php',
            ];
        }

        $context = $actor['context'];
        $existing = tots_repo_find_open_tot_status($user_id);
        $tot_id = $existing ? (int)($existing['tot_id'] ?? 0) : 0;

        if ($tot_id < 1) {
            $tot_id = (int)tots_repo_get_next_tot_id();
            if ($tot_id < 1 || !tots_repo_create_tot_status($tot_id, $user_id, (int)($context['counter'] ?? 0))) {
                return [
                    'ok' => false,
                    'message' => 'Could not start a new generated-units session.',
                    'code' => 'INIT_FAILED',
                    'redirect' => '../stock/store_dash.php',
                ];
            }
        }

        $_SESSION['tot'] = $tot_id;

        if (function_exists('logs')) {
            logs('STARTED|-|-|UNIT NO|' . $tot_id, '../');
        }

        $context['tot_id'] = $tot_id;

        return [
            'ok' => true,
            'message' => 'Generated units session is ready.',
            'code' => 'INIT_OK',
            'redirect' => 'index.php',
            'context' => $context,
        ];
    }
}

if (!function_exists('tots_service_health')) {
    function tots_service_health($user_id) {
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
                'context' => $actor['context'] ?? [],
            ];
        }

        return [
            'ok' => true,
            'message' => 'TOTS API health check passed.',
            'context' => $actor['context'],
        ];
    }
}

if (!function_exists('tots_service_bootstrap')) {
    function tots_service_bootstrap($user_id) {
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
                'context' => $actor['context'] ?? [],
            ];
        }

        $context = $actor['context'];
        $dpt = (string)($context['dpt'] ?? '');
        $categories = tots_repo_fetch_categories($dpt);
        $items = tots_repo_fetch_initial_items((string)($context['dpt'] ?? ''));
        $tot_id = (int)($context['tot_id'] ?? 0);
        $live_items = tots_repo_fetch_live_items($tot_id);

        return [
            'ok' => true,
            'message' => 'TOTS bootstrap loaded.',
            'categories_desktop_html' => tots_render_category_buttons_html($categories, 'desktop'),
            'categories_mobile_html' => tots_render_category_buttons_html($categories, 'mobile'),
            'items_html' => tots_render_item_buttons_html($items),
            'live_list_html' => tots_render_live_list_html($live_items, $tot_id > 0),
            'live_count' => count($live_items),
            'context' => $context,
        ];
    }
}

if (!function_exists('tots_service_get_categories')) {
    function tots_service_get_categories($user_id = 0) {
        $context = tots_repo_get_session_context((int)$user_id);
        $categories = tots_repo_fetch_categories((string)($context['dpt'] ?? ''));

        return [
            'ok' => true,
            'message' => 'Categories loaded.',
            'desktop_html' => tots_render_category_buttons_html($categories, 'desktop'),
            'mobile_html' => tots_render_category_buttons_html($categories, 'mobile'),
            'count' => count($categories),
        ];
    }
}

if (!function_exists('tots_service_get_initial_items')) {
    function tots_service_get_initial_items($user_id) {
        $context = tots_repo_get_session_context($user_id);
        $dpt = (string)($context['dpt'] ?? '');
        $items = tots_repo_fetch_initial_items($dpt);
        $html = tots_render_item_buttons_html($items);

        return [
            'ok' => true,
            'message' => 'Initial items loaded.',
            'html' => $html,
            'count' => count($items),
        ];
    }
}

if (!function_exists('tots_service_get_category_items')) {
    function tots_service_get_category_items($user_id, $category_id) {
        $context = tots_repo_get_session_context((int)$user_id);
        $category_id = (int)$category_id;

        return [
            'ok' => true,
            'message' => 'Category items loaded.',
            'html' => tots_render_item_buttons_html(tots_repo_fetch_items_by_category($category_id, (string)($context['dpt'] ?? ''))),
        ];
    }
}

if (!function_exists('tots_service_get_subcategory_items')) {
    function tots_service_get_subcategory_items($user_id, $sub_id) {
        $context = tots_repo_get_session_context((int)$user_id);
        $sub_id = (int)$sub_id;

        return [
            'ok' => true,
            'message' => 'Subcategory items loaded.',
            'html' => tots_render_item_buttons_html(tots_repo_fetch_items_by_subcategory($sub_id, (string)($context['dpt'] ?? ''))),
        ];
    }
}

if (!function_exists('tots_service_get_subcategories')) {
    function tots_service_get_subcategories($user_id, $category_id) {
        $context = tots_repo_get_session_context((int)$user_id);
        $category_id = (int)$category_id;

        return [
            'ok' => true,
            'message' => 'Subcategories loaded.',
            'html' => tots_render_sub_nav_html(tots_repo_fetch_subcategories($category_id, (string)($context['dpt'] ?? ''))),
        ];
    }
}

if (!function_exists('tots_service_get_live_list')) {
    function tots_service_get_live_list($user_id) {
        $context = tots_repo_get_session_context($user_id);
        $tot_id = (int)($context['tot_id'] ?? 0);
        $live_items = tots_repo_fetch_live_items($tot_id);

        return [
            'ok' => true,
            'message' => 'TOT list loaded.',
            'html' => tots_render_live_list_html($live_items, $tot_id > 0),
            'count' => count($live_items),
        ];
    }
}

if (!function_exists('tots_service_add_item')) {
    function tots_service_add_item($user_id, $item_id) {
        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $actor = tots_service_validate_actor($user_id, ['require_tot' => true]);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
            ];
        }

        $context = $actor['context'];
        $tot_id = (int)($context['tot_id'] ?? 0);
        $counter = (int)($context['counter'] ?? 0);
        $qty_column = kp_tot_qty_column($counter);
        $shift = (string)($context['shift'] ?? '');

        if ($item_id < 1) {
            return [
                'ok' => false,
                'message' => 'Item is required.',
                'code' => 'ITEM_REQUIRED',
            ];
        }

        if (tots_repo_find_tot_item($tot_id, $item_id)) {
            return [
                'ok' => false,
                'message' => 'Item already in the list.',
                'code' => 'ITEM_ALREADY_ADDED',
            ];
        }

        if ($counter < 1 || $qty_column === '') {
            return [
                'ok' => false,
                'message' => 'Please select or open a counter first before generating TOTs.',
                'code' => 'NO_COUNTER_SELECTED',
            ];
        }

        $item = tots_repo_get_item_row($item_id);
        if (!$item || !array_key_exists($qty_column, $item)) {
            return [
                'ok' => false,
                'message' => 'Please select or open a counter first before generating TOTs.',
                'code' => 'NO_COUNTER_SELECTED',
            ];
        }

        $tots = (float)($item['tot_num'] ?? 0);
        $dpt = (string)($item['dpt'] ?? '');
        $stock = (float)($item[$qty_column] ?? 0);
        $tot_item_id = (int)($item['tot_item_id'] ?? 0);

        if ($stock <= 0 || $tot_item_id < 1 || $tots <= 0) {
            return [
                'ok' => false,
                'message' => 'This item is not ready for TOT generation.',
                'code' => 'ITEM_NOT_AVAILABLE',
            ];
        }

        $created = tots_repo_insert_tot_item($tot_id, $user_id, $item_id, $tot_item_id, $tots, $shift, $counter);
        if (!$created) {
            return [
                'ok' => false,
                'message' => 'Failed to add the item. Please try again.',
                'code' => 'ADD_FAILED',
            ];
        }

        tots_repo_update_counter_qty($item_id, $qty_column, 1, '-');
        tots_repo_update_counter_qty($tot_item_id, $qty_column, $tots, '+');
        tots_repo_insert_stock_track($item_id, 1, $user_id, $tot_id, 'OUT', $shift, $dpt, $counter);
        tots_repo_insert_stock_track($tot_item_id, $tots, $user_id, $tot_id, 'IN', $shift, $dpt, $counter);

        if (function_exists('logs') && function_exists('get_item')) {
            $action = 'ADDED|' . $tots . '|' . get_item($item_id) . '|TO UNIT GENERATOR NO|' . $tot_id;
            logs($action, '../');
        }

        $live_items = tots_repo_fetch_live_items($tot_id);

        return [
            'ok' => true,
            'message' => 'Item added to TOT list.',
            'code' => 'ITEM_ADDED',
            'live_list_html' => tots_render_live_list_html($live_items, true),
            'live_count' => count($live_items),
        ];
    }
}

if (!function_exists('tots_service_delete_item')) {
    function tots_service_delete_item($user_id, $tot_item_id) {
        $user_id = (int)$user_id;
        $tot_item_id = (int)$tot_item_id;
        $actor = tots_service_validate_actor($user_id, ['require_tot' => true]);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
            ];
        }

        $context = $actor['context'];
        $tot_id = (int)($context['tot_id'] ?? 0);

        $row = tots_repo_find_tot_item_by_generated($tot_id, $tot_item_id);
        if (!$row) {
            return [
                'ok' => false,
                'message' => 'TOT item was not found.',
                'code' => 'ITEM_NOT_FOUND',
            ];
        }

        $item_id = (int)$row['item_id'];
        $generated_item_id = (int)$row['tot_item_id'];
        $tots = (float)$row['tots'];
        $counter = (int)$row['counter'];
        $qty_column = kp_tot_qty_column($counter);

        if ($counter < 1 || $qty_column === '') {
            return [
                'ok' => false,
                'message' => 'Counter information is missing for this TOT item.',
                'code' => 'NO_COUNTER_SELECTED',
            ];
        }

        tots_repo_update_counter_qty($item_id, $qty_column, 1, '+');
        tots_repo_update_counter_qty($generated_item_id, $qty_column, $tots, '-');
        tots_repo_delete_tot_item($tot_id, $item_id);
        tots_repo_delete_stock_track($item_id, $tot_id, $counter);
        tots_repo_delete_stock_track($generated_item_id, $tot_id, $counter);

        if (function_exists('logs') && function_exists('get_item')) {
            $action = 'DELETED|-|' . get_item($item_id) . '|FROM UNIT LIST NO|' . $tot_id;
            logs($action, '../logs');
        }

        $live_items = tots_repo_fetch_live_items($tot_id);

        return [
            'ok' => true,
            'message' => 'TOT item removed.',
            'code' => 'ITEM_DELETED',
            'live_list_html' => tots_render_live_list_html($live_items, true),
            'live_count' => count($live_items),
        ];
    }
}

if (!function_exists('tots_service_get_summary_modal')) {
    function tots_service_get_summary_modal($user_id) {
        $user_id = (int)$user_id;
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
            ];
        }

        $context = $actor['context'];
        $tot_id = (int)($context['tot_id'] ?? 0);
        $counter_id = (int)($context['counter'] ?? 0);
        $counter_name = function_exists('get_counter') ? (string)get_counter($counter_id) : ('Counter ' . $counter_id);
        $live_items = tots_repo_fetch_live_items($tot_id);

        return [
            'ok' => true,
            'message' => 'TOT summary loaded.',
            'html' => tots_render_summary_modal_html($counter_name, $tot_id, tots_render_live_list_html($live_items, $tot_id > 0)),
        ];
    }
}

if (!function_exists('tots_service_get_print_modal')) {
    function tots_service_get_print_modal($user_id) {
        $user_id = (int)$user_id;
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
            ];
        }

        $context = $actor['context'];
        $tot_id = (int)($context['tot_id'] ?? 0);
        $my_counter = (int)($context['counter'] ?? 0);

        return [
            'ok' => true,
            'message' => 'TOT printer modal loaded.',
            'html' => tots_render_print_modal_html($tot_id, tots_repo_fetch_print_counters(), $my_counter),
        ];
    }
}

if (!function_exists('tots_service_submit_print')) {
    function tots_service_submit_print($user_id, $cntr_id) {
        global $connection;

        $user_id = (int)$user_id;
        $cntr_id = (int)$cntr_id;
        $actor = tots_service_validate_actor($user_id, ['require_tot' => true]);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
            ];
        }

        $context = $actor['context'];
        $tot_id = (int)($context['tot_id'] ?? 0);
        $shift = (string)($context['shift'] ?? '');
        $print_mode = function_exists('get_print_mode') ? (int)get_print_mode() : 0;

        if ($print_mode !== 1 && $cntr_id < 1) {
            return [
                'ok' => false,
                'message' => 'Please select a printer counter.',
            ];
        }

        $counter = (int)($context['counter'] ?? 0);

        if (!tots_repo_tot_exists($tot_id)) {
            $created = tots_repo_create_tot_header($tot_id, $user_id, $shift, $counter);
            if (!$created) {
                return [
                    'ok' => false,
                    'message' => mysqli_error($connection),
                ];
            }

            if (function_exists('logs')) {
                $action = 'PLACED|-|-| TOT NO|' . $tot_id;
                logs($action, '../');
            }
        }

        tots_repo_clear_tot_status($tot_id);

        if ($print_mode === 1) {
            return [
                'ok' => true,
                'message' => 'TOT sent to printer.',
                'redirect' => '../chaba/render_tots.php?tot_id=' . $tot_id . '&auto_print=1&return_to=' . urlencode('../tot/tot_place.php'),
            ];
        }

        if (function_exists('ensure_chaba_print_system')) {
            ensure_chaba_print_system($connection);
        }

        $queue_id = function_exists('kp_print_enqueue') ? kp_print_enqueue($connection, [
            'sale_id' => 0,
            'ref_id' => $tot_id,
            'print_type' => 'tots',
            'label' => 'TOTS',
            'cntr_id' => $cntr_id,
            'route_path' => 'render_tots.php',
            'route_query' => 'id=' . $tot_id,
            'requested_by' => $user_id,
        ]) : 0;

        if ($queue_id < 1) {
            return [
                'ok' => false,
                'message' => 'Could not queue TOT print job.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'TOT queued for printing.',
            'redirect' => '../dash/dash.php',
        ];
    }
}

if (!function_exists('tots_service_get_cancel_modal')) {
    function tots_service_get_cancel_modal($user_id) {
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
            ];
        }

        return [
            'ok' => true,
            'message' => 'Cancel confirmation loaded.',
            'html' => tots_render_cancel_modal_html(),
        ];
    }
}

if (!function_exists('tots_service_cancel')) {
    function tots_service_cancel($user_id) {
        $actor = tots_service_validate_actor($user_id);
        if (!$actor['ok']) {
            return [
                'ok' => false,
                'message' => $actor['message'],
                'code' => $actor['code'],
                'redirect' => '../stock/store_dash.php',
            ];
        }

        $context = $actor['context'];
        $tot_id = (int)($context['tot_id'] ?? 0);

        if ($tot_id < 1) {
            unset($_SESSION['tot']);
            return [
                'ok' => true,
                'message' => 'There was no active generated-units session to cancel.',
                'code' => 'NO_ACTIVE_TOT',
                'redirect' => 'index.php',
            ];
        }

        $rows = tots_repo_fetch_tot_rows($tot_id);
        foreach ($rows as $row) {
            $item_id = (int)($row['item_id'] ?? 0);
            $tot_item_id = (int)($row['tot_item_id'] ?? 0);
            $tots = (float)($row['tots'] ?? 0);
            $counter = (int)($row['counter'] ?? 0);
            $qty_column = kp_tot_qty_column($counter);

            if ($item_id < 1 || $tot_item_id < 1 || $counter < 1 || $qty_column === '') {
                continue;
            }

            tots_repo_update_counter_qty($item_id, $qty_column, 1, '+');
            tots_repo_update_counter_qty($tot_item_id, $qty_column, $tots, '-');
        }

        tots_repo_delete_tot_items($tot_id);
        tots_repo_delete_tot_stock_track($tot_id);
        tots_repo_clear_tot_status($tot_id);

        if (function_exists('logs') && function_exists('get_counter')) {
            $counter_name = get_counter((int)($context['counter'] ?? 0));
            logs('CANCELLED|-|-|UNIT NO|' . $tot_id . '| FOR ' . $counter_name . ' COUNTER', '../logs');
        }

        unset($_SESSION['tot']);

        return [
            'ok' => true,
            'message' => 'Generated units session cancelled.',
            'code' => 'TOT_CANCELLED',
            'redirect' => 'index.php',
        ];
    }
}
