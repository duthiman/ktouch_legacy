<?php

if (!function_exists('view_repo_user_context')) {
    function view_repo_user_context($connection, $user_id)
    {
        $user_id = (int)$user_id;

        return [
            'user_id' => $user_id,
            'username' => $user_id > 0 && function_exists('get_user') ? (string)get_user($user_id) : '',
            'counter_id' => $user_id > 0 ? (int)get_column('kp_users', 'counter', "user_id='$user_id'") : 0,
            'counter_name' => $user_id > 0 && function_exists('get_counter')
                ? (string)get_counter((int)get_column('kp_users', 'counter', "user_id='$user_id'"))
                : '',
            'shift' => (string)get_column('kp_shift', 'shift', '1'),
        ];
    }
}

if (!function_exists('view_repo_counts')) {
    function view_repo_counts($connection, $user_id)
    {
        $user_id = (int)$user_id;

        return [
            'open_sales' => (int)column_count('kp_sales_open', 'sale_id', "user_id='$user_id' AND status=1"),
            'unpaid_sales' => (int)column_count('kp_sales', 'sale_id', "user_id='$user_id' AND pay_status!=1"),
            'finished_merges' => (int)column_count('kp_bill_merge', 'merge_id', "created_by='$user_id' AND status=1"),
            'pending_merge_requests' => (int)column_count('kp_bill_merge_request', 'request_id', "target_user_id='$user_id' AND status=2"),
        ];
    }
}

if (!function_exists('view_repo_fetch_sales_rows')) {
    function view_repo_fetch_sales_rows($connection, $user_id, $shift)
    {
        $sales_rows = [];
        $user_id = (int)$user_id;
        $shift = (string)$shift;
        $shift = mysqli_real_escape_string($connection, $shift);
        if ($shift === '' || $shift === '0' || $shift === '0000-00-00') {
            return $sales_rows;
        }

        $has_sale_parent = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'parent') : false;
        $has_sale_is_child_bill = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'is_child_bill') : false;
        $has_sale_bill_role = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'bill_role') : false;

        $live_sale_ids = [];
        $live_sale_res = mysqli_query(
            $connection,
            "SELECT DISTINCT ss.sale_id
             FROM kp_sale_status ss
             INNER JOIN kp_sales s ON s.sale_id=ss.sale_id
             WHERE ss.status IN (1,2) AND s.user_id='$user_id'"
        );
        while ($live_sale_res && $live_sale_row = mysqli_fetch_assoc($live_sale_res)) {
            $live_sale_ids[(int)$live_sale_row['sale_id']] = true;
        }

        $open_registry_by_sale = [];
        $open_registry_sql = mysqli_query(
            $connection,
            "SELECT o.sale_id, o.open_id, o.status
             FROM kp_sales_open o
             INNER JOIN (
               SELECT MAX(open_id) AS latest_open_id
               FROM kp_sales_open
               WHERE user_id='$user_id'
               GROUP BY sale_id
             ) latest ON latest.latest_open_id = o.open_id
             WHERE o.user_id='$user_id' AND o.status=1"
        );
        while ($open_registry_sql && $open_registry_row = mysqli_fetch_assoc($open_registry_sql)) {
            $parent_sale_id = (int)($open_registry_row['sale_id'] ?? 0);
            if ($parent_sale_id < 1) {
                continue;
            }
            $open_registry_by_sale[$parent_sale_id] = $open_registry_row;
        }

        $sale_parent_select = $has_sale_parent ? 's.parent' : '0 AS parent';
        $sale_is_child_select = $has_sale_is_child_bill ? 's.is_child_bill' : '0 AS is_child_bill';
        $sale_bill_role_select = $has_sale_bill_role ? 's.bill_role' : "'closed' AS bill_role";
        $res = mysqli_query(
            $connection,
            "SELECT s.sale_id, s.shift, s.counter, COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name, $sale_parent_select, $sale_is_child_select, $sale_bill_role_select,
                    COALESCE(SUM(COALESCE(si.total, si.qty * si.price)), 0) AS total,
                    COALESCE(GROUP_CONCAT(DISTINCT i.name ORDER BY i.name SEPARATOR ' '), '') AS item_names
             FROM kp_sales s
             LEFT JOIN kp_sale_items si ON si.sale_id = s.sale_id
                AND si.user_id = '$user_id' AND si.shift = '$shift'
             LEFT JOIN kp_items i ON i.item_id = si.item_id
             LEFT JOIN kp_counters c ON c.cntr_id = s.counter
             WHERE s.user_id = '$user_id' AND s.shift = '$shift' AND s.pay_status != 1
             GROUP BY s.sale_id, s.shift, s.counter, c.name
             ORDER BY s.sale_id DESC
             LIMIT 50"
        );

        while ($res && $row = mysqli_fetch_assoc($res)) {
            $sale_id = (int)$row['sale_id'];
            $parent_sale_id = (int)($row['parent'] ?? 0);
            if ($parent_sale_id > 0 && isset($open_registry_by_sale[$parent_sale_id]) && isset($live_sale_ids[$parent_sale_id])) {
                continue;
            }

            if (isset($open_registry_by_sale[$sale_id]) && isset($live_sale_ids[$sale_id])) {
                continue;
            }

            $total = (float)$row['total'];
            $bill_role = strtolower(trim((string)($row['bill_role'] ?? '')));
            $is_child_bill = (int)($row['is_child_bill'] ?? 0) === 1;
            if ($bill_role === '') {
                $bill_role = ($is_child_bill || $parent_sale_id > 0) ? 'child' : 'closed';
            }

            $bill_role_label = $bill_role === 'parent'
                ? 'Parent Bill'
                : ($bill_role === 'child' ? 'Child Bill' : 'Closed Bill');
            $sales_rows[] = [
                'kind' => 'sale',
                'id' => $sale_id,
                'sale_id' => $sale_id,
                'label' => '#' . $sale_id . ' · ' . $bill_role_label,
                'bill_role' => $bill_role,
                'bill_role_label' => $bill_role_label,
                'is_ongoing' => 0,
                'children_count' => 0,
                'shift' => (string)($row['shift'] ?? $shift),
                'counter' => (string)($row['counter_name'] ?? ('Counter ' . (int)($row['counter'] ?? 0))),
                'counter_id' => (int)($row['counter'] ?? 0),
                'total' => $total,
                'search' => strtolower($sale_id . ' ' . $bill_role_label . ' ' . (string)($row['shift'] ?? $shift) . ' ' . (string)($row['counter_name'] ?? '') . ' ' . (string)($row['item_names'] ?? '') . ' ' . number_format($total, 2)),
            ];
        }

        usort($sales_rows, function ($a, $b) {
            if (($a['kind'] ?? '') !== ($b['kind'] ?? '')) {
                return ($a['kind'] ?? '') === 'sale' ? -1 : 1;
            }

            return (int)($b['sale_id'] ?? 0) <=> (int)($a['sale_id'] ?? 0);
        });

        return $sales_rows;
    }
}

if (!function_exists('view_repo_fetch_sales_page')) {
    function view_repo_fetch_sales_page($connection, $user_id, $shift, $page = 1, $per_page = 10, $search = '')
    {
        $user_id = (int)$user_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $search = trim((string)$search);
        $search_sql = mysqli_real_escape_string($connection, $search);
        $page = max(1, (int)$page);
        $per_page = max(1, min(10, (int)$per_page));
        $offset = ($page - 1) * $per_page;

        if ($user_id < 1 || $shift === '' || $shift === '0' || $shift === '0000-00-00') {
            return ['rows' => [], 'page' => $page, 'per_page' => $per_page, 'total' => 0, 'pages' => 0];
        }

        $where = "s.user_id='$user_id' AND s.shift='$shift'";
        if ($search !== '') {
            $where .= " AND (CAST(s.sale_id AS CHAR) LIKE '%$search_sql%' OR s.shift LIKE '%$search_sql%')";
        }

        $count_result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM kp_sales s WHERE $where");
        $count_row = $count_result ? mysqli_fetch_assoc($count_result) : [];
        $total = (int)($count_row['total'] ?? 0);
        $pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

        $rows = [];
        $result = mysqli_query(
            $connection,
            "SELECT s.sale_id, s.shift, s.counter, COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name,
                    COALESCE(SUM(COALESCE(si.total, si.qty * si.price)), 0) AS total,
                    COALESCE(SUM(si.qty), 0) AS item_qty,
                    COUNT(si.item_id) AS item_count
             FROM kp_sales s
             LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id
                AND si.user_id='$user_id' AND si.shift='$shift'
             LEFT JOIN kp_counters c ON c.cntr_id=s.counter
             WHERE $where
             GROUP BY s.sale_id, s.shift, s.counter, c.name
             ORDER BY s.sale_id DESC
             LIMIT $offset, $per_page"
        );

        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $sale_id = (int)($row['sale_id'] ?? 0);
            $total_amount = (float)($row['total'] ?? 0);
            $shift_value = (string)($row['shift'] ?? $shift);
            $rows[] = [
                'kind' => 'sale',
                'id' => $sale_id,
                'sale_id' => $sale_id,
                'label' => '#' . $sale_id,
                'bill_role' => 'sale',
                'bill_role_label' => 'Sale',
                'is_ongoing' => 0,
                'children_count' => 0,
                'shift' => $shift_value,
                'counter' => (string)($row['counter_name'] ?? ('Counter ' . (int)($row['counter'] ?? 0))),
                'counter_id' => (int)($row['counter'] ?? 0),
                'total' => $total_amount,
                'item_qty' => (float)($row['item_qty'] ?? 0),
                'item_count' => (int)($row['item_count'] ?? 0),
                'search' => strtolower($sale_id . ' ' . $shift_value . ' ' . (string)($row['counter_name'] ?? '') . ' ' . number_format($total_amount, 2)),
                'items' => [],
            ];
        }

        if (!empty($rows)) {
            $sale_ids = implode(',', array_map(function ($row) {
                return (int)($row['sale_id'] ?? 0);
            }, $rows));
            $items_result = mysqli_query(
                $connection,
                "SELECT si.sale_id, si.item_id,
                        COALESCE(i.name, CONCAT('Item #', si.item_id)) AS item_name,
                        COALESCE(SUM(si.qty), 0) AS qty,
                        COALESCE(MAX(si.price), 0) AS price,
                        COALESCE(SUM(COALESCE(si.total, si.qty * si.price)), 0) AS total
                 FROM kp_sale_items si
                 LEFT JOIN kp_items i ON i.item_id=si.item_id
                 WHERE si.sale_id IN ($sale_ids)
                   AND si.user_id='$user_id'
                   AND si.shift='$shift'
                 GROUP BY si.sale_id, si.item_id, i.name
                 ORDER BY si.sale_id DESC, si.item_id ASC"
            );
            $items_by_sale = [];
            while ($items_result && ($item_row = mysqli_fetch_assoc($items_result))) {
                $item_sale_id = (int)($item_row['sale_id'] ?? 0);
                $items_by_sale[$item_sale_id][] = [
                    'item_id' => (int)($item_row['item_id'] ?? 0),
                    'item_name' => (string)($item_row['item_name'] ?? ''),
                    'qty' => (float)($item_row['qty'] ?? 0),
                    'price' => (float)($item_row['price'] ?? 0),
                    'total' => (float)($item_row['total'] ?? 0),
                ];
            }
            foreach ($rows as &$row) {
                $row['items'] = $items_by_sale[(int)$row['sale_id']] ?? [];
            }
            unset($row);
        }

        return [
            'rows' => $rows,
            'page' => $page,
            'per_page' => $per_page,
            'total' => $total,
            'pages' => $pages,
        ];
    }
}

if (!function_exists('view_repo_fetch_sold_rows')) {
    function view_repo_fetch_sold_rows($connection, $shift, $user_id)
    {
        $rows = [];
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $user_id = (int)$user_id;

        $res = mysqli_query(
            $connection,
            "SELECT SUM(si.qty) AS tq, i.name
             FROM kp_sale_items si
             LEFT JOIN kp_items i ON i.item_id = si.item_id
             WHERE si.shift = '$shift' AND si.user_id = '$user_id'
             GROUP BY si.item_id
             ORDER BY tq DESC"
        );

        while ($res && $row = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'item' => (string)($row['name'] ?? ''),
                'qty' => (float)($row['tq'] ?? 0),
            ];
        }

        return $rows;
    }
}

if (!function_exists('view_repo_fetch_merge_state')) {
    function view_repo_fetch_merge_state($connection, $user_id)
    {
        $user_id = (int)$user_id;
        $active_merge_draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, false) : null;
        $active_merge_id = $active_merge_draft ? (int)($active_merge_draft['merge_id'] ?? 0) : 0;
        $active_merge_items = $active_merge_id > 0 && function_exists('cash_merge_draft_items')
            ? cash_merge_draft_items($active_merge_id)
            : [];

        $finished_merge_rows = [];
        $finished_merge_res = mysqli_query(
            $connection,
            "SELECT * FROM kp_bill_merge
             WHERE created_by='$user_id' AND status=1
             ORDER BY merge_id DESC
             LIMIT 20"
        );
        while ($finished_merge_res && $finished_row = mysqli_fetch_assoc($finished_merge_res)) {
            $finished_merge_rows[] = $finished_row;
        }

        $merge_requests = [];
        $approved_foreign_sales = [];
        $request_filter = $active_merge_id > 0
            ? "merge_id='$active_merge_id'"
            : "(requester_user_id='$user_id' OR target_user_id='$user_id')";
        $requests_res = mysqli_query(
            $connection,
            "SELECT *
             FROM kp_bill_merge_request
             WHERE $request_filter
             ORDER BY request_id DESC
             LIMIT 50"
        );
        while ($requests_res && $row = mysqli_fetch_assoc($requests_res)) {
            $merge_requests[] = $row;
            if ((int)($row['status'] ?? 0) === 2 && (int)($row['requester_user_id'] ?? 0) === $user_id && (int)($row['merge_id'] ?? 0) === $active_merge_id) {
                $approved_foreign_sales[(int)($row['target_sale_id'] ?? 0)] = (int)($row['requester_sale_id'] ?? 0);
            }
        }

        $pending_merge_requests = 0;
        $active_merge_requests = 0;
        $active_merge_request_rows = [];
        foreach ($merge_requests as $merge_request_row) {
            $status = (int)($merge_request_row['status'] ?? 0);
            if ($status === 1) {
                $pending_merge_requests++;
            }
            if ($status === 1 || $status === 2) {
                $active_merge_requests++;
                $target_sale_id = (int)($merge_request_row['target_sale_id'] ?? 0);
                $active_merge_request_rows[] = [
                    'request_id' => (int)($merge_request_row['request_id'] ?? 0),
                    'requester_sale_id' => (int)($merge_request_row['requester_sale_id'] ?? 0),
                    'target_sale_id' => $target_sale_id,
                    'target_owner_name' => function_exists('get_user') ? (string)get_user((int)($merge_request_row['target_user_id'] ?? 0)) : '',
                    'amount' => (float)get_column('kp_sales', 'amount', "sale_id='$target_sale_id'"),
                    'status' => $status,
                    'created_at' => (string)($merge_request_row['created_at'] ?? ''),
                    'responded_at' => (string)($merge_request_row['responded_at'] ?? ''),
                ];
            }
        }

        return [
            'active_merge_id' => $active_merge_id,
            'active_merge_items' => $active_merge_items,
            'finished_merge_rows' => $finished_merge_rows,
            'merge_requests' => $merge_requests,
            'pending_merge_requests' => $pending_merge_requests,
            'active_merge_requests' => $active_merge_requests,
            'active_merge_request_rows' => $active_merge_request_rows,
            'approved_foreign_sales' => $approved_foreign_sales,
        ];
    }
}

if (!function_exists('view_repo_fetch_merge_cards')) {
    function view_repo_fetch_merge_cards($connection, $shift, $user_id, $approved_foreign_sales = [])
    {
        $cards = [];
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $user_id = (int)$user_id;

        $merge_res = mysqli_query(
            $connection,
            "SELECT s.sale_id, s.user_id, s.shift, u.username AS owner_name, c.name AS counter_name,
                    COALESCE(SUM(si.total), 0) AS total
             FROM kp_sales s
             LEFT JOIN kp_sale_items si ON si.sale_id = s.sale_id
             LEFT JOIN kp_users u ON u.user_id = s.user_id
             LEFT JOIN kp_counters c ON c.cntr_id = s.counter
             WHERE s.shift='$shift' AND s.pay_status != 1
             GROUP BY s.sale_id, s.user_id, s.shift, u.username, c.name
             ORDER BY s.sale_id DESC
             LIMIT 120"
        );

        while ($merge_res && $row = mysqli_fetch_assoc($merge_res)) {
            $sale_id = (int)($row['sale_id'] ?? 0);
            $root_sale_id = function_exists('cash_merge_root_sale_id') ? (int)cash_merge_root_sale_id($sale_id) : $sale_id;
            if (function_exists('cash_sale_in_open_merge') && cash_sale_in_open_merge($sale_id, $root_sale_id)) {
                continue;
            }

            $owner_id = (int)($row['user_id'] ?? 0);
            $cards[] = [
                'sale_id' => $sale_id,
                'root_sale_id' => $root_sale_id,
                'owner_id' => $owner_id,
                'owner_name' => (string)($row['owner_name'] ?? ''),
                'amount' => (float)($row['total'] ?? 0),
                'counter' => (string)($row['counter_name'] ?? ''),
                'item_type' => ($root_sale_id > 0 && $root_sale_id !== $sale_id) ? 'open_child' : 'single',
                'is_mine' => $owner_id === $user_id,
                'approved_for_merge' => isset($approved_foreign_sales[$sale_id]),
                'approved_requester_sale_id' => isset($approved_foreign_sales[$sale_id]) ? (int)$approved_foreign_sales[$sale_id] : 0,
            ];
        }

        return $cards;
    }
}

if (!function_exists('view_repo_bill_items_rows')) {
    function view_repo_bill_items_rows($connection, $sale_id, $user_id = 0, $shift = '')
    {
        $sale_id = (int)$sale_id;
        $rows = [];
        if ($sale_id < 1) {
            return $rows;
        }

        $where = "si.sale_id='$sale_id'";
        if ((int)$user_id > 0) {
            $safe_user_id = (int)$user_id;
            $safe_shift = mysqli_real_escape_string($connection, (string)$shift);
            $where .= " AND si.user_id='$safe_user_id' AND si.shift='$safe_shift'";
        }
        $result = mysqli_query(
            $connection,
            "SELECT si.item_id,
                    COALESCE(SUM(si.qty), 0) AS qty,
                    COALESCE(MAX(si.price), 0) AS price,
                    COALESCE(SUM(COALESCE(si.total, si.qty * si.price)), 0) AS total,
                    COALESCE(i.name, CONCAT('Item #', si.item_id)) AS item
             FROM kp_sale_items si
             LEFT JOIN kp_items i ON i.item_id=si.item_id
             WHERE $where
             GROUP BY si.item_id, i.name
             ORDER BY item ASC"
        );
        while ($result && $row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'item' => (string)($row['item'] ?? ('Item #' . (int)($row['item_id'] ?? 0))),
                'qty' => (float)($row['qty'] ?? 0),
                'price' => (float)($row['price'] ?? 0),
                'total' => (float)($row['total'] ?? 0),
            ];
        }

        return $rows;
    }
}
