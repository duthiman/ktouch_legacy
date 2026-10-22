<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../../inc/connection.php');
require_once('../../../inc/gen_funcs.php');

function handover_mode_total($pay_id, $shift, $counter, $user_id) {
  global $connection;
  $col = 'mode'.(int)$pay_id;

  $via_sales = mysqli_query(
    $connection,
    "SELECT SUM(payment_amount) AS total_amount FROM (
      SELECT DISTINCT kp_sale_payment.pay_id, kp_sale_payment.$col AS payment_amount
      FROM kp_sale_payment
      INNER JOIN kp_sales_paid ON kp_sale_payment.pay_id = kp_sales_paid.pay_id
      INNER JOIN kp_sales ON kp_sales.sale_id = kp_sales_paid.sale_id
      WHERE kp_sales_paid.pay_date='$shift' AND kp_sales.counter='$counter'
    ) AS handover_payments"
  );

  if ($via_sales && ($row = mysqli_fetch_assoc($via_sales)) && $row['total_amount'] !== null) {
    return (float)$row['total_amount'];
  }

  $fallback_counter = (float)column_total('kp_sale_payment',$col,"pay_date='$shift' AND counter='$counter'");
  if ($fallback_counter > 0) {
    return $fallback_counter;
  }

  return (float)column_total('kp_sale_payment',$col,"pay_date='$shift' AND user_id='$user_id'");
}

$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}
$counter = user_counter($user_id);
$cashiers = mysqli_query($connection,"SELECT user_id,username FROM kp_users WHERE drole=1 AND   work_status=2 ORDER BY username ASC") or die(mysqli_error($connection));
$pay_modes = mysqli_query($connection,"SELECT pay_id,name FROM kp_pay_mode ORDER BY pay_id ASC") or die(mysqli_error($connection));
?>
<style type="text/css">
  .cash-handover-shell .modal-content {
    width: 800px;
    max-width: 980px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .cash-handover-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .cash-handover-shell .modal-title {
    margin: 0;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-handover-shell .modal-body {
    padding: 16px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .cash-handover-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    gap: 10px;
  }
  .cash-handover-card {
    margin-bottom: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid rgba(146,110,52,0.12);
  }
  .cash-handover-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 14px;
  }
  .cash-handover-pill {
    padding: 12px;
    border-radius: 14px;
    background: linear-gradient(180deg, #fffdfa 0%, #f6edd7 100%);
    border: 1px solid rgba(146,110,52,0.12);
  }
  .cash-handover-pill strong,
  .cash-handover-pill span {
    display: block;
  }
  .cash-handover-pill strong {
    font-size: 11px;
    font-weight: 900;
    color: #7e3a20;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .cash-handover-pill span {
    margin-top: 6px;
    font-size: 22px;
    font-weight: 900;
    color: #332411;
  }
  .cash-handover-table-wrap {
    max-height: 360px;
    overflow-y: auto;
    border-radius: 14px;
    border: 1px solid rgba(146,110,52,0.12);
    background: #fff;
  }
  .cash-handover-table {
    margin: 0;
  }
  .cash-handover-table th,
  .cash-handover-table td {
    vertical-align: middle !important;
    font-size: 12px;
  }
  .cash-handover-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fbf4de;
    color: #7e3a20;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-handover-table .form-control {
    height: 38px;
    border-radius: 10px;
  }
  .cash-handover-status {
    font-weight: 900;
  }
  .cash-handover-status.match {
    color: #166534;
  }
  .cash-handover-status.variance {
    color: #b45309;
  }
  .cash-handover-btn {
    min-height: 46px;
    border: 0;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 1 1 0;
  }
  .cash-handover-close {
    background: #ead6d6;
    color: #7e2014;
  }
  .cash-handover-save {
    background: linear-gradient(135deg, #14532d 0%, #277246 100%);
    color: #fff;
  }
</style>
<div class="cash-handover-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Shift Handover</h4>
    </div>
    <div class="modal-body">
      <div id="handover-results"></div>
      <?php if (!$counter) { ?>
        <div class="alert alert-warning">No active counter found for this cashier.</div>
      <?php } else { ?>
        <div class="cash-handover-summary">
          <div class="cash-handover-pill"><strong>Shift</strong><span><?php echo htmlspecialchars($shift); ?></span></div>
          <div class="cash-handover-pill"><strong>Counter</strong><span><?php echo htmlspecialchars(get_counter($counter)); ?></span></div>
          <div class="cash-handover-pill"><strong>Current Cashier</strong><span><?php echo htmlspecialchars(get_user($user_id)); ?></span></div>
          <div class="cash-handover-pill"><strong>Unpaid Bills</strong><span><?php echo number_format((float)column_total('kp_sales','amount',"shift='$shift' AND counter='$counter' AND pay_status=2"), 2); ?></span></div>
        </div>
        <form id="handover-form">
          <input type="hidden" name="counter" value="<?php echo $counter; ?>">
          <div class="cash-handover-card">
            <label style="font-weight: 900; text-transform: uppercase; color: #7e3a20;">Hand Over To</label>
            <select name="to_user" class="form-control">
              <option value="0">Select receiving cashier</option>
              <?php while ($cashier = mysqli_fetch_assoc($cashiers)) { ?>
                <option value="<?php echo (int)$cashier['user_id']; ?>"><?php echo htmlspecialchars($cashier['username']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="cash-handover-card">
            <div class="cash-handover-table-wrap">
              <table class="table table-striped cash-handover-table">
                <thead>
                  <tr>
                    <th>Mode</th>
                    <th>System</th>
                    <th>Declared</th>
                    <th>Variance</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($mode = mysqli_fetch_assoc($pay_modes)) {
                    $pay_id = (int)$mode['pay_id'];
                    $expected = handover_mode_total($pay_id, $shift, $counter, $user_id);
                    ?>
                    <tr data-expected="<?php echo $expected; ?>">
                      <td>
                        <strong><?php echo htmlspecialchars($mode['name']); ?></strong>
                        <input type="hidden" name="mode_ids[]" value="<?php echo $pay_id; ?>">
                        <input type="hidden" name="expected_<?php echo $pay_id; ?>" value="<?php echo $expected; ?>">
                      </td>
                      <td class="handover-expected"><?php echo number_format($expected, 2); ?></td>
                      <td><input type="number" step="0.01" min="0" name="declared_<?php echo $pay_id; ?>" class="form-control handover-declared" data-payid="<?php echo $pay_id; ?>" value="<?php echo number_format($expected, 2, '.', ''); ?>"></td>
                      <td class="handover-variance">0.00</td>
                      <td class="handover-status match">Match</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="cash-handover-summary">
            <div class="cash-handover-pill"><strong>System Total</strong><span id="handover-system-total">0.00</span></div>
            <div class="cash-handover-pill"><strong>Declared Total</strong><span id="handover-declared-total">0.00</span></div>
            <div class="cash-handover-pill"><strong>Total Variance</strong><span id="handover-variance-total">0.00</span></div>
            <div class="cash-handover-pill"><strong>Overall</strong><span id="handover-overall-status">MATCH</span></div>
          </div>
          <div class="cash-handover-card">
            <label style="font-weight: 900; text-transform: uppercase; color: #7e3a20;">Handover Note</label>
            <textarea name="note" class="form-control" style="min-height: 100px; border-radius: 12px;" placeholder="Optional handover note"></textarea>
          </div>
        </form>
      <?php } ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="cash-handover-btn cash-handover-close" data-dismiss="modal">Close</button>
      <?php if ($counter) { ?>
        <button type="button" class="cash-handover-btn cash-handover-save" id="save-handover">Save Handover</button>
      <?php } ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  function showHandoverAlert(status, message, callback) {
    if (typeof swal === 'function') {
      var icon = status === 'success' ? 'success' : (status === 'warning' ? 'warning' : 'error');
      swal({
        title: status === 'success' ? 'Handover Saved' : (status === 'warning' ? 'Check Handover' : 'Handover Error'),
        text: message,
        icon: icon,
        button: {
          text: 'OK',
          className: status === 'success' ? 'btn btn-success' : 'btn btn-warning'
        }
      }).then(function(){
        if (typeof callback === 'function') {
          callback();
        }
      });
      return;
    }
    if (typeof hullabaloo !== 'undefined') {
      var hulla = new hullabaloo();
      hulla.send(message, status === 'success' ? 'success' : (status === 'warning' ? 'warning' : 'danger'));
    } else {
      alert(message);
    }
    if (typeof callback === 'function') {
      callback();
    }
  }

  function recalcHandover() {
    var systemTotal = 0;
    var declaredTotal = 0;
    var varianceTotal = 0;
    $('.cash-handover-table tbody tr').each(function(){
      var expected = parseFloat($(this).find('input[type="hidden"][name^="expected_"]').val()) || 0;
      var declared = parseFloat($(this).find('.handover-declared').val()) || 0;
      var variance = declared - expected;
      systemTotal += expected;
      declaredTotal += declared;
      varianceTotal += variance;
      $(this).find('.handover-variance').text(variance.toFixed(2));
      if (Math.abs(variance) < 0.009) {
        $(this).find('.handover-status').text('Match').removeClass('variance').addClass('match');
      } else {
        $(this).find('.handover-status').text('Variance').removeClass('match').addClass('variance');
      }
    });
    $('#handover-system-total').text(systemTotal.toFixed(2));
    $('#handover-declared-total').text(declaredTotal.toFixed(2));
    $('#handover-variance-total').text(varianceTotal.toFixed(2));
    $('#handover-overall-status').text(Math.abs(varianceTotal) < 0.009 ? 'MATCH' : 'VARIANCE');
  }

  $(document).off('keyup.handover change.handover').on('keyup.handover change.handover', '.handover-declared', function(){
    recalcHandover();
  });

  $(document).off('click.saveHandover').on('click.saveHandover', '#save-handover', function(e){
    e.preventDefault();
    $.ajax({
      url: 'API/cash_api.php',
      type: 'POST',
      data: $('#handover-form').serialize() + '&action=handover_save',
      dataType: 'json'
    })
    .done(function(data){
      var payload = data && data.data ? data.data : {};
      var status = payload.status || (data && data.ok ? 'success' : 'error');
      showHandoverAlert(status, data.message || payload.message, function(){
        if (status === 'success' && payload.redirect) {
          window.location.href = payload.redirect;
        }
      });
    })
    .fail(function(){
      showHandoverAlert('error', 'Could not save handover.');
    });
  });

  recalcHandover();
</script>
