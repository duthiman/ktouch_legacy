<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$user_id = (int)($_GET['id'] ?? 0);
$scope = 'previous';
$shift = get_column('kp_shift', 'shift', "1");

if ($user_id < 1) {
    ?>
    <div class="alert alert-warning">Select a waiter to view the shift summary.</div>
    <?php
    exit;
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
$clearance_q = mysqli_query($connection, "SELECT * FROM kp_staff_clearances WHERE staff_user_id='$user_id' AND shift_date='$shift' ORDER BY clearance_id DESC LIMIT 1") or die(mysqli_error($connection));
$clearance_row = $clearance_q ? mysqli_fetch_assoc($clearance_q) : null;
$is_cleared = $clearance_row ? true : false;
$cleared_by_name = $is_cleared && !empty($clearance_row['cleared_by']) ? get_user((int)$clearance_row['cleared_by']) : '';
$cleared_at = $is_cleared ? (string)($clearance_row['created_at'] ?? '') : '';
$cleared_variance = $is_cleared ? (float)($clearance_row['variance_amount'] ?? 0) : 0;
$cleared_variance_type = $is_cleared ? (string)($clearance_row['variance_type'] ?? '') : '';

$bill_sql = "SELECT * FROM kp_sales WHERE shift!='$shift' AND pay_status=2 AND user_id='$user_id' ORDER BY sale_id DESC";
$bill_title = "Previous Unpaid Bills";
$bill_note = "Earlier shifts not yet cleared.";
$bill_type = 2;

$bill_result = mysqli_query($connection, $bill_sql);
$bill_count_scope = $bill_result ? mysqli_num_rows($bill_result) : 0;
?>
<style type="text/css">
  .cash-staff-summary-shell {
    border: 1px solid #eadfce;
    border-radius: 18px;
    background: #fffdf9;
    overflow: hidden;
  }
  .cash-staff-summary-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    padding: 16px 18px;
    border-bottom: 1px solid #efe2d3;
    background: linear-gradient(135deg, #fff6ea 0%, #fffdfa 100%);
  }
  .cash-staff-summary-head h3 {
    margin: 0 0 4px;
    color: #77461b;
    font-weight: 900;
  }
  .cash-staff-summary-head p {
    margin: 0;
    color: #7f6a55;
  }
  .cash-staff-scope {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .cash-staff-scope button {
    border: 1px solid #d8c1a1;
    background: #fff;
    color: #82531d;
    border-radius: 999px;
    padding: 9px 14px;
    font-weight: 800;
    font-size: 12px;
  }
  .cash-staff-scope button.is-active {
    background: #f0a53a;
    border-color: #f0a53a;
    color: #fff;
  }
  .cash-staff-summary-body {
    padding: 16px 18px 18px;
  }
  .cash-staff-status {
    margin-bottom: 14px;
    border: 1px solid #cfe7d3;
    border-radius: 14px;
    background: #f2fbf3;
    padding: 12px 14px;
    color: #2f6840;
  }
  .cash-staff-status strong {
    display: block;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-staff-status span {
    display: block;
    margin-top: 4px;
  }
  .cash-staff-summary-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 16px;
    align-items: start;
  }
  .cash-staff-max-sale {
    margin-bottom: 14px;
    border: 1px solid #eee2d6;
    border-radius: 16px;
    background: #fff;
    overflow: hidden;
  }
  .cash-staff-max-sale-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    padding: 12px 14px;
    background: #fffaf3;
    border-bottom: 1px solid #f2e7db;
  }
  .cash-staff-max-sale-head h4 {
    margin: 0 0 3px;
    color: #5b4330;
    font-size: 15px;
    font-weight: 900;
  }
  .cash-staff-max-sale-head p {
    margin: 0;
    color: #8c7b68;
    font-size: 12px;
  }
  .cash-staff-max-sale-body {
    padding: 14px;
  }
  .cash-staff-max-sale-value {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    margin-bottom: 12px;
    color: #5b4330;
    font-weight: 800;
  }
  .cash-staff-max-sale-value strong {
    display: block;
    color: #99652f;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-staff-max-sale-value span {
    display: block;
    margin-top: 4px;
    color: #2d241a;
    font-size: 22px;
    font-weight: 900;
  }
  .cash-staff-max-sale-form {
    display: none;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f2e7db;
  }
  .cash-staff-max-sale-form.is-open {
    display: block;
  }
  .cash-staff-max-sale-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 10px;
  }
  .cash-staff-max-sale-actions .btn,
  .cash-staff-max-sale-head .btn {
    border-radius: 10px;
    font-weight: 800;
  }
  .cash-staff-inline-result {
    margin-top: 10px;
  }
  .cash-staff-metrics {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
  }
  .cash-staff-metric {
    border: 1px solid #eee2d6;
    border-radius: 14px;
    padding: 12px;
    background: #fff;
  }
  .cash-staff-metric strong,
  .cash-staff-metric span {
    display: block;
  }
  .cash-staff-metric strong {
    color: #99652f;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-staff-metric span {
    color: #2d241a;
    font-size: 21px;
    font-weight: 900;
    margin-top: 6px;
    line-height: 1.1;
  }
  .cash-staff-summary-card,
  .cash-staff-bills-card {
    border: 1px solid #eee2d6;
    border-radius: 16px;
    background: #fff;
    overflow: hidden;
  }
  .cash-staff-card-head {
    padding: 12px 14px;
    border-bottom: 1px solid #f2e7db;
    background: #fffaf3;
  }
  .cash-staff-card-head h4 {
    margin: 0 0 3px;
    color: #5b4330;
    font-size: 15px;
    font-weight: 900;
  }
  .cash-staff-card-head p {
    margin: 0;
    color: #8c7b68;
    font-size: 12px;
  }
  .cash-staff-summary-card .table,
  .cash-staff-bills-card .table {
    margin: 0;
  }
  .cash-staff-bills-wrap {
    max-height: 560px;
    overflow: auto;
  }
  .cash-staff-bills-wrap td,
  .cash-staff-bills-wrap th {
    white-space: nowrap;
  }
  .cash-staff-empty {
    padding: 20px 16px;
    color: #8b7863;
  }
  .cash-staff-foot {
    margin-top: 14px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
  }
  .cash-staff-foot .btn {
    border-radius: 12px;
    font-weight: 800;
    padding: 10px 16px;
  }
  @media (max-width: 1199px) {
    .cash-staff-metrics {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
  }
  @media (max-width: 991px) {
    .cash-staff-summary-grid {
      grid-template-columns: 1fr;
    }
    .cash-staff-metrics {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (max-width: 767px) {
    .cash-staff-summary-head {
      flex-direction: column;
    }
    .cash-staff-metrics {
      grid-template-columns: 1fr;
    }
    .cash-staff-foot .btn {
      width: 100%;
    }
  }
</style>
<div class="cash-staff-summary-shell">
  <div class="cash-staff-summary-head">
    <div>
      <h3><?php echo htmlspecialchars($staff_name); ?> Shift Summary</h3>
      <p>Shift: <?php echo htmlspecialchars($shift); ?></p>
    </div>
    <div class="cash-staff-scope">
      <button type="button" class="is-active" disabled>Previous Bills Only</button>
    </div>
  </div>
  <div class="cash-staff-summary-body">
    <?php if ($is_cleared) { ?>
      <div class="cash-staff-status">
        <strong>Already Cleared</strong>
        <span>Cleared by <?php echo htmlspecialchars($cleared_by_name ?: 'System'); ?> on <?php echo htmlspecialchars($cleared_at); ?>.</span>
        <span>Variance: <?php echo number_format($cleared_variance, 2); ?><?php echo $cleared_variance_type !== '' ? ' (' . strtoupper($cleared_variance_type) . ')' : ''; ?></span>
      </div>
    <?php } ?>
    <div class="cash-staff-summary-grid">
      <div>
        <div class="cash-staff-max-sale">
          <div class="cash-staff-max-sale-head">
            <div>
              <h4>Max Sale Capping</h4>
              <p>Adjust this waiter&apos;s maximum allowed sale without leaving the summary panel.</p>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="cashToggleStaffMaxSaleForm()">Increase Max Sale</button>
          </div>
          <div class="cash-staff-max-sale-body">
            <div class="cash-staff-max-sale-value">
              <div>
                <strong>Current Maximum Sale</strong>
                <span id="cash-staff-max-sale-display"><?php echo number_format($max_sale, 2); ?></span>
              </div>
            </div>
            <div id="cash-staff-max-sale-form" class="cash-staff-max-sale-form">
              <div class="form-group" style="margin-bottom:0;">
                <label for="cash_staff_new_max_sale">New Maximum Sale Amount</label>
                <input type="number" step="0.01" min="0" id="cash_staff_new_max_sale" class="form-control" value="<?php echo number_format($max_sale, 2, '.', ''); ?>" />
              </div>
              <div class="cash-staff-max-sale-actions">
                <button type="button" class="btn btn-success btn-sm" id="cash-save-staff-max-sale">Save Max Sale</button>
                <button type="button" class="btn btn-light btn-sm" onclick="cashToggleStaffMaxSaleForm(false)">Cancel</button>
              </div>
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
          <div class="cash-staff-card-head">
            <h4>Shift Breakdown</h4>
            <p>Quick view of this waiter's shift performance.</p>
          </div>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Section</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Count</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Total Shift Sales</td>
                <td class="text-right"><?php echo number_format($total_sales, 2); ?></td>
                <td class="text-right"><?php echo $bill_count; ?></td>
              </tr>
              <tr>
                <td>Cleared / Paid Bills</td>
                <td class="text-right"><?php echo number_format($paid_sales, 2); ?></td>
                <td class="text-right"><?php echo (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift' AND pay_status=1"); ?></td>
              </tr>
              <tr>
                <td>Unpaid Bills</td>
                <td class="text-right"><?php echo number_format($unpaid_sales, 2); ?></td>
                <td class="text-right"><?php echo (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift' AND pay_status=2"); ?></td>
              </tr>
              <tr>
                <td>Customer Credit Bills</td>
                <td class="text-right"><?php echo number_format($credit_sales, 2); ?></td>
                <td class="text-right"><?php echo (int)column_count("kp_sales", "sale_id", "user_id='$user_id' AND shift='$shift' AND pay_status=3"); ?></td>
              </tr>
              <tr>
                <td>Returned Items</td>
                <td class="text-right"><?php echo number_format((float)column_total("kp_ret_items", "amount", "user_id='$user_id' AND shift='$shift'"), 2); ?></td>
                <td class="text-right"><?php echo $return_count; ?></td>
              </tr>
              <tr>
                <td>Deleted Bills</td>
                <td class="text-right"><?php echo number_format((float)column_total("kp_del_orders", "amount", "user_id='$user_id' AND shift='$shift'"), 2); ?></td>
                <td class="text-right"><?php echo $deleted_count; ?></td>
              </tr>
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
    if (!shouldOpen) {
      $('#cash-staff-max-sale-result').html('');
    }
  }

  function cashOpenStaffClearModal(id) {
    $('#modal').modal('show');
    $.ajax({
      url: 'clrstaff/clear_staff_modal.php',
      type: 'POST',
      data: { id: id },
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data);
    })
    .fail(function(){
      $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load staff clearance.</div></div></div></div>');
    });
  }

  function cashOpenStaffSummaryPrint(id) {
    $('#modal').modal('show');
    $.ajax({
      url: 'clrstaff/print_summary_modal.php',
      type: 'POST',
      data: { id: id },
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data);
    })
    .fail(function(){
      $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load print preview.</div></div></div></div>');
    });
  }

  $(document).off('click.cashSaveStaffMaxSale', '#cash-save-staff-max-sale').on('click.cashSaveStaffMaxSale', '#cash-save-staff-max-sale', function(){
    var newMax = $('#cash_staff_new_max_sale').val();
    if (newMax === '' || isNaN(newMax) || parseFloat(newMax) < 0) {
      $('#cash-staff-max-sale-result').html('<div class="alert alert-warning">Enter a valid max sale amount.</div>');
      return;
    }

    var $button = $(this);
    $button.prop('disabled', true).text('Saving...');

    $.ajax({
      url: 'clrbill/max_sale_act.php',
      type: 'POST',
      data: { user_id: <?php echo $user_id; ?>, max_sale: newMax },
      dataType: 'json'
    })
    .done(function(data){
      if (data.status === 'success') {
        var formatted = parseFloat(newMax).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#cash-staff-max-sale-display').text(formatted);
        $('#cash_staff_new_max_sale').val(parseFloat(newMax).toFixed(2));
        $('#cash-staff-max-sale-result').html('<div class="alert alert-success">' + data.message + '</div>');
        setTimeout(function(){
          cashToggleStaffMaxSaleForm(false);
        }, 800);
      } else {
        $('#cash-staff-max-sale-result').html('<div class="alert alert-danger">' + data.message + '</div>');
      }
    })
    .fail(function(){
      $('#cash-staff-max-sale-result').html('<div class="alert alert-danger">Unable to save max sale.</div>');
    })
    .always(function(){
      $button.prop('disabled', false).text('Save Max Sale');
    });
  });
</script>
