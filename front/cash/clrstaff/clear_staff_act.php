<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

function ensure_staff_clearance_support($connection) {
    $splitStatus = mysqli_query($connection, "SHOW COLUMNS FROM kp_split_message LIKE 'clear_status'") or die(mysqli_error($connection));
    if (mysqli_num_rows($splitStatus) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_split_message ADD COLUMN clear_status INT NOT NULL DEFAULT 2") or die(mysqli_error($connection));
    }

    $pdqStatus = mysqli_query($connection, "SHOW COLUMNS FROM kp_user_pdq LIKE 'clear_status'") or die(mysqli_error($connection));
    if (mysqli_num_rows($pdqStatus) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_user_pdq ADD COLUMN clear_status INT NOT NULL DEFAULT 2") or die(mysqli_error($connection));
    }
}

function ensure_pay_mode_default_columns($connection) {
    $default_columns = [
        'is_default_cash',
        'is_default_mpesa',
        'is_default_card',
        'is_default_complimentary'
    ];
    foreach ($default_columns as $column) {
        $c = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE '$column'") or die(mysqli_error($connection));
        if (mysqli_num_rows($c) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN $column INT NOT NULL DEFAULT 2") or die(mysqli_error($connection));
        }
    }
}

function ensure_staff_clearances_table($connection) {
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_staff_clearances (
        clearance_id INT NOT NULL AUTO_INCREMENT,
        staff_user_id INT NOT NULL DEFAULT 0,
        cleared_by INT NOT NULL DEFAULT 0,
        shift_date DATE NOT NULL,
        total_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
        paid_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
        unpaid_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
        credit_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
        mpesa_total DECIMAL(10,2) NOT NULL DEFAULT 0,
        pdq_total DECIMAL(10,2) NOT NULL DEFAULT 0,
        expected_cash DECIMAL(10,2) NOT NULL DEFAULT 0,
        actual_cash DECIMAL(10,2) NOT NULL DEFAULT 0,
        variance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        variance_type VARCHAR(20) NOT NULL DEFAULT '',
        snapshot_json LONGTEXT NULL,
        note TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (clearance_id),
        KEY idx_staff_shift (staff_user_id, shift_date)
    )") or die(mysqli_error($connection));
}

function ensure_payment_mode_columns_for_clearance($connection, array $pay_modes) {
    foreach ($pay_modes as $mode_id) {
        $mode_id = preg_replace('/[^A-Za-z0-9_]/', '', (string)$mode_id);
        if ($mode_id === '') {
            continue;
        }
        $amount_col = "mode" . $mode_id;
        $ref_col = "moderef" . $mode_id;

        $check_amount = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE '$amount_col'") or die(mysqli_error($connection));
        if (mysqli_num_rows($check_amount) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN $amount_col DECIMAL(10,2) NOT NULL DEFAULT 0") or die(mysqli_error($connection));
        }

        $check_ref = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE '$ref_col'") or die(mysqli_error($connection));
        if (mysqli_num_rows($check_ref) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN $ref_col VARCHAR(120) NOT NULL DEFAULT ''") or die(mysqli_error($connection));
        }
    }

    $check_counter = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE 'counter'") or die(mysqli_error($connection));
    if (mysqli_num_rows($check_counter) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN counter INT NOT NULL DEFAULT 0") or die(mysqli_error($connection));
    }

    $check_ord_date = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE 'ord_date'") or die(mysqli_error($connection));
    if (mysqli_num_rows($check_ord_date) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN ord_date DATE NULL") or die(mysqli_error($connection));
    }
}

function get_clearance_pay_mode_ids($connection) {
    $map = ['cash' => 0, 'mpesa' => 0, 'pdq' => 0, 'complimentary' => 0];
    $r = mysqli_query($connection, "SELECT pay_id, name, is_default_cash, is_default_mpesa, is_default_card, is_default_complimentary FROM kp_pay_mode WHERE 1 ORDER BY pay_id ASC") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($r)) {
        $id = (int)$row['pay_id'];
        $name = strtoupper(trim((string)$row['name']));
        if ($map['cash'] === 0 && ((int)($row['is_default_cash'] ?? 2) === 1 || strpos($name, 'CASH') !== false)) {
            $map['cash'] = $id;
        }
        if ($map['mpesa'] === 0 && ((int)($row['is_default_mpesa'] ?? 2) === 1 || strpos($name, 'MPESA') !== false)) {
            $map['mpesa'] = $id;
        }
        if ($map['pdq'] === 0 && ((int)($row['is_default_card'] ?? 2) === 1 || strpos($name, 'PDQ') !== false || strpos($name, 'CARD') !== false)) {
            $map['pdq'] = $id;
        }
        if ($map['complimentary'] === 0 && ((int)($row['is_default_complimentary'] ?? 2) === 1 || strpos($name, 'COMP') !== false)) {
            $map['complimentary'] = $id;
        }
    }
    return $map;
}

function next_clearance_pay_id($connection) {
    if (function_exists('pay_id')) {
        return (int)pay_id();
    }
    $r = mysqli_query($connection, "SELECT COALESCE(MAX(pay_id),0) AS last_id FROM kp_sale_payment") or die(mysqli_error($connection));
    $row = mysqli_fetch_assoc($r);
    return ((int)$row['last_id']) + 1;
}

$staff_user_id = (int)($_POST['staff_user_id'] ?? 0);
$cleared_by = (int)($_SESSION['user_id'] ?? 0);
$shift = trim($_POST['shift'] ?? '');
$total_sales = (float)($_POST['total_sales'] ?? 0);
$paid_sales = (float)($_POST['paid_sales'] ?? 0);
$unpaid_sales = (float)($_POST['unpaid_sales'] ?? 0);
$credit_sales = (float)($_POST['credit_sales'] ?? 0);
$mpesa_total = (float)($_POST['mpesa_total'] ?? 0);
$mpesa_count = (int)($_POST['mpesa_count'] ?? 0);
$pdq_total = (float)($_POST['pdq_total'] ?? 0);
$pdq_count = (int)($_POST['pdq_count'] ?? 0);
$kpesa_active = isset($_POST['kpesa_active']) ? (int)$_POST['kpesa_active'] : 1;
$manual_mpesa_total = (float)($_POST['manual_mpesa_total'] ?? 0);
$manual_pdq_total = (float)($_POST['manual_pdq_total'] ?? 0);
$collectible_sales = (float)($_POST['collectible_sales'] ?? 0);
$manual_seed_mpesa_total = (float)($_POST['manual_seed_mpesa_total'] ?? 0);
$manual_seed_pdq_total = (float)($_POST['manual_seed_pdq_total'] ?? 0);
$manual_seed_cash_total = (float)($_POST['manual_seed_cash_total'] ?? 0);
$complimentary_total = (float)($_POST['complimentary_total'] ?? 0);
$expected_cash = (float)($_POST['expected_cash'] ?? 0);
$actual_cash = (float)($_POST['actual_cash'] ?? 0);
$note = trim($_POST['note'] ?? '');

if ($staff_user_id < 1 || $shift === '') {
    ?>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
          <div class="alert alert-warning" style="margin-bottom:0;">Missing staff clearance details.</div>
        </div>
      </div>
    </div>
    <?php
    exit;
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

$shift_esc = mysqli_real_escape_string($connection, $shift);
$variance = $actual_cash - $expected_cash;
$variance_type = 'balanced';
if ($variance < 0) {
    $variance_type = 'short';
} elseif ($variance > 0) {
    $variance_type = 'excess';
}

ensure_staff_clearance_support($connection);
ensure_pay_mode_default_columns($connection);
ensure_staff_clearances_table($connection);

$existing_clearance_q = mysqli_query(
    $connection,
    "SELECT * FROM kp_staff_clearances WHERE staff_user_id='$staff_user_id' AND shift_date='$shift_esc' ORDER BY clearance_id DESC LIMIT 1"
) or die(mysqli_error($connection));
$existing_clearance = $existing_clearance_q ? mysqli_fetch_assoc($existing_clearance_q) : null;

if ($existing_clearance) {
    $cleared_at = (string)($existing_clearance['created_at'] ?? '');
    $cleared_by_name = '';
    if (!empty($existing_clearance['cleared_by'])) {
        $cleared_by_name = get_user((int)$existing_clearance['cleared_by']);
    }
    ?>
    <div class="modal-dialog">
      <div class="modal-content" style="border-radius:18px; overflow:hidden;">
        <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
          <h4 class="modal-title" style="font-weight:900; color:#7a491b;">Already Cleared</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="background:#fffdf9;">
          <div class="alert alert-warning" style="margin-bottom:12px;">
            <strong><?php echo htmlspecialchars(get_user($staff_user_id)); ?></strong> is already cleared for shift <strong><?php echo htmlspecialchars($shift); ?></strong>.
          </div>
          <p style="margin:0 0 8px;"><strong>Cleared By:</strong> <?php echo htmlspecialchars($cleared_by_name ?: 'System'); ?></p>
          <p style="margin:0;"><strong>Cleared At:</strong> <?php echo htmlspecialchars($cleared_at); ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      if (typeof cashLoadStaffSummary === 'function') {
        cashLoadStaffSummary(<?php echo $staff_user_id; ?>, 'previous');
      }
    </script>
    <?php
    exit;
}

$unpaid_sales_q = mysqli_query($connection, "SELECT sale_id, amount, shift, counter FROM kp_sales WHERE user_id='$staff_user_id' AND pay_status=2 ORDER BY sale_id ASC") or die(mysqli_error($connection));
$unpaid_sale_ids = [];
$unpaid_sale_total = 0.0;
$unpaid_ord_dates = [];
$clear_counter = 0;
while ($sale = mysqli_fetch_assoc($unpaid_sales_q)) {
    $unpaid_sale_ids[] = (int)$sale['sale_id'];
    $unpaid_sale_total += (float)$sale['amount'];
    $unpaid_ord_dates[] = $sale['shift'];
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

$payment_mode_ids = get_clearance_pay_mode_ids($connection);
$active_mode_ids = [];
if ($payment_mode_ids['cash'] > 0 && $new_cash_amount > 0) {
    $active_mode_ids[] = $payment_mode_ids['cash'];
}
if ($payment_mode_ids['mpesa'] > 0 && $new_mpesa_amount > 0) {
    $active_mode_ids[] = $payment_mode_ids['mpesa'];
}
if ($payment_mode_ids['pdq'] > 0 && $new_pdq_amount > 0) {
    $active_mode_ids[] = $payment_mode_ids['pdq'];
}
if ($payment_mode_ids['complimentary'] > 0 && $new_complimentary_amount > 0) {
    $active_mode_ids[] = $payment_mode_ids['complimentary'];
}
if (count($active_mode_ids) > 0) {
    ensure_payment_mode_columns_for_clearance($connection, $active_mode_ids);
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
    'cleared_sales_total' => $unpaid_sale_total,
    'variance_amount' => $variance,
    'variance_type' => $variance_type,
    'note' => $note,
    'cleared_by' => $cleared_by,
    'cleared_by_name' => $cleared_by ? get_user($cleared_by) : ''
];

$note_esc = mysqli_real_escape_string($connection, $note);
$snapshot_esc = mysqli_real_escape_string($connection, json_encode($snapshot));

mysqli_query(
    $connection,
    "INSERT INTO kp_staff_clearances(
        staff_user_id, cleared_by, shift_date, total_sales, paid_sales, unpaid_sales, credit_sales,
        mpesa_total, pdq_total, expected_cash, actual_cash, variance_amount, variance_type, snapshot_json, note
     ) VALUES(
        '$staff_user_id', '$cleared_by', '$shift_esc', '$total_sales', '$paid_sales', '$unpaid_sales', '$credit_sales',
        '$mpesa_total', '$pdq_total', '$expected_cash', '$actual_cash', '$variance', '$variance_type', '$snapshot_esc', '$note_esc'
     )"
) or die(mysqli_error($connection));

if (count($unpaid_sale_ids) > 0) {
    if ($new_payment_total > 0 && count($active_mode_ids) > 0) {
        $pay_id = next_clearance_pay_id($connection);
        $ord_date = count($unpaid_ord_dates) > 0 ? reset($unpaid_ord_dates) : $shift;
        $fields = ['pay_id','sale_id','user_id','tendered','pay_date','paid_total','type','counter','ord_date'];
        $values = [
            "'$pay_id'",
            "'" . mysqli_real_escape_string($connection, $invoice_string) . "'",
            "'$cleared_by'",
            "'" . number_format($new_payment_total, 2, '.', '') . "'",
            "'$shift_esc'",
            "'" . number_format($new_payment_total, 2, '.', '') . "'",
            "'2'",
            "'" . (int)$clear_counter . "'",
            "'" . mysqli_real_escape_string($connection, $ord_date) . "'"
        ];

        if ($payment_mode_ids['cash'] > 0 && $new_cash_amount > 0) {
            $fields[] = 'mode' . $payment_mode_ids['cash'];
            $values[] = "'" . number_format($new_cash_amount, 2, '.', '') . "'";
        }
        if ($payment_mode_ids['mpesa'] > 0 && $new_mpesa_amount > 0) {
            $fields[] = 'mode' . $payment_mode_ids['mpesa'];
            $values[] = "'" . number_format($new_mpesa_amount, 2, '.', '') . "'";
        }
        if ($payment_mode_ids['pdq'] > 0 && $new_pdq_amount > 0) {
            $fields[] = 'mode' . $payment_mode_ids['pdq'];
            $values[] = "'" . number_format($new_pdq_amount, 2, '.', '') . "'";
        }
        if ($payment_mode_ids['complimentary'] > 0 && $new_complimentary_amount > 0) {
            $fields[] = 'mode' . $payment_mode_ids['complimentary'];
            $values[] = "'" . number_format($new_complimentary_amount, 2, '.', '') . "'";
        }

        mysqli_query($connection, "INSERT INTO kp_sale_payment (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")") or die(mysqli_error($connection));

        foreach ($unpaid_sale_ids as $sale_id) {
            $sale_id = (int)$sale_id;
            $sale_ord_date = mysqli_real_escape_string($connection, (string)get_column('kp_sales', 'shift', "sale_id='$sale_id'"));
            $sale_amount = (float)get_column('kp_sales', 'amount', "sale_id='$sale_id'");
            mysqli_query(
                $connection,
                "INSERT INTO kp_sales_paid(pay_id, sale_id, user_id, ord_user, paid_total, ord_date, pay_date)
                 VALUES('$pay_id','$sale_id','$cleared_by','$staff_user_id','$sale_amount','$sale_ord_date','$shift_esc')"
            ) or die(mysqli_error($connection));
        }

        if ($payment_mode_ids['cash'] > 0 && $new_cash_amount > 0) {
            $accbal = get_column('kp_pay_mode', 'accbal', "pay_id='" . $payment_mode_ids['cash'] . "'");
            mysqli_query($connection, "UPDATE kp_pay_mode SET accbal=accbal+'$new_cash_amount' WHERE pay_id='" . $payment_mode_ids['cash'] . "'") or die(mysqli_error($connection));
            mysqli_query($connection, "INSERT INTO kp_pay_mode_hist(pay_id,user_id,shift,day,amount,accbal) VALUES('" . $payment_mode_ids['cash'] . "','$cleared_by','$shift_esc',CURRENT_DATE,'$new_cash_amount','$accbal')") or die(mysqli_error($connection));
        }
        if ($payment_mode_ids['mpesa'] > 0 && $new_mpesa_amount > 0) {
            $accbal = get_column('kp_pay_mode', 'accbal', "pay_id='" . $payment_mode_ids['mpesa'] . "'");
            mysqli_query($connection, "UPDATE kp_pay_mode SET accbal=accbal+'$new_mpesa_amount' WHERE pay_id='" . $payment_mode_ids['mpesa'] . "'") or die(mysqli_error($connection));
            mysqli_query($connection, "INSERT INTO kp_pay_mode_hist(pay_id,user_id,shift,day,amount,accbal) VALUES('" . $payment_mode_ids['mpesa'] . "','$cleared_by','$shift_esc',CURRENT_DATE,'$new_mpesa_amount','$accbal')") or die(mysqli_error($connection));
        }
        if ($payment_mode_ids['pdq'] > 0 && $new_pdq_amount > 0) {
            $accbal = get_column('kp_pay_mode', 'accbal', "pay_id='" . $payment_mode_ids['pdq'] . "'");
            mysqli_query($connection, "UPDATE kp_pay_mode SET accbal=accbal+'$new_pdq_amount' WHERE pay_id='" . $payment_mode_ids['pdq'] . "'") or die(mysqli_error($connection));
            mysqli_query($connection, "INSERT INTO kp_pay_mode_hist(pay_id,user_id,shift,day,amount,accbal) VALUES('" . $payment_mode_ids['pdq'] . "','$cleared_by','$shift_esc',CURRENT_DATE,'$new_pdq_amount','$accbal')") or die(mysqli_error($connection));
        }
        if ($payment_mode_ids['complimentary'] > 0 && $new_complimentary_amount > 0) {
            $accbal = get_column('kp_pay_mode', 'accbal', "pay_id='" . $payment_mode_ids['complimentary'] . "'");
            mysqli_query($connection, "UPDATE kp_pay_mode SET accbal=accbal+'$new_complimentary_amount' WHERE pay_id='" . $payment_mode_ids['complimentary'] . "'") or die(mysqli_error($connection));
            mysqli_query($connection, "INSERT INTO kp_pay_mode_hist(pay_id,user_id,shift,day,amount,accbal) VALUES('" . $payment_mode_ids['complimentary'] . "','$cleared_by','$shift_esc',CURRENT_DATE,'$new_complimentary_amount','$accbal')") or die(mysqli_error($connection));
        }
    }

    mysqli_query($connection, "UPDATE kp_sales SET pay_status=1 WHERE user_id='$staff_user_id' AND pay_status=2") or die(mysqli_error($connection));
}

if ($kpesa_active === 1) {
    mysqli_query($connection, "UPDATE kp_split_message SET clear_status=1 WHERE clear_status=2 AND claim_staff='$staff_user_id'") or die(mysqli_error($connection));
    mysqli_query($connection, "UPDATE kp_user_pdq SET clear_status=1 WHERE clear_status=2 AND user_id='$staff_user_id'") or die(mysqli_error($connection));
}
?>
<div class="modal-dialog">
  <div class="modal-content" style="border-radius:18px; overflow:hidden;">
    <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
      <h4 class="modal-title" style="font-weight:900; color:#7a491b;">Staff Cleared</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body" style="background:#fffdf9;">
      <div class="alert alert-success" style="margin-bottom:12px;">
        Staff clearance saved successfully for <strong><?php echo htmlspecialchars(get_user($staff_user_id)); ?></strong>.
      </div>
      <p style="margin:0 0 8px;"><strong>Unpaid Sales Cleared:</strong> <?php echo count($unpaid_sale_ids); ?> bill(s)</p>
      <p style="margin:0 0 8px;"><strong>New Payment Posted:</strong> <?php echo number_format($new_payment_total, 2); ?></p>
      <p style="margin:0 0 8px;"><strong>Expected Cash:</strong> <?php echo number_format($expected_cash, 2); ?></p>
      <p style="margin:0 0 8px;"><strong>Actual Cash:</strong> <?php echo number_format($actual_cash, 2); ?></p>
      <p style="margin:0;"><strong>Variance:</strong> <?php echo number_format($variance, 2); ?> (<?php echo strtoupper($variance_type); ?>)</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-warning" data-dismiss="modal">Done</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  if (typeof cashLoadStaffSummary === 'function') {
    cashLoadStaffSummary(<?php echo $staff_user_id; ?>, 'previous');
  }
</script>
