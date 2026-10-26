<?php
$dash_gen_funcs = __DIR__ . '/../../../inc/gen_funcs.php';
if (file_exists($dash_gen_funcs)) {
    include_once($dash_gen_funcs);
}
function open_counters(){
    global $connection;
    $get_counter = mysqli_query($connection,"SELECT * FROM kp_counters WHERE counter_status=1") or die("Could not get the open counters".mysqli_error($connection));
    $open=mysqli_num_rows($get_counter);
    return $open;
}

if (!function_exists('front_counter_scope_where')) {
    function front_counter_scope_where($drole, $alias = 'kp_counters') {
        $alias = trim((string)$alias);
        if ($alias === '') {
            $alias = 'kp_counters';
        }
        $prefix = $alias . '.';
        $drole = (int)$drole;
        if ($drole === 3) {
            return $prefix . "dpt=2";
        }
        if ($drole === 1) {
            return "(" . $prefix . "dpt IS NULL OR " . $prefix . "dpt<>2)";
        }
        return "1=0";
    }
}

if (!function_exists('front_role_can_open_counters')) {
    function front_role_can_open_counters($drole) {
        $drole = (int)$drole;
        return in_array($drole, [1, 3], true);
    }
}

if (!function_exists('front_role_pending_counter_count')) {
    function front_role_pending_counter_count($drole) {
        global $connection;
        if (!front_role_can_open_counters($drole)) {
            return 0;
        }
        $scope = front_counter_scope_where($drole);
        return (int)column_count('kp_counters', 'cntr_id', $scope . " AND del_status=2 AND counter_status<>1");
    }
}

if (!function_exists('front_role_open_counter_count')) {
    function front_role_open_counter_count($drole) {
        global $connection;
        if (!front_role_can_open_counters($drole)) {
            return 0;
        }
        $scope = front_counter_scope_where($drole);
        return (int)column_count('kp_counters', 'cntr_id', $scope . " AND del_status=2 AND counter_status=1");
    }
}

if (!function_exists('front_waiter_blocked_by_counter_setup')) {
    function front_waiter_blocked_by_counter_setup() {
        global $connection;
        $service_scope = front_counter_scope_where(1);
        $pending_open = (int)column_count('kp_counters', 'cntr_id', $service_scope . " AND del_status=2 AND counter_status<>1");
        if ($pending_open > 0) {
            return true;
        }
        $stock_ongoing = (int)column_count('kp_counters', 'cntr_id', $service_scope . " AND del_status=2 AND counter_status=1 AND take_status=1");
        return $stock_ongoing > 0;
    }
}

if (!function_exists('dash_ensure_merge_request_table')) {
    function dash_ensure_merge_request_table() {
        global $connection;

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_bill_merge_request (
            request_id INT NOT NULL AUTO_INCREMENT,
            merge_id INT NOT NULL DEFAULT 0,
            requester_user_id INT NOT NULL DEFAULT 0,
            requester_sale_id INT NOT NULL DEFAULT 0,
            requester_root_sale_id INT NOT NULL DEFAULT 0,
            target_user_id INT NOT NULL DEFAULT 0,
            target_sale_id INT NOT NULL DEFAULT 0,
            target_root_sale_id INT NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            responded_at DATETIME NULL,
            responded_by INT NOT NULL DEFAULT 0,
            note TEXT NULL,
            PRIMARY KEY (request_id),
            INDEX idx_merge_id (merge_id),
            INDEX idx_requester (requester_user_id),
            INDEX idx_target (target_user_id),
            INDEX idx_status (status)
        )") or die(mysqli_error($connection));

        $merge_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge_request LIKE 'merge_id'") or die(mysqli_error($connection));
        if ($merge_col && mysqli_num_rows($merge_col) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_bill_merge_request ADD COLUMN merge_id INT NOT NULL DEFAULT 0 AFTER request_id") or die(mysqli_error($connection));
        }

        $req_sale_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge_request LIKE 'requester_sale_id'") or die(mysqli_error($connection));
        if ($req_sale_col && mysqli_num_rows($req_sale_col) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_bill_merge_request ADD COLUMN requester_sale_id INT NOT NULL DEFAULT 0 AFTER requester_user_id") or die(mysqli_error($connection));
        }

        $target_sale_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge_request LIKE 'target_sale_id'") or die(mysqli_error($connection));
        if ($target_sale_col && mysqli_num_rows($target_sale_col) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_bill_merge_request ADD COLUMN target_sale_id INT NOT NULL DEFAULT 0 AFTER target_user_id") or die(mysqli_error($connection));
        }
    }
}

if (!function_exists('dash_build_payload')) {
    function dash_ensure_runtime_tables() {
        static $checked = false;
        if ($checked || (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['dash_schema_healed']))) {
            $checked = true;
            return;
        }
        foreach (['kp_users', 'kp_sales', 'kp_sale_status', 'kp_items', 'kp_staff_token_entries', 'kp_staff_token_wallet', 'kp_print_queue'] as $table) {
            if (function_exists('ensure_runtime_table')) {
                ensure_runtime_table($table);
            }
        }
        if (function_exists('get_print_mode')) {
            get_print_mode();
        }
        $checked = true;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['dash_schema_healed'] = 1;
        }
    }

    function dash_build_payload($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        dash_ensure_runtime_tables();
        $job = get_column('kp_users', 'job', "user_id='$user_id'");
        $djob = get_column('kp_users', 'drole', "user_id='$user_id'");
        $role = get_column('kp_users', 'role_id', "user_id='$user_id'");
        $shift = trim((string)get_column('kp_shift', 'shift', "1"));
        if ($shift === '0' || $shift === '0000-00-00') {
            $shift = '';
        }
        $counter_id = get_column('kp_users', 'counter', "user_id='$user_id'");
        $counter_name = $counter_id ? get_counter($counter_id) : 'No Counter';
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');
        $today = date('Y-m-d');
        $my_total_tokens = function_exists('get_user_token_total') ? (float)get_user_token_total($user_id) : 0.0;
        $my_today_tokens = (float)column_total('kp_staff_token_entries', 'token_amount', "user_id='$user_id' AND status=1 AND DATE(created_at)='$today'");
        $my_month_tokens = (float)column_total('kp_staff_token_entries', 'token_amount', "user_id='$user_id' AND status=1 AND created_at>='{$month_start} 00:00:00' AND created_at<='{$month_end} 23:59:59'");

        $max_sale = 0.0;
        $max_sale_column = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
        if ($max_sale_column && mysqli_num_rows($max_sale_column) > 0) {
            $max_sale = (float)get_column('kp_users', 'max_sale', "user_id='$user_id'");
        }

        $current_unpaid_sales = !empty($shift) ? (float)column_total('kp_sales', 'amount', "shift='$shift' AND pay_status=2 AND user_id='$user_id'") : 0.0;
        $sale_cap_percent = 0.0;
        if ($max_sale > 0) {
            $sale_cap_percent = min(($current_unpaid_sales / $max_sale) * 100, 100);
        }

        $sale_cap_percent_label = number_format($sale_cap_percent, 1);
        $sale_cap_locked = $max_sale > 0 && $current_unpaid_sales >= $max_sale;
        $sale_cap_remaining = max($max_sale - $current_unpaid_sales, 0);
        $my_total_bills = !empty($shift) ? (int)column_count('kp_sales', 'sale_id', "shift='$shift' AND user_id='$user_id'") : 0;
        $my_paid_bills = !empty($shift) ? (int)column_count('kp_sales', 'sale_id', "shift='$shift' AND user_id='$user_id' AND pay_status=1") : 0;
        $my_paid_amount = !empty($shift) ? (float)column_total('kp_sales', 'amount', "shift='$shift' AND user_id='$user_id' AND pay_status=1") : 0.0;
        $my_unpaid_bills = !empty($shift) ? (int)column_count('kp_sales', 'sale_id', "shift='$shift' AND user_id='$user_id' AND pay_status=2") : 0;
        $my_total_sales = !empty($shift) ? (float)column_total('kp_sales', 'amount', "shift='$shift' AND user_id='$user_id'") : 0.0;
        $my_month_sales = (float)column_total('kp_sales', 'amount', "user_id='$user_id' AND datime>='{$month_start} 00:00:00' AND datime<='{$month_end} 23:59:59'");
        $my_today_sales = (float)column_total('kp_sales', 'amount', "user_id='$user_id' AND DATE(datime)='$today'");
        $my_shift_returns = !empty($shift) ? (float)column_total('kp_ret_items', 'amount', "shift='$shift' AND user_id='$user_id' AND confirm_status=1") : 0.0;
        // The sales summary is a current-shift view. Do not mix historical
        // returns into the total shown to the user.
        $my_total_returns = !empty($shift)
            ? (float)column_total('kp_ret_items', 'amount', "shift='$shift' AND user_id='$user_id' AND confirm_status=1")
            : 0.0;
        $my_sold_items = [];
        $my_returned_items = [];
        $my_counter_summary = [];
        $my_changes_count = !empty($shift) ? (int)column_count('kp_sale_changes', 'change_batch_id', "user_id='$user_id'") : 0;
        $my_voided_bills = !empty($shift) ? (int)column_count('kp_del_orders', 'sale_id', "shift='$shift' AND user_id='$user_id'") : 0;
        $my_total_mpesa = !empty($shift) ? (float)column_total('kp_split_message', 'amount', "shift='$shift' AND user_id='$user_id'") : 0.0;
        $my_items_sold = !empty($shift) ? (float)column_total('kp_sale_items', 'qty', "shift='$shift' AND user_id='$user_id'") : 0.0;
        $my_return_accepted = !empty($shift) ? (int)column_count('kp_ret_items', 'sale_id', "shift='$shift' AND user_id='$user_id' AND confirm_status=1") : 0;
        $my_return_rejected = !empty($shift) ? (int)column_count('kp_ret_items', 'sale_id', "shift='$shift' AND user_id='$user_id' AND confirm_status=3") : 0;
        $my_return_pending = !empty($shift) ? (int)column_count('kp_ret_items', 'sale_id', "shift='$shift' AND user_id='$user_id' AND ret_status=3") : 0;
        if (!empty($shift)) {
            $safe_shift = mysqli_real_escape_string($connection, (string)$shift);
            $sold_query = mysqli_query($connection, "SELECT si.item_id,
                    COALESCE(NULLIF(si.counter, 0), s.counter, 0) AS counter_id,
                    COALESCE(c.name, CONCAT('Counter ', COALESCE(NULLIF(si.counter, 0), s.counter, 0))) AS counter_name,
                    SUM(si.qty) AS qty,
                    SUM(COALESCE(si.total, si.qty * si.price)) AS total
                FROM kp_sale_items si
                LEFT JOIN kp_sales s ON s.sale_id=si.sale_id
                LEFT JOIN kp_counters c ON c.cntr_id=COALESCE(NULLIF(si.counter, 0), s.counter)
                WHERE si.user_id='$user_id' AND si.shift='$safe_shift'
                GROUP BY si.item_id, COALESCE(NULLIF(si.counter, 0), s.counter), c.name
                ORDER BY total DESC");
            while ($sold_query && ($row = mysqli_fetch_assoc($sold_query))) {
                $counter_id = (int)$row['counter_id'];
                $counter_key = (string)$counter_id;
                if (!isset($my_counter_summary[$counter_key])) {
                    $my_counter_summary[$counter_key] = [
                        'counter_id' => $counter_id,
                        'counter' => (string)$row['counter_name'],
                        'sold_qty' => 0,
                        'sold_total' => 0,
                        'return_qty' => 0,
                        'return_total' => 0,
                    ];
                }
                $my_counter_summary[$counter_key]['sold_qty'] += (float)$row['qty'];
                $my_counter_summary[$counter_key]['sold_total'] += (float)$row['total'];
                $my_sold_items[] = [
                    'name' => (string)get_item((int)$row['item_id']),
                    'qty' => (float)$row['qty'],
                    'total' => (float)$row['total'],
                    'counter' => (string)$row['counter_name'],
                    'counter_id' => (int)$row['counter_id'],
                ];
            }
            $returned_query = mysqli_query($connection, "SELECT ri.item_id, ri.qty, ri.amount, ri.confirm_status, ri.confirm_by,
                    COALESCE(ri.sale_counter, 0) AS counter_id,
                    COALESCE(c.name, CONCAT('Counter ', COALESCE(ri.sale_counter, 0))) AS counter_name
                FROM kp_ret_items ri
                LEFT JOIN kp_counters c ON c.cntr_id=ri.sale_counter
                WHERE ri.user_id='$user_id' AND ri.shift='$safe_shift'
                ORDER BY ri.id DESC");
            while ($returned_query && ($row = mysqli_fetch_assoc($returned_query))) {
                $status = (int)($row['confirm_status'] ?? 2);
                $counter_id = (int)($row['counter_id'] ?? 0);
                $counter_key = (string)$counter_id;
                if (!isset($my_counter_summary[$counter_key])) {
                    $my_counter_summary[$counter_key] = [
                        'counter_id' => $counter_id,
                        'counter' => (string)$row['counter_name'],
                        'sold_qty' => 0,
                        'sold_total' => 0,
                        'return_qty' => 0,
                        'return_total' => 0,
                    ];
                }
                $my_counter_summary[$counter_key]['return_qty'] += (float)$row['qty'];
                $my_counter_summary[$counter_key]['return_total'] += (float)$row['amount'];
                $my_returned_items[] = [
                    'name' => (string)get_item((int)$row['item_id']),
                    'qty' => (float)$row['qty'],
                    'amount' => (float)$row['amount'],
                    'counter' => (string)$row['counter_name'],
                    'status' => $status === 1 ? 'APPROVED' : ($status === 3 ? 'REJECTED' : 'PENDING'),
                    'confirmed_by' => (int)($row['confirm_by'] ?? 0) > 0 ? (string)get_user((int)$row['confirm_by']) : '—',
                ];
            }
        }
        $my_counter_summary = array_values($my_counter_summary);
        usort($my_counter_summary, function ($a, $b) {
            return ((float)$b['sold_total'] + (float)$b['return_total']) <=> ((float)$a['sold_total'] + (float)$a['return_total']);
        });

        // The dashboard orders table is loaded from the same active shift/user
        // as the sales totals. Keep this query tolerant of older kp_sales
        // schemas by reading the available timestamp field from each row.
        $recent_orders = [];
        if (!empty($shift)) {
            $safe_shift = mysqli_real_escape_string($connection, (string)$shift);
            $recent_query = mysqli_query(
                $connection,
                "SELECT * FROM kp_sales WHERE shift='$safe_shift' AND user_id='$user_id' AND pay_status=2 ORDER BY sale_id DESC LIMIT 5"
            );
            while ($recent_query && $recent_row = mysqli_fetch_assoc($recent_query)) {
                $pay_status = (int)($recent_row['pay_status'] ?? 0);
                $status = $pay_status === 1 ? 'PAID' : ($pay_status === 3 ? 'CREDIT' : 'PENDING');
                $time = '';
                foreach (['created_at', 'datime', 'order_time', 'sale_time', 'time'] as $time_column) {
                    if (!empty($recent_row[$time_column])) {
                        $time = (string)$recent_row[$time_column];
                        break;
                    }
                }
                $recent_orders[] = [
                    'sale_id' => (int)($recent_row['sale_id'] ?? 0),
                    'amount' => (float)($recent_row['amount'] ?? 0),
                    'status' => $status,
                    'time' => $time,
                ];
            }
        }

        $job_name = function_exists('get_job') ? strtolower((string)get_job($job)) : '';
        $is_barman = (strpos($job_name, 'bar') !== false || (int)$job === 1);
        $counter_return_pending = (!empty($shift) && !empty($counter_id) && $is_barman)
            ? (int)column_count('kp_ret_items', 'sale_id', "shift='$shift' AND stock_counter='$counter_id' AND ret_status=3")
            : 0;

        $sale_cap_state = 'safe';
        $sale_cap_state_label = 'Safe';
        $sale_cap_color = '#61b846';
        if ($sale_cap_percent > 90) {
            $sale_cap_state = 'danger';
            $sale_cap_state_label = 'Limit Reached';
            $sale_cap_color = '#b94d19';
        } elseif ($sale_cap_percent >= 70) {
            $sale_cap_state = 'warning';
            $sale_cap_state_label = 'Approaching Limit';
            $sale_cap_color = '#f36c26';
        }

        dash_ensure_merge_request_table();
        $incoming_merge_request_count = (int)column_count('kp_bill_merge_request', 'request_id', "target_user_id='$user_id' AND status=1");
        // Counter availability must be scoped to the user's department.
        // Otherwise an open bar counter can hide the Open Counter action for
        // a kitchen user even when a kitchen counter is still available.
        $counter_scope = front_counter_scope_where((int)$djob, 'kp_counters');
        $total_counters = (int)column_count('kp_counters', 'cntr_id', "del_status=2 AND {$counter_scope}");
        $open_counters = (int)column_count('kp_counters', 'cntr_id', "del_status=2 AND counter_status=1 AND {$counter_scope}");

        $shift_date = '';
        $shift_date_candidates = ['shift_date', 'date', 'sdate'];
        foreach ($shift_date_candidates as $shift_date_column) {
            $shift_date_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_shift LIKE '$shift_date_column'");
            if ($shift_date_chk && mysqli_num_rows($shift_date_chk) > 0) {
                $shift_date = trim((string)get_column('kp_shift', $shift_date_column, "1"));
                if ($shift_date !== '') {
                    break;
                }
            }
        }

        // kp_shift.shift is the active shift value in this installation.
        if ($shift_date === '') {
            $shift_date = trim((string)$shift);
        }

        $ranking_position = '—';
        if ($shift !== '') {
            $shift_escaped = mysqli_real_escape_string($connection, (string)$shift);
            $ranking_query = mysqli_query(
                $connection,
                "SELECT user_id, SUM(amount) AS total_sales
                 FROM kp_sales
                 WHERE shift='$shift_escaped'
                 GROUP BY user_id
                 ORDER BY total_sales DESC, user_id ASC"
            );
            $rank = 0;
            while ($ranking_query && $ranking_row = mysqli_fetch_assoc($ranking_query)) {
                $rank++;
                if ((int)($ranking_row['user_id'] ?? 0) === $user_id) {
                    $ranking_position = $rank;
                    break;
                }
            }
        }

        $shift_has_date = $shift_date !== '' && $shift_date !== '0000-00-00' && $shift_date !== '0000-00-00 00:00:00';
        // A stale date column must not make the dashboard treat the shift as open.
        // The active shift value is the source of truth.
        $shift_is_open = trim((string)$shift) !== '' && $shift_has_date;
        $can_open_shift = !$shift_is_open && (int)$djob === 1 && (int)$job === 2;
        $role_pending_counters = function_exists('front_role_pending_counter_count') ? (int)front_role_pending_counter_count($djob) : 0;
        // Counter visibility is based on the user's counter permission and
        // available counters. Opening a counter must not depend on a shift
        // already being open; the counter-open flow handles that separately.
        $can_open_counter = $shift_is_open
            && in_array((int)$djob, [1, 3], true)
            && (int)$job === 2
            && $open_counters < $total_counters;

        return [
            'user' => [
                'id' => $user_id,
                'name' => function_exists('get_user') ? get_user($user_id) : '',
                'job_id' => (int)$job,
                'job_name' => $job_name,
                'drole' => (int)$djob,
                'role_id' => (int)$role,
                'counter_id' => (int)$counter_id,
                'counter_name' => $counter_name,
                'is_barman' => $is_barman,
                'redirect_to_cashier' => ((int)$role === 3),
            ],
            'shift' => [
                'name' => $shift,
                'date' => $shift_date,
                'has_date' => $shift_is_open,
                'can_open_shift' => $can_open_shift,
                'can_open_counter' => $can_open_counter,
                'pending_counters' => $role_pending_counters,
            ],
            'counters' => [
                'total' => $total_counters,
                'open' => $open_counters,
            ],
            'tokens' => [
                'total' => $my_total_tokens,
                'today' => $my_today_tokens,
                'month' => $my_month_tokens,
            ],
            'sales' => [
                'total_bills' => $my_total_bills,
                'paid_bills' => $my_paid_bills,
                'paid_amount' => $my_paid_amount,
                'unpaid_bills' => $my_unpaid_bills,
                'total_sales' => $my_total_sales,
                'month_sales' => $my_month_sales,
                'today_sales' => $my_today_sales,
                'shift_returns' => $my_shift_returns,
                'total_returns' => $my_total_returns,
                'sold_items' => $my_sold_items,
                'returned_items' => $my_returned_items,
                'counter_summary' => $my_counter_summary,
                'changes_count' => $my_changes_count,
                'voided_bills' => $my_voided_bills,
                'total_mpesa' => $my_total_mpesa,
                'items_sold' => $my_items_sold,
                'position' => $ranking_position,
            ],
            'recent_orders' => $recent_orders,
            'position' => $ranking_position,
            'ranking_position' => $ranking_position,
            'returns' => [
                'accepted' => $my_return_accepted,
                'rejected' => $my_return_rejected,
                'pending' => $my_return_pending,
                'counter_pending' => $counter_return_pending,
            ],
            'sale_cap' => [
                'max_sale' => $max_sale,
                'current_unpaid_sales' => $current_unpaid_sales,
                'percent' => $sale_cap_percent,
                'percent_label' => $sale_cap_percent_label,
                'remaining' => $sale_cap_remaining,
                'locked' => $sale_cap_locked,
                'state' => $sale_cap_state,
                'state_label' => $sale_cap_state_label,
                'color' => $sale_cap_color,
            ],
            'merge_requests' => [
                'incoming_count' => $incoming_merge_request_count,
            ],
            'meta' => [
                'today' => $today,
                'month_start' => $month_start,
                'month_end' => $month_end,
            ],
        ];
    }
}

if (!function_exists('dash_build_alerts')) {
    function dash_build_alerts($user_id, $msg = null, $redir = '') {
        global $connection;

        $alerts = [];
        $user_id = (int)$user_id;
        $msg = is_null($msg) ? null : (int)$msg;
        $redir = trim((string)$redir);

        if ($redir === 'order_lost') {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Order Session Ended',
                'message' => 'Your active order session ended, so you were returned to the dashboard. Please open the order again to continue.',
                'items' => [],
            ];
        }

        if ($msg === 6) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Close Your Open Orders',
                'message' => 'Close your open orders.',
                'items' => [],
            ];
        } elseif ($msg === 5) {
            $items = [];
            $check_shift = mysqli_query($connection, "SELECT user_username FROM kp_users WHERE shift_status=1 AND job!=1");
            while ($check_shift && $row = mysqli_fetch_assoc($check_shift)) {
                $items[] = $row['user_username'];
            }
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Staff With Open Shift',
                'message' => 'Staff with open shift.',
                'items' => $items,
            ];
        } elseif ($msg === 4) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Open Orders Pending',
                'message' => 'Close your open orders to close your shift.',
                'items' => [],
            ];
        } elseif ($msg === 1) {
            $items = [];
            $check_shift = mysqli_query($connection, "SELECT user_id FROM kp_sale_status");
            while ($check_shift && $row = mysqli_fetch_assoc($check_shift)) {
                $items[] = function_exists('get_user') ? get_user((int)$row['user_id']) : (string)$row['user_id'];
            }
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Ask Staff To Close Orders',
                'message' => 'Ask the following staff to close their open orders.',
                'items' => $items,
            ];
        } elseif ($msg === 2) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Blank Order',
                'message' => "Order can't be blank.",
                'items' => [],
            ];
        }

        return $alerts;
    }
}
?>
