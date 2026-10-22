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

function get_staff_manual_collection_seed($connection, $staff_user_id, $shift) {
    $response = ['mpesa_total' => 0.0, 'pdq_total' => 0.0, 'complimentary_total' => 0.0];
    $modes_q = mysqli_query($connection, "SELECT pay_id, is_default_mpesa, is_default_card, is_default_complimentary FROM kp_pay_mode WHERE 1") or die(mysqli_error($connection));
    $mpesa_modes = [];
    $pdq_modes = [];
    $complimentary_modes = [];

    while ($mode = mysqli_fetch_assoc($modes_q)) {
        $mode_id = (int)$mode['pay_id'];
        if ($mode_id < 1) {
            continue;
        }
        $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE 'mode{$mode_id}'") or die(mysqli_error($connection));
        if (!$col_chk || mysqli_num_rows($col_chk) < 1) {
            continue;
        }
        if ((int)($mode['is_default_mpesa'] ?? 2) === 1) {
            $mpesa_modes[] = $mode_id;
        }
        if ((int)($mode['is_default_card'] ?? 2) === 1) {
            $pdq_modes[] = $mode_id;
        }
        if ((int)($mode['is_default_complimentary'] ?? 2) === 1) {
            $complimentary_modes[] = $mode_id;
        }
    }

    $mode_cols = array_unique(array_merge($mpesa_modes, $pdq_modes, $complimentary_modes));
    if (count($mode_cols) < 1) {
        return $response;
    }

    $select_cols = [];
    foreach ($mode_cols as $mode_id) {
        $select_cols[] = "p.mode{$mode_id}";
    }
    $select_sql = implode(', ', $select_cols);
    $staff_user_id = (int)$staff_user_id;
    $shift_esc = mysqli_real_escape_string($connection, $shift);

    $sql = "
        SELECT
            s.pay_id,
            SUM(CASE WHEN s.ord_user='$staff_user_id' AND s.pay_date='$shift_esc' THEN s.paid_total ELSE 0 END) AS staff_paid_total,
            SUM(CASE WHEN s.pay_date='$shift_esc' THEN s.paid_total ELSE 0 END) AS pay_alloc_total,
            $select_sql
        FROM kp_sales_paid s
        INNER JOIN kp_sale_payment p ON p.pay_id=s.pay_id
        WHERE s.pay_date='$shift_esc'
        GROUP BY s.pay_id
    ";
    $rows = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($rows)) {
        $staff_paid_total = (float)$row['staff_paid_total'];
        $pay_alloc_total = (float)$row['pay_alloc_total'];
        if ($staff_paid_total <= 0 || $pay_alloc_total <= 0) {
            continue;
        }
        $ratio = $staff_paid_total / $pay_alloc_total;
        foreach ($mpesa_modes as $mode_id) {
            $response['mpesa_total'] += ((float)($row["mode{$mode_id}"] ?? 0)) * $ratio;
        }
        foreach ($pdq_modes as $mode_id) {
            $response['pdq_total'] += ((float)($row["mode{$mode_id}"] ?? 0)) * $ratio;
        }
        foreach ($complimentary_modes as $mode_id) {
            $response['complimentary_total'] += ((float)($row["mode{$mode_id}"] ?? 0)) * $ratio;
        }
    }

    return $response;
}

$staff_user_id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}

if ($staff_user_id < 1) {
    ?>
    <div class="modal-content">
      <div class="modal-body">
        <div class="alert alert-warning" style="margin-bottom:0;">Select a valid staff user first.</div>
      </div>
    </div>
    <?php
    exit;
}

ensure_staff_clearance_support($connection);
ensure_pay_mode_default_columns($connection);

$staff_name = get_user($staff_user_id);
$kpesa_active = (int)get_column('kp_settings_print', 'kpesa_active', "print_doc=1");
if ($kpesa_active !== 2) {
    $kpesa_active = 1;
}
$total_sales = (float)column_total("kp_sales", "amount", "user_id='$staff_user_id' AND shift='$shift'");
$paid_sales = (float)column_total("kp_sales", "amount", "user_id='$staff_user_id' AND shift='$shift' AND pay_status=1");
$unpaid_sales = (float)column_total("kp_sales", "amount", "user_id='$staff_user_id' AND shift='$shift' AND pay_status=2");
$credit_sales = (float)column_total("kp_sales", "amount", "user_id='$staff_user_id' AND shift='$shift' AND pay_status=3");
$mpesa_total = $kpesa_active === 1 ? (float)column_total('kp_split_message', 'amount', "clear_status=2 AND claim_staff='$staff_user_id'") : 0;
$mpesa_count = $kpesa_active === 1 ? (int)column_count('kp_split_message', 'id', "clear_status=2 AND claim_staff='$staff_user_id'") : 0;
$pdq_total = $kpesa_active === 1 ? (float)column_total('kp_user_pdq', 'amount', "clear_status=2 AND user_id='$staff_user_id'") : 0;
$pdq_count = $kpesa_active === 1 ? (int)column_count('kp_user_pdq', 'id', "clear_status=2 AND user_id='$staff_user_id'") : 0;
$manual_seed = $kpesa_active === 2 ? get_staff_manual_collection_seed($connection, $staff_user_id, $shift) : ['mpesa_total' => 0.0, 'pdq_total' => 0.0];
$manual_mpesa_total = (float)$manual_seed['mpesa_total'];
$manual_pdq_total = (float)$manual_seed['pdq_total'];
$complimentary_total = (float)($manual_seed['complimentary_total'] ?? 0);
$manual_expected_cash = max(0, $paid_sales - $manual_mpesa_total - $manual_pdq_total - $complimentary_total);
$expected_cash = max(0, $paid_sales - $mpesa_total - $pdq_total - $complimentary_total);
?>
<div class="modal-dialog modal-lg" style="max-width:1240px;     width: 1240px;">
  <div class="modal-content" style="border-radius:18px; overflow:hidden;">
    <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
      <h4 class="modal-title" style="font-weight:900; color:#7a491b;">Clear Staff</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <form id="cash-clear-staff-form">
      <div class="modal-body" style="background:#fffdf9; padding:16px 18px;">
        <input type="hidden" name="staff_user_id" value="<?php echo $staff_user_id; ?>">
        <input type="hidden" name="shift" value="<?php echo htmlspecialchars($shift); ?>">
        <input type="hidden" name="total_sales" value="<?php echo $total_sales; ?>">
        <input type="hidden" name="paid_sales" value="<?php echo $paid_sales; ?>">
        <input type="hidden" name="unpaid_sales" value="<?php echo $unpaid_sales; ?>">
        <input type="hidden" name="credit_sales" value="<?php echo $credit_sales; ?>">
        <input type="hidden" name="mpesa_total" value="<?php echo $mpesa_total; ?>">
        <input type="hidden" name="mpesa_count" value="<?php echo $mpesa_count; ?>">
        <input type="hidden" name="pdq_total" value="<?php echo $pdq_total; ?>">
        <input type="hidden" name="pdq_count" value="<?php echo $pdq_count; ?>">
        <input type="hidden" name="kpesa_active" value="<?php echo $kpesa_active; ?>">
        <input type="hidden" name="collectible_sales" value="<?php echo $paid_sales; ?>">
        <input type="hidden" name="manual_seed_mpesa_total" value="<?php echo number_format($manual_mpesa_total, 2, '.', ''); ?>">
        <input type="hidden" name="manual_seed_pdq_total" value="<?php echo number_format($manual_pdq_total, 2, '.', ''); ?>">
        <input type="hidden" name="manual_seed_cash_total" value="<?php echo number_format($manual_expected_cash, 2, '.', ''); ?>">
        <input type="hidden" name="expected_cash" id="cash-clear-expected" value="<?php echo $kpesa_active === 2 ? $manual_expected_cash : $expected_cash; ?>">

        <div style="margin-bottom:12px;">
          <strong style="display:block; font-size:18px; color:#5b4330;"><?php echo htmlspecialchars($staff_name); ?></strong>
          <span style="display:block; color:#8c7b68; font-size:12px;">Shift <?php echo htmlspecialchars($shift); ?> clearance snapshot.</span>
        </div>

        <div class="cash-clear-modal-grid" style="display:grid; grid-template-columns:minmax(0, 1.2fr) minmax(0, 0.95fr) minmax(0, 1fr); gap:14px; align-items:start;">
          <div style="border:1px solid #eadfce; border-radius:16px; background:#fff; padding:14px;">
            <h5 style="margin:0 0 12px; color:#5b4330; font-weight:900;">Total Cards</h5>
            <div class="cash-clear-cards-grid" style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px;">
              <div style="border:1px solid #eadfce; border-radius:14px; background:#fffaf5; padding:12px;">
                <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Total Sales</strong>
                <span style="display:block; margin-top:6px; font-size:20px; font-weight:900;"><?php echo number_format($total_sales, 2); ?></span>
              </div>
              <div style="border:1px solid #eadfce; border-radius:14px; background:#fffaf5; padding:12px;">
                <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">M-Pesa Collected</strong>
                <span style="display:block; margin-top:6px; font-size:20px; font-weight:900;"><?php echo number_format($mpesa_total, 2); ?></span>
                <small><?php echo $kpesa_active === 1 ? ($mpesa_count . ' items') : ('Manual start ' . number_format($manual_mpesa_total, 2)); ?></small>
              </div>
              <div style="border:1px solid #eadfce; border-radius:14px; background:#fffaf5; padding:12px;">
                <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Card Collection</strong>
                <span style="display:block; margin-top:6px; font-size:20px; font-weight:900;"><?php echo number_format($pdq_total, 2); ?></span>
                <small><?php echo $kpesa_active === 1 ? ($pdq_count . ' items') : ('Manual start ' . number_format($manual_pdq_total, 2)); ?></small>
              </div>
              <div style="border:1px solid #eadfce; border-radius:14px; background:#fffaf5; padding:12px;">
                <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Expected Cash</strong>
                <span style="display:block; margin-top:6px; font-size:20px; font-weight:900;" id="cash-clear-expected-view"><?php echo number_format($kpesa_active === 2 ? $manual_expected_cash : $expected_cash, 2); ?></span>
              </div>
              <div style="border:1px solid #eadfce; border-radius:14px; background:#fffaf5; padding:12px; grid-column:1 / -1;">
                <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Complementary</strong>
                <span style="display:block; margin-top:6px; font-size:20px; font-weight:900;" id="cash-clear-complimentary-view"><?php echo number_format($complimentary_total, 2); ?></span>
              </div>
            </div>
          </div>
          <div style="border:1px solid #eadfce; border-radius:16px; background:#fff; padding:14px;">
            <h5 style="margin:0 0 12px; color:#5b4330; font-weight:900;">Clearance Figures</h5>
            <table class="table table-striped" style="margin-bottom:0;">
              <tbody>
                <tr><td>Paid Sales</td><td class="text-right"><?php echo number_format($paid_sales, 2); ?></td></tr>
                <tr><td>Unpaid Sales</td><td class="text-right"><?php echo number_format($unpaid_sales, 2); ?></td></tr>
                <tr><td>Credit Sales</td><td class="text-right"><?php echo number_format($credit_sales, 2); ?></td></tr>
                <tr><td>Complementary</td><td class="text-right" id="cash-clear-complimentary-table"><?php echo number_format($complimentary_total, 2); ?></td></tr>
                <tr><td>Expected Cash</td><td class="text-right"><?php echo number_format($expected_cash, 2); ?></td></tr>
              </tbody>
            </table>
          </div>
          <div style="border:1px solid #eadfce; border-radius:16px; background:#fff; padding:14px;">
            <?php if ($kpesa_active === 2) { ?>
            <div class="form-group">
              <label style="font-weight:800; color:#6c4a23;">Manual M-Pesa Total</label>
              <input type="number" step="0.01" min="0" class="form-control cash-clear-manual" name="manual_mpesa_total" id="cash-clear-manual-mpesa" value="<?php echo number_format($manual_mpesa_total, 2, '.', ''); ?>" data-vk-layout="numeric">
            </div>
            <div class="form-group">
              <label style="font-weight:800; color:#6c4a23;">Manual Card Total</label>
              <input type="number" step="0.01" min="0" class="form-control cash-clear-manual" name="manual_pdq_total" id="cash-clear-manual-pdq" value="<?php echo number_format($manual_pdq_total, 2, '.', ''); ?>" data-vk-layout="numeric">
            </div>
            <?php } ?>
            <div class="form-group">
              <label style="font-weight:800; color:#6c4a23;">Complementary Total</label>
              <input type="number" step="0.01" min="0" class="form-control cash-clear-manual" name="complimentary_total" id="cash-clear-complimentary" value="<?php echo number_format($complimentary_total, 2, '.', ''); ?>" data-vk-layout="numeric">
            </div>
            <div class="form-group">
              <label style="font-weight:800; color:#6c4a23;">Cash Issued / Handed In</label>
              <input type="number" step="0.01" min="0" class="form-control" name="actual_cash" id="cash-clear-actual" value="<?php echo number_format($kpesa_active === 2 ? $manual_expected_cash : $expected_cash, 2, '.', ''); ?>" data-vk-layout="numeric" required>
            </div>
            <div class="form-group">
              <label style="font-weight:800; color:#6c4a23;">Cash Issue Note</label>
              <textarea class="form-control" name="note" rows="3" placeholder="Add a note if there is a shortfall, excess, or anything to record." data-vk-layout="alpha"></textarea>
            </div>
            <div style="border-radius:14px; background:#fff8ef; border:1px solid #ecd4b0; padding:12px;">
              <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Variance</strong>
              <span id="cash-clear-variance" style="display:block; margin-top:6px; font-size:24px; font-weight:900; color:#5b4330;">0.00</span>
              <small id="cash-clear-variance-state" style="color:#8c7b68;">Balanced</small>
            </div>
          </div>
        </div>
        <style>
          @media (max-width: 1199px) {
            .cash-clear-modal-grid {
              grid-template-columns: 1fr 1fr !important;
            }
            .cash-clear-modal-grid > :last-child {
              grid-column: 1 / -1;
            }
          }
          @media (max-width: 767px) {
            .cash-clear-modal-grid,
            .cash-clear-cards-grid {
              grid-template-columns: 1fr !important;
            }
          }
        </style>
        <div data-vk-host style="margin-top:14px;"></div>
      </div>
      <div class="modal-footer" style="background:#fff; border-top:1px solid #eadfce;">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Save Staff Clearance</button>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  function cashUpdateStaffVariance() {
    var paidSales = parseFloat($('input[name="paid_sales"]').val() || 0);
    var manualMpesa = parseFloat($('#cash-clear-manual-mpesa').val() || 0);
    var manualPdq = parseFloat($('#cash-clear-manual-pdq').val() || 0);
    var complimentary = parseFloat($('#cash-clear-complimentary').val() || 0);
    var kpesaActive = parseInt($('input[name="kpesa_active"]').val() || 1, 10);
    var expected = parseFloat($('#cash-clear-expected').val() || 0);
    if (kpesaActive === 2) {
      expected = Math.max(0, paidSales - manualMpesa - manualPdq - complimentary);
      $('#cash-clear-expected').val(expected.toFixed(2));
      $('#cash-clear-expected-view').text(expected.toFixed(2));
    } else {
      expected = Math.max(0, paidSales - parseFloat($('input[name="mpesa_total"]').val() || 0) - parseFloat($('input[name="pdq_total"]').val() || 0) - complimentary);
      $('#cash-clear-expected').val(expected.toFixed(2));
      $('#cash-clear-expected-view').text(expected.toFixed(2));
    }
    $('#cash-clear-complimentary-view').text(complimentary.toFixed(2));
    $('#cash-clear-complimentary-table').text(complimentary.toFixed(2));
    var actual = parseFloat($('#cash-clear-actual').val() || 0);
    var variance = actual - expected;
    var state = 'Balanced';
    if (variance < 0) {
      state = 'Short by ' + Math.abs(variance).toFixed(2);
    } else if (variance > 0) {
      state = 'Excess by ' + variance.toFixed(2);
    }
    $('#cash-clear-variance').text(variance.toFixed(2));
    $('#cash-clear-variance-state').text(state);
  }

  $(document).off('input.cashClearStaffVariance', '#cash-clear-actual').on('input.cashClearStaffVariance', '#cash-clear-actual', function(){
    cashUpdateStaffVariance();
  });
  $(document).off('input.cashClearStaffManual', '.cash-clear-manual').on('input.cashClearStaffManual', '.cash-clear-manual', function(){
    cashUpdateStaffVariance();
  });

  $(document).off('submit.cashClearStaffForm', '#cash-clear-staff-form').on('submit.cashClearStaffForm', '#cash-clear-staff-form', function(e){
    e.preventDefault();
    $.ajax({
      url: 'clrstaff/clear_staff_act.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data);
    })
    .fail(function(){
      $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not save staff clearance.</div></div></div></div>');
    });
  });

  cashUpdateStaffVariance();
  if (window.KTouchKeyboard && typeof window.KTouchKeyboard.init === 'function') {
    window.KTouchKeyboard.init($('#load'), {
      inputSelector: '[data-vk-layout]',
      hostSelector: '[data-vk-host]',
      title: 'Staff Clearance Keyboard'
    });
  }
</script>
