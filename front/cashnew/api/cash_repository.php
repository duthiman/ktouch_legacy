<?php

if (!function_exists('cash_repo_fetch_all_assoc')) {
    function cash_repo_fetch_all_assoc($result) {
        $rows = [];
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('cash_api_distinct_count')) {
    function cash_api_distinct_count($connection, $table, $column, $where = '1') {
        $result = mysqli_query($connection, "SELECT COUNT(DISTINCT $column) AS total FROM $table WHERE $where");
        $row = $result ? mysqli_fetch_assoc($result) : [];
        return (int)($row['total'] ?? 0);
    }
}

if (!function_exists('cash_api_live_summary')) {
    function cash_api_live_summary($connection) {
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }
        $today = date('Y-m-d');
        $expense_total = (float)column_total('kp_shop_expenses','amount',"shift_date='$shift'");
        $expense_count = (int)column_count('kp_shop_expenses','exp_id',"shift_date='$shift'");
        $credit_total = (float)column_total('kp_cust_hist','amount',"trans='SALE' AND trans_date='$today'");
        $credit_paid_today = (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date='$shift'");
        $credit_paid_previous = (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date!='$shift'");
        $credit_customer_count = cash_api_distinct_count($connection, 'kp_cust_hist', 'cust_id', "trans='SALE' AND trans_date='$today'");
        $return_cashier_mode = function_exists('kp_return_requires_cashier_approval') ? kp_return_requires_cashier_approval($connection) : false;
        $pending_returns_count = $return_cashier_mode
            ? (int)column_count('kp_ret_items','id',"cashier_status=2 AND ret_status!=1")
            : 0;
        $print_mode = function_exists('get_print_mode') ? (int)get_print_mode() : 1;

        return [
            'shift' => $shift,
            'total_sales' => (float)column_total('kp_sales','amount',"shift='$shift'"),
            'sales_unpaid' => (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=2"),
            'sales_paid' => (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=1"),
            'previous_paid' => (float)column_total('kp_sales_paid','paid_total',"ord_date!=pay_date AND pay_date='$shift'"),
            'total_paid' => (float)column_total('kp_sale_payment','paid_total',"pay_date='$shift'"),
            'credit_today' => $credit_total,
            'credit_paid_today' => $credit_paid_today,
            'credit_paid_previous' => $credit_paid_previous,
            'credit_customer_count' => $credit_customer_count,
            'expense_total' => $expense_total,
            'expense_count' => $expense_count,
            'today_uncleared_bills' => (int)column_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2"),
            'previous_uncleared_bills' => (int)column_count('kp_sales','sale_id',"shift!='$shift' AND pay_status=2"),
            'waiters_to_clear' => cash_api_distinct_count($connection, 'kp_sales', 'user_id', "shift='$shift' AND pay_status=2"),
            'reprintable_bills' => (int)column_count('kp_sales','sale_id',"shift='$shift'"),
            'cashbox_entries' => (int)column_count('kp_sale_payment','pay_id',"pay_date='$shift'"),
            'cashbox_total_today' => (float)column_total('kp_sale_payment','paid_total',"pay_date='$shift'"),
            'kpesa_unapproved_count' => (int)column_count('kp_split_message','id',"claim_status=1 AND assign_status=2"),
            'kpesa_unsplit_count' => (int)column_count('mpesa_message','id',"split_status=0"),
            'return_cashier_mode' => $return_cashier_mode ? 1 : 0,
            'pending_returns_count' => $pending_returns_count,
            'pending_returns_label' => $return_cashier_mode ? 'Awaiting cashier approval' : 'Counter handles returns',
            'print_mode' => $print_mode,
            'shift_sum_href' => '../chaba/receipt_counter.php?type=shift_sum&id=1&return_to=' . urlencode('../cash/index.php'),
        ];
    }
}

if (!function_exists('cash_repo_cashier_summary')) {
    function cash_repo_cashier_summary($connection) {
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }
        $today = date('Y-m-d');
        $all_sales_total = (float)column_total('kp_sales', 'amount', "shift='$shift'");
        $all_waiters_total = (float)column_total('kp_sales', 'amount', "shift='$shift' AND pay_status IN (1,2,3)");
        $items_sales_total = (float)column_total('kp_sale_items', 'total', "shift='$shift'");
        $credit_sales_today = (float)column_total('kp_sales', 'amount', "shift='$shift' AND pay_status=3");
        $previous_credit_paid = (float)column_total('kp_customer_credit_payment_items', 'amount', "pay_date='$shift' AND ord_date!='$shift'");
        $total_expenses = (float)column_total('kp_shop_expenses', 'amount', "shift_date='$shift'");
        $total_purchases = (float)column_total('kp_po', 'amount', "shift='$shift'");
        $total_revenue = $all_sales_total + $previous_credit_paid - $total_expenses - $total_purchases;

        $waiter_rows = [];
        $waiter_q = mysqli_query($connection, "SELECT user_id, SUM(amount) AS total_amount, COUNT(sale_id) AS sale_count FROM kp_sales WHERE shift='$shift' GROUP BY user_id ORDER BY total_amount DESC");
        if ($waiter_q) {
            while ($row = mysqli_fetch_assoc($waiter_q)) {
                $waiter_rows[] = $row;
            }
        }

        $item_rows = [];
        $item_q = mysqli_query($connection, "SELECT si.item_id, COALESCE(i.name, CONCAT('Item ', si.item_id)) AS item_name, SUM(si.qty) AS qty_total, SUM(si.total) AS total_amount FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id = si.item_id WHERE si.shift='$shift' GROUP BY si.item_id, i.name ORDER BY total_amount DESC");
        if ($item_q) {
            while ($row = mysqli_fetch_assoc($item_q)) {
                $item_rows[] = $row;
            }
        }

        $counter_rows = [];
        $counter_q = mysqli_query($connection, "SELECT counter, SUM(amount) AS total_amount, COUNT(sale_id) AS sale_count FROM kp_sales WHERE shift='$shift' GROUP BY counter ORDER BY total_amount DESC");
        if ($counter_q) {
            while ($row = mysqli_fetch_assoc($counter_q)) {
                $counter_rows[] = $row;
            }
        }

        $dept_rows = [];
        $dept_q = mysqli_query($connection, "SELECT si.dpt, SUM(si.total) AS total_amount FROM kp_sale_items si WHERE si.shift='$shift' GROUP BY si.dpt ORDER BY total_amount DESC");
        if ($dept_q) {
            while ($row = mysqli_fetch_assoc($dept_q)) {
                $dept_rows[] = $row;
            }
        }

        $mode_rows = [];
        $mode_q = mysqli_query($connection, "SELECT pm.pay_id, pm.name, SUM(sp.paid_total) AS total_amount FROM kp_sale_payment sp LEFT JOIN kp_pay_mode pm ON pm.pay_id = sp.pay_id WHERE sp.pay_date='$shift' GROUP BY pm.pay_id, pm.name ORDER BY total_amount DESC");
        if ($mode_q) {
            while ($row = mysqli_fetch_assoc($mode_q)) {
                $mode_rows[] = $row;
            }
        }

        return [
            'shift' => $shift,
            'all_sales_total' => $all_sales_total,
            'all_waiters_total' => $all_waiters_total,
            'items_sales_total' => $items_sales_total,
            'counter_rows' => $counter_rows,
            'department_rows' => $dept_rows,
            'mode_rows' => $mode_rows,
            'credit_sales_today' => $credit_sales_today,
            'previous_credit_paid' => $previous_credit_paid,
            'total_revenue' => $total_revenue,
            'total_expenses' => $total_expenses,
            'total_purchases' => $total_purchases,
            'waiter_rows' => $waiter_rows,
        ];
    }
}

if (!function_exists('cash_repo_cashbox_summary')) {
    function cash_repo_cashbox_summary($connection) {
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }
        $today_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift' AND ord_date='$shift'");
        $previous_paid = (float)column_total('kp_sales_paid', 'paid_total', "ord_date!=pay_date AND pay_date='$shift'");
        $total_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift'");
        $payment_count = (int)column_count('kp_sale_payment', 'pay_id', "pay_date='$shift'");
        $credit_paid = (float)column_total('kp_customer_credit_payment_items', 'amount', "pay_date='$shift'");
        $cashbox_rows = [];

        $rows_q = mysqli_query($connection, "SELECT sp.pay_id, sp.sale_id, sp.user_id, sp.pay_date, sp.ord_date, sp.paid_total, sp.counter, pm.name AS payment_mode FROM kp_sale_payment sp LEFT JOIN kp_pay_mode pm ON pm.pay_id = sp.pay_id WHERE sp.pay_date='$shift' ORDER BY sp.pay_id DESC");
        if ($rows_q) {
            while ($row = mysqli_fetch_assoc($rows_q)) {
                $cashbox_rows[] = $row;
            }
        }

        $mode_rows = [];
        $mode_q = mysqli_query($connection, "SELECT pm.pay_id, pm.name, SUM(sp.paid_total) AS total_amount, COUNT(sp.pay_id) AS payment_count FROM kp_sale_payment sp LEFT JOIN kp_pay_mode pm ON pm.pay_id = sp.pay_id WHERE sp.pay_date='$shift' GROUP BY pm.pay_id, pm.name ORDER BY total_amount DESC");
        if ($mode_q) {
            while ($row = mysqli_fetch_assoc($mode_q)) {
                $mode_rows[] = $row;
            }
        }

        return [
            'shift' => $shift,
            'today_paid' => $today_paid,
            'previous_paid' => $previous_paid,
            'total_paid' => $total_paid,
            'payment_count' => $payment_count,
            'credit_paid' => $credit_paid,
            'mode_rows' => $mode_rows,
            'cashbox_rows' => $cashbox_rows,
        ];
    }
}

if (!function_exists('cash_repo_recent_mpesa_rows')) {
    function cash_repo_recent_mpesa_rows($connection, $limit = 200) {
        $limit = max(1, (int)$limit);
        $sql = "SELECT id, name, code, amount, date, time
                FROM kp_split_message
                WHERE status=2 AND claim_status=1 AND date > NOW() - INTERVAL 2 DAY
                ORDER BY id DESC
                LIMIT $limit";
        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}

if (!function_exists('cash_repo_search_mpesa_rows')) {
    function cash_repo_search_mpesa_rows($connection, $search, $limit = 200) {
        $limit = max(1, (int)$limit);
        $search = mysqli_real_escape_string($connection, trim((string)$search));
        $sql = "SELECT id, name, code, amount, date, time
                FROM kp_split_message
                WHERE code LIKE '%$search%' AND status=2
                ORDER BY id DESC
                LIMIT $limit";
        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}

if (!function_exists('cash_repo_sale_counter')) {
    function cash_repo_sale_counter($connection, $sale_id) {
        $sale_id = (int)$sale_id;
        $sql = "SELECT counter FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1";
        $result = mysqli_query($connection, $sql);
        $row = $result ? mysqli_fetch_assoc($result) : [];
        return (int)($row['counter'] ?? 0);
    }
}

if (!function_exists('cash_repo_next_pay_id')) {
    function cash_repo_next_pay_id() {
        return (int)pay_id();
    }
}

if (!function_exists('cash_repo_insert_sale_payment')) {
    function cash_repo_insert_sale_payment($connection, $columns, $values) {
        $sql = "INSERT INTO kp_sale_payment (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_insert_sales_paid')) {
    function cash_repo_insert_sales_paid($connection, $pay_id, $sale_id, $user_id, $ord_user, $paid_total, $ord_date, $pay_date) {
        $pay_id = (int)$pay_id;
        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $ord_user = (int)$ord_user;
        $paid_total = (float)$paid_total;
        $ord_date = mysqli_real_escape_string($connection, (string)$ord_date);
        $pay_date = mysqli_real_escape_string($connection, (string)$pay_date);
        $sql = "INSERT INTO kp_sales_paid(pay_id, sale_id, user_id, ord_user, paid_total, ord_date, pay_date)
                VALUES('$pay_id','$sale_id','$user_id','$ord_user','$paid_total','$ord_date','$pay_date')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_update_sale_payment_ord_date')) {
    function cash_repo_update_sale_payment_ord_date($connection, $pay_id, $ord_date) {
        $pay_id = (int)$pay_id;
        $ord_date = mysqli_real_escape_string($connection, (string)$ord_date);
        return mysqli_query($connection, "UPDATE kp_sale_payment SET ord_date='$ord_date' WHERE pay_id='$pay_id'");
    }
}

if (!function_exists('cash_repo_update_pay_mode_balance')) {
    function cash_repo_update_pay_mode_balance($connection, $mode_id, $amount) {
        $mode_id = (int)$mode_id;
        $amount = (float)$amount;
        return mysqli_query($connection, "UPDATE kp_pay_mode SET accbal=accbal+$amount WHERE pay_id='$mode_id'");
    }
}

if (!function_exists('cash_repo_insert_pay_mode_history')) {
    function cash_repo_insert_pay_mode_history($connection, $pay_id, $user_id, $shift, $amount, $accbal) {
        $pay_id = (int)$pay_id;
        $user_id = (int)$user_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $amount = (float)$amount;
        $accbal = (float)$accbal;
        $sql = "INSERT INTO kp_pay_mode_hist(pay_id,user_id,shift,day,amount,accbal)
                VALUES('$pay_id','$user_id','$shift',CURRENT_DATE,'$amount','$accbal')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_update_split_message_status')) {
    function cash_repo_update_split_message_status($connection, $code) {
        $code = mysqli_real_escape_string($connection, trim((string)$code));
        return mysqli_query($connection, "UPDATE split_message SET status=1 WHERE code='$code'");
    }
}

if (!function_exists('cash_repo_mark_sale_paid')) {
    function cash_repo_mark_sale_paid($connection, $sale_id, $shift) {
        $sale_id = (int)$sale_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        return mysqli_query($connection, "UPDATE kp_sales SET pay_status=1,pay_date='$shift' WHERE sale_id='$sale_id'");
    }
}

if (!function_exists('cash_repo_insert_cash_collection')) {
    function cash_repo_insert_cash_collection($connection, $pay_mode_id, $user_id, $appr_by, $shift, $amount) {
        $pay_mode_id = (int)$pay_mode_id;
        $user_id = (int)$user_id;
        $appr_by = (int)$appr_by;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $amount = (float)$amount;
        $sql = "INSERT INTO kp_cash_collection(pay_id,user_id,appr_by,shift,day,amount)
                VALUES('$pay_mode_id','$user_id','$appr_by','$shift',CURRENT_DATE,'$amount')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_reduce_pay_mode_balance')) {
    function cash_repo_reduce_pay_mode_balance($connection, $pay_mode_id, $amount) {
        $pay_mode_id = (int)$pay_mode_id;
        $amount = (float)$amount;
        return mysqli_query($connection, "UPDATE kp_pay_mode SET accbal=accbal-$amount WHERE pay_id='$pay_mode_id'");
    }
}

if (!function_exists('cash_repo_handover_receiver_available')) {
    function cash_repo_handover_receiver_available($connection, $user_id) {
        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT user_id FROM kp_users WHERE user_id='$user_id' AND drole=1 AND counter_status=2 LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('cash_repo_insert_counter_handover')) {
    function cash_repo_insert_counter_handover($connection, $shift, $counter, $from_user, $to_user, $system_total, $declared_total, $variance_total, $status, $note) {
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $counter = (int)$counter;
        $from_user = (int)$from_user;
        $to_user = (int)$to_user;
        $system_total = (float)$system_total;
        $declared_total = (float)$declared_total;
        $variance_total = (float)$variance_total;
        $status = mysqli_real_escape_string($connection, (string)$status);
        $note = mysqli_real_escape_string($connection, (string)$note);
        $sql = "INSERT INTO kp_counter_handover(shift_date,cntr_id,from_user,to_user,total_system,total_declared,total_variance,status,note)
                VALUES('$shift','$counter','$from_user','$to_user','$system_total','$declared_total','$variance_total','$status','$note')";
        $ok = mysqli_query($connection, $sql);
        return $ok ? (int)mysqli_insert_id($connection) : 0;
    }
}

if (!function_exists('cash_repo_insert_counter_handover_mode')) {
    function cash_repo_insert_counter_handover_mode($connection, $handover_id, $pay_id, $system_amount, $declared_amount, $variance_amount, $status) {
        $handover_id = (int)$handover_id;
        $pay_id = (int)$pay_id;
        $system_amount = (float)$system_amount;
        $declared_amount = (float)$declared_amount;
        $variance_amount = (float)$variance_amount;
        $status = mysqli_real_escape_string($connection, (string)$status);
        $sql = "INSERT INTO kp_counter_handover_modes(handover_id,pay_id,system_amount,declared_amount,variance_amount,status)
                VALUES('$handover_id','$pay_id','$system_amount','$declared_amount','$variance_amount','$status')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_handover_release_user_counter')) {
    function cash_repo_handover_release_user_counter($connection, $user_id) {
        $user_id = (int)$user_id;
        return mysqli_query($connection, "UPDATE kp_users SET job=2,counter_status=2,counter=0 WHERE user_id='$user_id'");
    }
}

if (!function_exists('cash_repo_handover_assign_user_counter')) {
    function cash_repo_handover_assign_user_counter($connection, $user_id, $counter) {
        $user_id = (int)$user_id;
        $counter = (int)$counter;
        return mysqli_query($connection, "UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$user_id'");
    }
}

if (!function_exists('cash_repo_handover_update_counter_owner')) {
    function cash_repo_handover_update_counter_owner($connection, $counter, $user_id) {
        $counter = (int)$counter;
        $user_id = (int)$user_id;
        return mysqli_query($connection, "UPDATE kp_counters SET user_id='$user_id',take_status=3,counter_status=1 WHERE cntr_id='$counter'");
    }
}

if (!function_exists('cash_repo_insert_expense')) {
    function cash_repo_insert_expense($connection, $shift_date, $title, $amount, $note, $user_id) {
        $shift_date = mysqli_real_escape_string($connection, (string)$shift_date);
        $title = mysqli_real_escape_string($connection, (string)$title);
        $amount = (float)$amount;
        $note = mysqli_real_escape_string($connection, (string)$note);
        $user_id = (int)$user_id;
        $sql = "INSERT INTO kp_shop_expenses(shift_date,title,amount,note,user_id)
                VALUES('$shift_date','$title','$amount','$note','$user_id')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_find_expense')) {
    function cash_repo_find_expense($connection, $exp_id) {
        $exp_id = (int)$exp_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_shop_expenses WHERE exp_id='$exp_id' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('cash_repo_find_user_by_pin')) {
    function cash_repo_find_user_by_pin($connection, $pin) {
        $pin = mysqli_real_escape_string($connection, (string)$pin);
        $result = mysqli_query($connection, "SELECT user_id,username,role_id,drole FROM kp_users WHERE pin='$pin' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('cash_repo_track_expense_delete')) {
    function cash_repo_track_expense_delete($connection, $expense, $deleted_by, $appr_by, $reason, $shift) {
        $exp_id = (int)($expense['exp_id'] ?? 0);
        $expense_shift = mysqli_real_escape_string($connection, (string)($expense['shift_date'] ?? ''));
        $title = mysqli_real_escape_string($connection, (string)($expense['title'] ?? ''));
        $amount = (float)($expense['amount'] ?? 0);
        $note = mysqli_real_escape_string($connection, (string)($expense['note'] ?? ''));
        $created_by = (int)($expense['user_id'] ?? 0);
        $deleted_by = (int)$deleted_by;
        $appr_by = (int)$appr_by;
        $reason = mysqli_real_escape_string($connection, (string)$reason);
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $sql = "INSERT INTO kp_shop_expense_delete(exp_id,shift_date,title,amount,note,created_by,deleted_by,appr_by,reason,delete_shift)
                VALUES('$exp_id','$expense_shift','$title','$amount','$note','$created_by','$deleted_by','$appr_by','$reason','$shift')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_delete_expense')) {
    function cash_repo_delete_expense($connection, $exp_id) {
        $exp_id = (int)$exp_id;
        return mysqli_query($connection, "DELETE FROM kp_shop_expenses WHERE exp_id='$exp_id' LIMIT 1");
    }
}

if (!function_exists('cash_repo_sales_error_rows')) {
    function cash_repo_sales_error_rows($connection, $shift, $counter_id, $mode) {
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $counter_id = (int)$counter_id;
        $mode = (int)$mode;

        if ($mode === 1) {
            $sql = "SELECT sale_id, user_id, amount FROM kp_sales WHERE shift='$shift' AND counter='$counter_id'";
        } else {
            $sql = "SELECT SUM(total) as total, sale_id, user_id FROM kp_sale_items WHERE shift='$shift' AND counter='$counter_id' GROUP BY sale_id";
        }

        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}

if (!function_exists('cash_repo_find_staff_clearance')) {
    function cash_repo_find_staff_clearance($connection, $staff_user_id, $shift_date) {
        $staff_user_id = (int)$staff_user_id;
        $shift_date = mysqli_real_escape_string($connection, (string)$shift_date);
        $result = mysqli_query($connection, "SELECT * FROM kp_staff_clearances WHERE staff_user_id='$staff_user_id' AND shift_date='$shift_date' ORDER BY clearance_id DESC LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('cash_repo_unpaid_sales_for_staff')) {
    function cash_repo_unpaid_sales_for_staff($connection, $staff_user_id) {
        $staff_user_id = (int)$staff_user_id;
        $sql = "SELECT sale_id, amount, shift, counter FROM kp_sales WHERE user_id='$staff_user_id' AND pay_status=2 ORDER BY sale_id ASC";
        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}

if (!function_exists('cash_repo_insert_staff_clearance')) {
    function cash_repo_insert_staff_clearance($connection, $payload) {
        $sql = sprintf(
            "INSERT INTO kp_staff_clearances(
                staff_user_id, cleared_by, shift_date, total_sales, paid_sales, unpaid_sales, credit_sales,
                mpesa_total, pdq_total, expected_cash, actual_cash, variance_amount, variance_type, snapshot_json, note
             ) VALUES(
                '%d', '%d', '%s', '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
             )",
            (int)$payload['staff_user_id'],
            (int)$payload['cleared_by'],
            mysqli_real_escape_string($connection, (string)$payload['shift_date']),
            mysqli_real_escape_string($connection, number_format((float)$payload['total_sales'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['paid_sales'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['unpaid_sales'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['credit_sales'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['mpesa_total'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['pdq_total'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['expected_cash'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['actual_cash'], 2, '.', '')),
            mysqli_real_escape_string($connection, number_format((float)$payload['variance_amount'], 2, '.', '')),
            mysqli_real_escape_string($connection, (string)$payload['variance_type']),
            mysqli_real_escape_string($connection, (string)$payload['snapshot_json']),
            mysqli_real_escape_string($connection, (string)$payload['note'])
        );
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('cash_repo_find_customer')) {
    function cash_repo_find_customer($connection, $cust_id) {
        $cust_id = (int)$cust_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_customers WHERE cust_id='$cust_id' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('cash_repo_customer_credit_sales')) {
    function cash_repo_customer_credit_sales($connection, $cust_id) {
        $cust_id = (int)$cust_id;
        $sql = "SELECT sale_id,shift,amount,sale_bal,counter FROM kp_sales WHERE cust_id='$cust_id' AND pay_status=3 AND sale_bal>0 ORDER BY shift ASC, sale_id ASC";
        $result = mysqli_query($connection, $sql);
        return [
            'rows' => $result ? cash_repo_fetch_all_assoc($result) : [],
            'error' => $result ? '' : mysqli_error($connection),
            'ok' => (bool)$result,
        ];
    }
}

if (!function_exists('cash_repo_active_pay_modes')) {
    function cash_repo_active_pay_modes($connection) {
        return cash_repo_fetch_all_assoc(mysqli_query($connection, "SELECT * FROM kp_pay_mode WHERE del_status=2 ORDER BY name ASC"));
    }
}

if (!function_exists('cash_repo_recent_credit_sales')) {
    function cash_repo_recent_credit_sales($connection, $cust_id, $limit = 8) {
        $cust_id = (int)$cust_id;
        $limit = max(1, (int)$limit);
        $sql = "SELECT trans_id,amount,trans_date,cf_bal FROM kp_cust_hist WHERE cust_id='$cust_id' AND trans='SALE' ORDER BY trans_date DESC, trans_id DESC LIMIT $limit";
        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}

if (!function_exists('cash_repo_current_unpaid_bills')) {
    function cash_repo_current_unpaid_bills($connection, $shift, $user_id = 0) {
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $user_id = (int)$user_id;
        $where = "shift='$shift' AND pay_status=2";
        if ($user_id > 0) {
            $where .= " AND user_id='$user_id'";
        }
        return cash_repo_fetch_all_assoc(mysqli_query($connection, "SELECT * FROM kp_sales WHERE $where ORDER BY sale_id DESC"));
    }
}

if (!function_exists('cash_repo_previous_unpaid_bills')) {
    function cash_repo_previous_unpaid_bills($connection, $shift, $user_id = 0) {
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $user_id = (int)$user_id;
        $where = "shift!='$shift' AND pay_status=2";
        if ($user_id > 0) {
            $where .= " AND user_id='$user_id'";
        }
        return cash_repo_fetch_all_assoc(mysqli_query($connection, "SELECT * FROM kp_sales WHERE $where ORDER BY sale_id DESC"));
    }
}

if (!function_exists('cash_repo_all_pay_modes')) {
    function cash_repo_all_pay_modes($connection) {
        return cash_repo_fetch_all_assoc(mysqli_query($connection, "SELECT * FROM kp_pay_mode WHERE 1 ORDER BY name ASC"));
    }
}

if (!function_exists('cash_repo_kpesa_unapproved_rows')) {
    function cash_repo_kpesa_unapproved_rows($connection, $limit = 120) {
        $limit = max(1, (int)$limit);
        $sql = "SELECT * FROM kp_split_message WHERE claim_status=1 AND assign_status=2 ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) DESC LIMIT $limit";
        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}

if (!function_exists('cash_repo_kpesa_unsplit_rows')) {
    function cash_repo_kpesa_unsplit_rows($connection, $limit = 120) {
        $limit = max(1, (int)$limit);
        $sql = "SELECT * FROM mpesa_message WHERE split_status=0 ORDER BY datime DESC LIMIT $limit";
        return cash_repo_fetch_all_assoc(mysqli_query($connection, $sql));
    }
}
