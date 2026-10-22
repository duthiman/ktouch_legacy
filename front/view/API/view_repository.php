<?php

if (!function_exists('view_repo_scalar')) {
    function view_repo_scalar($connection, $table, $column, $where)
    {
        return get_column($table, $column, $where);
    }
}

if (!function_exists('view_repo_user_name')) {
    function view_repo_user_name($connection, $user_id)
    {
        return (string)get_user((int)$user_id);
    }
}

if (!function_exists('view_repo_counter_name')) {
    function view_repo_counter_name($connection, $counter_id)
    {
        return (string)get_counter((int)$counter_id);
    }
}

if (!function_exists('view_repo_count')) {
    function view_repo_count($connection, $table, $column, $where)
    {
        return (int)column_count($table, $column, $where);
    }
}

if (!function_exists('view_repo_total')) {
    function view_repo_total($connection, $table, $column, $where)
    {
        return (float)column_total($table, $column, $where);
    }
}

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
        $shift = mysqli_real_escape_string($connection, (string)get_column('kp_shift', 'shift', '1'));

        return [
            'open_sales' => (int)column_count('kp_sales_open', 'sale_id', "user_id='$user_id' AND status=1"),
            // The View screen is shift-scoped. Do not include unpaid bills
            // carried over from an earlier shift in its bill count.
            'unpaid_sales' => (int)column_count('kp_sales', 'sale_id', "user_id='$user_id' AND shift='$shift' AND pay_status!=1"),
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
            "SELECT s.sale_id, s.shift, s.datime AS order_time, s.counter, COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name, $sale_parent_select, $sale_is_child_select, $sale_bill_role_select,
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
                'order_time' => (string)($row['order_time'] ?? ''),
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

        $where = "s.user_id='$user_id' AND s.shift='$shift' AND EXISTS (
            SELECT 1 FROM kp_sale_items shift_items
            WHERE shift_items.sale_id=s.sale_id AND shift_items.user_id='$user_id' AND shift_items.shift='$shift'
        )";
        if ($search !== '') {
            // Item filtering is driven by kp_sale_items, which carries its own
            // shift value. kp_sales is only used for bill-level fields.
            $where .= " AND (CAST(s.sale_id AS CHAR) LIKE '%$search_sql%'
                OR s.shift LIKE '%$search_sql%'
                OR EXISTS (
                    SELECT 1
                    FROM kp_sale_items filter_items
                    LEFT JOIN kp_items filter_catalog ON filter_catalog.item_id=filter_items.item_id
                    WHERE filter_items.sale_id=s.sale_id
                      AND filter_items.user_id='$user_id'
                      AND filter_items.shift='$shift'
                      AND filter_catalog.name LIKE '%$search_sql%'
                ))";
        }

        $count_result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM kp_sales s WHERE $where");
        $count_row = $count_result ? mysqli_fetch_assoc($count_result) : [];
        $total = (int)($count_row['total'] ?? 0);
        $pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

        $rows = [];
        $result = mysqli_query(
            $connection,
            "SELECT s.sale_id, s.shift, s.datime AS order_time, s.counter, COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name,
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
                'order_time' => (string)($row['order_time'] ?? ''),
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

if (!function_exists('view_repo_merge_request_row')) {
    function view_repo_merge_request_row($connection, $request_id, $pending_only = false)
    {
        $request_id = (int)$request_id;
        $status = $pending_only ? ' AND status=1' : '';
        $result = mysqli_query($connection, "SELECT * FROM kp_bill_merge_request WHERE request_id='$request_id'$status LIMIT 1");
        return $result ? (mysqli_fetch_assoc($result) ?: null) : null;
    }
}

if (!function_exists('view_repo_approved_merge_requests')) {
    function view_repo_approved_merge_requests($connection, $user_id, $target_sale_id, $merge_id = 0)
    {
        $rows = [];
        $user_id = (int)$user_id;
        $target_sale_id = (int)$target_sale_id;
        $merge_filter = (int)$merge_id > 0 ? " AND merge_id='" . (int)$merge_id . "'" : '';
        $result = mysqli_query($connection, "SELECT request_id, requester_sale_id FROM kp_bill_merge_request WHERE requester_user_id='$user_id' AND target_sale_id='$target_sale_id' AND status=2$merge_filter ORDER BY request_id DESC");
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('view_repo_create_merge_request')) {
    function view_repo_create_merge_request($connection, $merge_id, $requester_user_id, $requester_sale_id, $requester_root_sale_id, $target_user_id, $target_sale_id, $target_root_sale_id)
    {
        $merge_id = (int)$merge_id;
        $requester_user_id = (int)$requester_user_id;
        $requester_sale_id = (int)$requester_sale_id;
        $requester_root_sale_id = (int)$requester_root_sale_id;
        $target_user_id = (int)$target_user_id;
        $target_sale_id = (int)$target_sale_id;
        $target_root_sale_id = (int)$target_root_sale_id;
        return mysqli_query($connection, "INSERT INTO kp_bill_merge_request (merge_id, requester_user_id, requester_sale_id, requester_root_sale_id, target_user_id, target_sale_id, target_root_sale_id, status) VALUES('$merge_id','$requester_user_id','$requester_sale_id','$requester_root_sale_id','$target_user_id','$target_sale_id','$target_root_sale_id',1)");
    }
}

if (!function_exists('view_repo_update_merge_request_status')) {
    function view_repo_update_merge_request_status($connection, $request_id, $status, $responded_by = 0, $note = null)
    {
        $request_id = (int)$request_id;
        $status = (int)$status;
        $responded_by = (int)$responded_by;
        $note_sql = $note === null ? '' : ", note='" . mysqli_real_escape_string($connection, (string)$note) . "'";
        return mysqli_query($connection, "UPDATE kp_bill_merge_request SET status='$status', responded_at=NOW(), responded_by='$responded_by'$note_sql WHERE request_id='$request_id'");
    }
}

if (!function_exists('view_repo_finalize_merge')) {
    function view_repo_finalize_merge($connection, $merge_id)
    {
        $merge_id = (int)$merge_id;
        $merge = mysqli_query($connection, "UPDATE kp_bill_merge SET status=1, merge_state=2 WHERE merge_id='$merge_id'");
        $requests = mysqli_query($connection, "UPDATE kp_bill_merge_request SET status=4, responded_at=NOW(), note='Closed after merge finished' WHERE merge_id='$merge_id' AND status IN (1,2)");
        return $merge && $requests;
    }
}

if (!function_exists('view_repo_close_merge_requests')) {
    function view_repo_close_merge_requests($connection, $request_ids)
    {
        $ids = array_values(array_filter(array_map('intval', (array)$request_ids)));
        if (!$ids) return true;
        $id_sql = implode(',', $ids);
        return mysqli_query($connection, "UPDATE kp_bill_merge_request SET status=4, responded_at=NOW(), note='Used in merge' WHERE request_id IN ($id_sql) AND status=2");
    }
}

if (!function_exists('view_repo_record_reprint')) {
    function view_repo_record_reprint($connection, $sale_id, $user_id, $approver_id, $shift)
    {
        return mysqli_query($connection, "INSERT INTO kp_sale_reprint(sale_id,user_id,appr_by,shift) VALUES('" . (int)$sale_id . "','" . (int)$user_id . "','" . (int)$approver_id . "','" . mysqli_real_escape_string($connection, (string)$shift) . "')");
    }
}

if (!function_exists('view_repo_sale_for_user_shift')) {
    function view_repo_sale_for_user_shift($connection, $sale_id, $user_id, $shift)
    {
        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $result = mysqli_query($connection, "SELECT * FROM kp_sales WHERE sale_id='$sale_id' AND user_id='$user_id' AND shift='$shift' LIMIT 1");
        return $result ? (mysqli_fetch_assoc($result) ?: []) : [];
    }
}

if (!function_exists('view_repo_sale_item_hour')) {
    function view_repo_sale_item_hour($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT hour FROM kp_sale_items WHERE sale_id='$sale_id' AND hour IS NOT NULL AND hour<>'' LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return (string)($row['hour'] ?? '');
    }
}

if (!function_exists('view_repo_merge_record')) {
    function view_repo_merge_record($connection, $merge_id, $user_id)
    {
        $merge_id = (int)$merge_id;
        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_bill_merge WHERE merge_id='$merge_id' AND created_by='$user_id' AND status=1 LIMIT 1");
        return $result ? (mysqli_fetch_assoc($result) ?: []) : [];
    }
}

if (!function_exists('view_repo_merge_requests')) {
    function view_repo_merge_requests($connection, $user_id, $active_merge_id = 0, $incoming_only = false, $pending_only = false)
    {
        $user_id = (int)$user_id;
        $active_merge_id = (int)$active_merge_id;
        $where = $incoming_only
            ? "target_user_id='$user_id'"
            : ($active_merge_id > 0 ? "merge_id='$active_merge_id'" : "(requester_user_id='$user_id' OR target_user_id='$user_id')");
        if ($pending_only) $where .= ' AND status=1';
        $rows = [];
        $result = mysqli_query($connection, "SELECT * FROM kp_bill_merge_request WHERE $where ORDER BY request_id DESC LIMIT 80");
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[] = $row;
        return $rows;
    }
}

if (!function_exists('view_repo_item_sales_rows')) {
    function view_repo_item_sales_rows($connection, $user_id, $item_name, $shift)
    {
        $user_id = (int)$user_id;
        $item_name = mysqli_real_escape_string($connection, (string)$item_name);
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $rows = [];
        $result = mysqli_query($connection, "SELECT s.sale_id, s.shift, s.counter, COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name, s.amount, SUM(si.qty) AS qty FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id=si.item_id LEFT JOIN kp_sales s ON s.sale_id=si.sale_id LEFT JOIN kp_counters c ON c.cntr_id=s.counter WHERE s.user_id='$user_id' AND s.shift='$shift' AND i.name='$item_name' AND si.shift='$shift' GROUP BY s.sale_id, s.shift, s.counter, c.name, s.amount ORDER BY s.sale_id DESC LIMIT 80");
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[] = $row;
        return $rows;
    }
}

if (!function_exists('view_repo_void_sale')) {
    function view_repo_void_sale($connection, $sale_id, $user_id, $approver_id, $shift)
    {
        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $approver_id = (int)$approver_id;
        $shift_sql = mysqli_real_escape_string($connection, (string)$shift);
        $items = [];
        $items_result = mysqli_query($connection, "SELECT * FROM kp_sale_items WHERE sale_id='$sale_id'");
        while ($items_result && ($item = mysqli_fetch_assoc($items_result))) $items[] = $item;

        foreach ($items as $item) {
            $item_id = (int)($item['item_id'] ?? 0);
            $number = (float)($item['qty'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $counter = (int)($item['stock_counter'] ?? 0);
            $qty_column = 'cntr' . $counter . '_qty';
            $recipe = (int)view_repo_scalar($connection, 'kp_items', 'recipe', "item_id='$item_id'");
            if ($recipe === 1) {
                $recipe_result = mysqli_query($connection, "SELECT * FROM kp_recipe WHERE item_id='$item_id'");
                while ($recipe_result && ($recipe_row = mysqli_fetch_assoc($recipe_result))) {
                    $raw_id = (int)($recipe_row['raw_id'] ?? 0);
                    $qty = $number * (float)($recipe_row['qty'] ?? 0);
                    mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$qty' WHERE item_id='$raw_id'");
                    mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='$raw_id' AND user_id='$user_id' AND sale_id='$sale_id'");
                }
            } else {
                mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$number' WHERE item_id='$item_id'");
                mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='$item_id' AND user_id='$user_id' AND sale_id='$sale_id'");
            }
            mysqli_query($connection, "INSERT INTO kp_del_items(sale_id,item_id,qty,price,counter) VALUES('$sale_id','$item_id','$number','$price','$counter')");
        }

        $amount = view_repo_scalar($connection, 'kp_sales', 'amount', "sale_id='$sale_id'");
        mysqli_query($connection, "INSERT INTO kp_del_orders(sale_id,user_id,appr_by,amount,shift) VALUES('$sale_id','$user_id','$approver_id','$amount','$shift_sql')");
        if (function_exists('reverse_sale_tokens')) reverse_sale_tokens($sale_id, 'sale_void');
        $delete_items = mysqli_query($connection, "DELETE FROM kp_sale_items WHERE sale_id='$sale_id'");
        $delete_sale = mysqli_query($connection, "DELETE FROM kp_sales WHERE sale_id='$sale_id'");
        if ($delete_items && $delete_sale) {
            if (function_exists('logs')) logs('DELETED|-|-|-ORDER NO|' . $sale_id, '../');
            return true;
        }
        return false;
    }
}
