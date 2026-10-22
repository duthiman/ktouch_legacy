<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../../inc/connection.php');
require_once('funcs.php');
require_once('stock_alerts.php');

if (!function_exists('dash_money_live')) {
    function dash_money_live($value){
        return number_format((float)$value, 2);
    }
}

if (!function_exists('dash_user_name_live')) {
    function dash_user_name_live($user_id){
        if ((int)$user_id < 1) {
            return 'Unknown';
        }
        return get_user($user_id);
    }
}

if (!function_exists('dash_time_ago_live')) {
    function dash_time_ago_live($datetime){
        if (!$datetime || $datetime == '0000-00-00 00:00:00') {
            return 'just now';
        }
        $time = strtotime($datetime);
        if (!$time) {
            return 'just now';
        }
        $diff = time() - $time;
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' min ago';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' hr ago';
        }
        return floor($diff / 86400) . ' day ago';
    }
}
if (!function_exists('dash_table_exists_live')) {
    function dash_table_exists_live($table){
        global $connection;
        $safe = mysqli_real_escape_string($connection, $table);
        $check = mysqli_query($connection, "SHOW TABLES LIKE '$safe'");
        return $check && mysqli_num_rows($check) > 0;
    }
}
if (!function_exists('dash_add_activity_live')) {
    function dash_add_activity_live(&$rows, $title, $detail, $time_label, $tone, $sort_ts, $rank = 0){
        $rows[] = array(
            'title' => $title,
            'detail' => $detail,
            'time' => $time_label,
            'tone' => $tone,
            'sort_ts' => (int)$sort_ts,
            'sort_rank' => (int)$rank
        );
    }
}
$sale_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'created_at'") or die(mysqli_error($connection));
if (mysqli_num_rows($sale_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP") or die(mysqli_error($connection));
}
$payment_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE 'created_at'") or die(mysqli_error($connection));
if (mysqli_num_rows($payment_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP") or die(mysqli_error($connection));
}
$req_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'created_at'") or die(mysqli_error($connection));
if ($req_created_col && mysqli_num_rows($req_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP") or die(mysqli_error($connection));
}
$req_issue_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'issue_created_at'") or die(mysqli_error($connection));
if ($req_issue_created_col && mysqli_num_rows($req_issue_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN issue_created_at TIMESTAMP NULL DEFAULT NULL") or die(mysqli_error($connection));
}
$req_rec_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'rec_created_at'") or die(mysqli_error($connection));
if ($req_rec_created_col && mysqli_num_rows($req_rec_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN rec_created_at TIMESTAMP NULL DEFAULT NULL") or die(mysqli_error($connection));
}
$cust_hist_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_cust_hist LIKE 'created_at'") or die(mysqli_error($connection));
if ($cust_hist_created_col && mysqli_num_rows($cust_hist_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_cust_hist ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP") or die(mysqli_error($connection));
}

$shift = shift();
$today = date('Y-m-d');
if ($shift == 0 || empty($shift)) {
    $shift = $today;
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $shift)) {
    $parsed_shift = strtotime($shift);
    $shift = $parsed_shift ? date('Y-m-d', $parsed_shift) : $today;
}
$report_anchor = $shift;
$last_shift = '';
$last_shift_q = mysqli_query($connection,"SELECT shift FROM kp_sales WHERE shift<'$report_anchor' GROUP BY shift ORDER BY shift DESC LIMIT 1") or die(mysqli_error($connection));
if ($last_shift_q && mysqli_num_rows($last_shift_q) > 0) {
    $last_shift = mysqli_fetch_assoc($last_shift_q)['shift'];
}
if ($last_shift == '') {
    $last_shift = date('Y-m-d', strtotime($report_anchor . ' -1 day'));
}

$orders_total = $shift == 0 ? 0 : (float)column_total('kp_sales','amount',"shift='$shift'");
$orders_count = $shift == 0 ? 0 : (int)column_count('kp_sales','sale_id',"shift='$shift'");
$paid_total = $shift == 0 ? 0 : (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=1");
$unpaid_total = $shift == 0 ? 0 : (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=2");
$unpaid_count = $shift == 0 ? 0 : (int)column_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2");
$expenses_total = $shift == 0 ? 0 : (float)column_total('kp_shop_expenses','amount',"shift_date='$shift'");
$expense_count = $shift == 0 ? 0 : (int)column_count('kp_shop_expenses','exp_id',"shift_date='$shift'");
$credit_today = (float)column_total('kp_cust_hist','amount',"trans='SALE' AND trans_date='$today'");
$credit_paid_today = $shift == 0 ? 0 : (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date='$shift'");
$credit_paid_previous = $shift == 0 ? 0 : (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date!='$shift'");
$credit_customers = (int)column_count('kp_customers','cust_id',"bal>0");
$net_after_expenses = $paid_total - $expenses_total;
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$today_tokens_total = (float)column_total('kp_staff_token_entries','token_amount',"status=1 AND DATE(created_at)='$today'");
$monthly_tokens_total = (float)column_total('kp_staff_token_entries','token_amount',"status=1 AND created_at>='{$month_start} 00:00:00' AND created_at<='{$month_end} 23:59:59'");
$monthly_token_users = (int)column_count('kp_staff_token_wallet','user_id',"total_tokens>0");
$avg_per_order = $orders_count > 0 ? ($orders_total / $orders_count) : 0;
$previous_total = (float)column_total('kp_sales','amount',"shift='$last_shift'");
$sales_compare_pct = $previous_total > 0 ? (($orders_total - $previous_total) / $previous_total) * 100 : 0;
$counter_reqs_pending = (int)counter_req();
$open_counters_total = (int)column_count('kp_counters','cntr_id',"counter_status=1");
$active_cashiers_total = (int)column_count('kp_users','user_id',"job=1 AND counter_status=1");
$stock_alerts = dash_stock_alerts();

$open_counter_cards = array();
$open_counter_q = mysqli_query($connection,"SELECT c.name AS counter_name, u.username FROM kp_counters c LEFT JOIN kp_users u ON c.user_id=u.user_id WHERE c.counter_status=1 ORDER BY c.name ASC") or die(mysqli_error($connection));
while ($open_row = mysqli_fetch_assoc($open_counter_q)) {
    $open_counter_cards[] = array(
        'counter' => $open_row['counter_name'],
        'cashier' => $open_row['username'] != '' ? $open_row['username'] : 'No cashier'
    );
}

$counter_rows = array(array('Counter', 'Sales', 'Items'));
$counter_table = array();
$unbalanced_counters = 0;
$counter_q = mysqli_query($connection,"SELECT cntr_id,name FROM kp_counters ORDER BY name ASC") or die(mysqli_error($connection));
while ($row = mysqli_fetch_assoc($counter_q)) {
    $cntr_id = (int)$row['cntr_id'];
    $sales_amt = $shift == 0 ? 0 : (float)column_total('kp_sales','amount',"shift='$shift' AND counter='$cntr_id'");
    $item_amt = $shift == 0 ? 0 : (float)column_total('kp_sale_items','total',"shift='$shift' AND counter='$cntr_id'");
    $status = abs($sales_amt - $item_amt) < 0.01 ? 'BALANCED' : 'CHECK';
    if ($status !== 'BALANCED') {
        $unbalanced_counters++;
    }
    $counter_rows[] = array($row['name'], $sales_amt, $item_amt);
    $counter_table[] = array(
        'id' => $cntr_id,
        'name' => $row['name'],
        'sales' => dash_money_live($sales_amt),
        'status' => $status !== 'BALANCED' ? 'Unbalanced' : 'Balanced',
        'is_bad' => $status !== 'BALANCED'
    );
}

$payment_mode_rows = array(array('Mode', 'Amount'));
$pay_modes_q = mysqli_query($connection,"SELECT * FROM kp_pay_mode ORDER BY pay_id ASC") or die(mysqli_error($connection));
while ($mode = mysqli_fetch_assoc($pay_modes_q)) {
    $col = 'mode' . (int)$mode['pay_id'];
    $amount = $shift == 0 ? 0 : (float)column_total('kp_sale_payment',$col,"pay_date='$shift'");
    if ($amount > 0) {
        $payment_mode_rows[] = array($mode['name'], $amount);
    }
}

$staff_rows = array(array('Staff', 'Sales'));
$staff_table = array();
$staff_q = $shift == 0 ? false : mysqli_query($connection,"SELECT user_id,COUNT(sale_id) AS bills,SUM(amount) AS total FROM kp_sales WHERE shift='$shift' GROUP BY user_id ORDER BY total DESC") or die(mysqli_error($connection));
if ($staff_q) {
    while ($row = mysqli_fetch_assoc($staff_q)) {
        $staff_name = dash_user_name_live($row['user_id']);
        $staff_rows[] = array($staff_name, (float)$row['total']);
        $staff_table[] = array(
            'name' => $staff_name,
            'bills' => (int)$row['bills'],
            'total' => dash_money_live($row['total'])
        );
    }
}

$activity_rows = array();
if ($shift != 0) {
    $payment_activity_q = mysqli_query($connection,"SELECT pay_id, paid_total, user_id, created_at FROM kp_sale_payment WHERE pay_date='$shift' ORDER BY pay_id DESC LIMIT 4") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($payment_activity_q)) {
        dash_add_activity_live($activity_rows, 'Payment #' . $row['pay_id'], dash_user_name_live($row['user_id']) . ' | ' . dash_money_live($row['paid_total']), dash_time_ago_live($row['created_at']), 'success', strtotime($row['created_at']), (int)$row['pay_id']);
    }

    $sale_activity_q = mysqli_query($connection,"SELECT sale_id,amount,user_id,pay_status,created_at FROM kp_sales WHERE shift='$shift' ORDER BY sale_id DESC LIMIT 4") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($sale_activity_q)) {
        dash_add_activity_live($activity_rows, 'Sale #' . $row['sale_id'], dash_user_name_live($row['user_id']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($row['created_at']), $row['pay_status'] == 1 ? 'success' : ($row['pay_status'] == 3 ? 'info' : 'warning'), strtotime($row['created_at']), (int)$row['sale_id']);
    }

    $expense_activity_q = mysqli_query($connection,"SELECT exp_id,title,amount,created_at FROM kp_shop_expenses WHERE shift_date='$shift' ORDER BY exp_id DESC LIMIT 2") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($expense_activity_q)) {
        dash_add_activity_live($activity_rows, 'Expense: ' . $row['title'], dash_money_live($row['amount']), dash_time_ago_live($row['created_at']), 'danger', strtotime($row['created_at']), (int)$row['exp_id']);
    }

    if (dash_table_exists_live('kp_customer_credit_payments')) {
        $credit_pay_q = mysqli_query($connection,"SELECT credit_pay_id,cust_id,amount,created_at FROM kp_customer_credit_payments WHERE shift_date='$shift' ORDER BY credit_pay_id DESC LIMIT 2");
        if ($credit_pay_q) {
            while ($row = mysqli_fetch_assoc($credit_pay_q)) {
                dash_add_activity_live($activity_rows, 'Credit Payment #' . $row['credit_pay_id'], get_cust((int)$row['cust_id']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($row['created_at']), 'success', strtotime($row['created_at']), (int)$row['credit_pay_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_cust_hist')) {
        $credit_sale_q = mysqli_query($connection,"SELECT trans_id,cust_id,amount,trans_date,created_at FROM kp_cust_hist WHERE trans='SALE' AND trans_date='$today' ORDER BY COALESCE(created_at, CONCAT(trans_date,' 00:00:00')) DESC, trans_id DESC LIMIT 2");
        if ($credit_sale_q) {
            while ($row = mysqli_fetch_assoc($credit_sale_q)) {
                $credit_time = !empty($row['created_at']) && $row['created_at'] !== '0000-00-00 00:00:00'
                    ? $row['created_at']
                    : ($row['trans_date'] . ' 00:00:00');
                dash_add_activity_live($activity_rows, 'Credit Given #' . $row['trans_id'], get_cust((int)$row['cust_id']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($credit_time), 'info', strtotime($credit_time), (int)$row['trans_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_counter_handover')) {
        $handover_q = mysqli_query($connection,"SELECT handover_id,from_user,to_user,total_variance,created_at FROM kp_counter_handover WHERE shift_date='$shift' ORDER BY handover_id DESC LIMIT 2");
        if ($handover_q) {
            while ($row = mysqli_fetch_assoc($handover_q)) {
                dash_add_activity_live($activity_rows, 'Shift Handover #' . $row['handover_id'], get_user((int)$row['from_user']) . ' to ' . get_user((int)$row['to_user']) . ' | Var ' . dash_money_live($row['total_variance']), dash_time_ago_live($row['created_at']), abs((float)$row['total_variance']) < 0.01 ? 'success' : 'warning', strtotime($row['created_at']), (int)$row['handover_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_counter_req')) {
        $req_q = mysqli_query($connection,"SELECT req_id,req_user,counter,req_shift,created_at FROM kp_counter_req WHERE req_shift='$shift' ORDER BY COALESCE(created_at, CONCAT(req_shift,' 00:00:00')) DESC, req_id DESC LIMIT 2");
        if ($req_q) {
            while ($row = mysqli_fetch_assoc($req_q)) {
                $req_time = !empty($row['created_at']) && $row['created_at'] !== '0000-00-00 00:00:00' ? $row['created_at'] : ($row['req_shift'] . ' 00:00:00');
                dash_add_activity_live($activity_rows, 'Stock Requisition #' . $row['req_id'], get_user((int)$row['req_user']) . ' | ' . get_counter($row['counter']), dash_time_ago_live($req_time), 'warning', strtotime($req_time), (int)$row['req_id']);
            }
        }
        $issue_q = mysqli_query($connection,"SELECT req_id,issue_user,counter,issue_shift,issue_created_at FROM kp_counter_req WHERE issue_status=1 AND issue_shift='$shift' ORDER BY COALESCE(issue_created_at, CONCAT(issue_shift,' 00:00:00')) DESC, req_id DESC LIMIT 2");
        if ($issue_q) {
            while ($row = mysqli_fetch_assoc($issue_q)) {
                $issue_time = !empty($row['issue_created_at']) && $row['issue_created_at'] !== '0000-00-00 00:00:00' ? $row['issue_created_at'] : ($row['issue_shift'] . ' 00:00:00');
                dash_add_activity_live($activity_rows, 'Stock Issued #' . $row['req_id'], get_user((int)$row['issue_user']) . ' | ' . get_counter($row['counter']), dash_time_ago_live($issue_time), 'success', strtotime($issue_time), (int)$row['req_id']);
            }
        }
        $receive_req_q = mysqli_query($connection,"SELECT req_id,rec_user,counter,rec_created_at FROM kp_counter_req WHERE rec_status=1 AND req_shift='$shift' ORDER BY COALESCE(rec_created_at, CONCAT(req_shift,' 00:00:00')) DESC, req_id DESC LIMIT 2");
        if ($receive_req_q) {
            while ($row = mysqli_fetch_assoc($receive_req_q)) {
                $rec_time = !empty($row['rec_created_at']) && $row['rec_created_at'] !== '0000-00-00 00:00:00' ? $row['rec_created_at'] : ($shift . ' 00:00:00');
                dash_add_activity_live($activity_rows, 'Stock Received #' . $row['req_id'], get_user((int)$row['rec_user']) . ' | ' . get_counter($row['counter']), dash_time_ago_live($rec_time), 'success', strtotime($rec_time), (int)$row['req_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_ret_items')) {
        $return_q = mysqli_query($connection,"SELECT sale_id,user_id,amount,id FROM kp_ret_items WHERE shift='$shift' ORDER BY id DESC LIMIT 2");
        if ($return_q) {
            while ($row = mysqli_fetch_assoc($return_q)) {
                dash_add_activity_live($activity_rows, 'Return #' . $row['sale_id'], dash_user_name_live((int)$row['user_id']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($shift . ' 20:00:00'), 'warning', strtotime($shift . ' 20:00:00'), (int)$row['id']);
            }
        }
    }

    if (dash_table_exists_live('kp_del_orders')) {
        $void_q = mysqli_query($connection,"SELECT sale_id,user_id,amount FROM kp_del_orders WHERE shift='$shift' GROUP BY sale_id,user_id,amount ORDER BY sale_id DESC LIMIT 2");
        if ($void_q) {
            while ($row = mysqli_fetch_assoc($void_q)) {
                dash_add_activity_live($activity_rows, 'Voided Sale #' . $row['sale_id'], dash_user_name_live((int)$row['user_id']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($shift . ' 19:00:00'), 'danger', strtotime($shift . ' 19:00:00'), (int)$row['sale_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_cancelled')) {
        $cancel_q = mysqli_query($connection,"SELECT sale_id,user_id,amount FROM kp_cancelled WHERE shift='$shift' ORDER BY sale_id DESC LIMIT 2");
        if ($cancel_q) {
            while ($row = mysqli_fetch_assoc($cancel_q)) {
                dash_add_activity_live($activity_rows, 'Cancelled Sale #' . $row['sale_id'], dash_user_name_live((int)$row['user_id']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($shift . ' 18:00:00'), 'danger', strtotime($shift . ' 18:00:00'), (int)$row['sale_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_tot')) {
        $tot_q = mysqli_query($connection,"SELECT tot_id,user_id,counter FROM kp_tot WHERE shift='$shift' ORDER BY tot_id DESC LIMIT 2");
        if ($tot_q) {
            while ($row = mysqli_fetch_assoc($tot_q)) {
                dash_add_activity_live($activity_rows, 'T.O.T Generated #' . $row['tot_id'], dash_user_name_live((int)$row['user_id']) . ' | ' . get_counter($row['counter']), dash_time_ago_live($shift . ' 17:00:00'), 'info', strtotime($shift . ' 17:00:00'), (int)$row['tot_id']);
            }
        }
    }

    if (dash_table_exists_live('kp_po')) {
        $purchase_q = mysqli_query($connection,"SELECT po_id,ord_user,amount,po_date FROM kp_po WHERE shift='$shift' ORDER BY po_id DESC LIMIT 2");
        if ($purchase_q) {
            while ($row = mysqli_fetch_assoc($purchase_q)) {
                dash_add_activity_live($activity_rows, 'Purchase #' . $row['po_id'], dash_user_name_live((int)$row['ord_user']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($row['po_date'] . ' 16:00:00'), 'info', strtotime($row['po_date'] . ' 16:00:00'), (int)$row['po_id']);
            }
        }
        $receive_po_q = mysqli_query($connection,"SELECT po_id,rec_staff,amount,rec_date FROM kp_po WHERE rec_status=1 AND rec_date='$shift' ORDER BY po_id DESC LIMIT 2");
        if ($receive_po_q) {
            while ($row = mysqli_fetch_assoc($receive_po_q)) {
                dash_add_activity_live($activity_rows, 'Stock Receive #' . $row['po_id'], dash_user_name_live((int)$row['rec_staff']) . ' | ' . dash_money_live($row['amount']), dash_time_ago_live($row['rec_date'] . ' 15:00:00'), 'success', strtotime($row['rec_date'] . ' 15:00:00'), (int)$row['po_id']);
            }
        }
    }
}
usort($activity_rows, function($a, $b) {
    $time_cmp = ($b['sort_ts'] ?? 0) <=> ($a['sort_ts'] ?? 0);
    if ($time_cmp !== 0) {
        return $time_cmp;
    }
    return ($b['sort_rank'] ?? 0) <=> ($a['sort_rank'] ?? 0);
});
$activity_rows = array_slice($activity_rows, 0, 8);

$hero_html = '';
if (empty($open_counter_cards)) {
    $hero_html = '<div class="db-hero-counter-empty">No open counters</div>';
} else {
    foreach ($open_counter_cards as $counter_card) {
        $hero_html .= '<div class="db-hero-counter-card"><div class="db-hero-counter-label">Open Counter</div><div class="db-hero-counter-name">' . htmlspecialchars($counter_card['counter']) . '</div><div class="db-hero-counter-meta">' . htmlspecialchars($counter_card['cashier']) . '</div></div>';
    }
}

$activity_html = '';
if (empty($activity_rows)) {
    $activity_html = '<div class="text-center text-muted py-3" style="font-size:.68rem;">No recent activity yet.</div>';
} else {
    foreach ($activity_rows as $activity) {
        $tone_class = $activity['tone'] === 'success' ? 'db-alert-success' : ($activity['tone'] === 'warning' ? 'db-alert-warning' : ($activity['tone'] === 'danger' ? 'db-alert-danger' : 'db-alert-info'));
        $activity_html .= '<div class="db-feed-item"><div class="db-feed-dot ' . $tone_class . '"><i class="fa fa-circle"></i></div><div class="db-feed-text"><div class="db-feed-title">' . htmlspecialchars($activity['title']) . '</div><div class="db-feed-detail">' . htmlspecialchars($activity['detail']) . '</div></div><div class="db-feed-time">' . htmlspecialchars($activity['time']) . '</div></div>';
    }
}

$staff_sales_html = '<table class="db-top-tbl db-tight-tbl"><thead><tr><th>#</th><th>Staff</th><th>Orders</th><th>Total</th></tr></thead><tbody>';
if (empty($staff_table)) {
    $staff_sales_html .= '<tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.68rem;">No staff sales yet.</td></tr>';
} else {
    $staff_no = 0;
    foreach ($staff_table as $row) {
        $staff_no++;
        $rank_class = $staff_no <= 5 ? 'r' . $staff_no : 'rX';
        $staff_sales_html .= '<tr><td><span class="db-staff-rank ' . $rank_class . '">' . $staff_no . '</span></td><td class="db-staff-name">' . htmlspecialchars($row['name']) . '</td><td class="db-staff-orders">' . $row['bills'] . '</td><td class="db-staff-total">' . $row['total'] . '</td></tr>';
    }
}
$staff_sales_html .= '</tbody></table>';

$counter_health_html = '';
$no = 0;
foreach ($counter_table as $row) {
    $no++;
    $counter_health_html .= '<tr' . ($row['is_bad'] ? ' class="db-counter-row-alert"' : '') . '><td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="' . (int)$row['id'] . '">' . $no . '</a></td><td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="' . (int)$row['id'] . '">' . htmlspecialchars($row['name']) . '</a></td><td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="' . (int)$row['id'] . '">' . $row['sales'] . '</a></td><td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="' . (int)$row['id'] . '"><span class="db-status-pill ' . ($row['is_bad'] ? 'db-status-bad' : 'db-status-ok') . '">' . $row['status'] . '</span></a></td></tr>';
}

$payload = array(
    'totals' => array(
        'orders_total' => dash_money_live($orders_total),
        'orders_count' => $orders_count,
        'avg_per_order' => dash_money_live($avg_per_order),
        'credit_today' => dash_money_live($credit_today),
        'credit_collected' => dash_money_live($credit_paid_today + $credit_paid_previous),
        'expenses_total' => dash_money_live($expenses_total),
        'expense_count' => $expense_count,
        'unpaid_total' => dash_money_live($unpaid_total),
        'unpaid_count' => $unpaid_count,
        'net_after_expenses' => dash_money_live($net_after_expenses),
        'paid_total' => dash_money_live($paid_total),
        'today_tokens_total' => dash_money_live($today_tokens_total),
        'monthly_tokens_total' => dash_money_live($monthly_tokens_total),
        'monthly_token_users' => $monthly_token_users,
        'sales_compare_pct' => number_format($sales_compare_pct, 2)
    ),
    'counts' => array(
        'counter_reqs_pending' => $counter_reqs_pending,
        'open_counters_total' => $open_counters_total,
        'active_cashiers_total' => $active_cashiers_total,
        'credit_customers' => $credit_customers,
        'unbalanced_counters' => $unbalanced_counters
    ),
    'stock_alerts' => $stock_alerts,
    'html' => array(
        'hero_counters' => $hero_html,
        'activity' => $activity_html,
        'staff_sales' => $staff_sales_html,
        'counter_health' => $counter_health_html,
        'req_chip' => $counter_reqs_pending > 0 ? '<span class="db-notice-chip"><span class="db-notice-dot"></span>' . $counter_reqs_pending . ' Active</span>' : '',
        'counter_health_badge' => $unbalanced_counters > 0 ? '<span class="db-notice-chip"><span class="db-notice-dot"></span>' . $unbalanced_counters . ' Unbalanced</span>' : '<span class="db-card-badge" style="background:#dcfce7;color:#166534;">All Balanced</span>'
    ),
    'charts' => array(
        'payment_mode_rows' => $payment_mode_rows,
        'counter_rows' => $counter_rows,
        'staff_rows' => $staff_rows
    )
);

header('Content-Type: application/json');
echo json_encode($payload);
