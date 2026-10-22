<?php

if (!function_exists('cash_api_capture_include')) {
    function cash_api_capture_include($relative_file) {
        global $connection;
        ob_start();
        include $relative_file;
        return ob_get_clean();
    }
}

if (!function_exists('cash_api_capture_json_include')) {
    function cash_api_capture_json_include($relative_file) {
        global $connection;
        ob_start();
        include $relative_file;
        $raw = trim(ob_get_clean());
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            'ok' => false,
            'message' => $raw === '' ? 'Empty response.' : $raw,
            'status' => 500,
            'raw' => $raw,
        ];
    }
}

if (!function_exists('cash_api_live')) {
    function cash_api_live($connection, $user_id) {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }

        return [
            'ok' => true,
            'message' => 'Cash live summary loaded.',
            'status' => 200,
            'summary' => cash_api_live_summary($connection),
        ];
    }
}

if (!function_exists('cash_api_panel_map')) {
    function cash_api_panel_map() {
        return [
            // Mode 1: cashier clears selected bills one bill or batch at a time.
            'clear_bill_mode' => '__service__:clear_bill_mode',
            'cashbox' => '__service__:cashbox',
            'kpesa' => '__service__:kpesa',
            'merge' => dirname(__DIR__) . '/merge/index.php',
            'clearb' => '__service__:clearb',
            'clearp' => '__service__:clearp',
            // Mode 2: cashier clears a waiter after reviewing the full waiter summary.
            'clear_waiter_mode' => '__service__:clear_waiter_mode',
            'clearsta' => '__service__:clearsta',
        ];
    }
}

if (!function_exists('cash_api_modal_map')) {
    function cash_api_modal_map() {
        return [
            'pay_modal' => dirname(__DIR__) . '/pay/pay_modal.php',
            'credit_assign_modal' => dirname(__DIR__) . '/CUSTCRED/credit_assign_modal.php',
            'max_sale_modal' => dirname(__DIR__) . '/clrbill/max_sale_modal.php',
            'staff_clear_modal' => dirname(__DIR__) . '/clrstaff/clear_staff_modal.php',
            'credit_snapshot_modal' => dirname(__DIR__) . '/CUSTCRED/credit_snapshot_modal.php',
            'expenses_panel_modal' => dirname(__DIR__) . '/exp/expenses_panel_modal.php',
            'cashier_summary_modal' => dirname(__DIR__) . '/report/cashier_summary_modal.php',
            'cashbox_summary_modal' => dirname(__DIR__) . '/report/cashbox_summary_modal.php',
            'paid_summary_modal' => dirname(__DIR__) . '/pay/paid_summary_modal.php',
            'handover_modal' => dirname(__DIR__) . '/shifthand/handover_modal.php',
            'expense_modal' => dirname(__DIR__) . '/exp/expense_modal.php',
            'expense_delete_modal' => dirname(__DIR__) . '/exp/expense_delete_modal.php',
            'customer_credit_modal' => dirname(__DIR__) . '/CUSTCRED/customer_credit_modal.php',
            'staff_print_summary_modal' => dirname(__DIR__) . '/clrstaff/print_summary_modal.php',
            'collect_modal' => dirname(__DIR__) . '/colle_modal.php',
            'error_view' => dirname(__DIR__) . '/errview.php',
            'returns_approve' => dirname(__DIR__) . '/../claims/approve_modal.php',
            'shift_sum' => dirname(__DIR__) . '/../chaba/shift_sum.php',
            'split_modal' => dirname(__DIR__) . '/../split/split_modal.php',
            'bulk_form' => dirname(__DIR__) . '/clrbill/bulk_form.php',
        ];
    }
}

if (!function_exists('cash_api_load_current_bill_rows')) {
    function cash_api_load_current_bill_rows() {
        global $connection;

        $db = isset($connection) ? $connection : null;
        $st = isset($_GET['st']) ? cash_api_clean_text($_GET['st']) : '2';
        $shift = get_column('kp_shift', 'shift', "1");
        $user_id = isset($_GET['id']) ? cash_api_clean_int($_GET['id']) : 0;
        $user_name = $user_id > 0 ? get_user($user_id) : 'All Staff';
        $max_sale = 0.0;
        $current_sale = 0.0;
        $usage_percent = 0.0;
        $usage_percent_label = '0%';
        $usage_class = 'cash-max-progress-green';

        if ($user_id > 0) {
            $column_check = mysqli_query($db, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
            if ($column_check && mysqli_num_rows($column_check) > 0) {
                $max_sale = (float)get_column('kp_users', 'max_sale', "user_id='$user_id'");
            }
            $current_sale = (float)column_total('kp_sales', 'amount', "shift='$shift' AND pay_status=2 AND user_id='$user_id'");
            if ($max_sale > 0) {
                $usage_percent = min(($current_sale / $max_sale) * 100, 100);
            } elseif ($current_sale > 0) {
                $usage_percent = 100;
            }
            $usage_percent_label = number_format($usage_percent, 1) . '%';
            if ($usage_percent > 90) {
                $usage_class = 'cash-max-progress-red';
            } elseif ($usage_percent >= 70) {
                $usage_class = 'cash-max-progress-orange';
            }
        }

        $rows = ((int)$st === 1 && $user_id > 0)
            ? cash_repo_current_unpaid_bills($connection, $shift, $user_id)
            : cash_repo_current_unpaid_bills($connection, $shift, 0);
        $title = ((int)$st === 1 && $user_id > 0) ? $user_name . " TODAY'S UNPAID BILLS" : "ALL STAFF TODAYS UNPAID BILLS";

        if (count($rows) < 1) {
            return [
                'ok' => true,
                'message' => 'Current bill rows loaded.',
                'status' => 200,
                'html' => 'There are no staff sales in the system at the moment',
            ];
        }

        ob_start();
        ?>
  <style type="text/css">
    .cash-staff-sales-summary { margin:12px; padding:14px 16px; border:1px solid #eee2d6; border-radius:16px; background:linear-gradient(135deg,#fffaf3 0%,#ffffff 100%); box-shadow:0 8px 18px rgba(77, 48, 22, 0.06); }
    .cash-staff-sales-summary-head { display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:12px; flex-wrap:wrap; }
    .cash-staff-sales-summary-head h4 { margin:0 0 3px; color:#5b4330; font-size:16px; font-weight:900; }
    .cash-staff-sales-summary-head p { margin:0; color:#8c7b68; font-size:12px; }
    .cash-staff-sales-summary .btn { border-radius:10px; font-weight:800; }
    .cash-staff-sales-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-bottom:12px; }
    .cash-staff-sales-stat { border:1px solid #efe4d7; border-radius:14px; background:#fff; padding:12px; }
    .cash-staff-sales-stat strong,.cash-staff-sales-stat span { display:block; }
    .cash-staff-sales-stat strong { color:#977042; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
    .cash-staff-sales-stat span { margin-top:5px; color:#2e2216; font-size:21px; font-weight:900; line-height:1.1; }
    .cash-max-progress-wrap { border:1px solid #efe4d7; border-radius:14px; background:#fff; padding:12px; }
    .cash-max-progress-meta { display:flex; justify-content:space-between; gap:10px; align-items:center; margin-bottom:8px; color:#7d6a58; font-size:12px; font-weight:700; flex-wrap:wrap; }
    .cash-max-progress-track { width:100%; height:12px; border-radius:999px; background:#f1e6d8; overflow:hidden; }
    .cash-max-progress-bar { height:100%; border-radius:999px; transition:width .2s ease; }
    .cash-max-progress-green { background:linear-gradient(90deg,#1f9d55 0%,#36c275 100%); }
    .cash-max-progress-orange { background:linear-gradient(90deg,#de8a13 0%,#f0a53a 100%); }
    .cash-max-progress-red { background:linear-gradient(90deg,#d53032 0%,#f05a5a 100%); }
    @media (max-width: 767px) { .cash-staff-sales-stats { grid-template-columns:1fr; } }
  </style>
  <table class="table table-striped">
    <thead>
      <tr><th id="user" data-id='<?php echo $user_id; ?>' data-id2='1' colspan="6" style="font-size: 20px;" class="text-center"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></th></tr>
      <tr><th class="text-left"><h4>SELECT</h4></th><th><h4>ORDER NO:</h4></th><th><h4>SHIFT</h4></th><th><h4>STAFF</h4></th><th><h4>COUNTER</h4></th><th><h4>AMOUNT</h4></th></tr>
    </thead>
    <?php if ((int)$st === 1 && $user_id > 0) { ?>
      <tbody><tr><td colspan="6" style="padding:0;">
        <div class="cash-staff-sales-summary">
          <div class="cash-staff-sales-summary-head">
            <div><h4><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?> Sale Cap</h4><p>Review the current sale against the user max, then open the modal to increase the cap.</p></div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="cashOpenMaxSaleModal(<?php echo $user_id; ?>, '<?php echo htmlspecialchars(addslashes($user_name), ENT_QUOTES); ?>')">Increase Max Sale</button>
          </div>
          <div class="cash-staff-sales-stats">
            <div class="cash-staff-sales-stat"><strong>Current Sale</strong><span><?php echo number_format($current_sale, 2); ?></span></div>
            <div class="cash-staff-sales-stat"><strong>Maximum Sale</strong><span><?php echo number_format($max_sale, 2); ?></span></div>
            <div class="cash-staff-sales-stat"><strong>Cap Used</strong><span><?php echo $usage_percent_label; ?></span></div>
          </div>
          <div class="cash-max-progress-wrap">
            <div class="cash-max-progress-meta">
              <span><?php echo number_format($current_sale, 2); ?> of <?php echo number_format($max_sale, 2); ?> used</span>
              <span><?php echo $usage_percent > 90 ? 'Danger Zone' : ($usage_percent >= 70 ? 'Warning Zone' : 'Safe Zone'); ?></span>
            </div>
            <div class="cash-max-progress-track"><div class="cash-max-progress-bar <?php echo $usage_class; ?>" style="width: <?php echo $usage_percent; ?>%;"></div></div>
          </div>
        </div>
      </td></tr></tbody>
    <?php } ?>
    <tbody>
      <?php foreach ($rows as $row) { $sale_user_id = (int)$row['user_id']; $sale_id = (int)$row['sale_id']; $sale_shift = (string)$row['shift']; $totals = $row['amount']; $counter = (int)$row['counter']; ?>
        <tr style="font-size:16px; font-weight:bolder;">
          <td class="text-left"><input type="checkbox" name="pay-sale[]" onchange="bulk_pay()" value="<?php echo $sale_id; ?>" class="bulk cash-bill-pick" data-amount="<?php echo (float)$totals; ?>" /></td>
          <td><?php echo $sale_id; ?></td><td><?php echo htmlspecialchars($sale_shift, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars(get_user($sale_user_id), ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars(get_column('kp_counters', 'name', "cntr_id='$counter'"), ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo $totals; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <script type="text/javascript">
    function cashOpenMaxSaleModal(userId, userName) {
      window.cashReloadMaxSaleContext = function() { if (typeof cashLoadCurrentBills === 'function') { cashLoadCurrentBills(userId); } };
      $('#modal').modal('show');
      $.ajax({ url: 'API/cash_api.php', type: 'GET', data: { action: 'modal', modal: 'max_sale_modal', user_id: userId }, dataType: 'html' })
      .done(function(data){ $('#load').html(data); })
      .fail(function(){ $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load max sale for ' + userName + '.</div></div></div></div>'); });
    }
  </script>
        <?php
        return [
            'ok' => true,
            'message' => 'Current bill rows loaded.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_load_previous_bill_rows')) {
    function cash_api_load_previous_bill_rows() {
        global $connection;

        $shift = get_column('kp_shift', 'shift', "1");
        $st = isset($_REQUEST['st']) ? cash_api_clean_int($_REQUEST['st']) : 2;
        $user_id = isset($_REQUEST['id']) ? cash_api_clean_int($_REQUEST['id']) : 0;
        $rows = ($st === 1 && $user_id > 0)
            ? cash_repo_previous_unpaid_bills($connection, $shift, $user_id)
            : cash_repo_previous_unpaid_bills($connection, $shift, 0);
        $title = ($st === 1 && $user_id > 0) ? get_user($user_id) . " PREVIOUS UNPAID BILLS" : "ALL STAFF PREVIOUS UNPAID BILLS";

        ob_start();
        ?>
<table class="table table-striped">
  <thead>
    <tr><th id="user" data-id='<?php echo $user_id; ?>' data-id2='2' colspan="6" style="font-size: 20px;" class="text-center"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></th></tr>
    <tr><th class="text-left"><h4>SELECT</h4></th><th><h4>ORDER NO:</h4></th><th><h4>SHIFT</h4></th><th><h4>STAFF</h4></th><th><h4>COUNTER</h4></th><th><h4>AMOUNT</h4></th></tr>
  </thead>
  <tbody>
    <?php if (!empty($rows)) { foreach ($rows as $row) { $sale_user_id = (int)$row['user_id']; $sale_id = (int)$row['sale_id']; $sale_shift = (string)$row['shift']; $totals = isset($row['amount']) ? $row['amount'] : (isset($row['total_amount']) ? $row['total_amount'] : 0); $counter = (int)$row['counter']; ?>
      <tr style="font-size:16px; font-weight:bolder;">
        <td class="text-left"><input type="checkbox" name="pay-sale[]" onchange="bulk_pay()" value="<?php echo $sale_id; ?>" class="bulk cash-bill-pick" data-amount="<?php echo (float)$totals; ?>" /></td>
        <td><?php echo $sale_id; ?></td><td><?php echo htmlspecialchars($sale_shift, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars(get_user($sale_user_id), ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars(get_column('kp_counters', 'name', "cntr_id='$counter'"), ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo $totals; ?></td>
      </tr>
    <?php }} else { ?>
      <tr><td colspan="6" class="text-center" style="padding:20px;">There are no previous unpaid bills in the system at the moment</td></tr>
    <?php } ?>
  </tbody>
</table>
        <?php
        return [
            'ok' => true,
            'message' => 'Previous bill rows loaded.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_save_max_sale')) {
    function cash_api_save_max_sale($connection, $user_id, $max_sale) {
        $user_id = (int)$user_id;
        $max_sale = trim((string)$max_sale);

        if ($user_id <= 0 || $max_sale === '') {
            return ['ok' => false, 'message' => 'Invalid request.', 'status' => 400];
        }
        if (!is_numeric($max_sale) || (float)$max_sale < 0) {
            return ['ok' => false, 'message' => 'Max sale must be a valid non-negative number.', 'status' => 400];
        }

        $column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
        if (!$column_check) {
            return ['ok' => false, 'message' => 'Database error.', 'status' => 500];
        }
        if (mysqli_num_rows($column_check) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_users ADD COLUMN max_sale DECIMAL(12,2) NOT NULL DEFAULT 0");
        }

        $amount = number_format((float)$max_sale, 2, '.', '');
        $update = mysqli_query($connection, "UPDATE kp_users SET max_sale = '$amount' WHERE user_id = '$user_id'");
        if (!$update) {
            return ['ok' => false, 'message' => 'Unable to update max sale.', 'status' => 500];
        }

        return [
            'ok' => true,
            'message' => 'Max sale increased to KES ' . number_format((float)$amount, 2) . '.',
            'status' => 200,
        ];
    }
}

if (!function_exists('cash_api_load_staff_clearance_summary')) {
    function cash_api_load_staff_clearance_summary() {
        global $connection;

        $user_id = (int)($_GET['id'] ?? 0);
        $shift = get_column('kp_shift', 'shift', "1");

        if ($user_id < 1) {
            return [
                'ok' => true,
                'message' => 'No waiter selected.',
                'status' => 200,
                'html' => '<div class="alert alert-warning">Select a waiter to view the shift summary.</div>',
            ];
        }

        if ($shift == 0) {
            $shift = date('Y-m-d');
        }

        $staff_name = get_user($user_id);
        $max_sale = 0;
        $column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
        if ($column_check && mysqli_num_rows($column_check) > 0) {
            $max_sale = (float)get_column('kp_users', 'max_sale', "user_id='$user_id'");
        }
        $total_sales = (float)column_total("kp_sales", "amount", "user_id='$user_id' AND shift='$shift'");
        $paid_sales = (float)column_total("kp_sales", "amount", "user_id='$user_id' AND shift='$shift' AND pay_status=1");
        $unpaid_sales = (float)column_total("kp_sales", "amount", "user_id='$user_id' AND shift='$shift' AND pay_status=2");
        $credit_sales = (float)column_total("kp_sales", "amount", "user_id='$user_id' AND shift='$shift' AND pay_status=3");
        $bill_count = (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift'");
        $return_count = (int)column_count("kp_ret_items", "id", "user_id='$user_id' AND shift='$shift'");
        $deleted_count = (int)column_count("kp_del_orders", "id", "user_id='$user_id' AND shift='$shift'");
        $item_qty = (float)column_total("kp_sale_items", "qty", "user_id='$user_id' AND shift='$shift'");
        $avg_bill = $bill_count > 0 ? $total_sales / $bill_count : 0;
        $shift_tokens = function_exists('get_user_shift_token_total') ? (float)get_user_shift_token_total($user_id, $shift) : 0;
        $lifetime_tokens = function_exists('get_user_token_total') ? (float)get_user_token_total($user_id) : 0;
        $clearance_row = cash_repo_find_staff_clearance($connection, $user_id, $shift);
        $is_cleared = $clearance_row ? true : false;
        $cleared_by_name = $is_cleared && !empty($clearance_row['cleared_by']) ? get_user((int)$clearance_row['cleared_by']) : '';
        $cleared_at = $is_cleared ? (string)($clearance_row['created_at'] ?? '') : '';
        $cleared_variance = $is_cleared ? (float)($clearance_row['variance_amount'] ?? 0) : 0;
        $cleared_variance_type = $is_cleared ? (string)($clearance_row['variance_type'] ?? '') : '';
        $bill_count_scope = count(cash_repo_previous_unpaid_bills($connection, $shift, $user_id));

        ob_start();
        ?>
<style type="text/css">
  .cash-staff-summary-shell { border:1px solid #eadfce; border-radius:18px; background:#fffdf9; overflow:hidden; }
  .cash-staff-summary-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; padding:16px 18px; border-bottom:1px solid #efe2d3; background:linear-gradient(135deg,#fff6ea 0%,#fffdfa 100%); }
  .cash-staff-summary-head h3 { margin:0 0 4px; color:#77461b; font-weight:900; }
  .cash-staff-summary-head p { margin:0; color:#7f6a55; }
  .cash-staff-scope { display:flex; gap:8px; flex-wrap:wrap; }
  .cash-staff-scope button { border:1px solid #d8c1a1; background:#fff; color:#82531d; border-radius:999px; padding:9px 14px; font-weight:800; font-size:12px; }
  .cash-staff-scope button.is-active { background:#f0a53a; border-color:#f0a53a; color:#fff; }
  .cash-staff-summary-body { padding:16px 18px 18px; }
  .cash-staff-status { margin-bottom:14px; border:1px solid #cfe7d3; border-radius:14px; background:#f2fbf3; padding:12px 14px; color:#2f6840; }
  .cash-staff-status strong { display:block; font-size:14px; text-transform:uppercase; letter-spacing:.05em; }
  .cash-staff-status span { display:block; margin-top:4px; }
  .cash-staff-summary-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:16px; align-items:start; }
  .cash-staff-max-sale { margin-bottom:14px; border:1px solid #eee2d6; border-radius:16px; background:#fff; overflow:hidden; }
  .cash-staff-max-sale-head { display:flex; justify-content:space-between; gap:12px; align-items:center; padding:12px 14px; background:#fffaf3; border-bottom:1px solid #f2e7db; }
  .cash-staff-max-sale-head h4 { margin:0 0 3px; color:#5b4330; font-size:15px; font-weight:900; }
  .cash-staff-max-sale-head p { margin:0; color:#8c7b68; font-size:12px; }
  .cash-staff-max-sale-body { padding:14px; }
  .cash-staff-max-sale-value { display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:12px; color:#5b4330; font-weight:800; }
  .cash-staff-max-sale-value strong { display:block; color:#99652f; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
  .cash-staff-max-sale-value span { display:block; margin-top:4px; color:#2d241a; font-size:22px; font-weight:900; }
  .cash-staff-max-sale-form { display:none; margin-top:12px; padding-top:12px; border-top:1px solid #f2e7db; }
  .cash-staff-max-sale-form.is-open { display:block; }
  .cash-staff-max-sale-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
  .cash-staff-max-sale-actions .btn,.cash-staff-max-sale-head .btn { border-radius:10px; font-weight:800; }
  .cash-staff-inline-result { margin-top:10px; }
  .cash-staff-metrics { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; margin-bottom:14px; }
  .cash-staff-metric { border:1px solid #eee2d6; border-radius:14px; padding:12px; background:#fff; }
  .cash-staff-metric strong,.cash-staff-metric span { display:block; }
  .cash-staff-metric strong { color:#99652f; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
  .cash-staff-metric span { color:#2d241a; font-size:21px; font-weight:900; margin-top:6px; line-height:1.1; }
  .cash-staff-summary-card,.cash-staff-bills-card { border:1px solid #eee2d6; border-radius:16px; background:#fff; overflow:hidden; }
  .cash-staff-card-head { padding:12px 14px; border-bottom:1px solid #f2e7db; background:#fffaf3; }
  .cash-staff-card-head h4 { margin:0 0 3px; color:#5b4330; font-size:15px; font-weight:900; }
  .cash-staff-card-head p { margin:0; color:#8c7b68; font-size:12px; }
  .cash-staff-summary-card .table,.cash-staff-bills-card .table { margin:0; }
  .cash-staff-bills-wrap { max-height:560px; overflow:auto; }
  .cash-staff-bills-wrap td,.cash-staff-bills-wrap th { white-space:nowrap; }
  .cash-staff-empty { padding:20px 16px; color:#8b7863; }
  .cash-staff-foot { margin-top:14px; display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
  .cash-staff-foot .btn { border-radius:12px; font-weight:800; padding:10px 16px; }
  @media (max-width:1199px) { .cash-staff-metrics { grid-template-columns:repeat(3,minmax(0,1fr)); } }
  @media (max-width:991px) { .cash-staff-summary-grid { grid-template-columns:1fr; } .cash-staff-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); } }
  @media (max-width:767px) { .cash-staff-summary-head { flex-direction:column; } .cash-staff-metrics { grid-template-columns:1fr; } .cash-staff-foot .btn { width:100%; } }
</style>
<div class="cash-staff-summary-shell">
  <div class="cash-staff-summary-head">
    <div><h3><?php echo htmlspecialchars($staff_name, ENT_QUOTES, 'UTF-8'); ?> Shift Summary</h3><p>Shift: <?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?></p></div>
    <div class="cash-staff-scope"><button type="button" class="is-active" disabled>Previous Bills Only</button></div>
  </div>
  <div class="cash-staff-summary-body">
    <?php if ($is_cleared) { ?>
      <div class="cash-staff-status"><strong>Already Cleared</strong><span>Cleared by <?php echo htmlspecialchars($cleared_by_name ?: 'System', ENT_QUOTES, 'UTF-8'); ?> on <?php echo htmlspecialchars($cleared_at, ENT_QUOTES, 'UTF-8'); ?>.</span><span>Variance: <?php echo number_format($cleared_variance, 2); ?><?php echo $cleared_variance_type !== '' ? ' (' . strtoupper($cleared_variance_type) . ')' : ''; ?></span></div>
    <?php } ?>
    <div class="cash-staff-summary-grid">
      <div>
        <div class="cash-staff-max-sale">
          <div class="cash-staff-max-sale-head">
            <div><h4>Max Sale Capping</h4><p>Adjust this waiter&apos;s maximum allowed sale without leaving the summary panel.</p></div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="cashToggleStaffMaxSaleForm()">Increase Max Sale</button>
          </div>
          <div class="cash-staff-max-sale-body">
            <div class="cash-staff-max-sale-value"><div><strong>Current Maximum Sale</strong><span id="cash-staff-max-sale-display"><?php echo number_format($max_sale, 2); ?></span></div></div>
            <div id="cash-staff-max-sale-form" class="cash-staff-max-sale-form">
              <div class="form-group" style="margin-bottom:0;"><label for="cash_staff_new_max_sale">New Maximum Sale Amount</label><input type="number" step="0.01" min="0" id="cash_staff_new_max_sale" class="form-control" value="<?php echo number_format($max_sale, 2, '.', ''); ?>" /></div>
              <div class="cash-staff-max-sale-actions"><button type="button" class="btn btn-success btn-sm" id="cash-save-staff-max-sale">Save Max Sale</button><button type="button" class="btn btn-light btn-sm" onclick="cashToggleStaffMaxSaleForm(false)">Cancel</button></div>
              <div id="cash-staff-max-sale-result" class="cash-staff-inline-result"></div>
            </div>
          </div>
        </div>
        <div class="cash-staff-metrics">
          <div class="cash-staff-metric"><strong>Total Sales</strong><span><?php echo number_format($total_sales, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>Paid Sales</strong><span><?php echo number_format($paid_sales, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>Unpaid Sales</strong><span><?php echo number_format($unpaid_sales, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>Credit Sales</strong><span><?php echo number_format($credit_sales, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>Bills Raised</strong><span><?php echo $bill_count; ?></span></div>
          <div class="cash-staff-metric"><strong>Items Qty</strong><span><?php echo number_format($item_qty, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>Avg Bill</strong><span><?php echo number_format($avg_bill, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>Returns / Deleted</strong><span><?php echo $return_count; ?> / <?php echo $deleted_count; ?></span></div>
          <div class="cash-staff-metric"><strong>Shift Tokens</strong><span><?php echo number_format($shift_tokens, 2); ?></span></div>
          <div class="cash-staff-metric"><strong>All Tokens</strong><span><?php echo number_format($lifetime_tokens, 2); ?></span></div>
        </div>
      </div>
      <div class="cash-staff-summary-card">
        <div class="cash-staff-card-head"><h4>Shift Breakdown</h4><p>Quick view of this waiter's shift performance.</p></div>
        <table class="table table-striped">
          <thead><tr><th>Section</th><th class="text-right">Amount</th><th class="text-right">Count</th></tr></thead>
          <tbody>
            <tr><td>Total Shift Sales</td><td class="text-right"><?php echo number_format($total_sales, 2); ?></td><td class="text-right"><?php echo $bill_count; ?></td></tr>
            <tr><td>Cleared / Paid Bills</td><td class="text-right"><?php echo number_format($paid_sales, 2); ?></td><td class="text-right"><?php echo (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift' AND pay_status=1"); ?></td></tr>
            <tr><td>Unpaid Bills</td><td class="text-right"><?php echo number_format($unpaid_sales, 2); ?></td><td class="text-right"><?php echo (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift' AND pay_status=2"); ?></td></tr>
            <tr><td>Customer Credit Bills</td><td class="text-right"><?php echo number_format($credit_sales, 2); ?></td><td class="text-right"><?php echo (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift' AND pay_status=3"); ?></td></tr>
            <tr><td>Returned Items</td><td class="text-right"><?php echo number_format((float)column_total("kp_ret_items", "amount", "user_id='$user_id' AND shift='$shift'"), 2); ?></td><td class="text-right"><?php echo $return_count; ?></td></tr>
            <tr><td>Deleted Bills</td><td class="text-right"><?php echo number_format((float)column_total("kp_del_orders", "amount", "user_id='$user_id' AND shift='$shift'"), 2); ?></td><td class="text-right"><?php echo $deleted_count; ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="cash-staff-foot">
      <button type="button" class="btn btn-outline-secondary" onclick="cashLoadStaffSummary(<?php echo $user_id; ?>, 'previous')">Refresh</button>
      <button type="button" class="btn btn-success" onclick="cashOpenStaffClearModal(<?php echo $user_id; ?>)" <?php echo $is_cleared ? 'disabled title="This staff is already cleared for this shift."' : ''; ?>><?php echo $is_cleared ? 'Already Cleared' : 'Clear Staff'; ?></button>
      <button type="button" class="btn btn-warning" onclick="cashOpenStaffSummaryPrint(<?php echo $user_id; ?>)">Print Shift Summary</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  function cashToggleStaffMaxSaleForm(forceOpen) {
    var $form = $('#cash-staff-max-sale-form');
    var shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !$form.hasClass('is-open');
    $form.toggleClass('is-open', shouldOpen);
    if (!shouldOpen) { $('#cash-staff-max-sale-result').html(''); }
  }
  function cashOpenStaffClearModal(id) {
    $('#modal').modal('show');
    $.ajax({ url: 'API/cash_api.php', type: 'POST', data: { action: 'modal', modal: 'staff_clear_modal', id: id }, dataType: 'html' })
    .done(function(data){ $('#load').html(data); })
    .fail(function(){ $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load staff clearance.</div></div></div></div>'); });
  }
  function cashOpenStaffSummaryPrint(id) {
    $('#modal').modal('show');
    $.ajax({ url: 'API/cash_api.php', type: 'POST', data: { action: 'modal', modal: 'staff_print_summary_modal', id: id }, dataType: 'html' })
    .done(function(data){ $('#load').html(data); })
    .fail(function(){ $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load print preview.</div></div></div></div>'); });
  }
  $(document).off('click.cashSaveStaffMaxSale', '#cash-save-staff-max-sale').on('click.cashSaveStaffMaxSale', '#cash-save-staff-max-sale', function(){
    var newMax = $('#cash_staff_new_max_sale').val();
    if (newMax === '' || isNaN(newMax) || parseFloat(newMax) < 0) { $('#cash-staff-max-sale-result').html('<div class="alert alert-warning">Enter a valid max sale amount.</div>'); return; }
    var $button = $(this); $button.prop('disabled', true).text('Saving...');
    $.ajax({ url: 'API/cash_api.php', type: 'POST', data: { action: 'clrbill_save_max_sale', user_id: <?php echo $user_id; ?>, max_sale: newMax }, dataType: 'json' })
    .done(function(data){
      if (data && data.ok) {
        var formatted = parseFloat(newMax).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#cash-staff-max-sale-display').text(formatted);
        $('#cash_staff_new_max_sale').val(parseFloat(newMax).toFixed(2));
        $('#cash-staff-max-sale-result').html('<div class="alert alert-success">' + (data.message || 'Max sale updated.') + '</div>');
        setTimeout(function(){ cashToggleStaffMaxSaleForm(false); }, 800);
      } else {
        $('#cash-staff-max-sale-result').html('<div class="alert alert-danger">' + ((data && data.message) ? data.message : 'Could not save the max sale.') + '</div>');
      }
    })
    .fail(function(){ $('#cash-staff-max-sale-result').html('<div class="alert alert-danger">Unable to save max sale.</div>'); })
    .always(function(){ $button.prop('disabled', false).text('Save Max Sale'); });
  });
</script>
        <?php
        return [
            'ok' => true,
            'message' => 'Staff clearance summary loaded.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_save_staff_clearance')) {
    function cash_api_save_staff_clearance() {
        global $connection;

        $staff_user_id = cash_api_clean_int($_POST['staff_user_id'] ?? 0);
        $cleared_by = (int)($_SESSION['user_id'] ?? 0);
        $shift = cash_api_clean_text($_POST['shift'] ?? '');
        $total_sales = cash_api_clean_float($_POST['total_sales'] ?? 0);
        $paid_sales = cash_api_clean_float($_POST['paid_sales'] ?? 0);
        $unpaid_sales = cash_api_clean_float($_POST['unpaid_sales'] ?? 0);
        $credit_sales = cash_api_clean_float($_POST['credit_sales'] ?? 0);
        $mpesa_total = cash_api_clean_float($_POST['mpesa_total'] ?? 0);
        $mpesa_count = cash_api_clean_int($_POST['mpesa_count'] ?? 0);
        $pdq_total = cash_api_clean_float($_POST['pdq_total'] ?? 0);
        $pdq_count = cash_api_clean_int($_POST['pdq_count'] ?? 0);
        $kpesa_active = isset($_POST['kpesa_active']) ? (int)$_POST['kpesa_active'] : 1;
        $manual_mpesa_total = cash_api_clean_float($_POST['manual_mpesa_total'] ?? 0);
        $manual_pdq_total = cash_api_clean_float($_POST['manual_pdq_total'] ?? 0);
        $collectible_sales = cash_api_clean_float($_POST['collectible_sales'] ?? 0);
        $manual_seed_mpesa_total = cash_api_clean_float($_POST['manual_seed_mpesa_total'] ?? 0);
        $manual_seed_pdq_total = cash_api_clean_float($_POST['manual_seed_pdq_total'] ?? 0);
        $manual_seed_cash_total = cash_api_clean_float($_POST['manual_seed_cash_total'] ?? 0);
        $complimentary_total = cash_api_clean_float($_POST['complimentary_total'] ?? 0);
        $expected_cash = cash_api_clean_float($_POST['expected_cash'] ?? 0);
        $actual_cash = cash_api_clean_float($_POST['actual_cash'] ?? 0);
        $note = cash_api_clean_text($_POST['note'] ?? '');

        if ($staff_user_id < 1 || $shift === '') {
            return [
                'ok' => true,
                'message' => 'Missing staff clearance details.',
                'status' => 200,
                'html' => '<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-warning" style="margin-bottom:0;">Missing staff clearance details.</div></div></div></div>',
            ];
        }

        if ($kpesa_active === 2) {
            $mpesa_total = $manual_mpesa_total;
            $pdq_total = $manual_pdq_total;
            $mpesa_count = 0;
            $pdq_count = 0;
            $expected_cash = max(0, $collectible_sales - $mpesa_total - $pdq_total - $complimentary_total);
        } else {
            $kpesa_active = 1;
            $expected_cash = max(0, $paid_sales - $mpesa_total - $pdq_total - $complimentary_total);
        }

        $variance = $actual_cash - $expected_cash;
        $variance_type = 'balanced';
        if ($variance < 0) {
            $variance_type = 'short';
        } elseif ($variance > 0) {
            $variance_type = 'excess';
        }

        $existing_clearance = cash_repo_find_staff_clearance($connection, $staff_user_id, $shift);
        if ($existing_clearance) {
            $cleared_at = (string)($existing_clearance['created_at'] ?? '');
            $cleared_by_name = !empty($existing_clearance['cleared_by']) ? get_user((int)$existing_clearance['cleared_by']) : '';
            ob_start();
            ?>
<div class="modal-dialog">
  <div class="modal-content" style="border-radius:18px; overflow:hidden;">
    <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
      <h4 class="modal-title" style="font-weight:900; color:#7a491b;">Already Cleared</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="modal-body" style="background:#fffdf9;">
      <div class="alert alert-warning" style="margin-bottom:12px;"><strong><?php echo htmlspecialchars(get_user($staff_user_id), ENT_QUOTES, 'UTF-8'); ?></strong> is already cleared for shift <strong><?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?></strong>.</div>
      <p style="margin:0 0 8px;"><strong>Cleared By:</strong> <?php echo htmlspecialchars($cleared_by_name ?: 'System', ENT_QUOTES, 'UTF-8'); ?></p>
      <p style="margin:0;"><strong>Cleared At:</strong> <?php echo htmlspecialchars($cleared_at, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-warning" data-dismiss="modal">Close</button></div>
  </div>
</div>
<script type="text/javascript">if (typeof cashLoadStaffSummary === 'function') { cashLoadStaffSummary(<?php echo $staff_user_id; ?>, 'previous'); }</script>
            <?php
            return [
                'ok' => true,
                'message' => 'Already cleared.',
                'status' => 200,
                'html' => ob_get_clean(),
            ];
        }

        $unpaid_sales_rows = cash_repo_unpaid_sales_for_staff($connection, $staff_user_id);
        $unpaid_sale_ids = [];
        $unpaid_ord_dates = [];
        $clear_counter = 0;
        foreach ($unpaid_sales_rows as $sale) {
            $unpaid_sale_ids[] = (int)$sale['sale_id'];
            $unpaid_ord_dates[] = (string)$sale['shift'];
            if ($clear_counter < 1) {
                $clear_counter = (int)$sale['counter'];
            }
        }
        $invoice_string = implode(',', $unpaid_sale_ids);

        $new_mpesa_amount = $kpesa_active === 2 ? max(0, $mpesa_total - $manual_seed_mpesa_total) : max(0, $mpesa_total);
        $new_pdq_amount = $kpesa_active === 2 ? max(0, $pdq_total - $manual_seed_pdq_total) : max(0, $pdq_total);
        $new_cash_amount = $kpesa_active === 2 ? max(0, $actual_cash - $manual_seed_cash_total) : max(0, $actual_cash);
        $new_complimentary_amount = max(0, $complimentary_total);
        $new_payment_total = $new_cash_amount + $new_mpesa_amount + $new_pdq_amount + $new_complimentary_amount;

        $payment_mode_ids = ['cash' => 0, 'mpesa' => 0, 'pdq' => 0, 'complimentary' => 0];
        $modes = mysqli_query($connection, "SELECT pay_id, name, is_default_cash, is_default_mpesa, is_default_card, is_default_complimentary FROM kp_pay_mode WHERE 1 ORDER BY pay_id ASC") or die(mysqli_error($connection));
        while ($row = mysqli_fetch_assoc($modes)) {
            $id = (int)$row['pay_id'];
            $name = strtoupper(trim((string)$row['name']));
            if ($payment_mode_ids['cash'] === 0 && ((int)($row['is_default_cash'] ?? 2) === 1 || strpos($name, 'CASH') !== false)) {
                $payment_mode_ids['cash'] = $id;
            }
            if ($payment_mode_ids['mpesa'] === 0 && ((int)($row['is_default_mpesa'] ?? 2) === 1 || strpos($name, 'MPESA') !== false)) {
                $payment_mode_ids['mpesa'] = $id;
            }
            if ($payment_mode_ids['pdq'] === 0 && ((int)($row['is_default_card'] ?? 2) === 1 || strpos($name, 'PDQ') !== false || strpos($name, 'CARD') !== false)) {
                $payment_mode_ids['pdq'] = $id;
            }
            if ($payment_mode_ids['complimentary'] === 0 && ((int)($row['is_default_complimentary'] ?? 2) === 1 || strpos($name, 'COMP') !== false)) {
                $payment_mode_ids['complimentary'] = $id;
            }
        }

        $active_mode_ids = [];
        foreach (['cash' => $new_cash_amount, 'mpesa' => $new_mpesa_amount, 'pdq' => $new_pdq_amount, 'complimentary' => $new_complimentary_amount] as $mode_key => $mode_amount) {
            if (($payment_mode_ids[$mode_key] ?? 0) > 0 && $mode_amount > 0) {
                $active_mode_ids[] = $payment_mode_ids[$mode_key];
            }
        }
        if (count($active_mode_ids) > 0) {
            ensure_payment_mode_columns($active_mode_ids);
        }

        $snapshot = [
            'staff_user_id' => $staff_user_id,
            'staff_name' => get_user($staff_user_id),
            'shift' => $shift,
            'total_sales' => $total_sales,
            'paid_sales' => $paid_sales,
            'unpaid_sales' => $unpaid_sales,
            'credit_sales' => $credit_sales,
            'mpesa_total' => $mpesa_total,
            'mpesa_count' => $mpesa_count,
            'pdq_total' => $pdq_total,
            'pdq_count' => $pdq_count,
            'complimentary_total' => $complimentary_total,
            'kpesa_active' => $kpesa_active,
            'manual_mpesa_total' => $manual_mpesa_total,
            'manual_pdq_total' => $manual_pdq_total,
            'collectible_sales' => $collectible_sales,
            'manual_seed_mpesa_total' => $manual_seed_mpesa_total,
            'manual_seed_pdq_total' => $manual_seed_pdq_total,
            'manual_seed_cash_total' => $manual_seed_cash_total,
            'expected_cash' => $expected_cash,
            'actual_cash' => $actual_cash,
            'new_cash_amount' => $new_cash_amount,
            'new_mpesa_amount' => $new_mpesa_amount,
            'new_pdq_amount' => $new_pdq_amount,
            'new_complimentary_amount' => $new_complimentary_amount,
            'new_payment_total' => $new_payment_total,
            'cleared_sale_ids' => $unpaid_sale_ids,
            'variance_amount' => $variance,
            'variance_type' => $variance_type,
            'note' => $note,
            'cleared_by' => $cleared_by,
        ];

        mysqli_begin_transaction($connection);
        try {
            if (!cash_repo_insert_staff_clearance($connection, [
                'staff_user_id' => $staff_user_id,
                'cleared_by' => $cleared_by,
                'shift_date' => $shift,
                'total_sales' => $total_sales,
                'paid_sales' => $paid_sales,
                'unpaid_sales' => $unpaid_sales,
                'credit_sales' => $credit_sales,
                'mpesa_total' => $mpesa_total,
                'pdq_total' => $pdq_total,
                'expected_cash' => $expected_cash,
                'actual_cash' => $actual_cash,
                'variance_amount' => $variance,
                'variance_type' => $variance_type,
                'snapshot_json' => json_encode($snapshot),
                'note' => $note,
            ])) {
                throw new Exception(mysqli_error($connection));
            }

            if (count($unpaid_sale_ids) > 0) {
                if ($new_payment_total > 0 && count($active_mode_ids) > 0) {
                    $pay_id = cash_repo_next_pay_id();
                    $ord_date = count($unpaid_ord_dates) > 0 ? reset($unpaid_ord_dates) : $shift;
                    $fields = ['pay_id','sale_id','user_id','tendered','pay_date','paid_total','type','counter','ord_date'];
                    $values = [
                        "'$pay_id'",
                        "'" . mysqli_real_escape_string($connection, $invoice_string) . "'",
                        "'$cleared_by'",
                        "'" . number_format($new_payment_total, 2, '.', '') . "'",
                        "'" . mysqli_real_escape_string($connection, $shift) . "'",
                        "'" . number_format($new_payment_total, 2, '.', '') . "'",
                        "'2'",
                        "'" . (int)$clear_counter . "'",
                        "'" . mysqli_real_escape_string($connection, $ord_date) . "'"
                    ];
                    foreach (['cash' => $new_cash_amount, 'mpesa' => $new_mpesa_amount, 'pdq' => $new_pdq_amount, 'complimentary' => $new_complimentary_amount] as $key => $mode_amount) {
                        if (($payment_mode_ids[$key] ?? 0) > 0 && $mode_amount > 0) {
                            $fields[] = 'mode' . $payment_mode_ids[$key];
                            $values[] = "'" . number_format($mode_amount, 2, '.', '') . "'";
                        }
                    }
                    if (!cash_repo_insert_sale_payment($connection, $fields, $values)) {
                        throw new Exception(mysqli_error($connection));
                    }

                    foreach ($unpaid_sale_ids as $sale_id) {
                        $sale_ord_date = (string)get_column('kp_sales', 'shift', "sale_id='$sale_id'");
                        $sale_amount = (float)get_column('kp_sales', 'amount', "sale_id='$sale_id'");
                        if (!cash_repo_insert_sales_paid($connection, $pay_id, $sale_id, $cleared_by, $staff_user_id, $sale_amount, $sale_ord_date, $shift)) {
                            throw new Exception(mysqli_error($connection));
                        }
                    }

                    foreach (['cash' => $new_cash_amount, 'mpesa' => $new_mpesa_amount, 'pdq' => $new_pdq_amount, 'complimentary' => $new_complimentary_amount] as $key => $mode_amount) {
                        $mode_id = (int)($payment_mode_ids[$key] ?? 0);
                        if ($mode_id > 0 && $mode_amount > 0) {
                            $accbal = (float)get_column('kp_pay_mode', 'accbal', "pay_id='$mode_id'");
                            if (!cash_repo_update_pay_mode_balance($connection, $mode_id, $mode_amount)) {
                                throw new Exception(mysqli_error($connection));
                            }
                            if (!cash_repo_insert_pay_mode_history($connection, $mode_id, $cleared_by, $shift, $mode_amount, $accbal)) {
                                throw new Exception(mysqli_error($connection));
                            }
                        }
                    }
                }

                if (!mysqli_query($connection, "UPDATE kp_sales SET pay_status=1 WHERE user_id='$staff_user_id' AND pay_status=2")) {
                    throw new Exception(mysqli_error($connection));
                }
            }

            if ($kpesa_active === 1) {
                if (!mysqli_query($connection, "UPDATE kp_split_message SET clear_status=1 WHERE clear_status=2 AND claim_staff='$staff_user_id'")) {
                    throw new Exception(mysqli_error($connection));
                }
                if (!mysqli_query($connection, "UPDATE kp_user_pdq SET clear_status=1 WHERE clear_status=2 AND user_id='$staff_user_id'")) {
                    throw new Exception(mysqli_error($connection));
                }
            }

            mysqli_commit($connection);
        } catch (Throwable $error) {
            mysqli_rollback($connection);
            if (function_exists('cash_log_event')) {
                cash_log_event('staff_clearance_failed', [
                    'staff_user_id' => $staff_user_id,
                    'cleared_by' => $cleared_by,
                    'shift' => $shift,
                    'message' => $error->getMessage(),
                ]);
            }
            return [
                'ok' => true,
                'message' => 'Staff clearance failed.',
                'status' => 200,
                'html' => '<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8') . '</div></div></div></div>',
            ];
        }

        if (function_exists('cash_log_event')) {
            cash_log_event('staff_clearance_saved', [
                'staff_user_id' => $staff_user_id,
                'cleared_by' => $cleared_by,
                'shift' => $shift,
                'new_payment_total' => $new_payment_total,
                'variance' => $variance,
                'variance_type' => $variance_type,
            ]);
        }

        ob_start();
        ?>
<div class="modal-dialog">
  <div class="modal-content" style="border-radius:18px; overflow:hidden;">
    <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
      <h4 class="modal-title" style="font-weight:900; color:#7a491b;">Staff Cleared</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="modal-body" style="background:#fffdf9;">
      <div class="alert alert-success" style="margin-bottom:12px;">Staff clearance saved successfully for <strong><?php echo htmlspecialchars(get_user($staff_user_id), ENT_QUOTES, 'UTF-8'); ?></strong>.</div>
      <p style="margin:0 0 8px;"><strong>Unpaid Sales Cleared:</strong> <?php echo count($unpaid_sale_ids); ?> bill(s)</p>
      <p style="margin:0 0 8px;"><strong>New Payment Posted:</strong> <?php echo number_format($new_payment_total, 2); ?></p>
      <p style="margin:0 0 8px;"><strong>Expected Cash:</strong> <?php echo number_format($expected_cash, 2); ?></p>
      <p style="margin:0 0 8px;"><strong>Actual Cash:</strong> <?php echo number_format($actual_cash, 2); ?></p>
      <p style="margin:0;"><strong>Variance:</strong> <?php echo number_format($variance, 2); ?> (<?php echo strtoupper($variance_type); ?>)</p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-warning" data-dismiss="modal">Done</button></div>
  </div>
</div>
<script type="text/javascript">if (typeof cashLoadStaffSummary === 'function') { cashLoadStaffSummary(<?php echo $staff_user_id; ?>, 'previous'); }</script>
        <?php

        return [
            'ok' => true,
            'message' => 'Staff clearance processed.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_save_expense')) {
    function cash_api_save_expense() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $shift_date = cash_api_clean_text($_POST['shift_date'] ?? date('Y-m-d'));
        $title = cash_api_clean_text($_POST['title'] ?? '');
        $amount = cash_api_clean_float($_POST['amount'] ?? 0);
        $note = cash_api_clean_text($_POST['note'] ?? '');

        if ($user_id < 1) {
            return [
                'ok' => true,
                'message' => 'Session expired.',
                'status' => 200,
                'html' => '<div class="alert alert-danger">Session expired. Please log in again.</div>',
            ];
        }
        if ($title === '' || $amount <= 0) {
            return [
                'ok' => true,
                'message' => 'Invalid expense.',
                'status' => 200,
                'html' => '<div class="alert alert-warning">Enter both expense title and a valid amount.</div>',
            ];
        }

        $save = cash_repo_insert_expense($connection, $shift_date, $title, $amount, $note, $user_id);
        if ($save && function_exists('cash_log_event')) {
            cash_log_event('expense_save', [
                'user_id' => $user_id,
                'shift_date' => $shift_date,
                'title' => $title,
                'amount' => $amount,
            ]);
        }

        return [
            'ok' => true,
            'message' => 'Expense processed.',
            'status' => 200,
            'html' => $save
                ? '<div class="alert alert-success expense-saved-ok">Expense saved successfully.</div>'
                : '<div class="alert alert-danger">Could not save expense.</div>',
        ];
    }
}

if (!function_exists('cash_api_delete_expense')) {
    function cash_api_delete_expense() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $exp_id = cash_api_clean_int($_POST['id'] ?? 0);
        $pin = cash_api_clean_text($_POST['pass'] ?? '');
        $reason = cash_api_clean_text($_POST['reason'] ?? '');
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }

        if ($user_id < 1) {
            return ['ok' => true, 'message' => 'Session expired.', 'status' => 200, 'html' => '<div class="alert alert-danger">Session expired. Please log in again.</div>'];
        }
        if ($exp_id < 1) {
            return ['ok' => true, 'message' => 'Invalid expense.', 'status' => 200, 'html' => '<div class="alert alert-warning">Select a valid expense to delete.</div>'];
        }
        if ($pin === '') {
            return ['ok' => true, 'message' => 'Pin required.', 'status' => 200, 'html' => '<div class="alert alert-warning">Enter supervisor password to continue.</div>'];
        }
        if ($reason === '') {
            return ['ok' => true, 'message' => 'Reason required.', 'status' => 200, 'html' => '<div class="alert alert-warning">Enter a reason for deleting this expense.</div>'];
        }

        $approval = cash_repo_find_user_by_pin($connection, $pin);
        if (!$approval) {
            return ['ok' => true, 'message' => 'Wrong password.', 'status' => 200, 'html' => '<div class="alert alert-danger">Wrong supervisor password. Trials are documented.</div>'];
        }
        if ((int)($approval['role_id'] ?? 0) === 3) {
            return ['ok' => true, 'message' => 'Approval denied.', 'status' => 200, 'html' => '<div class="alert alert-danger">Only supervisor or admin approval can delete expenses.</div>'];
        }

        $expense = cash_repo_find_expense($connection, $exp_id);
        if (!$expense) {
            return ['ok' => true, 'message' => 'Expense missing.', 'status' => 200, 'html' => '<div class="alert alert-warning">That expense was already removed or is missing.</div>'];
        }

        mysqli_begin_transaction($connection);
        try {
            if (!cash_repo_track_expense_delete($connection, $expense, $user_id, (int)$approval['user_id'], $reason, $shift)) {
                throw new Exception('Could not track the deletion.');
            }
            if (!cash_repo_delete_expense($connection, $exp_id)) {
                throw new Exception('Could not delete expense.');
            }
            mysqli_commit($connection);
        } catch (Throwable $error) {
            mysqli_rollback($connection);
            if (function_exists('cash_log_event')) {
                cash_log_event('expense_delete_failed', [
                    'user_id' => $user_id,
                    'exp_id' => $exp_id,
                    'message' => $error->getMessage(),
                ]);
            }
            return ['ok' => true, 'message' => 'Delete failed.', 'status' => 200, 'html' => '<div class="alert alert-danger">' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>'];
        }

        $action = "DELETED SHOP EXPENSE|$exp_id|" . ($expense['title'] ?? '') . "|" . ($expense['amount'] ?? 0) . "|REASON " . $reason . "|APPROVED BY " . ($approval['username'] ?? '');
        logs($action, "../../");
        if (function_exists('cash_log_event')) {
            cash_log_event('expense_delete', [
                'user_id' => $user_id,
                'exp_id' => $exp_id,
                'approver_id' => (int)$approval['user_id'],
                'reason' => $reason,
            ]);
        }

        return [
            'ok' => true,
            'message' => 'Expense delete processed.',
            'status' => 200,
            'html' => '<div class="alert alert-success expense-delete-ok">Expense deleted with supervisor approval.</div>',
        ];
    }
}

if (!function_exists('cash_api_create_merge')) {
    function cash_api_create_merge() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }

        if (function_exists('cash_ensure_merge_tables')) {
            cash_ensure_merge_tables();
        }

        $roots = $_POST['roots'] ?? [];
        $error_message = '';
        $created = function_exists('cash_create_merge_record')
            ? cash_create_merge_record($user_id, $roots, $error_message)
            : false;

        if (!$created) {
            return [
                'ok' => false,
                'message' => $error_message !== '' ? $error_message : 'Could not create the merge.',
                'status' => 400,
            ];
        }

        $merge_id = (int)($created['merge_id'] ?? 0);
        $merge_no = (string)($created['merge_no'] ?? '');
        $merge_total = (float)($created['total_amount'] ?? 0);
        $expanded_items = isset($created['items']) && is_array($created['items']) ? $created['items'] : [];

        $grouped = [];
        foreach ($expanded_items as $item) {
            $root_sale_id = (int)($item['root_sale_id'] ?? 0);
            if (!isset($grouped[$root_sale_id])) {
                $grouped[$root_sale_id] = 0;
            }
            $grouped[$root_sale_id] += (float)($item['amount'] ?? 0);
        }

        $preview = '<div class="cash-panel"><div class="cash-panel-head"><h4>New Merge Created</h4></div><div class="cash-panel-body">';
        $preview .= '<div class="cash-merge-basket-card" style="margin:0;"><small>Merge Number</small><strong>' . htmlspecialchars($merge_no, ENT_QUOTES, 'UTF-8') . '</strong><p><b>Total:</b> ' . number_format($merge_total, 2) . '</p></div>';
        foreach ($grouped as $root_sale_id => $root_total) {
            $preview .= '<div class="cash-merge-basket-card"><small>Included Bill Group</small><strong>#' . (int)$root_sale_id . '</strong><p><b>Amount:</b> ' . number_format((float)$root_total, 2) . '</p></div>';
        }
        $preview .= '</div></div>';

        return [
            'ok' => true,
            'message' => 'Merge created successfully.',
            'status' => 200,
            'merge_id' => $merge_id,
            'merge_no' => $merge_no,
            'total_amount' => number_format($merge_total, 2, '.', ''),
            'preview_html' => $preview,
        ];
    }
}

if (!function_exists('cash_api_customer_credit_lookup')) {
    function cash_api_customer_credit_lookup() {
        global $connection;

        $cust_id = cash_api_clean_int($_REQUEST['cust_id'] ?? 0);
        if ($cust_id < 1) {
            return ['ok' => true, 'message' => 'Select customer.', 'status' => 200, 'html' => '<div class="cash-credit-existing-empty">Select a customer to view credit details.</div>'];
        }
        $customer = cash_repo_find_customer($connection, $cust_id);
        if (!$customer) {
            return ['ok' => true, 'message' => 'Customer not found.', 'status' => 200, 'html' => '<div class="cash-credit-existing-empty">Customer not found.</div>'];
        }
        $recent = cash_repo_recent_credit_sales($connection, $cust_id, 8);

        ob_start();
        ?>
<div class="cash-credit-existing-card">
  <strong>Current Balance</strong>
  <span><?php echo number_format((float)$customer['bal'], 2); ?></span>
</div>
<div class="cash-credit-existing-card">
  <strong>Customer</strong>
  <span><?php echo htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($customer['phone'])) { ?> | <?php echo htmlspecialchars($customer['phone'], ENT_QUOTES, 'UTF-8'); ?><?php } ?></span>
</div>
<div class="cash-credit-existing-card cash-credit-existing-bills">
  <strong>Recent Credit Bills</strong>
  <?php if (count($recent) < 1) { ?>
    <div class="cash-credit-existing-empty">No previous credit bills for this customer.</div>
  <?php } else { ?>
    <table class="table table-striped" style="margin-bottom: 0;">
      <thead><tr><th>Bill</th><th>Date</th><th>Amount</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $row) { ?>
        <tr><td><?php echo (int)$row['trans_id']; ?></td><td><?php echo htmlspecialchars($row['trans_date'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo number_format((float)$row['amount'], 2); ?></td></tr>
      <?php } ?>
      </tbody>
    </table>
  <?php } ?>
</div>
        <?php
        return [
            'ok' => true,
            'message' => 'Customer credit lookup loaded.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_credit_assign')) {
    function cash_api_credit_assign() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $invoices = trim((string)($_POST['invoices'] ?? ''));
        $cust_id = cash_api_clean_int($_POST['cust_id'] ?? 0);
        $name = strtoupper(trim((string)($_POST['name'] ?? '')));
        $phone_number = trim((string)($_POST['phone'] ?? ''));
        $phone = preg_replace('/^0/', '+254', $phone_number);
        $loc = strtoupper(trim((string)($_POST['loc'] ?? '')));

        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired. Please log in again.', 'status' => 401];
        }
        if ($invoices === '') {
            return ['ok' => false, 'message' => 'Select at least one bill first.', 'status' => 400];
        }

        $invoice_ids = array_values(array_filter(array_map('intval', array_map('trim', explode(',', $invoices)))));
        if (empty($invoice_ids)) {
            return ['ok' => false, 'message' => 'No valid bills were selected.', 'status' => 400];
        }

        if ($cust_id < 1) {
            if ($name === '' || $phone === '') {
                return ['ok' => false, 'message' => 'Pick a customer or enter new customer name and phone.', 'status' => 400];
            }

            $phone_safe = mysqli_real_escape_string($connection, $phone);
            $existing = mysqli_query($connection, "SELECT cust_id FROM kp_customers WHERE phone='$phone_safe' LIMIT 1");
            if ($existing && mysqli_num_rows($existing) > 0) {
                $cust_id = (int)get_column('kp_customers', 'cust_id', "phone='$phone_safe'");
            } else {
                $name_safe = mysqli_real_escape_string($connection, $name);
                $loc_safe = mysqli_real_escape_string($connection, $loc);
                mysqli_query($connection, "INSERT INTO kp_customers(name,phone,location) VALUES('$name_safe','$phone_safe','$loc_safe')");
                $cust_id = (int)mysqli_insert_id($connection);
            }
        }

        $done = 0;
        $total = 0;
        foreach ($invoice_ids as $sale_id) {
            if ($sale_id < 1) {
                continue;
            }

            $sale_q = mysqli_query($connection, "SELECT sale_id,amount,pay_status FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");
            if (!$sale_q || mysqli_num_rows($sale_q) < 1) {
                continue;
            }

            $sale = mysqli_fetch_assoc($sale_q);
            if ((int)$sale['pay_status'] === 1 || (int)$sale['pay_status'] === 3) {
                continue;
            }

            $bill_total = (float)$sale['amount'];
            mysqli_query($connection, "UPDATE kp_sales SET pay_status=3,cust_id='$cust_id',sale_bal='$bill_total',cf_bal=cf_bal+'$bill_total',type=2 WHERE sale_id='$sale_id'");
            mysqli_query($connection, "UPDATE kp_sale_items SET pay_status=3,type=2 WHERE sale_id='$sale_id'");
            mysqli_query($connection, "UPDATE kp_customers SET bal=bal+'$bill_total' WHERE cust_id='$cust_id'");
            if (function_exists('cust_trans')) {
                cust_trans($cust_id, 'SALE', $sale_id, $bill_total);
            }
            $done++;
            $total += $bill_total;
        }

        if ($done < 1) {
            return ['ok' => false, 'message' => 'No selected bills were moved to customer credit.', 'status' => 400];
        }

        return [
            'ok' => true,
            'message' => 'Moved ' . $done . ' bill(s) to ' . get_cust($cust_id) . ' credit for ' . number_format($total, 2) . '.',
            'status' => 200,
            'cust_id' => $cust_id,
        ];
    }
}

if (!function_exists('cash_api_customer_credit_workbench')) {
    function cash_api_customer_credit_workbench() {
        global $connection;

        $cust_id = cash_api_clean_int($_REQUEST['cust_id'] ?? 0);
        $shift = (string)get_column('kp_shift','shift',"1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }
        $today = date('Y-m-d');

        $customer = cash_repo_find_customer($connection, $cust_id);
        $sales_payload = $customer ? cash_repo_customer_credit_sales($connection, $cust_id) : ['rows' => [], 'error' => '', 'ok' => false];
        $sales_rows = $sales_payload['rows'];
        $sales_error = $sales_payload['error'];
        $pay_modes = cash_repo_active_pay_modes($connection);

        ob_start();
        ?>
<style type="text/css">
  .credit-workbench-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(280px,0.8fr); gap:14px; align-items:start; }
  .credit-workbench-card { padding:14px; border-radius:16px; background:#fff; border:1px solid rgba(146,110,52,0.12); min-width:0; }
  .credit-workbench-summary { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:12px; }
  .credit-workbench-pill { padding:12px; border-radius:14px; background:linear-gradient(180deg,#fffdfa 0%,#f6edd7 100%); border:1px solid rgba(146,110,52,0.12); }
  .credit-workbench-pill strong,.credit-workbench-pill span { display:block; }
  .credit-workbench-pill strong { font-size:11px; font-weight:900; color:#7e3a20; text-transform:uppercase; letter-spacing:0.05em; }
  .credit-workbench-pill span { margin-top:6px; font-size:22px; font-weight:900; color:#332411; }
  .credit-workbench-dual { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px; }
  .credit-workbench-dual strong,.credit-workbench-dual span { display:block; }
  .credit-workbench-dual strong { font-size:10px; font-weight:900; color:#7e3a20; text-transform:uppercase; letter-spacing:0.05em; }
  .credit-workbench-dual span { margin-top:4px; font-size:18px; font-weight:900; color:#332411; }
  .credit-workbench-table-wrap { max-height:380px; overflow-y:auto; border-radius:14px; border:1px solid rgba(146,110,52,0.12); }
  .credit-workbench-table { margin:0; }
  .credit-workbench-table th,.credit-workbench-table td { vertical-align:middle !important; font-size:12px; }
  .credit-workbench-table th { position:sticky; top:0; background:#fbf4de; z-index:1; color:#7e3a20; font-weight:900; }
  .credit-workbench-mode { margin-bottom:10px; padding:10px; border-radius:12px; background:#faf6ea; }
  .credit-workbench-mode .form-control { height:40px; border-radius:10px; margin-top:8px; }
  .credit-workbench-status { padding:10px 12px; border-radius:12px; background:#fff7dc; color:#8a6a3a; font-size:12px; font-weight:800; margin-bottom:12px; }
  .credit-workbench-actions { display:flex; gap:10px; }
  .credit-workbench-pay-btn { min-height:46px; border:0; border-radius:12px; font-size:13px; font-weight:900; text-transform:uppercase; flex:1 1 0; }
  .credit-workbench-pay-btn.primary { background:linear-gradient(135deg,#14532d 0%,#277246 100%); color:#fff; }
  .credit-workbench-pay-btn.secondary { background:#ead6d6; color:#7e2014; }
  @media (max-width: 840px) { .credit-workbench-grid,.credit-workbench-summary { grid-template-columns:1fr; } }
</style>
        <?php if (!$customer) { ?>
  <div class="credit-workbench-status">Customer not found.</div>
        <?php } else {
            $today_credit = column_total('kp_cust_hist','amount',"cust_id='$cust_id' AND trans='SALE' AND trans_date='$today'");
            $today_paid = column_total('kp_customer_credit_payment_items','amount',"cust_id='$cust_id' AND pay_date='$shift' AND ord_date='$shift'");
            $previous_paid = column_total('kp_customer_credit_payment_items','amount',"cust_id='$cust_id' AND pay_date='$shift' AND ord_date!='$shift'");
            ?>
  <div class="credit-workbench-summary">
    <div class="credit-workbench-pill"><strong>Credit Today</strong><span><?php echo number_format((float)$today_credit, 2); ?></span></div>
    <div class="credit-workbench-pill">
      <strong>Paid Credit</strong>
      <div class="credit-workbench-dual">
        <div><strong>Today</strong><span><?php echo number_format((float)$today_paid, 2); ?></span></div>
        <div><strong>Previous</strong><span><?php echo number_format((float)$previous_paid, 2); ?></span></div>
      </div>
    </div>
  </div>
  <div class="credit-workbench-grid">
    <div class="credit-workbench-card">
      <div class="credit-workbench-status">Outstanding credit invoices for <?php echo htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?>. Select the invoices being paid now.</div>
      <div class="credit-workbench-table-wrap">
        <table class="table table-striped credit-workbench-table">
          <thead><tr><th>Select</th><th>Bill</th><th>Shift</th><th>Counter</th><th>Balance</th></tr></thead>
          <tbody>
            <?php if (empty($sales_rows)) { ?>
              <tr><td colspan="5">No outstanding credit invoices for this customer.</td></tr>
            <?php } else { foreach ($sales_rows as $sale) { ?>
              <tr>
                <td><input type="checkbox" class="credit-bill" value="<?php echo (int)$sale['sale_id']; ?>" data-amount="<?php echo (float)$sale['sale_bal']; ?>" data-date="<?php echo htmlspecialchars($sale['shift'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><?php echo (int)$sale['sale_id']; ?></td>
                <td><?php echo htmlspecialchars($sale['shift'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(get_column('kp_counters','name',"cntr_id='".(int)$sale['counter']."'"), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo number_format((float)$sale['sale_bal'], 2); ?></td>
              </tr>
            <?php }} ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="credit-workbench-card">
      <div id="credit-pay-results"></div>
      <?php if (!empty($sales_error)) { ?><div class="alert alert-warning">Could not load credit invoices fully: <?php echo htmlspecialchars($sales_error, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
      <form id="customer-credit-pay-form">
        <input type="hidden" name="cust_id" value="<?php echo $cust_id; ?>">
        <input type="hidden" name="invoices" id="credit-pay-invoices" value="">
        <input type="hidden" name="tendered" id="credit-pay-tendered" value="0">
        <div class="credit-workbench-pill" style="margin-bottom: 10px;"><strong>Selected Balance</strong><span id="credit-selected-total">0.00</span></div>
        <div class="credit-workbench-pill" style="margin-bottom: 10px;"><strong>Payment Total</strong><span id="credit-tendered-total">0.00</span></div>
        <div class="credit-workbench-pill" style="margin-bottom: 10px;"><strong>Customer Balance</strong><span><?php echo number_format((float)$customer['bal'], 2); ?></span></div>
        <?php foreach ($pay_modes as $mode) { ?>
          <div class="credit-workbench-mode">
            <label style="font-weight: 900;"><input type="checkbox" class="credit-mode-check" value="<?php echo (int)$mode['pay_id']; ?>" data-ref="<?php echo (int)$mode['has_ref']; ?>"> <?php echo htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8'); ?></label>
            <?php if ((int)$mode['has_ref'] === 1) { ?><input type="text" class="form-control credit-mode-ref" data-mode="<?php echo (int)$mode['pay_id']; ?>" name="ref<?php echo (int)$mode['pay_id']; ?>" placeholder="Reference / code" style="display: none;"><?php } ?>
            <input type="number" step="0.01" min="0" class="form-control credit-mode-amount" data-mode="<?php echo (int)$mode['pay_id']; ?>" name="name<?php echo (int)$mode['pay_id']; ?>" value="0" style="display: none;">
          </div>
        <?php } ?>
        <textarea name="note" class="form-control" placeholder="Payment note" style="min-height: 92px; border-radius: 10px; margin-top: 10px;"></textarea>
        <div class="credit-workbench-actions" style="margin-top: 12px;">
          <button type="button" class="credit-workbench-pay-btn secondary" onclick="loadCustomerCreditWorkspace('<?php echo $cust_id; ?>')">Refresh</button>
          <button type="button" id="submit-credit-payment" class="credit-workbench-pay-btn primary" style="display: none;">Record Payment</button>
        </div>
      </form>
    </div>
  </div>
        <?php } ?>
<script type="text/javascript">
  function recalcCustomerCreditTotal() {
    var invoices = [];
    var total = 0;
    $('.credit-bill:checked').each(function(){ invoices.push($(this).val()); total += parseFloat($(this).data('amount')) || 0; });
    $('#credit-pay-invoices').val(invoices.join(','));
    $('#credit-selected-total').text(total.toFixed(2));
    recalcCustomerCreditTendered();
  }
  function recalcCustomerCreditTendered() {
    var total = 0;
    var modes = [];
    $('.credit-mode-check:checked').each(function(){ var mode = $(this).val(); modes.push(mode); total += parseFloat($('.credit-mode-amount[data-mode="' + mode + '"]').val()) || 0; });
    $('#credit-pay-tendered').val(total.toFixed(2));
    $('#credit-tendered-total').text(total.toFixed(2));
    var selected = parseFloat($('#credit-selected-total').text()) || 0;
    if (selected > 0 && total > 0 && total <= selected && modes.length > 0) { $('#submit-credit-payment').show(); } else { $('#submit-credit-payment').hide(); }
  }
  $(document).off('change.creditBills').on('change.creditBills', '.credit-bill', function(){ recalcCustomerCreditTotal(); });
  $(document).off('change.creditModes').on('change.creditModes', '.credit-mode-check', function(){
    var mode = $(this).val(); var ref = $(this).data('ref');
    if (this.checked) { $('.credit-mode-amount[data-mode="' + mode + '"]').show().val('0'); if (parseInt(ref, 10) === 1) { $('.credit-mode-ref[data-mode="' + mode + '"]').show().val(''); } }
    else { $('.credit-mode-amount[data-mode="' + mode + '"]').hide().val('0'); $('.credit-mode-ref[data-mode="' + mode + '"]').hide().val(''); }
    recalcCustomerCreditTendered();
  });
  $(document).off('keyup.creditAmounts change.creditAmounts').on('keyup.creditAmounts change.creditAmounts', '.credit-mode-amount', function(){ recalcCustomerCreditTendered(); });
  $(document).off('click.submitCreditPayment').on('click.submitCreditPayment', '#submit-credit-payment', function(e){
    e.preventDefault();
    $.ajax({
      url: 'API/cash_api.php',
      type: 'POST',
      data: $('#customer-credit-pay-form').serialize() + '&action=customer_credit_pay&pay-mode[]=' + $('.credit-mode-check:checked').map(function(){ return $(this).val(); }).get().join('&pay-mode[]='),
      dataType: 'json'
    })
    .done(function(data){
      var payload = data && data.data ? data.data : {};
      var status = payload.status || (data && data.ok ? 'success' : 'error');
      $('#credit-pay-results').html('<div class="alert alert-' + (status === 'success' ? 'success' : (status === 'warning' ? 'warning' : 'danger')) + '">' + (data.message || payload.message || 'Customer credit payment processed.') + '</div>');
      if (status === 'success') {
        if (typeof loadCreditPanel === 'function') { loadCreditPanel(); }
        if (typeof loadCashLive === 'function') { loadCashLive(); }
        setTimeout(function(){
          $('#modal').modal('hide');
          if (payload.print_select_url) { window.location.href = payload.print_select_url; return; }
          window.location.reload();
        }, 700);
      }
    })
    .fail(function(){ $('#credit-pay-results').html('<div class="alert alert-danger">Could not record customer credit payment.</div>'); });
  });
</script>
        <?php
        return [
            'ok' => true,
            'message' => 'Customer credit workbench loaded.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_customer_credit_pay')) {
    function cash_api_customer_credit_pay() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $cust_id = cash_api_clean_int($_POST['cust_id'] ?? 0);
        $invoices = trim((string)($_POST['invoices'] ?? ''));
        $mode_ids = isset($_POST['pay-mode']) ? (array)$_POST['pay-mode'] : [];
        $tendered = cash_api_clean_float($_POST['tendered'] ?? 0);
        $note = trim((string)($_POST['note'] ?? ''));
        $shift = get_column('kp_shift', 'shift', "1");
        if ($shift == 0) {
            $shift = date('Y-m-d');
        }

        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired. Please log in again.', 'status' => 401];
        }
        if ($cust_id < 1) {
            return ['ok' => false, 'message' => 'Select a valid customer.', 'status' => 400];
        }
        if ($invoices === '') {
            return ['ok' => false, 'message' => 'Select at least one outstanding invoice.', 'status' => 400];
        }
        if (empty($mode_ids)) {
            return ['ok' => false, 'message' => 'Select at least one payment mode.', 'status' => 400];
        }

        $invoice_ids = array_values(array_filter(array_map('intval', explode(',', $invoices))));
        if (empty($invoice_ids)) {
            return ['ok' => false, 'message' => 'No valid invoices selected.', 'status' => 400];
        }

        $selected_total = 0;
        $invoice_rows = [];
        foreach ($invoice_ids as $sale_id) {
            $sale_q = mysqli_query($connection, "SELECT sale_id,shift,sale_bal,pay_status FROM kp_sales WHERE sale_id='$sale_id' AND cust_id='$cust_id' LIMIT 1");
            if (!$sale_q || mysqli_num_rows($sale_q) < 1) {
                continue;
            }
            $sale = mysqli_fetch_assoc($sale_q);
            if ((int)$sale['pay_status'] !== 3 || (float)$sale['sale_bal'] <= 0) {
                continue;
            }
            $invoice_rows[] = $sale;
            $selected_total += (float)$sale['sale_bal'];
        }

        if ($selected_total <= 0 || empty($invoice_rows)) {
            return ['ok' => false, 'message' => 'Selected invoices are no longer outstanding.', 'status' => 400];
        }

        $mode_total = 0;
        $mode_rows = [];
        foreach ($mode_ids as $mode_id) {
            $mode_id = (int)$mode_id;
            if ($mode_id < 1) {
                continue;
            }
            $amount_key = 'name' . $mode_id;
            $ref_key = 'ref' . $mode_id;
            $mode_amount = cash_api_clean_float($_POST[$amount_key] ?? 0);
            $mode_ref = trim((string)($_POST[$ref_key] ?? ''));
            if ($mode_amount <= 0) {
                continue;
            }
            $mode_rows[] = [
                'pay_mode_id' => $mode_id,
                'amount' => $mode_amount,
                'reference_no' => $mode_ref,
            ];
            $mode_total += $mode_amount;
        }

        if (empty($mode_rows)) {
            return ['ok' => false, 'message' => 'Enter payment amount for the selected payment mode.', 'status' => 400];
        }
        if ($mode_total <= 0 || $tendered <= 0) {
            return ['ok' => false, 'message' => 'Enter a valid payment amount.', 'status' => 400];
        }
        if (abs($mode_total - $tendered) > 0.009) {
            return ['ok' => false, 'message' => 'Payment mode total must match the payment total.', 'status' => 400];
        }
        if ($tendered - $selected_total > 0.009) {
            return ['ok' => false, 'message' => 'Payment cannot be more than the selected credit balance.', 'status' => 400];
        }

        $note_safe = mysqli_real_escape_string($connection, $note);
        $credit_pay_id = pay_id();
        mysqli_query($connection, "INSERT INTO kp_customer_credit_payments(credit_pay_id,cust_id,user_id,amount,pay_mode_id,reference_no,note,shift_date) VALUES('$credit_pay_id','$cust_id','$user_id','$tendered','0','','{$note_safe}','$shift')");

        foreach ($mode_rows as $mode_row) {
            $ref_safe = mysqli_real_escape_string($connection, (string)$mode_row['reference_no']);
            mysqli_query($connection, "INSERT INTO kp_customer_credit_payment_modes(credit_pay_id,pay_mode_id,amount,reference_no) VALUES('$credit_pay_id','".$mode_row['pay_mode_id']."','".$mode_row['amount']."','$ref_safe')");
        }

        $remaining_payment = $tendered;
        foreach ($invoice_rows as $sale) {
            if ($remaining_payment <= 0) {
                break;
            }
            $sale_id = (int)$sale['sale_id'];
            $ord_date = mysqli_real_escape_string($connection, (string)$sale['shift']);
            $sale_balance = (float)$sale['sale_bal'];
            $amount = $remaining_payment >= $sale_balance ? $sale_balance : $remaining_payment;
            $new_balance = $sale_balance - $amount;

            mysqli_query($connection, "INSERT INTO kp_customer_credit_payment_items(credit_pay_id,cust_id,sale_id,ord_date,pay_date,amount) VALUES('$credit_pay_id','$cust_id','$sale_id','$ord_date','$shift','$amount')");
            if ($new_balance <= 0.009) {
                mysqli_query($connection, "UPDATE kp_sales SET sale_bal=0,pay_status=1,pay_date='$shift' WHERE sale_id='$sale_id'");
                mysqli_query($connection, "UPDATE kp_sale_items SET pay_status=1 WHERE sale_id='$sale_id'");
            } else {
                mysqli_query($connection, "UPDATE kp_sales SET sale_bal='$new_balance',pay_status=3 WHERE sale_id='$sale_id'");
                mysqli_query($connection, "UPDATE kp_sale_items SET pay_status=3 WHERE sale_id='$sale_id'");
            }
            $remaining_payment -= $amount;
        }

        mysqli_query($connection, "UPDATE kp_customers SET bal=bal-'$tendered' WHERE cust_id='$cust_id'");
        if (function_exists('cust_trans')) {
            cust_trans($cust_id, 'PAYMENT', $credit_pay_id, $tendered);
        }

        return [
            'ok' => true,
            'message' => 'Customer credit payment recorded for ' . get_cust($cust_id) . ' amount ' . number_format($tendered, 2) . '.',
            'status' => 200,
            'cust_id' => $cust_id,
            'credit_pay_id' => $credit_pay_id,
            'print_select_url' => function_exists('kp_print_counter_select_url')
                ? kp_print_counter_select_url('cust_credit_pay', $credit_pay_id, '../cash/index.php')
                : '',
        ];
    }
}

if (!function_exists('cash_api_payment_mpesa_rows')) {
    function cash_api_payment_mpesa_rows() {
        global $connection;
        $pay_id = cash_api_clean_int($_REQUEST['id'] ?? 0);
        $rows = cash_repo_recent_mpesa_rows($connection);
        return [
            'ok' => true,
            'message' => 'Mpesa rows loaded.',
            'status' => 200,
            'html' => cash_api_render_mpesa_table($rows, $pay_id),
        ];
    }
}

if (!function_exists('cash_api_payment_mpesa_search')) {
    function cash_api_payment_mpesa_search() {
        global $connection;
        $pay_id = cash_api_clean_int($_REQUEST['id'] ?? 0);
        $search = cash_api_clean_text($_REQUEST['search'] ?? '');
        $rows = $search === '' ? [] : cash_repo_search_mpesa_rows($connection, $search);
        return [
            'ok' => true,
            'message' => 'Mpesa search loaded.',
            'status' => 200,
            'html' => cash_api_render_mpesa_rows($rows, $pay_id, false),
        ];
    }
}

if (!function_exists('cash_api_payment_submit')) {
    function cash_api_payment_submit() {
        global $connection;

        page_restrict();

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }

        $mode_ids = array_values(array_filter(array_map('intval', cash_api_request_array($_POST['pay-mode'] ?? []))));
        $invoices = cash_api_clean_text($_POST['invoices'] ?? '');
        $balance = cash_api_clean_float($_POST['balance'] ?? 0);
        $sale_amount = cash_api_clean_float($_POST['grand_total'] ?? 0);
        $type = cash_api_clean_int($_POST['type'] ?? 0);
        $tendered = cash_api_clean_float($_POST['tendered'] ?? 0);

        if (empty($mode_ids)) {
            return ['status' => 'warning', 'message' => 'You have to select payment mode.'];
        }
        if ($tendered <= 0) {
            return ['status' => 'warning', 'message' => 'You have to input money given.'];
        }
        if ($tendered < $sale_amount) {
            return ['status' => 'warning', 'message' => 'You have to input money amounting to this order.'];
        }

        ensure_payment_mode_columns($mode_ids);

        $invoice_list = array_values(array_filter(array_map('intval', explode(',', $invoices))));
        $counter = 0;
        if (!empty($invoice_list)) {
            $counter = cash_repo_sale_counter($connection, $invoice_list[0]);
        }
        if ($counter < 1) {
            $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        }

        $extra_columns = [];
        $extra_values = [];
        foreach ($mode_ids as $mode_id) {
            $amount_column = 'mode' . $mode_id;
            $ref_column = 'moderef' . $mode_id;
            $amount_field = 'name' . $mode_id;
            $ref_field = 'ref' . $mode_id;
            $mode_amount = cash_api_clean_float($_POST[$amount_field] ?? 0) - $balance;
            $extra_columns[] = $amount_column;
            $extra_values[] = "'" . mysqli_real_escape_string($connection, number_format($mode_amount, 2, '.', '')) . "'";

            $ref_value = cash_api_clean_text($_POST[$ref_field] ?? '');
            if ($ref_value !== '' && $ref_value !== '0') {
                $extra_columns[] = $ref_column;
                $extra_values[] = "'" . mysqli_real_escape_string($connection, $ref_value) . "'";
            }
        }

        $pay_id = cash_repo_next_pay_id();
        $columns = array_merge(
            ['pay_id', 'sale_id', 'user_id', 'tendered', 'pay_date', 'paid_total', 'type', 'counter'],
            $extra_columns
        );
        $values = array_merge(
            [
                "'" . $pay_id . "'",
                "'" . mysqli_real_escape_string($connection, $invoices) . "'",
                "'" . $user_id . "'",
                "'" . mysqli_real_escape_string($connection, number_format($tendered, 2, '.', '')) . "'",
                "'" . mysqli_real_escape_string($connection, $shift) . "'",
                "'" . mysqli_real_escape_string($connection, number_format($sale_amount, 2, '.', '')) . "'",
                "'" . $type . "'",
                "'" . $counter . "'",
            ],
            $extra_values
        );

        mysqli_begin_transaction($connection);
        try {
            if (!cash_repo_insert_sale_payment($connection, $columns, $values)) {
                throw new Exception('System could not insert payments. Call for assistance.');
            }

            foreach ($invoice_list as $sale_id) {
                $ord_date = (string)get_column('kp_sales', 'shift', "sale_id='$sale_id'");
                $ord_user = (int)get_column('kp_sales', 'user_id', "sale_id='$sale_id'");
                $paid_total = (float)get_column('kp_sales', 'amount', "sale_id='$sale_id'");

                if (!cash_repo_insert_sales_paid($connection, $pay_id, $sale_id, $user_id, $ord_user, $paid_total, $ord_date, $shift)) {
                    throw new Exception('Error creating pay history.');
                }

                if (!cash_repo_update_sale_payment_ord_date($connection, $pay_id, $ord_date)) {
                    throw new Exception('Could not update payment order date.');
                }
            }

            foreach ($mode_ids as $mode_id) {
                $amount = cash_api_clean_float($_POST['name' . $mode_id] ?? 0);
                $accbal = (float)get_column('kp_pay_mode', 'accbal', "pay_id='$mode_id'");

                if (!cash_repo_update_pay_mode_balance($connection, $mode_id, $amount)) {
                    throw new Exception('Could not update payment mode balance.');
                }
                if (!cash_repo_insert_pay_mode_history($connection, $pay_id, $user_id, $shift, $amount, $accbal)) {
                    throw new Exception('Error creating pay history.');
                }
            }

            $gate_codes = array_filter(array_map('trim', explode(',', cash_api_clean_text($_POST['gate'] ?? ''))));
            foreach ($gate_codes as $code) {
                if (!cash_repo_update_split_message_status($connection, $code)) {
                    throw new Exception('Could not update wallet reference.');
                }
            }

            foreach ($invoice_list as $sale_id) {
                if (!cash_repo_mark_sale_paid($connection, $sale_id, $shift)) {
                    throw new Exception('System could not create payment history. Call for assistance.');
                }
            }

        mysqli_commit($connection);
        } catch (Throwable $error) {
            mysqli_rollback($connection);
            if (function_exists('cash_log_event')) {
                cash_log_event('payment_submit_failed', [
                    'user_id' => $user_id,
                    'invoices' => $invoices,
                    'message' => $error->getMessage(),
                ]);
            }
            return ['status' => 'error', 'message' => $error->getMessage()];
        }

        if (function_exists('cash_log_event')) {
            cash_log_event('payment_submit', [
                'user_id' => $user_id,
                'invoices' => $invoices,
                'tendered' => $tendered,
                'sale_amount' => $sale_amount,
                'mode_ids' => $mode_ids,
            ]);
        }

        return [
            'status' => 'success',
            'message' => 'Payment recorded successfully.',
            'reload' => true,
            'invoices' => $invoices,
        ];
    }
}

if (!function_exists('cash_api_payment_confirm_sale')) {
    function cash_api_payment_confirm_sale() {
        return [
            'ok' => true,
            'message' => 'Sale confirmation loaded.',
            'status' => 200,
            'html' => '',
        ];
    }
}

if (!function_exists('cash_api_collect_approve')) {
    function cash_api_collect_approve() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $pin = cash_api_clean_text($_REQUEST['pass'] ?? '');
        $amount = cash_api_clean_float($_REQUEST['amount'] ?? 0);
        $pay_mode_id = cash_api_clean_int($_REQUEST['paid'] ?? 0);
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }

        if ($pay_mode_id < 1 || $amount <= 0) {
            return [
                'status' => 'warning',
                'message' => 'Invalid collection request. Enter a valid amount and try again.'
            ];
        }
        if ($pin === '') {
            return [
                'status' => 'warning',
                'message' => 'Passcode is required for supervisor approval.'
            ];
        }
        if (!front_perm($pin, 8)) {
            return [
                'status' => 'warning',
                'message' => 'Wrong passcode or insufficient approval rights.'
            ];
        }

        $pin_safe = mysqli_real_escape_string($connection, $pin);
        $appr_by = (int)get_column("kp_users", "user_id", "pin='$pin_safe'");
        if ($appr_by < 1) {
            return [
                'status' => 'error',
                'message' => 'Approver account could not be validated.'
            ];
        }

        mysqli_begin_transaction($connection);
        try {
            if (!cash_repo_insert_cash_collection($connection, $pay_mode_id, $user_id, $appr_by, $shift, $amount)) {
                throw new Exception('Failed to record collection history: ' . mysqli_error($connection));
            }
            if (!cash_repo_reduce_pay_mode_balance($connection, $pay_mode_id, $amount)) {
                throw new Exception('Failed to update payment mode balance: ' . mysqli_error($connection));
            }

            mysqli_commit($connection);
        } catch (Throwable $error) {
            mysqli_rollback($connection);
            if (function_exists('cash_log_event')) {
                cash_log_event('collect_approve_failed', [
                    'user_id' => $user_id,
                    'pay_mode_id' => $pay_mode_id,
                    'amount' => $amount,
                    'message' => $error->getMessage(),
                ]);
            }
            return [
                'status' => 'error',
                'message' => $error->getMessage()
            ];
        }

        if (function_exists('cash_log_event')) {
            cash_log_event('collect_approve', [
                'user_id' => $user_id,
                'approver_id' => $appr_by,
                'pay_mode_id' => $pay_mode_id,
                'amount' => $amount,
                'shift' => $shift,
            ]);
        }

        return [
            'status' => 'success',
            'message' => 'Cash collection approved and posted successfully.'
        ];
    }
}

if (!function_exists('cash_api_handover_save')) {
    function cash_api_handover_save() {
        global $connection;

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $counter = cash_api_clean_int($_POST['counter'] ?? 0);
        $to_user = cash_api_clean_int($_POST['to_user'] ?? 0);
        $mode_ids = array_values(array_filter(array_map('intval', cash_api_request_array($_POST['mode_ids'] ?? []))));
        $note = cash_api_clean_text($_POST['note'] ?? '');
        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_handover (
          handover_id INT NOT NULL AUTO_INCREMENT,
          shift_date DATE NOT NULL,
          cntr_id INT NOT NULL DEFAULT 0,
          from_user INT NOT NULL DEFAULT 0,
          to_user INT NOT NULL DEFAULT 0,
          total_system DECIMAL(10,2) NOT NULL DEFAULT 0,
          total_declared DECIMAL(10,2) NOT NULL DEFAULT 0,
          total_variance DECIMAL(10,2) NOT NULL DEFAULT 0,
          status VARCHAR(20) NOT NULL DEFAULT 'MATCH',
          note TEXT NULL,
          created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (handover_id)
        )") or die(mysqli_error($connection));

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_handover_modes (
          handover_mode_id INT NOT NULL AUTO_INCREMENT,
          handover_id INT NOT NULL DEFAULT 0,
          pay_id INT NOT NULL DEFAULT 0,
          system_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
          declared_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
          variance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
          status VARCHAR(20) NOT NULL DEFAULT 'MATCH',
          PRIMARY KEY (handover_mode_id)
        )") or die(mysqli_error($connection));

        if ($user_id < 1) {
            return ['status' => 'error', 'message' => 'Session expired. Please log in again.'];
        }
        if ($counter < 1 || user_counter($user_id) != $counter) {
            return ['status' => 'warning', 'message' => 'This cashier has no active counter to hand over.'];
        }
        if ($to_user < 1 || $to_user === $user_id) {
            return ['status' => 'warning', 'message' => 'Select a valid receiving cashier.'];
        }
        if (!cash_repo_handover_receiver_available($connection, $to_user)) {
            return ['status' => 'warning', 'message' => 'Selected cashier is not available for handover.'];
        }

        $system_total = 0.0;
        $declared_total = 0.0;
        $variance_total = 0.0;
        $rows = [];
        foreach ($mode_ids as $pay_id) {
            $expected = cash_api_clean_float($_POST['expected_' . $pay_id] ?? 0);
            $declared = cash_api_clean_float($_POST['declared_' . $pay_id] ?? 0);
            $variance = $declared - $expected;
            $status = abs($variance) < 0.009 ? 'MATCH' : 'VARIANCE';
            $system_total += $expected;
            $declared_total += $declared;
            $variance_total += $variance;
            $rows[] = [
                'pay_id' => $pay_id,
                'system_amount' => $expected,
                'declared_amount' => $declared,
                'variance_amount' => $variance,
                'status' => $status,
            ];
        }

        $overall_status = abs($variance_total) < 0.009 ? 'MATCH' : 'VARIANCE';

        mysqli_begin_transaction($connection);
        try {
            $handover_id = cash_repo_insert_counter_handover(
                $connection,
                $shift,
                $counter,
                $user_id,
                $to_user,
                $system_total,
                $declared_total,
                $variance_total,
                $overall_status,
                $note
            );
            if ($handover_id < 1) {
                throw new Exception(mysqli_error($connection));
            }

            foreach ($rows as $row) {
                if (!cash_repo_insert_counter_handover_mode(
                    $connection,
                    $handover_id,
                    $row['pay_id'],
                    $row['system_amount'],
                    $row['declared_amount'],
                    $row['variance_amount'],
                    $row['status']
                )) {
                    throw new Exception(mysqli_error($connection));
                }
            }

            if (!cash_repo_handover_release_user_counter($connection, $user_id)) {
                throw new Exception(mysqli_error($connection));
            }
            if (!cash_repo_handover_assign_user_counter($connection, $to_user, $counter)) {
                throw new Exception(mysqli_error($connection));
            }
            if (!cash_repo_handover_update_counter_owner($connection, $counter, $to_user)) {
                throw new Exception(mysqli_error($connection));
            }

            mysqli_commit($connection);
        } catch (Throwable $error) {
            mysqli_rollback($connection);
            if (function_exists('cash_log_event')) {
                cash_log_event('handover_save_failed', [
                    'user_id' => $user_id,
                    'counter' => $counter,
                    'to_user' => $to_user,
                    'message' => $error->getMessage(),
                ]);
            }
            return ['status' => 'error', 'message' => $error->getMessage()];
        }

        $action = "COUNTER HANDOVER|$shift|COUNTER " . get_counter($counter) . "|FROM " . get_user($user_id) . " TO " . get_user($to_user) . "|STATUS $overall_status";
        logs($action, '../../logs');

        if (function_exists('cash_log_event')) {
            cash_log_event('handover_save', [
                'user_id' => $user_id,
                'counter' => $counter,
                'to_user' => $to_user,
                'status' => $overall_status,
                'system_total' => $system_total,
                'declared_total' => $declared_total,
                'variance_total' => $variance_total,
            ]);
        }

        return [
            'status' => 'success',
            'message' => 'Handover saved with ' . $overall_status . ' and counter moved to ' . get_user($to_user) . '.',
            'redirect' => '../log_out.php'
        ];
    }
}

if (!function_exists('cash_api_error_check')) {
    function cash_api_error_check() {
        global $connection;

        $shift = (string)get_column('kp_shift', 'shift', "1");
        if ($shift === '0' || $shift === '') {
            $shift = date('Y-m-d');
        }
        $counter_id = cash_api_clean_int($_REQUEST['id'] ?? 0);
        $mode = cash_api_clean_int($_REQUEST['st'] ?? 1);
        $rows = cash_repo_sales_error_rows($connection, $shift, $counter_id, $mode);

        ob_start();
        ?>
<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>SALE ID</th>
      <th>WAITER</th>
      <th>SALE AMOUNT</th>
      <th>ITEMS AMOUNT</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $index => $row) {
        $sale_id = (int)($row['sale_id'] ?? 0);
        $waiter_id = (int)($row['user_id'] ?? 0);
        if ($mode === 1) {
            $amount = (float)($row['amount'] ?? 0);
            $item_amount = (float)column_total('kp_sale_items', 'total', "sale_id='$sale_id'");
        } else {
            $amount = (float)column_total('kp_sales', 'amount', "sale_id='$sale_id'");
            $item_amount = (float)($row['total'] ?? 0);
        }
        ?>
      <tr>
        <td><b><?php echo $index + 1; ?></b></td>
        <td><b><?php echo $sale_id; ?></b></td>
        <td><b><?php echo htmlspecialchars(get_column('kp_users', 'username', "user_id='$waiter_id'"), ENT_QUOTES, 'UTF-8'); ?></b></td>
        <td><b><?php echo $amount; ?></b></td>
        <td><b><?php echo $item_amount; ?></b></td>
      </tr>
    <?php } ?>
  </tbody>
</table>
        <?php

        return [
            'ok' => true,
            'message' => 'Error check loaded.',
            'status' => 200,
            'html' => ob_get_clean(),
        ];
    }
}

if (!function_exists('cash_api_load_panel')) {
    function cash_api_load_panel($panel) {
        $map = cash_api_panel_map();
        if (!isset($map[$panel])) {
            return ['ok' => false, 'message' => 'Unknown panel.', 'status' => 404];
        }

        if (strpos($map[$panel], '__service__:') === 0) {
            return cash_api_render_service_panel(substr($map[$panel], strlen('__service__:')));
        }
        if (!is_file($map[$panel])) {
            return ['ok' => false, 'message' => 'Unknown panel.', 'status' => 404];
        }

        return [
            'ok' => true,
            'message' => 'Panel loaded.',
            'status' => 200,
            'html' => cash_api_capture_include($map[$panel]),
        ];
    }
}

if (!function_exists('cash_api_render_service_panel')) {
    function cash_api_render_service_panel($panel) {
        switch ($panel) {
            case 'cashbox':
                return cash_api_render_cashbox_panel();
            case 'kpesa':
                return cash_api_render_kpesa_panel();
            case 'clearb':
            case 'clear_bill_mode':
                return [
                    'ok' => true,
                    'message' => 'Panel loaded.',
                    'status' => 200,
                    'html' => cash_api_render_clearb_panel(),
                ];
            case 'clearp':
                return [
                    'ok' => true,
                    'message' => 'Panel loaded.',
                    'status' => 200,
                    'html' => cash_api_render_clearp_panel(),
                ];
            case 'clearsta':
            case 'clear_waiter_mode':
                return [
                    'ok' => true,
                    'message' => 'Panel loaded.',
                    'status' => 200,
                    'html' => cash_api_render_clearsta_panel(),
                ];
            default:
                return ['ok' => false, 'message' => 'Unknown panel.', 'status' => 404];
        }
    }
}

if (!function_exists('cash_api_render_cashbox_panel')) {
    function cash_api_render_cashbox_panel() {
        global $connection;
        $shift = get_column('kp_shift', 'shift', "1");
        if ($shift == 0) {
            $shift = date('Y-m-d');
        }
        $purchase = cash_repo_all_pay_modes($connection);
        $today_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift' AND ord_date='$shift'");
        $previous_paid = (float)column_total('kp_sales_paid', 'paid_total', "ord_date!=pay_date AND pay_date='$shift'");
        $total_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift'");

        ob_start();
        ?>
<style type="text/css">
  .cashbox-shell { border:1px solid #eadfce; border-radius:18px; background:#fffdf9; overflow:hidden; }
  .cashbox-head { padding:16px 18px; border-bottom:1px solid #efe2d3; background:linear-gradient(135deg,#fff5e8 0%,#fffdfa 100%); }
  .cashbox-head h3 { margin:0 0 4px; color:#744416; font-weight:900; }
  .cashbox-head p { margin:0; color:#816c57; }
  .cashbox-body { padding:16px 18px 18px; }
  .cashbox-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-bottom:14px; }
  .cashbox-stat { border:1px solid #eee2d6; border-radius:14px; padding:12px; background:#fff; }
  .cashbox-stat strong,.cashbox-stat span { display:block; }
  .cashbox-stat strong { color:#9c6b33; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
  .cashbox-stat span { margin-top:6px; color:#2d2419; font-size:24px; font-weight:900; }
  .cashbox-table-wrap { max-height:520px; overflow:auto; border-radius:16px; border:1px solid #eee2d6; background:#fff; }
  .cashbox-table { margin:0; }
  .cashbox-table th { position:sticky; top:0; z-index:1; background:#fff7ec; color:#5c4330; }
  @media (max-width:767px) { .cashbox-stats { grid-template-columns:1fr; } }
</style>
<div class="cashbox-shell">
  <div class="cashbox-head"><h3>Cash Box</h3><p>Today's payment dashboard by payment mode for shift <?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?>.</p></div>
  <div class="cashbox-body">
    <div class="cashbox-stats">
      <div class="cashbox-stat"><strong>Today's Paid</strong><span><?php echo number_format($today_paid, 2); ?></span></div>
      <div class="cashbox-stat"><strong>Previous Bills Paid</strong><span><?php echo number_format($previous_paid, 2); ?></span></div>
      <div class="cashbox-stat"><strong>Total Paid</strong><span><?php echo number_format($total_paid, 2); ?></span></div>
    </div>
    <div class="cashbox-table-wrap">
      <table class="table table-striped cashbox-table">
        <thead><tr><th>#</th><th>Action</th><th>Mode</th><th class="text-right">Today</th><th class="text-right">Previous</th><th class="text-right">Total</th><th class="text-right">Balance</th></tr></thead>
        <tbody>
          <?php $no = 0; if (count($purchase) < 1) { echo "<tr><td colspan='7'>No payment modes found.</td></tr>"; } else { foreach ($purchase as $pow) { $pay_id = (int)$pow['pay_id']; $name = $pow['name']; $has_colle = (int)$pow['has_colle']; $accbal = $pow['accbal']; $col = "mode" . $pay_id; $amount = (float)column_total('kp_sale_payment', $col, "pay_date='$shift' AND ord_date='$shift'"); $prevamount = (float)column_total('kp_sale_payment', $col, "pay_date='$shift' AND ord_date!='$shift'"); $total = (float)column_total('kp_sale_payment', $col, "pay_date='$shift'"); $no++; ?>
            <tr>
              <td><strong><?php echo $no; ?></strong></td>
              <td><?php if ($has_colle == 1) { ?><button id="colle" class="btn btn-xs btn-outline-primary" data-id="<?php echo $pay_id; ?>">Collect</button><?php } else { ?><span class="text-muted">-</span><?php } ?></td>
              <td><strong><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></strong></td>
              <td class="text-right"><?php echo number_format($amount, 2); ?></td>
              <td class="text-right"><?php echo number_format($prevamount, 2); ?></td>
              <td class="text-right"><?php echo number_format($total, 2); ?></td>
              <td class="text-right"><?php echo number_format((float)$accbal, 2); ?></td>
            </tr>
          <?php }} ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
        <?php
        return ['ok' => true, 'message' => 'Panel loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('cash_api_render_clearb_panel')) {
    function cash_api_render_clearb_panel() {
        ob_start();
        require __DIR__ . '/../clrbill/clearb.php';
        return ob_get_clean();
    }
}

if (!function_exists('cash_api_render_clearp_panel')) {
    function cash_api_render_clearp_panel() {
        ob_start();
        require __DIR__ . '/../clrbill/clearp.php';
        return ob_get_clean();
    }
}

if (!function_exists('cash_api_render_clearsta_panel')) {
    function cash_api_render_clearsta_panel() {
        ob_start();
        require __DIR__ . '/../clrstaff/clearsta.php';
        return ob_get_clean();
    }
}

if (!function_exists('cash_api_render_kpesa_panel')) {
    function cash_api_render_kpesa_panel() {
        global $connection;
        $unapproved = cash_repo_kpesa_unapproved_rows($connection, 120);
        $unsplit = cash_repo_kpesa_unsplit_rows($connection, 120);
        $unapproved_count = (int)column_count('kp_split_message','id',"claim_status=1 AND assign_status=2");
        $unsplit_count = (int)column_count('mpesa_message','id',"split_status=0");

        ob_start();
        ?>
<style type="text/css">
  .kpesa-shell { border:1px solid #eadfce; border-radius:18px; background:#fffdf9; overflow:hidden; }
  .kpesa-head { padding:16px 18px; border-bottom:1px solid #efe2d3; background:linear-gradient(135deg,#eef7ff 0%,#fffdfa 100%); }
  .kpesa-head h3 { margin:0 0 4px; color:#154a73; font-weight:900; }
  .kpesa-head p { margin:0; color:#64748b; }
  .kpesa-body { padding:16px 18px 18px; }
  .kpesa-stats { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin-bottom:14px; }
  .kpesa-stat { border:1px solid #dbe6f2; border-radius:14px; padding:12px; background:#fff; }
  .kpesa-stat strong,.kpesa-stat span { display:block; }
  .kpesa-stat strong { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5d7992; }
  .kpesa-stat span { margin-top:6px; font-size:24px; font-weight:900; color:#12344d; }
  .kpesa-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:14px; }
  .kpesa-card { border:1px solid #dbe6f2; border-radius:16px; overflow:hidden; background:#fff; }
  .kpesa-card-head { padding:12px 14px; background:#f5faff; border-bottom:1px solid #e3edf7; }
  .kpesa-card-head h4 { margin:0 0 3px; color:#1e4f72; font-size:15px; font-weight:900; }
  .kpesa-card-head p { margin:0; color:#6b8194; font-size:12px; }
  .kpesa-table-wrap { max-height:520px; overflow:auto; }
  .kpesa-table { margin:0; }
  .kpesa-table th { position:sticky; top:0; background:#fbfdff; z-index:1; }
  .kpesa-empty { padding:18px 14px; color:#6a7f90; }
  @media (max-width:991px) { .kpesa-grid,.kpesa-stats { grid-template-columns:1fr; } }
</style>
<div class="kpesa-shell">
  <div class="kpesa-head"><h3>KPesa Desk</h3><p>Approve claimed split messages and split new M-Pesa messages from one place.</p></div>
  <div class="kpesa-body">
    <div class="kpesa-stats">
      <div class="kpesa-stat"><strong>Split Unapproved</strong><span><?php echo $unapproved_count; ?></span></div>
      <div class="kpesa-stat"><strong>Unsplit Messages</strong><span><?php echo $unsplit_count; ?></span></div>
    </div>
    <div class="kpesa-grid">
      <div class="kpesa-card">
        <div class="kpesa-card-head"><h4>Split Unapproved</h4><p>Approve waiter claims that are waiting.</p></div>
        <div class="kpesa-table-wrap">
          <?php if (count($unapproved) < 1) { ?><div class="kpesa-empty">No split approvals are waiting.</div><?php } else { ?>
            <table class="table table-striped kpesa-table">
              <thead><tr><th>Action</th><th>Waiter</th><th>Code</th><th class="text-right">Amount</th><th>Time</th><th>Name</th></tr></thead>
              <tbody>
              <?php foreach ($unapproved as $pow) { $claim_staff = (int)$pow['claim_staff']; ?>
                <tr>
                  <td><button class="btn btn-xs btn-lime" id="approve" data-id="<?php echo (int)$pow['id']; ?>">Approve</button></td>
                  <td><strong><?php echo htmlspecialchars(get_column('kp_users', 'username', "user_id='$claim_staff'"), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                  <td><?php echo htmlspecialchars($pow['code'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td class="text-right"><?php echo number_format((float)$pow['amount'], 2); ?></td>
                  <td><?php echo htmlspecialchars($pow['time'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($pow['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          <?php } ?>
        </div>
      </div>
      <div class="kpesa-card">
        <div class="kpesa-card-head"><h4>Unsplit Messages</h4><p>Split incoming M-Pesa messages and assign them.</p></div>
        <div class="kpesa-table-wrap">
          <?php if (count($unsplit) < 1) { ?><div class="kpesa-empty">No unsplit M-Pesa messages found.</div><?php } else { ?>
            <table class="table table-striped kpesa-table">
              <thead><tr><th>Message</th><th>Action</th></tr></thead>
              <tbody>
              <?php foreach ($unsplit as $pow) { ?>
                <tr>
                  <td style="min-width:260px;"><?php echo htmlspecialchars($pow['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><button class="btn btn-sm btn-outline-info" id="split" data-id="<?php echo (int)$pow['id']; ?>">Split Message</button></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>
        <?php
        return ['ok' => true, 'message' => 'Panel loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('cash_api_render_mpesa_rows')) {
    function cash_api_render_mpesa_rows($rows, $pay_id, $with_wrapper = true) {
        ob_start();
        if ($with_wrapper) {
            ?>
<table id="data-table-combine" class="table table-striped table-bordered">
  <thead>
    <tr>
      <th colspan="6">
        <input type="text" class="form-control pull-right" id="mpesaphone" placeholder="ENTER PHONE NUMBER TO SEARCH" style="width: 100%;" onkeyup="mpesasearch(<?php echo (int)$pay_id; ?>)">
      </th>
    </tr>
    <tr style="padding: 3px;">
      <th>Select</th>
      <th>Code.</th>
      <th>Name.</th>
      <th>Amount</th>
      <th>Date</th>
      <th>Time</th>
    </tr>
  </thead>
  <tbody id="mpesarows">
            <?php
        }

        foreach ($rows as $row) {
            $code = htmlspecialchars((string)($row['code'] ?? ''), ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $amount = number_format((float)($row['amount'] ?? 0), 2);
            $rawAmount = htmlspecialchars((string)($row['amount'] ?? 0), ENT_QUOTES, 'UTF-8');
            $date = htmlspecialchars((string)($row['date'] ?? ''), ENT_QUOTES, 'UTF-8');
            $time = htmlspecialchars((string)($row['time'] ?? ''), ENT_QUOTES, 'UTF-8');
            ?>
    <tr>
      <td>
        <input type="checkbox" name="pay-sale[]" onclick="sel<?php echo (int)$pay_id; ?>()" value="<?php echo $rawAmount; ?>" data-value1="<?php echo $code; ?>" id="r<?php echo $code; ?>" class="bulk2" />
        <label for="r<?php echo $code; ?>" style="font-weight:bold;color:#FFFFFF !important;font-size:12px;background:#000;border:2px solid #e0bd65;padding:3px;border-radius:5px;">&nbsp; SELECT</label>
      </td>
      <td><?php echo $code; ?></td>
      <td><?php echo $name; ?></td>
      <td><?php echo $amount; ?></td>
      <td><?php echo $date; ?></td>
      <td><?php echo $time; ?></td>
    </tr>
            <?php
        }

        if (empty($rows)) {
            ?>
    <tr>
      <td colspan="6" class="text-center text-muted">No MPesa transactions found.</td>
    </tr>
            <?php
        }

        if ($with_wrapper) {
            ?>
  </tbody>
</table>
            <?php
        }

        return ob_get_clean();
    }
}

if (!function_exists('cash_api_render_mpesa_table')) {
    function cash_api_render_mpesa_table($rows, $pay_id) {
        return cash_api_render_mpesa_rows($rows, $pay_id, true);
    }
}

if (!function_exists('cash_api_load_modal')) {
    function cash_api_load_modal($modal) {
        $map = cash_api_modal_map();
        if (!isset($map[$modal]) || !is_file($map[$modal])) {
            return ['ok' => false, 'message' => 'Unknown modal.', 'status' => 404];
        }

        return [
            'ok' => true,
            'message' => 'Modal loaded.',
            'status' => 200,
            'html' => cash_api_capture_include($map[$modal]),
        ];
    }
}
