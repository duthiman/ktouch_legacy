<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_GET['session_ping'])) {
  header('Content-Type: application/json; charset=utf-8');
  $activeUserId = 0;
  if (!empty($_SESSION['user_id'])) {
    $activeUserId = (int)$_SESSION['user_id'];
  } elseif (!empty($_SESSION['mwene'])) {
    $activeUserId = (int)$_SESSION['mwene'];
  }

  echo json_encode([
    'ok' => $activeUserId > 0,
    'user_id' => $activeUserId,
  ]);
  exit;
}

$activeUserId = 0;
if (!empty($_SESSION['user_id'])) {
  $activeUserId = (int)$_SESSION['user_id'];
} elseif (!empty($_SESSION['mwene'])) {
  $activeUserId = (int)$_SESSION['mwene'];
}

if ($activeUserId < 1) {
  header('Location: ../../log_out.php?expired=1');
  exit;
}

require("../clude/head.php");
require_once("funcs.php");
require_once("stock_alerts.php");
 
echo '<script>
  window.KTOUCH_LOGOUT_URL = "../../log_out.php?expired=1";
  window.KTOUCH_SESSION_PING_URL = "admin_dash.php?session_ping=1";
  (function () {
    var logoutUrl = window.KTOUCH_LOGOUT_URL;
    var pingUrl = window.KTOUCH_SESSION_PING_URL;
    var busy = false;

    function expireSession() {
      window.location.replace(logoutUrl);
    }

    function checkSession() {
      if (busy || !pingUrl) {
        return;
      }
      busy = true;
      fetch(pingUrl, {
        method: "GET",
        credentials: "same-origin",
        cache: "no-store",
        headers: { "X-Requested-With": "XMLHttpRequest" }
      }).then(function (response) {
        if (!response.ok) {
          throw new Error("Session check failed");
        }
        return response.json();
      }).then(function (payload) {
        if (!payload || !payload.ok) {
          expireSession();
        }
      }).catch(function () {
        expireSession();
      }).finally(function () {
        busy = false;
      });
    }

    window.addEventListener("focus", checkSession);
    document.addEventListener("visibilitychange", function () {
      if (!document.hidden) {
        checkSession();
      }
    });
    window.setInterval(checkSession, 60000);
  }());
</script>'; 

$shift = shift();
$today = date('Y-m-d');
if ($shift == 0 || empty($shift)) {
  $shift = $today;
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $shift)) {
  $parsed_shift = strtotime($shift);
  $shift = $parsed_shift ? date('Y-m-d', $parsed_shift) : $today;
}

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_shop_expenses (
  exp_id INT NOT NULL AUTO_INCREMENT,
  shift_date DATE NOT NULL,
  title VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  note TEXT NULL,
  user_id INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (exp_id)
)") or die(mysqli_error($connection));

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payments (
  credit_pay_id INT NOT NULL AUTO_INCREMENT,
  cust_id INT NOT NULL DEFAULT 0,
  user_id INT NOT NULL DEFAULT 0,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  pay_mode_id INT NOT NULL DEFAULT 0,
  reference_no VARCHAR(120) NOT NULL DEFAULT '',
  note TEXT NULL,
  shift_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (credit_pay_id)
)") or die(mysqli_error($connection));

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_items (
  credit_item_id INT NOT NULL AUTO_INCREMENT,
  credit_pay_id INT NOT NULL DEFAULT 0,
  cust_id INT NOT NULL DEFAULT 0,
  sale_id INT NOT NULL DEFAULT 0,
  ord_date DATE NOT NULL,
  pay_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (credit_item_id)
)") or die(mysqli_error($connection));

if (!function_exists('dash_money')) {
  function dash_money($value){
    return number_format((float)$value, 2);
  }
}

if (!function_exists('dash_user_name')) {
  function dash_user_name($user_id){
    if ((int)$user_id < 1) {
      return 'Unknown';
    }
    return get_user($user_id);
  }
}

if (!function_exists('dash_time_ago')) {
  function dash_time_ago($datetime){
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

if (!function_exists('dash_table_exists')) {
  function dash_table_exists($table){
    global $connection;
    $safe = mysqli_real_escape_string($connection, $table);
    $check = mysqli_query($connection, "SHOW TABLES LIKE '$safe'");
    return $check && mysqli_num_rows($check) > 0;
  }
}

if (!function_exists('dash_add_activity')) {
  function dash_add_activity(&$rows, $title, $detail, $time_label, $tone, $sort_ts, $rank = 0){
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

$open_shift_label = $shift == $today ? $today : $shift;
$orders_total = (float)column_total('kp_sales','amount',"shift='$shift'");
$orders_count = (int)column_count('kp_sales','sale_id',"shift='$shift'");
$paid_total = (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=1");
$unpaid_total = (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=2");
$unpaid_count = (int)column_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2");
$front_purchase_total = (float)column_total('kp_po','amount',"shift='$shift'");
$front_purchase_pending = (int)pending_purchase_approvals();
$front_purchase_receiving = (int)pending_purchase_receiving();
$purchase_pending_link = '../purchase/pending.php';
$counter_reqs_pending = (int)counter_req();
$open_counters_total = (int)column_count('kp_counters','cntr_id',"counter_status=1");
$active_cashiers_total = (int)column_count('kp_users','user_id',"job=1 AND counter_status=1");
$pending_returns_total = (int)column_count('kp_ret_items','id',"shift='$shift' AND ret_status=3");
$pending_returns_amount = (float)column_total('kp_ret_items','amount',"shift='$shift' AND ret_status=3");
$pending_transfers_total = (int)column_count('kp_counter_req','req_id',"issue_status=1 AND rec_status=2");
$stock_alerts = dash_stock_alerts();
$expenses_total = (float)column_total('kp_shop_expenses','amount',"shift_date='$shift'");
$expense_count = (int)column_count('kp_shop_expenses','exp_id',"shift_date='$shift'");
$credit_today = (float)column_total('kp_cust_hist','amount',"trans='SALE' AND trans_date='$today'");
$credit_paid_today = (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date='$shift'");
$credit_paid_previous = (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date!='$shift'");
$credit_outstanding = (float)column_total('kp_customers','bal',"bal>0");
$credit_customers = (int)column_count('kp_customers','cust_id',"bal>0");
$net_after_expenses = $paid_total - $expenses_total;
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$today_tokens_total = function_exists('column_total') ? (float)column_total('kp_staff_token_entries','token_amount',"status=1 AND DATE(created_at)='$today'") : 0;
$monthly_tokens_total = function_exists('column_total') ? (float)column_total('kp_staff_token_entries','token_amount',"status=1 AND created_at>='{$month_start} 00:00:00' AND created_at<='{$month_end} 23:59:59'") : 0;
$monthly_token_users = function_exists('column_count') ? (int)column_count('kp_staff_token_wallet','user_id',"total_tokens>0") : 0;
$current_cashier_name = $active_cashiers_total > 0 ? get_column('kp_users','username',"job=1 AND counter_status=1") : 'No active cashier';
$open_counter_cards = array();
$open_counter_q = mysqli_query($connection,"SELECT c.name AS counter_name, u.username FROM kp_counters c LEFT JOIN kp_users u ON c.user_id=u.user_id WHERE c.counter_status=1 ORDER BY c.name ASC") or die(mysqli_error($connection));
while ($open_row = mysqli_fetch_assoc($open_counter_q)) {
  $open_counter_cards[] = array(
    'counter' => $open_row['counter_name'],
    'cashier' => $open_row['username'] != '' ? $open_row['username'] : 'No cashier'
  );
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
$previous_total = (float)column_total('kp_sales','amount',"shift='$last_shift'");
$avg_per_order = $orders_count > 0 ? ($orders_total / $orders_count) : 0;
$sales_compare_pct = $previous_total > 0 ? (($orders_total - $previous_total) / $previous_total) * 100 : 0;
$sales_compare_text = ($previous_total > 0 ? number_format($sales_compare_pct, 2) : '0.00') . '% compare to previous shift';
$compare_label = date('d M Y', strtotime($report_anchor));
$prev_compare_label = date('d M Y', strtotime($last_shift));

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
    'sales' => $sales_amt,
    'items' => $item_amt,
    'status' => $status,
    'variance' => $sales_amt - $item_amt
  );
}

$staff_rows = array(array('Staff', 'Sales'));
$staff_table = array();
$staff_q = $shift == 0 ? false : mysqli_query($connection,"SELECT user_id,COUNT(sale_id) AS bills,SUM(amount) AS total FROM kp_sales WHERE shift='$shift' GROUP BY user_id ORDER BY total DESC") or die(mysqli_error($connection));
if ($staff_q) {
  while ($row = mysqli_fetch_assoc($staff_q)) {
    $name = dash_user_name($row['user_id']);
    $total = (float)$row['total'];
    $staff_rows[] = array($name, $total);
    $staff_table[] = array(
      'name' => $name,
      'bills' => (int)$row['bills'],
      'total' => $total
    );
  }
}

$item_rows = array(array('Item', 'Sales'));
$item_pie_rows = array(array('Item', 'Amount'));
$item_table = array();
$item_q = $shift == 0 ? false : mysqli_query($connection,"SELECT item_id,SUM(qty) AS qty,SUM(total) AS total FROM kp_sale_items WHERE shift='$shift' GROUP BY item_id ORDER BY total DESC LIMIT 10") or die(mysqli_error($connection));
if ($item_q) {
  while ($row = mysqli_fetch_assoc($item_q)) {
    $item_name = get_item($row['item_id']);
    $total = (float)$row['total'];
    $qty = (float)$row['qty'];
    $item_rows[] = array($item_name, $total);
    $item_pie_rows[] = array($item_name, $total);
    $item_table[] = array(
      'name' => $item_name,
      'qty' => $qty,
      'total' => $total
    );
  }
}

$trend_rows = array(array('Shift', 'Sales', 'Purchases', 'Expenses'));
$trend_days = array();
$trend_q = mysqli_query($connection,"SELECT shift FROM kp_sales GROUP BY shift ORDER BY shift DESC LIMIT 7") or die(mysqli_error($connection));
while ($row = mysqli_fetch_assoc($trend_q)) {
  $trend_days[] = $row['shift'];
}
$trend_days = array_reverse($trend_days);
foreach ($trend_days as $day) {
  $trend_rows[] = array(
    date('d M', strtotime($day)),
    (float)column_total('kp_sales','amount',"shift='$day'"),
    (float)column_total('kp_po','amount',"shift='$day'"),
    (float)column_total('kp_shop_expenses','amount',"shift_date='$day'")
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

$activity_rows = array();
if ($shift != 0) {
  $sale_activity_q = mysqli_query($connection,"SELECT sale_id,amount,user_id,pay_status,created_at FROM kp_sales WHERE shift='$shift' ORDER BY sale_id DESC LIMIT 4") or die(mysqli_error($connection));
  while ($row = mysqli_fetch_assoc($sale_activity_q)) {
    dash_add_activity(
      $activity_rows,
      'Sale #' . $row['sale_id'],
      dash_user_name($row['user_id']) . ' | ' . dash_money($row['amount']),
      dash_time_ago($row['created_at']),
      $row['pay_status'] == 1 ? 'success' : ($row['pay_status'] == 3 ? 'info' : 'warning'),
      strtotime($row['created_at']),
      (int)$row['sale_id']
    );
  }

  $payment_activity_q = mysqli_query($connection,"SELECT pay_id,paid_total,user_id,created_at FROM kp_sale_payment WHERE pay_date='$shift' ORDER BY pay_id DESC LIMIT 4") or die(mysqli_error($connection));
  while ($row = mysqli_fetch_assoc($payment_activity_q)) {
    dash_add_activity(
      $activity_rows,
      'Payment #' . $row['pay_id'],
      dash_user_name($row['user_id']) . ' | ' . dash_money($row['paid_total']),
      dash_time_ago($row['created_at']),
      'success',
      strtotime($row['created_at']),
      (int)$row['pay_id']
    );
  }

  $expense_activity_q = mysqli_query($connection,"SELECT exp_id,title,amount,created_at FROM kp_shop_expenses WHERE shift_date='$shift' ORDER BY exp_id DESC LIMIT 2") or die(mysqli_error($connection));
  while ($row = mysqli_fetch_assoc($expense_activity_q)) {
    dash_add_activity(
      $activity_rows,
      'Expense: ' . $row['title'],
      dash_money($row['amount']),
      dash_time_ago($row['created_at']),
      'danger',
      strtotime($row['created_at']),
      (int)$row['exp_id']
    );
  }

  if (dash_table_exists('kp_customer_credit_payments')) {
    $credit_pay_q = mysqli_query($connection,"SELECT credit_pay_id,cust_id,amount,user_id,created_at FROM kp_customer_credit_payments WHERE shift_date='$shift' ORDER BY credit_pay_id DESC LIMIT 3");
    if ($credit_pay_q) {
      while ($row = mysqli_fetch_assoc($credit_pay_q)) {
        dash_add_activity(
          $activity_rows,
          'Credit Payment #' . $row['credit_pay_id'],
          get_cust((int)$row['cust_id']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($row['created_at']),
          'success',
          strtotime($row['created_at']),
          (int)$row['credit_pay_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_cust_hist')) {
    $credit_sale_q = mysqli_query($connection,"SELECT trans_id,cust_id,amount,trans_date,created_at FROM kp_cust_hist WHERE trans='SALE' AND trans_date='$today' ORDER BY COALESCE(created_at, CONCAT(trans_date,' 00:00:00')) DESC, trans_id DESC LIMIT 3");
    if ($credit_sale_q) {
      while ($row = mysqli_fetch_assoc($credit_sale_q)) {
        $credit_time = !empty($row['created_at']) && $row['created_at'] !== '0000-00-00 00:00:00'
          ? $row['created_at']
          : ($row['trans_date'] . ' 00:00:00');
        dash_add_activity(
          $activity_rows,
          'Credit Given #' . $row['trans_id'],
          get_cust((int)$row['cust_id']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($credit_time),
          'info',
          strtotime($credit_time),
          (int)$row['trans_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_counter_handover')) {
    $handover_q = mysqli_query($connection,"SELECT handover_id,from_user,to_user,total_variance,created_at FROM kp_counter_handover WHERE shift_date='$shift' ORDER BY handover_id DESC LIMIT 2");
    if ($handover_q) {
      while ($row = mysqli_fetch_assoc($handover_q)) {
        dash_add_activity(
          $activity_rows,
          'Shift Handover #' . $row['handover_id'],
          get_user((int)$row['from_user']) . ' to ' . get_user((int)$row['to_user']) . ' | Var ' . dash_money($row['total_variance']),
          dash_time_ago($row['created_at']),
          abs((float)$row['total_variance']) < 0.01 ? 'success' : 'warning',
          strtotime($row['created_at']),
          (int)$row['handover_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_counter_req')) {
    $req_q = mysqli_query($connection,"SELECT req_id,req_user,counter,req_shift,created_at FROM kp_counter_req WHERE req_shift='$shift' ORDER BY COALESCE(created_at, CONCAT(req_shift,' 00:00:00')) DESC, req_id DESC LIMIT 2");
    if ($req_q) {
      while ($row = mysqli_fetch_assoc($req_q)) {
        $req_time = !empty($row['created_at']) && $row['created_at'] !== '0000-00-00 00:00:00'
          ? $row['created_at']
          : ($row['req_shift'] . ' 00:00:00');
        dash_add_activity(
          $activity_rows,
          'Stock Requisition #' . $row['req_id'],
          get_user((int)$row['req_user']) . ' | ' . get_counter($row['counter']),
          dash_time_ago($req_time),
          'warning',
          strtotime($req_time),
          (int)$row['req_id']
        );
      }
    }

    $issue_q = mysqli_query($connection,"SELECT req_id,issue_user,counter,issue_shift,issue_created_at FROM kp_counter_req WHERE issue_status=1 AND issue_shift='$shift' ORDER BY COALESCE(issue_created_at, CONCAT(issue_shift,' 00:00:00')) DESC, req_id DESC LIMIT 2");
    if ($issue_q) {
      while ($row = mysqli_fetch_assoc($issue_q)) {
        $issue_time = !empty($row['issue_created_at']) && $row['issue_created_at'] !== '0000-00-00 00:00:00'
          ? $row['issue_created_at']
          : ($row['issue_shift'] . ' 00:00:00');
        dash_add_activity(
          $activity_rows,
          'Stock Issued #' . $row['req_id'],
          get_user((int)$row['issue_user']) . ' | ' . get_counter($row['counter']),
          dash_time_ago($issue_time),
          'success',
          strtotime($issue_time),
          (int)$row['req_id']
        );
      }
    }

    $receive_req_q = mysqli_query($connection,"SELECT req_id,rec_user,counter,rec_created_at FROM kp_counter_req WHERE rec_status=1 AND req_shift='$shift' ORDER BY COALESCE(rec_created_at, CONCAT(req_shift,' 00:00:00')) DESC, req_id DESC LIMIT 2");
    if ($receive_req_q) {
      while ($row = mysqli_fetch_assoc($receive_req_q)) {
        $rec_time = !empty($row['rec_created_at']) && $row['rec_created_at'] !== '0000-00-00 00:00:00'
          ? $row['rec_created_at']
          : ($shift . ' 00:00:00');
        dash_add_activity(
          $activity_rows,
          'Stock Received #' . $row['req_id'],
          get_user((int)$row['rec_user']) . ' | ' . get_counter($row['counter']),
          dash_time_ago($rec_time),
          'success',
          strtotime($rec_time),
          (int)$row['req_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_ret_items')) {
    $return_q = mysqli_query($connection,"SELECT sale_id,user_id,amount,id FROM kp_ret_items WHERE shift='$shift' ORDER BY id DESC LIMIT 2");
    if ($return_q) {
      while ($row = mysqli_fetch_assoc($return_q)) {
        dash_add_activity(
          $activity_rows,
          'Return #' . $row['sale_id'],
          dash_user_name((int)$row['user_id']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($shift . ' 20:00:00'),
          'warning',
          strtotime($shift . ' 20:00:00'),
          (int)$row['id']
        );
      }
    }
  }

  if (dash_table_exists('kp_del_orders')) {
    $void_q = mysqli_query($connection,"SELECT sale_id,user_id,amount FROM kp_del_orders WHERE shift='$shift' GROUP BY sale_id,user_id,amount ORDER BY sale_id DESC LIMIT 2");
    if ($void_q) {
      while ($row = mysqli_fetch_assoc($void_q)) {
        dash_add_activity(
          $activity_rows,
          'Voided Sale #' . $row['sale_id'],
          dash_user_name((int)$row['user_id']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($shift . ' 19:00:00'),
          'danger',
          strtotime($shift . ' 19:00:00'),
          (int)$row['sale_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_cancelled')) {
    $cancel_q = mysqli_query($connection,"SELECT sale_id,user_id,amount FROM kp_cancelled WHERE shift='$shift' ORDER BY sale_id DESC LIMIT 2");
    if ($cancel_q) {
      while ($row = mysqli_fetch_assoc($cancel_q)) {
        dash_add_activity(
          $activity_rows,
          'Cancelled Sale #' . $row['sale_id'],
          dash_user_name((int)$row['user_id']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($shift . ' 18:00:00'),
          'danger',
          strtotime($shift . ' 18:00:00'),
          (int)$row['sale_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_tot')) {
    $tot_q = mysqli_query($connection,"SELECT tot_id,user_id,counter FROM kp_tot WHERE shift='$shift' ORDER BY tot_id DESC LIMIT 2");
    if ($tot_q) {
      while ($row = mysqli_fetch_assoc($tot_q)) {
        dash_add_activity(
          $activity_rows,
          'T.O.T Generated #' . $row['tot_id'],
          dash_user_name((int)$row['user_id']) . ' | ' . get_counter($row['counter']),
          dash_time_ago($shift . ' 17:00:00'),
          'info',
          strtotime($shift . ' 17:00:00'),
          (int)$row['tot_id']
        );
      }
    }
  }

  if (dash_table_exists('kp_po')) {
    $purchase_q = mysqli_query($connection,"SELECT po_id,ord_user,amount,po_date FROM kp_po WHERE shift='$shift' ORDER BY po_id DESC LIMIT 2");
    if ($purchase_q) {
      while ($row = mysqli_fetch_assoc($purchase_q)) {
        dash_add_activity(
          $activity_rows,
          'Purchase #' . $row['po_id'],
          dash_user_name((int)$row['ord_user']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($row['po_date'] . ' 16:00:00'),
          'info',
          strtotime($row['po_date'] . ' 16:00:00'),
          (int)$row['po_id']
        );
      }
    }

    $receive_po_q = mysqli_query($connection,"SELECT po_id,rec_staff,amount,rec_date FROM kp_po WHERE rec_status=1 AND rec_date='$shift' ORDER BY po_id DESC LIMIT 2");
    if ($receive_po_q) {
      while ($row = mysqli_fetch_assoc($receive_po_q)) {
        dash_add_activity(
          $activity_rows,
          'Stock Receive #' . $row['po_id'],
          dash_user_name((int)$row['rec_staff']) . ' | ' . dash_money($row['amount']),
          dash_time_ago($row['rec_date'] . ' 15:00:00'),
          'success',
          strtotime($row['rec_date'] . ' 15:00:00'),
          (int)$row['po_id']
        );
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

$front_pos = mysqli_query($connection,"SELECT * FROM kp_po WHERE refno LIKE 'FRONT BUY FOR %' AND (rec_status=2 OR stock_status=2) ORDER BY po_id DESC LIMIT 20") or die(mysqli_error($connection));
?>
<script type="text/javascript" src="gstatic/loader.js"></script>
<style>
  .db-page { padding: 0; font-family: Inter, sans-serif; background:
      radial-gradient(circle at 20% 20%, rgba(132,204,22,.08) 0, rgba(132,204,22,0) 26%),
      radial-gradient(circle at 82% 18%, rgba(245,158,11,.09) 0, rgba(245,158,11,0) 24%),
      radial-gradient(circle at 72% 78%, rgba(37,99,235,.08) 0, rgba(37,99,235,0) 22%),
      linear-gradient(180deg,#f8fafc 0%,#eef2f7 100%);
    position:relative; }
  .db-page:before { content:""; position:fixed; inset:0; pointer-events:none; opacity:.32; background-image:
      linear-gradient(rgba(148,163,184,.07) 1px, transparent 1px),
      linear-gradient(90deg, rgba(148,163,184,.06) 1px, transparent 1px),
      radial-gradient(rgba(255,255,255,.8) 1px, transparent 1px);
    background-size: 28px 28px, 28px 28px, 18px 18px;
    background-position: 0 0, 0 0, 9px 9px;
    z-index:0; }
  .db-page > * { position:relative; z-index:1; }
  .db-topbar { background:linear-gradient(180deg,#ffffff,#f7f7f7); border-bottom:1px solid #e5e7eb; padding:.55rem 1rem; text-align:center; margin-bottom:.75rem; box-shadow:0 1px 4px rgba(15,23,42,.04); }
  .db-topbar-title { font-size:1rem; font-weight:800; letter-spacing:.02em; color:#1f2937; }
  .db-shell { display:grid; grid-template-columns:minmax(220px, 250px) minmax(0,1fr) minmax(220px, 250px); gap:.75rem; align-items:start; }
  .db-rail { position:sticky; top:0; }
  .db-rail-right { position:sticky; top:0; }
  .db-canvas { min-width:0; }
  .db-hero { display:flex; justify-content:space-between; gap:.8rem; align-items:center; background:linear-gradient(135deg,#ffffff 0%,#f7f7f7 52%,#eef7db 100%); border:1px solid #e5e7eb; border-radius:16px; padding:.8rem .95rem; box-shadow:0 10px 24px rgba(15,23,42,.06); margin-bottom:.7rem; position:relative; overflow:hidden; }
  .db-hero:before { content:""; position:absolute; inset:auto auto -28px -18px; width:140px; height:140px; background:radial-gradient(circle,#f59e0b33 0%,transparent 70%); pointer-events:none; }
  .db-hero:after { content:""; position:absolute; inset:-34px -24px auto auto; width:170px; height:170px; background:radial-gradient(circle,#84cc1630 0%,transparent 72%); pointer-events:none; }
  .db-eyebrow { font-size:.62rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; color:#f59e0b; margin-bottom:.2rem; }
  .db-hero-title { font-size:1.55rem; font-weight:900; color:#111827; margin:0; line-height:1.05; }
  .db-hero-sub { font-size:.68rem; color:#6b7280; margin-top:.1rem; }
  .db-hero-datetime { display:flex; align-items:center; gap:.5rem; margin-top:.18rem; flex-wrap:wrap; }
  .db-hero-date-chip, .db-hero-time-chip { display:inline-flex; align-items:center; gap:.32rem; padding:.2rem .55rem; border-radius:999px; font-size:.62rem; font-weight:800; }
  .db-hero-date-chip { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
  .db-hero-time-chip { background:#f7fee7; color:#4d7c0f; border:1px solid #d9f99d; }
  .db-hero-meta { display:flex; align-items:center; justify-content:flex-end; min-width:0; flex:1; gap:.7rem; position:relative; z-index:1; }
  .db-hero-counters { display:flex; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; width:100%; }
  .db-hero-counter-card { min-width:132px; max-width:180px; background:linear-gradient(180deg,#ffffff,#f9fafb); border:1px solid #e5e7eb; border-radius:12px; padding:.45rem .65rem; box-shadow:0 4px 10px rgba(15,23,42,.05); }
  .db-hero-counter-label { font-size:.52rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; color:#f59e0b; }
  .db-hero-counter-name { font-size:.82rem; font-weight:800; color:#0f172a; line-height:1.15; margin-top:.12rem; }
  .db-hero-counter-meta { font-size:.62rem; color:#64748b; margin-top:.12rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .db-hero-counter-empty { width:100%; text-align:right; font-size:.68rem; color:#64748b; font-weight:700; }
  .db-rail-brand { margin-bottom:.75rem; background:linear-gradient(180deg,#ffffff,#fafafa); border:1px solid #e5e7eb; border-radius:14px; padding:.7rem .8rem; box-shadow:0 8px 18px rgba(15,23,42,.05); text-align:center; }
  .db-rail-brand.platform { box-shadow:inset 0 0 0 1px #fed7aa, 0 8px 18px rgba(15,23,42,.05); }
  .db-rail-brand.customer { box-shadow:inset 0 0 0 1px #d9f99d, 0 8px 18px rgba(15,23,42,.05); }
  .db-rail-brand-kicker { font-size:.54rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; color:#9ca3af; margin-bottom:.45rem; }
  .db-rail-brand-logo-wrap { height:72px; display:flex; align-items:center; justify-content:center; }
  .db-rail-brand-logo { max-width:100%; max-height:68px; object-fit:contain; filter:drop-shadow(0 2px 4px rgba(0,0,0,.08)); }
  .db-rail-brand-name { font-size:.8rem; font-weight:800; color:#111827; margin-top:.35rem; line-height:1.1; }
  .db-rail-brand-sub { font-size:.62rem; color:#6b7280; margin-top:.15rem; }
  .db-section { margin-bottom:.8rem; }
  .db-section-head { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:.45rem; }
  .db-section-kicker { font-size:.58rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; color:#0891b2; }
  .db-section-title { font-size:.84rem; font-weight:800; color:#1f2937; }
  .db-kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:.65rem; margin-bottom:.8rem; }
  .db-kpi { background:#fff; border-radius:10px; padding:.75rem .9rem; border:1px solid #f0f0f0; position:relative; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); transition:all .15s; }
  .db-kpi:hover { box-shadow:0 3px 12px rgba(0,0,0,.07); transform:translateY(-1px); }
  .db-kpi-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.9rem; position:absolute; top:.75rem; right:.9rem; }
  .db-kpi-label { font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:.2rem; }
  .db-kpi-val { font-size:1.35rem; font-weight:800; color:#1f2937; line-height:1.2; }
  .db-kpi-sub { font-size:.62rem; color:#6c757d; margin-top:.2rem; display:flex; align-items:center; gap:.3rem; }
  .db-kpi-sub .up { color:#16a34a; font-weight:700; }
  .db-kpi-sub .down { color:#ef4444; font-weight:700; }
  .db-kpi-sub .neutral { color:#6c757d; font-weight:600; }
  .db-kpi-accent { position:absolute; left:0; top:0; bottom:0; width:3px; border-radius:10px 0 0 10px; }
  .db-card { background:linear-gradient(180deg,#ffffff,#fbfbfb); border-radius:12px; border:1px solid #e8e8e8; box-shadow:0 8px 18px rgba(15,23,42,.045); margin-bottom:.8rem; overflow:hidden; height:100%; width:100%; display:flex; flex-direction:column; }
  .db-card-head { padding:.55rem .85rem; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; }
  .db-card-title { font-size:.72rem; font-weight:700; color:#374151; display:flex; align-items:center; gap:.35rem; }
  .db-card-title i { font-size:.62rem; color:#f59e0b; }
  .db-card-badge { font-size:.52rem; font-weight:700; padding:.12rem .4rem; border-radius:4px; text-transform:uppercase; }
  .db-card-body { padding:.6rem .85rem; flex:1; }
  .db-card-body-np { padding:0; }
  .db-card-body-fixed { min-height:320px; max-height:320px; overflow:auto; }
  #payment-modes-chart { height:240px; min-height:240px; max-height:240px; overflow:hidden; }
  .db-mini-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.65rem; }
  .db-mini-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:.8rem .9rem; box-shadow:0 1px 4px rgba(0,0,0,.04); }
  .db-mini-label { font-size:.58rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; color:#94a3b8; margin-bottom:.2rem; }
  .db-mini-value { font-size:1rem; font-weight:800; color:#0f172a; }
  .db-mini-sub { font-size:.62rem; color:#64748b; margin-top:.2rem; }
  .db-rank-chip { display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border-radius:999px; background:#dcfce7; color:#166534; font-size:.62rem; font-weight:800; }
  .db-rank-name { font-weight:800; color:#166534; }
  .db-rank-sub { display:block; font-size:.6rem; color:#6b7280; margin-top:.08rem; }
  .db-progress { width:100%; height:8px; border-radius:999px; background:#ecfdf5; overflow:hidden; margin-top:.22rem; }
  .db-progress-bar { height:100%; border-radius:999px; background:linear-gradient(90deg,#16a34a,#22c55e); }
  .db-progress-text { font-size:.58rem; color:#166534; font-weight:700; margin-top:.18rem; display:block; }
  .db-main-grid { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:.75rem; margin-bottom:.8rem; align-items:stretch; }
  .db-main-grid-3 { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:.75rem; margin-bottom:.8rem; align-items:stretch; }
  .db-main-span-7 { grid-column:span 7; min-width:0; }
  .db-main-span-5 { grid-column:span 5; min-width:0; }
  .db-main-span-6 { grid-column:span 6; min-width:0; }
  .db-main-span-3 { grid-column:span 3; min-width:0; }
  .db-main-span-12 { grid-column:span 12; min-width:0; }
  .db-qa { display:grid; grid-template-columns:repeat(auto-fit,minmax(105px,1fr)); gap:.5rem; }
  .db-qa-btn { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:.6rem .4rem; border-radius:8px; border:1px solid #f0f0f0; background:#fafbfc; cursor:pointer; transition:all .12s; text-decoration:none; }
  .db-qa-btn:hover { background:#f0fdf4; border-color:#86efac; transform:translateY(-1px); box-shadow:0 2px 8px rgba(0,0,0,.05); }
  .db-qa-btn i { font-size:1rem; margin-bottom:.3rem; }
  .db-qa-btn span { font-size:.58rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.03em; text-align:center; }
  .db-feed { max-height:310px; overflow-y:auto; }
  .db-feed-item { display:flex; gap:.55rem; padding:.4rem 0; border-bottom:1px solid #f9fafb; align-items:flex-start; }
  .db-feed-item:last-child { border-bottom:none; }
  .db-feed-dot { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.6rem; flex-shrink:0; }
  .db-feed-text { flex:1; min-width:0; }
  .db-feed-title { font-size:.7rem; font-weight:600; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .db-feed-detail { font-size:.6rem; color:#6c757d; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .db-feed-time { font-size:.52rem; color:#9ca3af; font-weight:600; white-space:nowrap; flex-shrink:0; margin-top:.1rem; }
  .db-top-tbl { width:100%; }
  .db-top-tbl th { font-size:.54rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#9ca3af; padding:.3rem .4rem; border-bottom:1px solid #f3f4f6; }
  .db-top-tbl td { font-size:.7rem; padding:.35rem .4rem; border-bottom:1px solid #fafbfc; color:#374151; }
  .db-top-tbl td:first-child { font-weight:700; color:#6c757d; width:20px; }
  .db-top-tbl tbody tr:hover td { background:#fafbfc; }
  .db-tight-tbl th { padding:.28rem .55rem; font-size:.52rem; }
  .db-tight-tbl td { padding:.28rem .55rem; font-size:.66rem; }
  .db-tight-tbl td:first-child { width:auto; }
  .db-greet { margin-bottom:.7rem; }
  .db-greet-hi { font-size:1.15rem; font-weight:800; color:#1f2937; margin:0; }
  .db-greet-sub { font-size:.7rem; color:#6c757d; margin-top:.1rem; }
  .db-greet-date { font-size:.62rem; color:#9ca3af; font-weight:600; }
  .db-alert-item { display:flex; justify-content:space-between; gap:.75rem; align-items:center; padding:.45rem 0; border-bottom:1px solid #f3f4f6; }
  .db-alert-item:last-child { border-bottom:none; }
  .db-alert-title { font-size:.68rem; font-weight:700; color:#1f2937; }
  .db-alert-value { font-size:.6rem; font-weight:700; padding:.15rem .45rem; border-radius:999px; }
  .db-alert-link { text-decoration:none; color:inherit; display:flex; justify-content:space-between; gap:.75rem; align-items:center; width:100%; }
  .db-alert-link:hover .db-alert-title { color:#b45309; }
  .db-counter-health-link { color:inherit; text-decoration:none; display:contents; cursor:pointer; }
  .db-counter-health-link:hover td { background:#eff6ff !important; }
  .db-alert-success { background:#dcfce7; color:#166534; }
  .db-alert-info { background:#dbeafe; color:#1d4ed8; }
  .db-alert-warning { background:#fef3c7; color:#92400e; }
  .db-alert-danger { background:#fee2e2; color:#b91c1c; }
  .db-status-pill { display:inline-flex; align-items:center; gap:.28rem; padding:.16rem .5rem; border-radius:999px; font-size:.56rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
  .db-status-ok { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
  .db-status-bad { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
  .db-counter-row-alert td { background:#fff5f5; }
  .db-notice-chip { display:inline-flex; align-items:center; gap:.28rem; padding:.16rem .48rem; border-radius:999px; background:#fff7ed; color:#c2410c; font-size:.58rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; border:1px solid #fdba74; box-shadow:0 2px 10px rgba(245,158,11,.08); }
  .db-notice-dot { width:8px; height:8px; border-radius:999px; background:#f97316; box-shadow:0 0 0 rgba(249,115,22,.55); animation:dbPulse 1.4s infinite; }
  .db-staff-table-wrap { padding:.2rem 0; }
  .db-staff-rank { display:inline-flex; width:22px; height:22px; align-items:center; justify-content:center; border-radius:999px; font-size:.58rem; font-weight:800; color:#fff; }
  .db-staff-rank.r1 { background:#ef4444; }
  .db-staff-rank.r2 { background:#f59e0b; }
  .db-staff-rank.r3 { background:#10b981; }
  .db-staff-rank.r4 { background:#3b82f6; }
  .db-staff-rank.r5 { background:#8b5cf6; }
  .db-staff-rank.rX { background:#64748b; }
  .db-staff-name { font-weight:800; color:#0f172a; }
  .db-staff-orders { color:#475569; font-weight:700; }
  .db-staff-total { font-weight:800; color:#166534; }
  .db-alert-success-soft { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
  .db-alert-info-soft { background:#eff6ff; color:#2563eb; border:1px solid #93c5fd; }
  .db-alert-warning-soft { background:#fffbeb; color:#d97706; border:1px solid #fcd34d; }
  .db-alert-danger-soft { background:#fef2f2; color:#dc2626; border:1px solid #fca5a5; }
  #modal .db-stock-dialog { max-width:760px; }
  .db-stock-modal-wrap { overflow:hidden; border:0; border-radius:18px; box-shadow:0 24px 70px rgba(15,23,42,.24); }
  .db-stock-modal-head { padding:1.05rem 1.2rem .95rem; border:0; background:linear-gradient(135deg,#0f172a,#1e3a8a); color:#fff; }
  .db-stock-modal-head .close { color:#fff; opacity:.75; text-shadow:none; font-size:1.35rem; }
  .db-stock-modal-head .close:hover { opacity:1; }
  .db-stock-modal-kicker { margin-bottom:.2rem; color:#93c5fd; font-size:.55rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
  .db-stock-modal-title { margin:0; font-size:1.02rem; font-weight:900; letter-spacing:-.02em; }
  .db-stock-modal-subtitle { margin-top:.25rem; color:#cbd5e1; font-size:.64rem; }
  .db-stock-modal { padding:1rem 1.1rem 1.1rem; background:#f8fafc; }
  .db-stock-section-label { margin:.1rem 0 .45rem; color:#64748b; font-size:.55rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
  .db-stock-counter-tabs, .db-stock-status-tabs { display:flex; gap:.45rem; flex-wrap:wrap; margin-bottom:.85rem; }
  .db-stock-counter-tab, .db-stock-status-tab { display:inline-flex; align-items:center; gap:.38rem; border:1px solid #e2e8f0; background:#fff; color:#475569; border-radius:10px; padding:.48rem .62rem; font-size:.62rem; font-weight:800; cursor:pointer; box-shadow:0 2px 5px rgba(15,23,42,.035); transition:all .15s ease; }
  .db-stock-counter-tab:hover, .db-stock-status-tab:hover { border-color:#93c5fd; color:#1d4ed8; transform:translateY(-1px); }
  .db-stock-counter-tab.active, .db-stock-status-tab.active { background:#2563eb; border-color:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.22); }
  .db-stock-counter-tab b, .db-stock-status-tab b { min-width:20px; padding:.14rem .3rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-size:.55rem; text-align:center; }
  .db-stock-counter-tab.active b, .db-stock-status-tab.active b { background:rgba(255,255,255,.2); color:#fff; }
  .db-stock-status-tabs { padding:.55rem; margin-bottom:.7rem; border:1px solid #e2e8f0; border-radius:12px; background:#eef2ff; }
  .db-stock-status-tab { border:0; background:transparent; box-shadow:none; }
  .db-stock-status-tab:hover { background:#fff; transform:none; }
  .db-stock-status-tab.active { background:#fff; color:#1d4ed8; box-shadow:0 3px 8px rgba(37,99,235,.12); }
  .db-stock-status-tab.active b { background:#dbeafe; color:#1d4ed8; }
  .db-stock-table-shell { overflow:auto; max-height:52vh; border:1px solid #e2e8f0; border-radius:13px; background:#fff; box-shadow:0 4px 12px rgba(15,23,42,.04); }
  .db-stock-table { width:100%; border-collapse:separate; border-spacing:0; }
  .db-stock-table th { position:sticky; top:0; z-index:1; color:#64748b; background:#f8fafc; font-size:.56rem; font-weight:900; letter-spacing:.07em; text-transform:uppercase; padding:.62rem .7rem; border-bottom:1px solid #e2e8f0; text-align:left; }
  .db-stock-table td { color:#334155; font-size:.68rem; padding:.62rem .7rem; border-bottom:1px solid #f1f5f9; }
  .db-stock-table tbody tr:last-child td { border-bottom:0; }
  .db-stock-table tbody tr:hover td { background:#f8fbff; }
  .db-stock-table td:nth-child(2), .db-stock-table td:nth-child(3) { text-align:right; white-space:nowrap; font-weight:800; }
  .db-stock-table td:nth-child(2) { color:#0f172a; }
  .db-stock-table td:nth-child(3) { color:#64748b; }
  .db-stock-modal-empty { padding:2rem .5rem; border:1px dashed #cbd5e1; border-radius:13px; background:#fff; color:#64748b; font-size:.7rem; text-align:center; }
  .db-rail-right > .db-card > .alerts {
    margin: 0 !important;
    padding: 0 12px !important;
    background: #fff7f7 !important;
    border: 1px solid #fecaca !important;
    border-radius: 12px !important;
    box-shadow: 0 5px 18px rgba(185, 28, 28, .08) !important;
  }
  .db-rail-right > .db-card > .alerts .panel {
    padding: 0 0 10px !important;
  }
  .db-rail-right > .db-card > .alerts h2 {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    margin: 0 !important;
    padding: 0 2px;
    color: #991b1b;
    font-size: .78rem;
    font-weight: 900;
    letter-spacing: .07em;
    text-transform: uppercase;
  }
  .db-rail-right > .db-card > .alerts h2:before {
    content: "!";
    display: inline-grid;
    place-items: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #dc2626;
    color: #fff;
    font-size: .75rem;
    font-weight: 900;
  }
  .db-rail-right > .db-card > .alerts .scroll {
    display: grid;
    gap: 7px;
    max-height: 190px;
    overflow: auto;
    padding: 0 0 2px;
  }
  .db-rail-right > .db-card > .alerts .alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 39px;
    margin: 0 !important;
    padding: 7px 9px !important;
    border: 1px solid #fecaca !important;
    border-radius: 8px !important;
    background: #fff !important;
    color: #991b1b !important;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1.2;
  }
  .db-rail-right > .db-card > .alerts .alert span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .db-rail-right > .db-card > .alerts .alert b {
    display: inline-grid;
    place-items: center;
    flex: 0 0 28px;
    min-width: 28px;
    height: 25px;
    border-radius: 7px;
    background: #dc2626 !important;
    color: #fff !important;
    font-size: .68rem;
    font-weight: 900;
  }
  .db-rail-right > .db-card > .alerts .alert:hover {
    border-color: #ef4444 !important;
    background: #fff1f2 !important;
  }
  .db-rail-right > .db-card > .alerts .empty {
    padding: 10px 8px;
    border-radius: 8px;
    background: #fff;
    color: #b91c1c;
    font-size: .66rem;
    font-weight: 700;
    text-align: center;
  }
  #sdToastWrap { position:fixed; top:16px; right:16px; display:flex; flex-direction:column; gap:8px; pointer-events:none; max-width:370px; z-index:10001; }
  .sd-toast { pointer-events:auto; display:flex; align-items:flex-start; gap:10px; padding:12px 14px; border-radius:12px; background:rgba(255,255,255,.97); backdrop-filter:blur(12px); box-shadow:0 8px 32px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.06); border:1px solid rgba(0,0,0,.06); min-width:300px; max-width:370px; animation:sdToastIn .35s cubic-bezier(.21,1.02,.73,1) forwards; position:relative; overflow:hidden; font-family:Inter,sans-serif; }
  .sd-toast.sd-toast-out { animation:sdToastOut .28s cubic-bezier(.55,.06,.68,.19) forwards; }
  .sd-toast::before { content:''; position:absolute; left:0; top:0; bottom:0; width:4px; border-radius:12px 0 0 12px; }
  .sd-toast.sd-t-info::before { background:linear-gradient(180deg,#3B82F6,#2563EB); }
  .sd-toast.sd-t-success::before { background:linear-gradient(180deg,#10B981,#059669); }
  .sd-toast.sd-t-warning::before { background:linear-gradient(180deg,#F59E0B,#D97706); }
  .sd-toast.sd-t-error::before { background:linear-gradient(180deg,#EF4444,#DC2626); }
  .sd-toast-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .sd-t-info .sd-toast-icon { background:#EFF6FF; color:#3B82F6; }
  .sd-t-success .sd-toast-icon { background:#ECFDF5; color:#059669; }
  .sd-t-warning .sd-toast-icon { background:#FFFBEB; color:#D97706; }
  .sd-t-error .sd-toast-icon { background:#FEF2F2; color:#DC2626; }
  .sd-toast-close { position:absolute; top:6px; right:8px; cursor:pointer; opacity:.35; transition:opacity .15s; line-height:1; }
  .sd-toast-close:hover { opacity:.8; }
  .sd-toast-bar { position:absolute; bottom:0; left:0; height:3px; border-radius:0 0 0 12px; opacity:.5; }
  .sd-t-info .sd-toast-bar { background:#3B82F6; }
  .sd-t-success .sd-toast-bar { background:#10B981; }
  .sd-t-warning .sd-toast-bar { background:#F59E0B; }
  .sd-t-error .sd-toast-bar { background:#EF4444; }
  @keyframes sdToastIn { from { opacity:0; transform:translateX(60px) scale(.95); } to { opacity:1; transform:translateX(0) scale(1); } }
  @keyframes sdToastOut { from { opacity:1; transform:translateX(0) scale(1); max-height:200px; margin-bottom:0; } to { opacity:0; transform:translateX(80px) scale(.92); max-height:0; margin-bottom:-8px; } }
  @keyframes sdBarShrink { from { width:100%; } to { width:0%; } }
  @keyframes dbPulse {
    0% { box-shadow:0 0 0 0 rgba(249,115,22,.55); }
    70% { box-shadow:0 0 0 8px rgba(249,115,22,0); }
    100% { box-shadow:0 0 0 0 rgba(249,115,22,0); }
  }
  @media(max-width:1199px) {
    .db-shell { grid-template-columns:1fr; }
    .db-rail, .db-rail-right { position:static; }
    .db-main-span-7, .db-main-span-6, .db-main-span-5, .db-main-span-3 { grid-column:span 12; }
    .db-canvas { order:1; }
    .db-rail { order:2; }
    .db-rail-right { order:3; }
  }
  @media(max-width:900px) {
    .db-mini-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media(max-width:991px) {
    #page-container.page-with-top-menu .content {
      margin-left:0 !important;
      padding:10px !important;
    }
    .db-topbar {
      position:sticky;
      top:0;
      z-index:90;
      margin:-10px -10px 12px;
      padding:.72rem .9rem;
      border-radius:0 0 14px 14px;
    }
    .db-topbar-title {
      font-size:.92rem;
      line-height:1.3;
    }
    .db-shell {
      gap:12px;
    }
    .db-rail-brand {
      margin-bottom:12px;
      padding:.75rem;
    }
    .db-rail-brand-logo-wrap {
      height:60px;
    }
    .db-rail-brand-logo {
      max-height:54px;
    }
    .db-section-head {
      margin-bottom:.35rem;
    }
    .db-card {
      border-radius:14px;
    }
    .db-card-head {
      padding:.65rem .8rem;
    }
    .db-card-body {
      padding:.7rem .8rem;
    }
    .db-card-body-fixed {
      min-height:260px;
      max-height:260px;
    }
    #weekly-chart {
      min-height:240px !important;
    }
    #payment-modes-chart {
      height:220px;
      min-height:220px;
      max-height:220px;
    }
    #donutchart,
    #staff-performance-chart {
      min-height:230px;
    }
    .db-feed {
      max-height:300px;
    }
    .db-tight-tbl {
      display:block;
      overflow-x:auto;
      white-space:nowrap;
    }
  }
  @media(max-width:767px) {
    .db-page:before {
      opacity:.2;
    }
    .db-hero {
      flex-direction:column;
      align-items:stretch;
      gap:12px;
      padding:.9rem;
    }
    .db-hero-title {
      font-size:1.12rem;
    }
    .db-hero-sub {
      font-size:.72rem;
      line-height:1.4;
    }
    .db-hero-meta {
      align-items:stretch;
      min-width:0;
      width:100%;
    }
    .db-hero-counters {
      justify-content:flex-start;
      flex-wrap:nowrap;
      overflow-x:auto;
      padding-bottom:4px;
      scrollbar-width:thin;
    }
    .db-hero-counter-card {
      min-width:132px;
      max-width:none;
      flex:0 0 132px;
    }
    .db-hero-counter-empty { text-align:left; }
    .db-kpi-grid { grid-template-columns:repeat(2,1fr); gap:10px; }
    .db-kpi {
      padding:.75rem .78rem;
      min-height:102px;
    }
    .db-kpi-icon {
      width:32px;
      height:32px;
      font-size:.8rem;
      top:.65rem;
      right:.7rem;
    }
    .db-kpi-label {
      font-size:.53rem;
      max-width:calc(100% - 38px);
    }
    .db-kpi-val { font-size:1rem; }
    .db-kpi-sub {
      font-size:.56rem;
      display:block;
      line-height:1.35;
    }
    .db-mini-grid { grid-template-columns:1fr; }
    .db-feed-item {
      gap:.45rem;
      padding:.5rem 0;
    }
    .db-feed-dot {
      width:26px;
      height:26px;
    }
    .db-feed-title {
      font-size:.68rem;
      white-space:normal;
    }
    .db-feed-detail {
      white-space:normal;
      line-height:1.35;
    }
    .db-feed-time {
      font-size:.5rem;
    }
    .db-alert-item {
      gap:.5rem;
      padding:.52rem 0;
    }
    .db-alert-title {
      font-size:.65rem;
      line-height:1.35;
    }
    .db-alert-value,
    .db-card-badge,
    .db-notice-chip {
      font-size:.52rem;
    }
    .db-top-tbl th {
      font-size:.5rem;
      padding:.3rem .38rem;
    }
    .db-top-tbl td {
      font-size:.64rem;
      padding:.34rem .38rem;
    }
    .db-staff-table-wrap {
      overflow-x:auto;
    }
    .db-staff-rank {
      min-width:22px;
      height:22px;
      font-size:.56rem;
    }
  }
  @media(max-width:575px) {
    .db-topbar-title {
      font-size:.84rem;
    }
    .db-hero-title {
      font-size:1.02rem;
    }
    .db-hero-date-chip,
    .db-hero-time-chip {
      font-size:.56rem;
      padding:.18rem .46rem;
    }
    .db-kpi {
      min-height:96px;
    }
    .db-card-body-fixed {
      min-height:230px;
      max-height:230px;
    }
    #weekly-chart {
      min-height:220px !important;
    }
    #payment-modes-chart {
      height:200px;
      min-height:200px;
      max-height:200px;
    }
  }
</style>
<style id="reference-dashboard-layout">
  /* Final reference layout overrides. Kept after the dashboard's original CSS. */
  #page-container:has(.db-page) { background:#f4f7fb !important; }
  #page-container:has(.db-page) #content { padding:10px 16px 24px !important; }
  #page-container:has(.db-page) .db-page { max-width:1900px !important; margin:0 auto !important; }
  #page-container:has(.db-page) .db-shell { display:grid !important; grid-template-columns:250px minmax(0,1fr) 260px !important; gap:14px !important; align-items:start !important; }
  #page-container:has(.db-page) .db-rail, #page-container:has(.db-page) .db-canvas, #page-container:has(.db-page) .db-rail-right { min-width:0 !important; }
  #page-container:has(.db-page) .db-topbar { display:block !important; text-align:center !important; margin:5px 0 12px !important; }
  #page-container:has(.db-page) .db-topbar-title { font-size:20px !important; font-weight:800 !important; color:#17233d !important; }
  #page-container:has(.db-page) .db-hero { min-height:126px !important; display:flex !important; justify-content:space-between !important; align-items:center !important; gap:18px !important; padding:20px 22px !important; margin-bottom:12px !important; background:#fff !important; border:1px solid #e4e9f1 !important; border-radius:12px !important; box-shadow:0 5px 18px rgba(25,39,66,.07) !important; }
  #page-container:has(.db-page) .db-hero-title { margin:3px 0 6px !important; font-size:25px !important; color:#17233d !important; font-weight:800 !important; }
  #page-container:has(.db-page) .db-kpi-grid { display:grid !important; grid-template-columns:repeat(6,minmax(0,1fr)) !important; gap:10px !important; margin-bottom:12px !important; }
  #page-container:has(.db-page) .db-kpi { min-height:130px !important; padding:16px 12px 12px 54px !important; background:#fff !important; border:1px solid #e4e9f1 !important; border-radius:12px !important; box-shadow:0 5px 16px rgba(25,39,66,.06) !important; }
  #page-container:has(.db-page) .db-kpi-val { font-size:24px !important; line-height:1.15 !important; font-weight:800 !important; color:#17233d !important; }
  #page-container:has(.db-page) .db-main-grid-3, #page-container:has(.db-page) .db-main-grid { display:grid !important; grid-template-columns:repeat(12,minmax(0,1fr)) !important; gap:12px !important; margin-bottom:12px !important; }
  #page-container:has(.db-page) .db-main-span-3 { grid-column:span 3 !important; min-width:0 !important; }
  #page-container:has(.db-page) .db-main-span-6 { grid-column:span 6 !important; min-width:0 !important; }
  #page-container:has(.db-page) .db-main-span-9 { grid-column:span 9 !important; min-width:0 !important; }
  #page-container:has(.db-page) .db-main-span-12 { grid-column:span 12 !important; min-width:0 !important; }
  #page-container:has(.db-page) .db-card { margin-bottom:0 !important; background:#fff !important; border:1px solid #e4e9f1 !important; border-radius:12px !important; box-shadow:0 5px 18px rgba(25,39,66,.07) !important; overflow:hidden !important; }
  #page-container:has(.db-page) .db-card-head { min-height:42px !important; padding:12px 14px !important; background:#fff !important; border-bottom:1px solid #edf0f5 !important; }
  #page-container:has(.db-page) .db-card-body, #page-container:has(.db-page) .db-card-body-np { padding:13px 14px !important; }
  #page-container:has(.db-page) .db-rail-brand, #page-container:has(.db-page) .db-rail-right .db-rail-brand { margin-bottom:12px !important; background:#fff !important; border:1px solid #e4e9f1 !important; border-radius:12px !important; box-shadow:0 5px 18px rgba(25,39,66,.07) !important; }
  #page-container:has(.db-page) .db-feed { max-height:330px !important; overflow:auto !important; }
  @media (max-width:1400px) { #page-container:has(.db-page) .db-shell { grid-template-columns:220px minmax(0,1fr) 220px !important; } #page-container:has(.db-page) .db-kpi-grid { grid-template-columns:repeat(3,minmax(0,1fr)) !important; } }
  @media (max-width:992px) { #page-container:has(.db-page) .db-shell { grid-template-columns:1fr !important; } #page-container:has(.db-page) .db-kpi-grid { grid-template-columns:repeat(2,minmax(0,1fr)) !important; } #page-container:has(.db-page) .db-main-span-3, #page-container:has(.db-page) .db-main-span-6, #page-container:has(.db-page) .db-main-span-9 { grid-column:span 12 !important; } #page-container:has(.db-page) .db-hero { flex-direction:column !important; align-items:flex-start !important; } }
  @media (max-width:600px) { #page-container:has(.db-page) #content { padding:8px !important; } #page-container:has(.db-page) .db-kpi-grid { grid-template-columns:1fr !important; } }
</style>
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>

  <link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/gritter/css/jquery.gritter.css" rel="stylesheet" />

  <div id="content" class="content">
    <div class="db-page">
      <div class="db-topbar">
        <div class="db-topbar-title"><i class="fa fa-th-large me-1"></i>MAIN COMPANY DASHBOARD</div>
      </div>
      <div class="db-shell">
        <aside class="db-rail">
          <div class="db-rail-brand platform">
            <div class="db-rail-brand-kicker">Platform Logo</div>
            <div class="db-rail-brand-logo-wrap"><img src="../../assets/img/logo/ktouch.png" alt="KTouch" class="db-rail-brand-logo"></div>
            <div class="db-rail-brand-name">KTouch</div>
            <div class="db-rail-brand-sub">Front operations platform</div>
          </div>
          <div class="db-section-head"><div><div class="db-section-kicker">Utility Rail</div><div class="db-section-title">Targets, actions, alerts</div></div></div>
          <div class="db-card">
            <div class="db-card-head"><div class="db-card-title"><i class="fa fa-bullseye"></i> Front Position</div><span class="db-card-badge" style="background:#d1fae5;color:#065f46;"><?php echo $open_shift_label; ?></span></div>
            <div class="db-card-body-np"><table class="db-top-tbl db-tight-tbl"><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody><tr><td>Purchases</td><td><?php echo dash_money($front_purchase_total); ?></td></tr><tr><td>Unpaid</td><td><?php echo dash_money($unpaid_total); ?></td></tr><tr><td>Credit</td><td><?php echo dash_money($credit_today); ?></td></tr><tr><td>Expenses</td><td><?php echo dash_money($expenses_total); ?></td></tr><tr><td>Net</td><td><?php echo dash_money($net_after_expenses); ?></td></tr></tbody></table></div>
          </div>
          <div class="db-card">
            <div class="db-card-head">
              <div class="db-card-title"><i class="fa fa-bell"></i> Alerts and Status</div>
            </div>
            <div class="db-card-body"><div class="db-alert-item">
              <a class="db-alert-link" href="../../front/cash/index.php">
                <div class="db-alert-title">Unpaid Bills</div>
                <span class="db-alert-value <?php echo $unpaid_count > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $unpaid_count; ?></span>
              </a>
            </div>
            <div class="db-alert-item">
              <a class="db-alert-link" href="#">
                <div class="db-alert-title">Pending Returns</div>
                <span class="db-alert-value <?php echo $pending_returns_total > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $pending_returns_total; ?></span>
              </a>
            </div>
            <div class="db-alert-item">
              <a class="db-alert-link" href="#">
                <div class="db-alert-title">Unreceived Stock</div>
                <span class="db-alert-value <?php echo $pending_transfers_total > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $pending_transfers_total; ?></span>
              </a>
            </div>
            <div class="db-alert-item">
              <a class="db-alert-link" href="../kugura/appr">
                <div class="db-alert-title">Purchases Pending Approval</div>
                <span class="db-alert-value <?php echo $front_purchase_pending > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $front_purchase_pending; ?></span>
              </a>
            </div>
            <div class="db-alert-item">
              <a class="db-alert-link" href="../kugura/po_pending.php">
                <div class="db-alert-title">Purchases Pending Receiving</div>
                <span class="db-alert-value <?php echo $front_purchase_receiving > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $front_purchase_receiving; ?></span>
              </a>
            </div>
          </div>
          </div>
          <div class="db-card" style="background:linear-gradient(180deg,#fff7ed 0%,#ffffff 100%); border:1px solid #fdba74; box-shadow:inset 0 0 0 1px #fed7aa, 0 14px 28px rgba(194, 65, 12, .12);"><div class="db-card-head"><div class="db-card-title" style="color:#9a3412;"><i class="fa fa-print"></i> Print Worker and Printouts</div><span id="db-print-running-pill" class="db-card-badge" style="background:#fef3c7;color:#92400e;">Checking...</span></div><div class="db-card-body"><div class="db-alert-item" style="background:rgba(255,255,255,.75); border-radius:12px; padding:.55rem .65rem; margin-bottom:.45rem;"><div class="db-alert-title">Pending queue</div><span id="db-print-metric-pending" class="db-alert-value db-alert-info">0</span></div><div class="db-alert-item" style="background:rgba(255,255,255,.75); border-radius:12px; padding:.55rem .65rem; margin-bottom:.45rem;"><div class="db-alert-title">Processing now</div><span id="db-print-metric-processing" class="db-alert-value db-alert-warning">0</span></div><div class="db-alert-item" style="background:rgba(255,255,255,.75); border-radius:12px; padding:.55rem .65rem; margin-bottom:.45rem;"><div class="db-alert-title">Failed jobs</div><span id="db-print-metric-failed" class="db-alert-value db-alert-danger">0</span></div><div class="db-alert-item" style="background:rgba(255,255,255,.75); border-radius:12px; padding:.55rem .65rem; margin-bottom:.45rem;"><div class="db-alert-title">Not printed</div><span id="db-print-metric-unprinted" class="db-alert-value db-alert-warning">0</span></div><div class="db-alert-item" style="background:rgba(255,255,255,.75); border-radius:12px; padding:.55rem .65rem; margin-bottom:.35rem;"><div class="db-alert-title">Worker heartbeat</div><span id="db-print-heartbeat-age" class="db-rank-name">Checking...</span></div><div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.75rem;"><a href="#" id="db-print-refresh-btn" class="db-card-badge" style="background:#eff6ff;color:#1d4ed8;text-decoration:none;">Refresh</a><a href="#" id="db-print-queue-btn" class="db-card-badge" style="background:#f3e8ff;color:#7c3aed;text-decoration:none;">Open Queue</a><a href="../../gen/startup_print.bat" download class="db-card-badge" style="background:#dbeafe;color:#1d4ed8;text-decoration:none;">Download Setup</a><a href="../../front/chaba/index.php" target="_blank" rel="noopener" class="db-card-badge" style="background:#ffedd5;color:#c2410c;text-decoration:none;">Open Printing Page</a><a href="../settings/settings.php?tab=printer-set" class="db-card-badge" style="background:#ecfdf5;color:#047857;text-decoration:none;">Printer Setup</a></div></div></div>
          <div class="db-card">
            <div class="db-card-head"><div class="db-card-title"><i class="fa fa-stream"></i> Current Activities</div><span class="db-card-badge" style="background:#f3f4f6;color:#6c757d;">LIVE</span></div>
            <div class="db-card-body db-feed" id="db-activity-feed"><?php if (empty($activity_rows)) { ?><div class="text-center text-muted py-3" style="font-size:.68rem;">No recent activity yet.</div><?php } else { foreach ($activity_rows as $activity) { ?><div class="db-feed-item"><div class="db-feed-dot <?php echo $activity['tone'] === 'success' ? 'db-alert-success' : ($activity['tone'] === 'warning' ? 'db-alert-warning' : ($activity['tone'] === 'danger' ? 'db-alert-danger' : 'db-alert-info')); ?>"><i class="fa fa-circle"></i></div><div class="db-feed-text"><div class="db-feed-title"><?php echo htmlspecialchars($activity['title']); ?></div><div class="db-feed-detail"><?php echo htmlspecialchars($activity['detail']); ?></div></div><div class="db-feed-time"><?php echo htmlspecialchars($activity['time']); ?></div></div><?php } } ?></div>
          </div>
        </aside>
        <main class="db-canvas">
          <div class="db-hero"><div><div class="db-eyebrow">Company Performance</div><h3 class="db-hero-title">Current Shift <?php echo $compare_label; ?></h3><div class="db-hero-sub">Compared to last shift <?php echo $prev_compare_label; ?></div><div class="db-hero-datetime"><span class="db-hero-date-chip"><i class="fa fa-calendar"></i><?php echo $compare_label; ?></span><span class="db-hero-time-chip"><i class="fa fa-clock"></i><span id="db-live-clock"></span></span></div></div><div class="db-hero-meta"><div class="db-hero-counters" id="db-hero-counters"><?php if (empty($open_counter_cards)) { ?><div class="db-hero-counter-empty">No open counters</div><?php } else { foreach ($open_counter_cards as $counter_card) { ?><div class="db-hero-counter-card"><div class="db-hero-counter-label">Open Counter</div><div class="db-hero-counter-name"><?php echo htmlspecialchars($counter_card['counter']); ?></div><div class="db-hero-counter-meta"><?php echo htmlspecialchars($counter_card['cashier']); ?></div></div><?php } } ?></div></div></div>
          <div class="db-section"><div class="db-section-head"><div><div class="db-section-kicker">Company KPIs</div><div class="db-section-title">Head office snapshot</div></div></div><div class="db-kpi-grid"><div class="db-kpi"><div class="db-kpi-accent" style="background:#ef4444;"></div><div class="db-kpi-icon" style="background:#fee2e2;color:#b91c1c;"><i class="fa fa-coins"></i></div><div class="db-kpi-label">Total Sales</div><div class="db-kpi-val" id="db-kpi-total-sales"><?php echo dash_money($orders_total); ?></div><div class="db-kpi-sub"><span class="<?php echo $sales_compare_pct >= 0 ? 'up' : 'down'; ?>" id="db-kpi-compare-pct"><?php echo number_format($sales_compare_pct,2); ?>%</span> compare to last shift</div></div><div class="db-kpi"><div class="db-kpi-accent" style="background:#0f766e;"></div><div class="db-kpi-icon" style="background:#ccfbf1;color:#0f766e;"><i class="fa fa-file-invoice-dollar"></i></div><div class="db-kpi-label">Sales Orders</div><div class="db-kpi-val" id="db-kpi-sales-orders"><?php echo $orders_count; ?></div><div class="db-kpi-sub"><span class="neutral">Avg/order</span> <span id="db-kpi-avg-order"><?php echo dash_money($avg_per_order); ?></span></div></div><div class="db-kpi"><div class="db-kpi-accent" style="background:#2563eb;"></div><div class="db-kpi-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fa fa-user-tag"></i></div><div class="db-kpi-label">Credit Today</div><div class="db-kpi-val" id="db-kpi-credit-today"><?php echo dash_money($credit_today); ?></div><div class="db-kpi-sub"><span class="neutral">Collected</span> <span id="db-kpi-credit-collected"><?php echo dash_money($credit_paid_today + $credit_paid_previous); ?></span></div></div><div class="db-kpi"><div class="db-kpi-accent" style="background:#7c3aed;"></div><div class="db-kpi-icon" style="background:#ede9fe;color:#6d28d9;"><i class="fa fa-receipt"></i></div><div class="db-kpi-label">Expenses</div><div class="db-kpi-val" id="db-kpi-expenses"><?php echo dash_money($expenses_total); ?></div><div class="db-kpi-sub"><span class="neutral" id="db-kpi-expense-count"><?php echo $expense_count; ?></span> expense entries</div></div><div class="db-kpi"><div class="db-kpi-accent" style="background:#d97706;"></div><div class="db-kpi-icon" style="background:#fef3c7;color:#b45309;"><i class="fa fa-hourglass-half"></i></div><div class="db-kpi-label">Unpaid Bills</div><div class="db-kpi-val" id="db-kpi-unpaid"><?php echo dash_money($unpaid_total); ?></div><div class="db-kpi-sub"><span class="neutral" id="db-kpi-unpaid-count"><?php echo $unpaid_count; ?></span> pending bills</div></div><div class="db-kpi"><div class="db-kpi-accent" style="background:#16a34a;"></div><div class="db-kpi-icon" style="background:#dcfce7;color:#166534;"><i class="fa fa-wallet"></i></div><div class="db-kpi-label">Net Less Expenses</div><div class="db-kpi-val" id="db-kpi-net"><?php echo dash_money($net_after_expenses); ?></div><div class="db-kpi-sub"><span class="neutral">Paid sales</span> <span id="db-kpi-paid"><?php echo dash_money($paid_total); ?></span></div></div><div class="db-kpi"><div class="db-kpi-accent" style="background:#7e22ce;"></div><div class="db-kpi-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fa fa-award"></i></div><div class="db-kpi-label">Tokens This Month</div><div class="db-kpi-val" id="db-kpi-month-tokens"><?php echo dash_money($monthly_tokens_total); ?></div><div class="db-kpi-sub">Today <span class="neutral" id="db-kpi-today-tokens"><?php echo dash_money($today_tokens_total); ?></span></div></div></div></div>
          <div class="db-main-grid-3"><div class="db-main-span-6"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-chart-bar"></i> Sales and Purchases</div><span class="db-card-badge" style="background:#dbeafe;color:#1d4ed8;" id="db-orders-badge"><?php echo dash_money($orders_total); ?></span></div><div class="db-card-body" id="weekly-chart" style="min-height:280px;"></div></div></div><div class="db-main-span-3"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-credit-card"></i> Payment Modes</div><span class="db-card-badge" style="background:#dcfce7;color:#166534;">THIS SHIFT</span></div><div class="db-card-body" id="payment-modes-chart"></div></div></div><div class="db-main-span-3"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-wave-square"></i> Sales Mix Snapshot</div><span id="db-req-chip"><?php if ($counter_reqs_pending > 0) { ?><span class="db-notice-chip"><span class="db-notice-dot"></span><?php echo $counter_reqs_pending; ?> Active</span><?php } ?></span></div><div class="db-card-body"><div class="db-alert-item"><a class="db-alert-link" href="../../front/counter/index.php"><div class="db-alert-title">Open Counters</div><span class="db-alert-value db-alert-info" id="db-open-counters-count"><?php echo $open_counters_total; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="../../front/counter/index.php"><div class="db-alert-title">Active Cashiers</div><span class="db-alert-value db-alert-success" id="db-active-cashiers-count"><?php echo $active_cashiers_total; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="../../front/cash/index.php"><div class="db-alert-title">Customers On Credit</div><span class="db-alert-value db-alert-info" id="db-credit-customers-count"><?php echo $credit_customers; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="../inventory/counter_req.php?msg=1"><div class="db-alert-title">Requisitions Issue</div><span class="db-alert-value <?php echo $counter_reqs_pending > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>" id="db-req-count"><?php echo $counter_reqs_pending; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="director_report.php" target="_blank" rel="noopener"><div class="db-alert-title">Director Report</div><span class="db-alert-value db-alert-info">Open</span></a></div></div></div></div></div>

          <?php if ($front_pos && mysqli_num_rows($front_pos) > 0) { ?>
          <div class="db-main-grid" style="margin-bottom:.8rem;"><div class="db-main-span-12"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-shopping-basket"></i> Front Purchase Orders &mdash; Pending Approval</div><span class="db-card-badge" style="background:#fef3c7;color:#92400e;"><?php echo $front_purchase_pending; ?> PENDING</span></div><div class="db-card-body-np" style="overflow-x:auto;"><table class="db-top-tbl db-tight-tbl"><thead><tr><th>PO #</th><th>Counter</th><th>Date</th><th>Amount</th><th>Supplier</th><th>Action</th></tr></thead><tbody><?php mysqli_data_seek($front_pos, 0); while ($fpo = mysqli_fetch_assoc($front_pos)) { $fpo_counter = front_po_counter_name($fpo['po_id']); $fpo_sup = $fpo['sup_id'] > 0 ? get_sup($fpo['sup_id']) : '<span style="color:#b91c1c;font-weight:700;">None</span>'; ?><tr><td style="font-weight:800;"><?php echo $fpo['po_id']; ?></td><td><?php echo htmlspecialchars($fpo_counter); ?></td><td><?php echo $fpo['po_date']; ?></td><td style="font-weight:800;"><?php echo dash_money($fpo['amount']); ?></td><td><?php echo $fpo_sup; ?></td><td><?php if ($fpo['sup_id'] > 0) { ?><a href="../kugura/front_po_approve.php?id=<?php echo $fpo['po_id']; ?>" class="btn btn-xs btn-success" style="font-size:.62rem;padding:2px 8px;font-weight:800;">Approve</a><?php } else { ?><a href="#" class="btn btn-xs btn-warning" id="front-po-supplier-btn" data-id="<?php echo $fpo['po_id']; ?>" style="font-size:.62rem;padding:2px 8px;font-weight:800;">Set Supplier</a><?php } ?></td></tr><?php } ?></tbody></table></div></div></div></div>
          <?php } ?>
          <div class="db-main-grid-3"><div class="db-main-span-3"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-users"></i> Staff Sales</div><span class="db-card-badge" style="background:#dbeafe;color:#1d4ed8;">LIVE</span></div><div class="db-card-body db-card-body-fixed db-staff-table-wrap" id="db-staff-sales-table"><table class="db-top-tbl db-tight-tbl"><thead><tr><th>#</th><th>Staff</th><th>Orders</th><th>Total</th></tr></thead><tbody><?php if (empty($staff_table)) { ?><tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.68rem;">No staff sales yet.</td></tr><?php } else { $no = 0; foreach ($staff_table as $row) { $no++; $rank_class = $no <= 5 ? 'r' . $no : 'rX'; ?><tr><td><span class="db-staff-rank <?php echo $rank_class; ?>"><?php echo $no; ?></span></td><td class="db-staff-name"><?php echo htmlspecialchars($row['name']); ?></td><td class="db-staff-orders"><?php echo $row['bills']; ?></td><td class="db-staff-total"><?php echo dash_money($row['total']); ?></td></tr><?php } } ?></tbody></table></div></div></div><div class="db-main-span-3"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-fire"></i> Top Selling Items</div></div><div class="db-card-body db-card-body-fixed" id="donutchart"></div></div></div><div class="db-main-span-3"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-bullseye"></i> KPI Storyline</div></div><div class="db-card-body db-card-body-fixed"><div class="db-alert-item"><a class="db-alert-link" href="../../front/ret/returns.php"><div class="db-alert-title">Pending Returns</div><span class="db-alert-value <?php echo $pending_returns_total > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $pending_returns_total; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="../../front/rec/rec_pending.php"><div class="db-alert-title">Unreceived Stock</div><span class="db-alert-value <?php echo $pending_transfers_total > 0 ? 'db-alert-warning' : 'db-alert-success'; ?>"><?php echo $pending_transfers_total; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="../kugura/po_pending.php"><div class="db-alert-title">PO Approvals</div><span class="db-alert-value db-alert-info"><?php echo $front_purchase_pending; ?></span></a></div><div class="db-alert-item"><a class="db-alert-link" href="../../front/counter/index.php"><div class="db-alert-title">Cashier</div><span class="db-rank-name"><?php echo htmlspecialchars($current_cashier_name); ?></span></a></div></div></div></div><div class="db-main-span-3"><div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-users"></i> Staff Performance</div></div><div class="db-card-body db-card-body-fixed" id="staff-performance-chart"></div></div></div></div>
        </main>
        <aside class="db-rail-right">
          <div class="db-rail-brand customer">
            <div class="db-rail-brand-kicker">Customer Logo</div>
            <div class="db-rail-brand-logo-wrap"><img src="../../assets/img/logo/GOLDEN%20PINTS.jpeg" alt="Golden Pints" class="db-rail-brand-logo"></div>
            <div class="db-rail-brand-name">Golden Pints</div>
            <div class="db-rail-brand-sub">Customer brand</div>
          </div>
          <div class="db-section-head"><div><div class="db-section-kicker">Performance Rail</div><div class="db-section-title">Ops, counters, items</div></div></div>
          <div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-table-list"></i> Operations Strip</div></div><div class="db-card-body-np"><table class="db-top-tbl db-tight-tbl"><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody><tr><td>Shift</td><td><?php echo $open_shift_label; ?></td></tr><tr><td>Sales</td><td><?php echo dash_money($orders_total); ?></td></tr><tr><td>Paid</td><td><?php echo dash_money($paid_total); ?></td></tr><tr><td>Unpaid</td><td><?php echo dash_money($unpaid_total); ?></td></tr><tr><td>Credit</td><td><?php echo dash_money($credit_today); ?></td></tr><tr><td>Expenses</td><td><?php echo dash_money($expenses_total); ?></td></tr></tbody></table></div></div>
          <div class="db-card">
            <section class="card alerts">
              <div class="panel">
                <h2>Alerts</h2>
                <div class="scroll">
                  <div id="alerts-empty" class="empty" style="display:<?php echo ($stock_alerts['out_of_stock'] + $stock_alerts['below_reorder']) > 0 ? 'none' : 'block'; ?>">No alerts</div>
                  <div class="alert db-stock-alert" data-alert="reorder" tabindex="0" role="button"><span>⚠ Below Reorder</span><b id="alert-reorder"><?php echo (int)$stock_alerts['below_reorder']; ?></b></div>
                  <div class="alert db-stock-alert" data-alert="out" tabindex="0" role="button"><span>⊘ Stock Out</span><b id="alert-out"><?php echo (int)$stock_alerts['out_of_stock']; ?></b></div>
                  <div class="alert db-stock-alert" data-alert="all" tabindex="0" role="button"><span>▦ All Counter Stock</span><b id="alert-all"><?php echo (int)$stock_alerts['all_items']; ?></b></div>
                </div>
              </div>
            </section>
          </div>
          <div class="db-card">
            <div class="db-card-head">
              <div class="db-card-title">
                <i class="fa fa-store"></i> Counter Health
              </div>
              <span id="db-counter-health-badge">
                <?php if ($unbalanced_counters > 0) { ?>
                <span class="db-notice-chip">
                  <span class="db-notice-dot"></span>
                  <?php echo $unbalanced_counters; ?> Unbalanced
                </span>
                <?php } else { ?>
                <span class="db-card-badge" style="background:#dcfce7;color:#166534;">All Balanced</span>
                  <?php } ?>
                </span>
              </div>
              <div class="db-card-body-np" style="overflow-x:auto;max-height:300px;">
                <table class="db-top-tbl db-tight-tbl">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Counter</th>
                      <th>Sales</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="db-counter-health-body">
                    <?php $no=0; foreach ($counter_table as $row) { $no++; $is_bad = $row['status'] !== 'BALANCED'; ?>
                    <tr <?php if ($is_bad) { ?> class="db-counter-row-alert"<?php } ?>>
                    <td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="<?php echo $row['id']; ?>"><?php echo $no; ?></a></td>
                    <td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></a></td>
                    <td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="<?php echo $row['id']; ?>"><?php echo dash_money($row['sales']); ?></a></td>
                    <td><a href="#" class="db-counter-health-link db-load-modal" data-url="../../front/cash/errview.php" data-id="<?php echo $row['id']; ?>"><span class="db-status-pill <?php echo $is_bad ? 'db-status-bad' : 'db-status-ok'; ?>"><?php echo $is_bad ? 'Unbalanced' : 'Balanced'; ?></span></a></td>
                  </tr><?php } ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="db-card"><div class="db-card-head"><div class="db-card-title"><i class="fa fa-chart-radar"></i> Top Items</div><span class="db-card-badge" style="background:#ede9fe;color:#6d28d9;">TODAY</span></div><div class="db-card-body"><?php if (empty($item_table)) { ?><div class="text-center text-muted py-3" style="font-size:.68rem;">No item movement yet.</div><?php } else { foreach ($item_table as $index => $row) { if ($index >= 5) break; ?><div class="db-alert-item"><div><div class="db-alert-title"><?php echo htmlspecialchars($row['name']); ?></div><div class="db-feed-detail">Qty <?php echo number_format((float)$row['qty'],2); ?></div></div><span class="db-alert-value db-alert-success"><?php echo dash_money($row['total']); ?></span></div><?php } } ?></div></div>
        </aside>
      </div>
    </div>
  </div>

  <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
</div>
<div id="sdToastWrap"></div>
<div id="modal" class="modal fade" tabindex="-1">
  <div class="modal-dialog" id="load"></div>
</div>

<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/plugins/select2/dist/js/select2.min.js"></script> 
<script src="../../assets/plugins/gritter/js/jquery.gritter.js"></script>
<script src="../../assets/js/apps.min.js"></script>

<script type="text/javascript">
  var adminDashClock = new Date(<?php echo (int)(time() * 1000); ?>);
  var adminDashState = {
    paymentModeRows: <?php echo json_encode($payment_mode_rows); ?>,
    counterRows: <?php echo json_encode($counter_rows); ?>,
    staffRows: <?php echo json_encode($staff_rows); ?>,
    reqCount: <?php echo (int)$counter_reqs_pending; ?>,
    paidTotal: <?php echo json_encode(dash_money($paid_total)); ?>,
    unbalancedCounters: <?php echo (int)$unbalanced_counters; ?>,
    stockAlerts: <?php echo json_encode($stock_alerts); ?>,
    printNotPrinted: 0,
    printFailed: 0,
    printRunning: false,
    liveBooted: false
  };

  $(document).ready(function() {
    App.init();
    startAdminDashClock();

    $(document).on('click','#ccash', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      $('#modal').modal('show');
      $.ajax({
        url: '../users/ccash.php',
        typr: 'POST',
        data: {id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    });

    $(document).on('click','#front-po-supplier-btn', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      $('#modal').modal('show');
      $.ajax({
        url: '../kugura/front_po_supplier.php',
        typr: 'POST',
        data: {id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    });

    $(document).on('click', '#db-print-refresh-btn', function(e){
      e.preventDefault();
      refreshPrintWorkerPanel();
    });

    $(document).on('click', '#db-print-queue-btn', function(e){
      e.preventDefault();
      loadDashModal('print_queue_modal.php');
    });

    startAdminDashLiveRefresh();
  });

  function formatAdminDashClock(dateObj) {
    var h = String(dateObj.getHours()).padStart(2, '0');
    var m = String(dateObj.getMinutes()).padStart(2, '0');
    var s = String(dateObj.getSeconds()).padStart(2, '0');
    return h + ':' + m + ':' + s;
  }

  function startAdminDashClock() {
    $('#db-live-clock').text(formatAdminDashClock(adminDashClock));
    setInterval(function() {
      adminDashClock = new Date(adminDashClock.getTime() + 1000);
      $('#db-live-clock').text(formatAdminDashClock(adminDashClock));
    }, 1000);
  }

  function sdToastDismiss(el) {
    if (!el || el.classList.contains('sd-toast-out')) return;
    el.classList.add('sd-toast-out');
    setTimeout(function() {
      if (el.parentElement) el.parentElement.removeChild(el);
    }, 300);
  }

  function sdToast(title, message, type, duration, icon) {
    type = type || 'info';
    duration = (duration === undefined || duration === null) ? 5000 : duration;
    var defaultIcons = {
      info: 'info',
      success: 'check_circle',
      warning: 'warning',
      error: 'error'
    };
    icon = icon || defaultIcons[type] || 'info';
    var wrap = document.getElementById('sdToastWrap');
    if (!wrap) return;

    var el = document.createElement('div');
    el.className = 'sd-toast sd-t-' + type;
    el.innerHTML =
      '<div class="sd-toast-icon"><span class="fa fa-circle"></span></div>' +
      '<div style="flex:1;min-width:0;padding-right:16px;">' +
        '<div style="font-size:12px;font-weight:700;color:#1F2937;line-height:1.3;">' + title + '</div>' +
        (message ? '<div style="font-size:11px;color:#6B7280;line-height:1.4;margin-top:2px;">' + message + '</div>' : '') +
      '</div>' +
      '<span class="sd-toast-close fa fa-times" onclick="sdToastDismiss(this.parentElement)"></span>' +
      (duration > 0 ? '<div class="sd-toast-bar" style="animation:sdBarShrink ' + duration + 'ms linear forwards;"></div>' : '');

    wrap.appendChild(el);
    if (duration > 0) {
      setTimeout(function() { sdToastDismiss(el); }, duration);
    }
    var all = wrap.querySelectorAll('.sd-toast:not(.sd-toast-out)');
    if (all.length > 5) sdToastDismiss(all[0]);
  }

  google.charts.load('current', {packages:['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawAdminDashCharts);

  function drawAdminDashCharts() {
    if (typeof google === 'undefined' || !google.visualization || !google.visualization.arrayToDataTable) {
      return;
    }
    drawTrendChart();
    drawPaymentModesChart();
    drawItemsChart();
    drawStaffChart();
  }

  function drawTrendChart() {
    if (typeof google === 'undefined' || !google.visualization || !google.visualization.arrayToDataTable) {
      return;
    }
    var data = google.visualization.arrayToDataTable(<?php echo json_encode($trend_rows); ?>);
    var options = {
      curveType: 'function',
      legend: { position: 'bottom' },
      chartArea: { width: '84%', height: '70%' },
      series: {
        0: { color: '#f59e0b' },
        1: { color: '#84cc16' },
        2: { color: '#2563eb' }
      },
      backgroundColor: 'transparent'
    };
    var chart = new google.visualization.ColumnChart(document.getElementById('weekly-chart'));
    chart.draw(data, options);
  }

  function drawPaymentModesChart() {
    if (typeof google === 'undefined' || !google.visualization || !google.visualization.arrayToDataTable) {
      return;
    }
    var container = document.getElementById('payment-modes-chart');
    if (!container) {
      return;
    }
    var rows = adminDashState.paymentModeRows;
    if (!rows || rows.length <= 1) {
      container.innerHTML = '<div class="text-center text-muted" style="padding-top:100px;font-size:.78rem;">No payments posted for this shift yet.</div>';
      return;
    }
    container.innerHTML = '';
    container.style.height = '240px';
    container.style.minHeight = '240px';
    container.style.maxHeight = '240px';
    var data = google.visualization.arrayToDataTable(rows);
    var options = {
      legend: { position: 'bottom' },
      chartArea: { width: '82%', height: '66%' },
      backgroundColor: 'transparent',
      pieSliceText: 'value',
      colors: ['#f59e0b', '#84cc16', '#16a34a', '#2563eb', '#38bdf8', '#65a30d', '#fb923c', '#22c55e']
    };
    var chart = new google.visualization.PieChart(container);
    chart.draw(data, options);
  }

  function drawItemsChart() {
    if (typeof google === 'undefined' || !google.visualization || !google.visualization.arrayToDataTable) {
      return;
    }
    if (!document.getElementById('donutchart')) {
      return;
    }
    var data = google.visualization.arrayToDataTable(<?php echo json_encode($item_pie_rows); ?>);
    var options = {
      title: 'Top Items By Sales Value',
      pieHole: 0.45,
      chartArea: { width: '90%', height: '80%' },
      backgroundColor: 'transparent',
      colors: ['#f59e0b', '#84cc16', '#16a34a', '#2563eb', '#38bdf8', '#65a30d', '#fb923c', '#22c55e', '#60a5fa', '#bef264']
    };
    var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
    chart.draw(data, options);
  }

  function drawStaffChart() {
    if (typeof google === 'undefined' || !google.visualization || !google.visualization.arrayToDataTable) {
      return;
    }
    if (!document.getElementById('staff-performance-chart')) {
      return;
    }
    var data = google.visualization.arrayToDataTable(adminDashState.staffRows);
    var options = {
      legend: { position: 'none' },
      chartArea: { width: '76%', height: '78%' },
      backgroundColor: 'transparent',
      colors: ['#2563eb'],
      bar: { groupWidth: '42%' }
    };
    var chart = new google.visualization.BarChart(document.getElementById('staff-performance-chart'));
    chart.draw(data, options);
  }

  function showDashGritter(title, text, className) {
    var type = 'info';
    if (className === 'db-gritter-success') type = 'success';
    if (className === 'db-gritter-warning') type = 'warning';
    if (className === 'db-gritter-danger') type = 'error';
    if (typeof sdToast === 'function') {
      sdToast(title, text, type, 5000);
      return;
    }
    if ($.gritter && $.gritter.add) {
      $.gritter.add({
        title: title,
        text: text,
        time: 5000,
        class_name: className || ''
      });
    }
  }

  function escapeAdminDashHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function refreshPrintWorkerPanel() {
    var statusReq = $.ajax({
      url: '../../gen/print_worker_api.php',
      type: 'GET',
      dataType: 'json',
      data: { action: 'status' },
      cache: false
    });
    var historyReq = $.ajax({
      url: '../../gen/print_worker_api.php',
      type: 'GET',
      dataType: 'json',
      data: { action: 'history' },
      cache: false
    });

    $.when(statusReq, historyReq).done(function(statusRes, historyRes) {
      var status = statusRes && statusRes[0] ? statusRes[0] : null;
      var history = historyRes && historyRes[0] ? historyRes[0] : null;
      if (!status || status.ok !== true) {
        return;
      }

      var pending = parseInt(status.pending || 0, 10);
      var processing = parseInt(status.processing || 0, 10);
      var failed = parseInt(status.failed || 0, 10);
      var notPrinted = parseInt(status.not_printed_total || (pending + processing), 10);
      var running = !!status.running;
      var heartbeatAge = parseInt(status.heartbeat_age_seconds, 10);
      var heartbeatLabel = isNaN(heartbeatAge) || heartbeatAge < 0 ? 'No heartbeat' : (heartbeatAge + 's ago');

      $('#db-print-metric-pending').text(pending);
      $('#db-print-metric-processing').text(processing);
      $('#db-print-metric-failed').text(failed);
      $('#db-print-metric-unprinted').text(notPrinted);
      $('#db-print-heartbeat-age').text(heartbeatLabel);

      if (running) {
        $('#db-print-running-pill').text('Worker Running').attr('style', 'background:#dcfce7;color:#166534;');
      } else {
        $('#db-print-running-pill').text('Worker Offline').attr('style', 'background:#fee2e2;color:#b91c1c;');
      }

      var jobs = (history && history.ok === true && Array.isArray(history.jobs)) ? history.jobs : [];
      if (!jobs.length) {
        $('#db-print-jobs-body').html('<tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.68rem;">No print jobs yet.</td></tr>');
      } else {
        var rows = [];
        for (var i = 0; i < jobs.length && i < 12; i++) {
          var job = jobs[i] || {};
          var tone = String(job.row_tone || 'warn');
          var statusClass = 'db-alert-warning-soft';
          if (tone === 'done') statusClass = 'db-alert-success-soft';
          if (tone === 'danger') statusClass = 'db-alert-danger-soft';
          if (tone === 'info') statusClass = 'db-alert-info-soft';
          rows.push(
            '<tr>' +
              '<td>' + escapeAdminDashHtml(job.queue_id || '-') + '</td>' +
              '<td>' + escapeAdminDashHtml(job.sale_id || '-') + '</td>' +
              '<td>' + escapeAdminDashHtml(job.printer_display || '-') + '</td>' +
              '<td><span class="db-card-badge ' + statusClass + '">' + escapeAdminDashHtml(job.status_label || '-') + '</span></td>' +
            '</tr>'
          );
        }
        $('#db-print-jobs-body').html(rows.join(''));
      }

      if (failed > adminDashState.printFailed) {
        showDashGritter('Print Queue Alert', 'A print job failed. Check printer setup or worker logs.', 'db-gritter-danger');
      }
      if (adminDashState.printRunning && !running) {
        showDashGritter('Print Worker Offline', 'Print worker stopped. New printouts will remain in queue.', 'db-gritter-warning');
      }

      adminDashState.printNotPrinted = notPrinted;
      adminDashState.printFailed = failed;
      adminDashState.printRunning = running;
    }).fail(function() {
      $('#db-print-running-pill').text('Status Unavailable').attr('style', 'background:#fef3c7;color:#92400e;');
      $('#db-print-heartbeat-age').text('Unavailable');
      $('#db-print-jobs-body').html('<tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.68rem;">Could not load printer status.</td></tr>');
    });
  }

  function startAdminDashLiveRefresh() {
    refreshAdminDashLive();
    refreshPrintWorkerPanel();
    setInterval(function() {
      refreshAdminDashLive();
      refreshPrintWorkerPanel();
    }, 15000);
  }

  function loadDashModal(url, data) {
    $('#load').removeClass('db-stock-dialog');
    $('#modal').modal('show');
    $.ajax({
      url: url,
      type: 'POST',
      data: data || {},
      dataType: 'html'
    }).done(function(res) {
      $('#load').html(res);
    }).fail(function() {
      $('#load').html('<div class="alert alert-danger">Failed to load details.</div>');
    });
  }

  var adminStockModal = { counter: 'all', kind: 'out' };

  function dashEscapeHtml(value) {
    return String(value === null || typeof value === 'undefined' ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function dashStockItems(counter, kind) {
    var items = counter.items || [];
    if (kind === 'out') return items.filter(function(item) { return item.status === 'out'; });
    if (kind === 'reorder') return items.filter(function(item) { return item.status === 'reorder'; });
    return items;
  }

  function renderDashStockModal() {
    var stock = adminDashState.stockAlerts || { counters: [] };
    var counters = stock.counters || [];
    var selected = adminStockModal.counter === 'all' ? null : counters.find(function(counter) { return String(counter.id) === String(adminStockModal.counter); });
    var visibleCounters = selected ? [selected] : counters;
    var counterTabs = '<div class="db-stock-section-label">Choose counter</div><div class="db-stock-counter-tabs"><button type="button" class="db-stock-counter-tab ' + (adminStockModal.counter === 'all' ? 'active' : '') + '" data-stock-counter="all">All counters <b>' + (stock.all_items || 0) + '</b></button>';
    counters.forEach(function(counter) {
      counterTabs += '<button type="button" class="db-stock-counter-tab ' + (String(adminStockModal.counter) === String(counter.id) ? 'active' : '') + '" data-stock-counter="' + counter.id + '">' + dashEscapeHtml(counter.name) + ' <b>' + ((counter.out_of_stock || 0) + (counter.below_reorder || 0)) + '</b></button>';
    });
    counterTabs += '</div>';
    var statusTabs = '<div class="db-stock-section-label">Stock view</div><div class="db-stock-status-tabs">';
    [['out', 'Out of stock'], ['reorder', 'Below reorder'], ['all', 'All items']].forEach(function(tab) {
      var count = selected ? dashStockItems(selected, tab[0]).length : visibleCounters.reduce(function(total, counter) { return total + dashStockItems(counter, tab[0]).length; }, 0);
      statusTabs += '<button type="button" class="db-stock-status-tab ' + (adminStockModal.kind === tab[0] ? 'active' : '') + '" data-stock-kind="' + tab[0] + '">' + tab[1] + ' <b>' + count + '</b></button>';
    });
    statusTabs += '</div>';
    var rows = [];
    visibleCounters.forEach(function(counter) {
      dashStockItems(counter, adminStockModal.kind).forEach(function(item) {
        rows.push('<tr><td><strong>' + dashEscapeHtml(item.name) + '</strong><div style="font-size:.58rem;color:#94a3b8;">' + dashEscapeHtml(counter.name) + '</div></td><td>' + Number(item.current || 0).toLocaleString() + '</td><td>' + Number(item.reorder_level || 0).toLocaleString() + '</td></tr>');
      });
    });
    var body = rows.length ? '<div class="db-stock-table-shell"><table class="db-stock-table"><thead><tr><th>Item / Counter</th><th>Current</th><th>Reorder</th></tr></thead><tbody>' + rows.join('') + '</tbody></table></div>' : '<div class="db-stock-modal-empty"><i class="fa fa-check-circle" style="display:block;margin-bottom:.45rem;font-size:1.3rem;color:#10b981;"></i>No matching stock items.</div>';
    $('#load').html('<div class="modal-content db-stock-modal-wrap"><div class="modal-header db-stock-modal-head"><div><div class="db-stock-modal-kicker">Inventory control</div><h4 class="db-stock-modal-title">Counter stock overview</h4><div class="db-stock-modal-subtitle">Review stock levels and reorder attention by counter.</div></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body db-stock-modal">' + counterTabs + statusTabs + body + '</div></div>');
  }

  function openDashStockAlerts(kind) {
    adminStockModal.kind = kind || 'out';
    adminStockModal.counter = 'all';
    $('#load').addClass('db-stock-dialog');
    $('#modal').modal('show');
    renderDashStockModal();
  }

  $(document).on('click', '.db-stock-alert', function() {
    openDashStockAlerts($(this).data('alert'));
  });
  $(document).on('click', '[data-stock-counter]', function() {
    adminStockModal.counter = $(this).data('stock-counter');
    renderDashStockModal();
  });
  $(document).on('click', '[data-stock-kind]', function() {
    adminStockModal.kind = $(this).data('stock-kind');
    renderDashStockModal();
  });

  function refreshAdminDashLive() {
    $.ajax({
      url: 'admin_dash_live.php',
      type: 'GET',
      dataType: 'json',
      cache: false
    }).done(function(data) {
      if (!data) {
        return;
      }

      var previousReqCount = parseInt(adminDashState.reqCount || 0, 10);
      var previousPaidTotal = parseFloat(String(adminDashState.paidTotal || '0').replace(/,/g, '')) || 0;
      var previousUnbalanced = parseInt(adminDashState.unbalancedCounters || 0, 10);
      var newPaidTotal = parseFloat(String(data.totals.paid_total || '0').replace(/,/g, '')) || 0;

      adminDashState.paymentModeRows = data.charts.payment_mode_rows || adminDashState.paymentModeRows;
      adminDashState.counterRows = data.charts.counter_rows || adminDashState.counterRows;
      adminDashState.staffRows = data.charts.staff_rows || adminDashState.staffRows;
      adminDashState.reqCount = data.counts.counter_reqs_pending;
      adminDashState.paidTotal = data.totals.paid_total;
      adminDashState.unbalancedCounters = data.counts.unbalanced_counters;
      adminDashState.stockAlerts = data.stock_alerts || adminDashState.stockAlerts;

      var stockOut = parseInt((adminDashState.stockAlerts || {}).out_of_stock || 0, 10);
      var stockReorder = parseInt((adminDashState.stockAlerts || {}).below_reorder || 0, 10);
      var stockAll = parseInt((adminDashState.stockAlerts || {}).all_items || 0, 10);
      $('#alert-out').text(stockOut);
      $('#alert-reorder').text(stockReorder);
      $('#alert-all').text(stockAll);
      $('#alerts-empty').toggle(stockOut + stockReorder === 0);

      $('#db-hero-counters').html(data.html.hero_counters);
      $('#db-activity-feed').html(data.html.activity);
      $('#db-staff-sales-table').html(data.html.staff_sales);
      $('#db-counter-health-body').html(data.html.counter_health);
      $('#db-counter-health-badge').html(data.html.counter_health_badge);
      $('#db-req-chip').html(data.html.req_chip);

      $('#db-kpi-total-sales').text(data.totals.orders_total);
      $('#db-kpi-sales-orders').text(data.totals.orders_count);
      $('#db-kpi-avg-order').text(data.totals.avg_per_order);
      $('#db-kpi-credit-today').text(data.totals.credit_today);
      $('#db-kpi-credit-collected').text(data.totals.credit_collected);
      $('#db-kpi-expenses').text(data.totals.expenses_total);
      $('#db-kpi-expense-count').text(data.totals.expense_count);
      $('#db-kpi-unpaid').text(data.totals.unpaid_total);
      $('#db-kpi-unpaid-count').text(data.totals.unpaid_count);
      $('#db-kpi-net').text(data.totals.net_after_expenses);
      $('#db-kpi-paid').text(data.totals.paid_total);
      $('#db-kpi-month-tokens').text(data.totals.monthly_tokens_total);
      $('#db-kpi-today-tokens').text(data.totals.today_tokens_total);
      $('#db-orders-badge').text(data.totals.orders_total);
      $('#db-kpi-compare-pct').text(data.totals.sales_compare_pct + '%')
        .removeClass('up down')
        .addClass(parseFloat(data.totals.sales_compare_pct) >= 0 ? 'up' : 'down');

      $('#db-open-counters-count').text(data.counts.open_counters_total);
      $('#db-active-cashiers-count').text(data.counts.active_cashiers_total);
      $('#db-credit-customers-count').text(data.counts.credit_customers);
      $('#db-req-count')
        .text(data.counts.counter_reqs_pending)
        .removeClass('db-alert-warning db-alert-success')
        .addClass(parseInt(data.counts.counter_reqs_pending, 10) > 0 ? 'db-alert-warning' : 'db-alert-success');

      if (typeof google !== 'undefined' && google.visualization && google.visualization.arrayToDataTable) {
        drawPaymentModesChart();
        drawStaffChart();
      }

      if (parseInt(data.counts.counter_reqs_pending, 10) > previousReqCount) {
        showDashGritter('Requisition Alert', 'There is a new active requisition waiting in inventory.', 'db-gritter-warning');
      }
      if (newPaidTotal > previousPaidTotal) {
        showDashGritter('Payment Posted', 'A new payment has been received on the current shift.', 'db-gritter-success');
      }
      if (parseInt(data.counts.unbalanced_counters, 10) > previousUnbalanced) {
        showDashGritter('Counter Health Alert', 'A counter has become unbalanced.', 'db-gritter-danger');
      }
      if (!adminDashState.liveBooted) {
        if (parseInt(data.counts.counter_reqs_pending, 10) > 0) {
          showDashGritter('Requisition Alert', data.counts.counter_reqs_pending + ' requisition(s) are active in inventory.', 'db-gritter-warning');
        }
        if (newPaidTotal > 0) {
          showDashGritter('Payments Live', 'Shift payments are being tracked live on this dashboard.', 'db-gritter-success');
        }
        if (parseInt(data.counts.unbalanced_counters, 10) > 0) {
          showDashGritter('Counter Health Alert', data.counts.unbalanced_counters + ' counter(s) are currently unbalanced.', 'db-gritter-danger');
        }
        adminDashState.liveBooted = true;
      }
    });
  }

  $(document).on('click', '.db-load-modal', function(e) {
    e.preventDefault();
    loadDashModal($(this).data('url'), { id: $(this).data('id') });
  });

  $(window).on('resize', function(){
    if (typeof google !== 'undefined' && google.visualization) {
      drawAdminDashCharts();
    }
  });
</script>
</body>
</html>
