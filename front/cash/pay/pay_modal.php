<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$amount = isset($_REQUEST['amount']) ? (float)$_REQUEST['amount'] : 0;
$orduser = isset($_REQUEST['orduser']) ? (int)$_REQUEST['orduser'] : 0;
$type = isset($_REQUEST['type']) ? (int)$_REQUEST['type'] : 0;
$invoices = isset($_REQUEST['invoices']) ? trim((string)$_REQUEST['invoices']) : '';
$action = "LOADED|-|-|-|PAYMENT COLLECTOR MODAL";
logs($action, "../logs");

$scope_label = $type == 1 ? "TODAY'S" : ($type == 2 ? "PREVIOUS" : "SELECTED");
$ordlabel = $orduser > 0 ? get_column('kp_users', 'username', "user_id='$orduser'") : 'SELECTED';
$dialog_title = "Collect Payment";
$dialog_note = "Capture money received and clear the selected bills.";
$pay_modes = mysqli_query($connection, "SELECT * FROM kp_pay_mode WHERE del_status=2 ORDER BY name ASC") or die("Could not get the payment modes");
?>
<div class="modal-dialog cash-pay-dialog" role="document">
  <div class="modal-content cash-pay-modal">
    <div data-vk-host></div>
    <div id="pay-res"></div>
    <form id="cash-pay-form" name="quick">
      <input type="hidden" name="invoices" id="invoices" value="<?php echo htmlspecialchars($invoices, ENT_QUOTES); ?>">
      <input type="hidden" name="grand_total" id="grand_total" value="<?php echo htmlspecialchars((string)$amount, ENT_QUOTES); ?>">
      <input type="hidden" name="orduser" id="orduser" value="<?php echo $orduser; ?>">
      <input type="hidden" name="type" id="type" value="<?php echo $type; ?>">
      <input type="hidden" name="tendered" id="tendered" value="0">

      <div class="modal-header cash-pay-head">
        <div>
          <h4><?php echo $dialog_title; ?></h4>
          <p><?php echo htmlspecialchars($ordlabel . ' ' . $scope_label); ?> bills. <?php echo $dialog_note; ?></p>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body cash-pay-body">
        <div class="cash-pay-summary">
          <div class="cash-pay-card">
            <strong>Invoice Balance</strong>
            <span><?php echo number_format($amount, 2); ?></span>
          </div>
          <div class="cash-pay-card">
            <strong>Tendered</strong>
            <input type="text" id="total_received" class="form-control" disabled="true" value="0.00">
          </div>
          <div class="cash-pay-card">
            <strong>Balance</strong>
            <input type="text" id="balance" class="form-control" disabled="true" value="<?php echo number_format($amount, 2); ?>">
            <input type="hidden" id="balance_two" name="balance" value="<?php echo number_format($amount, 2, '.', ''); ?>">
          </div>
        </div>

      <div class="cash-pay-mode-shell">
        <div class="cash-pay-mode-head">
          <h5>Payment Modes</h5>
          <p>Select one or more payment modes and enter the amount for each.</p>
        </div>
        <div class="cash-pay-helperbar">
          <button type="button" class="btn btn-outline-warning btn-sm cash-pay-fillbtn" onclick="fillSelectedModes()">Use Full Amount</button>
        </div>
        <div class="cash-pay-mode-grid">
            <?php mysqli_data_seek($pay_modes, 0); while ($row = mysqli_fetch_assoc($pay_modes)) { ?>
              <label class="cash-pay-mode-pill" style="--mode-color: <?php echo htmlspecialchars($row['btn_color']); ?>">
                <input type="checkbox"
                       name="pay-mode[]"
                       value="<?php echo $row['pay_id']; ?>"
                       class="check cash-pay-mode-check"
                       id="c<?php echo $row['pay_id']; ?>"
                       data-id="<?php echo htmlspecialchars($invoices, ENT_QUOTES); ?>"
                       data-id2="<?php echo (int)$row['gate']; ?>">
                <span><?php echo htmlspecialchars($row['name']); ?></span>
              </label>
            <?php } ?>
          </div>
        </div>

        <fieldset id="form_fieldset" class="cash-pay-fields">
          <?php mysqli_data_seek($pay_modes, 0); while ($row = mysqli_fetch_assoc($pay_modes)) {
            $name = $row['name'];
            $has_ref = (int)$row['has_ref'];
            $has_change = (int)$row['has_change'];
            $pay_id = (int)$row['pay_id'];
            $gate = (int)$row['gate'];
          ?>
            <div class="cash-pay-row" id="pay-row-<?php echo $pay_id; ?>" data-pay-row="<?php echo $pay_id; ?>" data-has-change="<?php echo $has_change; ?>" style="display:none;">
              <?php if ($has_ref == 1) { ?>
                <div class="form-group cash-pay-field">
                  <label>Code / Ref</label>
                  <input type="text" id="refid<?php echo $pay_id; ?>" class="form-control" name="ref<?php echo $pay_id; ?>" value="0" data-vk-layout="alpha">
                </div>
              <?php } ?>
              <div class="form-group cash-pay-field">
                <label><?php echo htmlspecialchars($name); ?> Amount</label>
                <input type="text"
                       id="nameid<?php echo $pay_id; ?>"
                       class="form-control cash-pay-amount"
                       name="name<?php echo $pay_id; ?>"
                       value="0"
                       data-has-change="<?php echo $has_change; ?>"
                       data-vk-layout="numeric">
              </div>
            </div>
            <?php if ($gate == 1) { ?>
              <script type="text/javascript">
                function sel<?php echo $pay_id; ?>() {
                  var sum = 0;
                  var carray = [];
                  $("input.bulk2:checked").each(function() {
                    var rowAmount = parseFloat($(this).val()) || 0;
                    sum += rowAmount;
                    carray.push($(this).data("value1"));
                  });
                  $("#refid<?php echo $pay_id; ?>").val(carray.join(','));
                  $("#gate-input<?php echo $pay_id; ?>").val(carray.join(','));
                  $("#nameid<?php echo $pay_id; ?>").val(sum.toFixed(2));
                  calculate();
                }
              </script>
            <?php } ?>
          <?php } ?>
          <div id="filts" class="cash-pay-mpesa-panel"></div>
        </fieldset>
      </div>

      <div class="modal-footer cash-pay-foot">
        <button type="button" data-dismiss="modal" class="btn btn-outline-danger btn-md">Close</button>
        <button type="button" id="main-btn" class="btn btn-outline-info btn-md" style="display:none;" disabled onclick="sub_form();">Take Payment / Clear Bills</button>
      </div>
    </form>
  </div>
</div>

<style type="text/css">
  .cash-pay-modal {
    border: 0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 24px 48px rgba(64, 35, 10, 0.18);
  }
  .cash-pay-dialog {
    width: min(1180px, calc(100vw - 28px));
    max-width: 1180px;
    margin: 18px auto;
  }
  .cash-pay-head {
    border-bottom: 1px solid #f0dfc7;
    background: linear-gradient(135deg, #fff7ec 0%, #fffdf9 100%);
    padding: 18px 22px;
    align-items: flex-start;
  }
  .cash-pay-head h4 {
    margin: 0 0 4px;
    color: #7b4212;
    font-weight: 900;
  }
  .cash-pay-head p {
    margin: 0;
    color: #866f59;
  }
  .cash-pay-body {
    padding: 18px 22px;
    background: #fffdf9;
  }
  .cash-pay-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
  }
  .cash-pay-card {
    border: 1px solid #ecdfd0;
    border-radius: 18px;
    background: #fff;
    padding: 14px;
  }
  .cash-pay-card strong,
  .cash-pay-card span {
    display: block;
  }
  .cash-pay-card strong {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #9a7048;
  }
  .cash-pay-card span {
    margin-top: 8px;
    font-size: 26px;
    font-weight: 900;
    color: #2d2115;
    line-height: 1;
  }
  .cash-pay-card .form-control {
    margin-top: 8px;
    height: 46px;
    border-radius: 14px;
    border: 1px solid #ecd9c1;
    font-size: 22px;
    font-weight: 900;
    color: #2d2115;
    text-align: center;
    background: #fffaf2;
  }
  .cash-pay-mode-shell {
    border: 1px solid #ecdfd0;
    border-radius: 18px;
    background: #fff;
    padding: 14px;
    margin-bottom: 16px;
  }
  .cash-pay-mode-head h5 {
    margin: 0 0 3px;
    color: #5c4330;
    font-weight: 900;
  }
  .cash-pay-mode-head p {
    margin: 0 0 12px;
    color: #87705d;
    font-size: 12px;
  }
  .cash-pay-mode-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .cash-pay-helperbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 12px;
  }
  .cash-pay-fillbtn {
    border-radius: 999px;
    font-weight: 800;
    padding: 8px 14px;
  }
  .cash-pay-mode-pill {
    position: relative;
    margin: 0;
    cursor: pointer;
  }
  .cash-pay-mode-pill input {
    position: absolute;
    opacity: 0;
    inset: 0;
  }
  .cash-pay-mode-pill span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 16px;
    border-radius: 999px;
    border: 1px solid color-mix(in srgb, var(--mode-color) 35%, #f1dcc0);
    background: #fff8ee;
    color: #5a3818;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-pay-mode-pill input:checked + span {
    background: var(--mode-color);
    color: #fff;
    border-color: var(--mode-color);
    box-shadow: 0 10px 22px color-mix(in srgb, var(--mode-color) 35%, transparent);
  }
  .cash-pay-fields {
    border: 1px solid #ecdfd0;
    border-radius: 18px;
    background: #fff;
    padding: 14px;
  }
  .cash-pay-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 12px;
  }
  .cash-pay-field label {
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #8a6641;
  }
  .cash-pay-field .form-control {
    height: 48px;
    border-radius: 14px;
    border: 1px solid #e8d7c3;
    font-size: 18px;
    font-weight: 800;
  }
  .cash-pay-mpesa-panel {
    overflow-y: auto;
    max-height: 320px;
    border-top: 1px dashed #ead9c3;
    padding-top: 12px;
  }
  .cash-pay-foot {
    border-top: 1px solid #f0dfc7;
    background: #fffaf4;
    padding: 12px 22px;
  }
  @media (max-width: 767px) {
    .cash-pay-dialog {
      width: calc(100vw - 14px);
      max-width: calc(100vw - 14px);
      margin: 10px auto;
    }
    .cash-pay-summary,
    .cash-pay-row {
      grid-template-columns: 1fr;
    }
  }
</style>

<script type="text/javascript">
  function showPaymentAlert(status, message, callback) {
    if (typeof swal === 'function') {
      var icon = status === 'success' ? 'success' : (status === 'warning' ? 'warning' : 'error');
      swal({
        title: status === 'success' ? 'Payment Complete' : (status === 'warning' ? 'Check Payment' : 'Payment Error'),
        text: message,
        icon: icon,
        button: {
          text: 'OK',
          className: status === 'success' ? 'btn btn-success' : 'btn btn-warning'
        }
      }).then(function() {
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

  function loadmpesa(id) {
    $('#filts').html('');
    $.ajax({
      url: 'mpesas.php',
      type: 'POST',
      data: { id: id },
      dataType: 'html'
    })
    .done(function(data) {
      $('#filts').html(data);
    })
    .fail(function() {
      $('#filts').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
    });
  }

  function mpesasearch(id) {
    var search = $('#mpesaphone').val();
    $.ajax({
      url: 'mpesa_check.php',
      type: 'POST',
      data: { id: id, search: search },
      dataType: 'html'
    })
    .done(function(data) {
      $('#mpesarows').html(data);
    })
    .fail(function() {
      showPaymentAlert('error', 'Mpesa search failed. Please try again.');
    });
  }

  function calculate() {
    var grandTotal = parseFloat($('#grand_total').val()) || 0;
    var tendered = 0;
    var hasMode = $('input[name="pay-mode[]"]:checked').length > 0;
    var allowChange = false;

    $('.cash-pay-mode-check:checked').each(function() {
      var payId = $(this).val();
      var amount = parseFloat($('#nameid' + payId).val()) || 0;
      var changeFlag = parseInt($('#pay-row-' + payId).data('has-change'), 10) || 0;
      tendered += amount;
      if (changeFlag === 1) {
        allowChange = true;
      }
    });

    var balance = grandTotal - tendered;
    $('#total_received').val(tendered.toFixed(2));
    $('#tendered').val(tendered.toFixed(2));
    $('#balance').val(balance.toFixed(2));
    $('#balance_two').val(balance.toFixed(2));

    if (!hasMode) {
      $('#main-btn').hide().prop('disabled', true);
      return;
    }

    var valid = tendered > 0 && (allowChange ? tendered >= grandTotal : Math.abs(balance) < 0.01);
    $('#main-btn').show().prop('disabled', !valid);
  }

  function fillSelectedModes() {
    var currentBalance = parseFloat($('#balance_two').val());
    var grandTotal = parseFloat($('#grand_total').val()) || 0;
    var fillAmount = !isNaN(currentBalance) ? currentBalance : grandTotal;
    var checked = $('.cash-pay-mode-check:checked');
    if (!checked.length) {
      return;
    }
    if (fillAmount < 0) {
      fillAmount = 0;
    }
    $('#nameid' + checked.last().val()).val(fillAmount.toFixed(2));
    calculate();
  }

  function sub_form() {
    $('#main-btn').hide();
    var str = $('form[name="quick"]').serializeArray();
    $.ajax({
      url: 'pay.php',
      type: 'POST',
      data: str,
      dataType: 'json'
    })
    .done(function(data) {
      showPaymentAlert(data.status, data.message, function() {
        if (data.status === 'success') {
          $('#modal').modal('hide');
          window.location.reload();
        }
      });
    })
    .fail(function() {
      showPaymentAlert('error', 'Payment request failed. Please try again.');
    })
    .always(function() {
      $('#main-btn').show();
    });
  }

  $('input[name="pay-mode[]"]').change(function() {
    var pid = $(this).val();
    var gate = $(this).data('id2');
    if (this.checked) {
      if (gate == 1) {
        loadmpesa(pid);
      }
      $('#pay-row-' + pid).css('display', 'grid');
    } else {
      $('#refid' + pid).val('0');
      $('#nameid' + pid).val('0');
      $('#pay-row-' + pid).hide();
    }
    calculate();
  });

  $(document).on('keyup change', '.cash-pay-amount', function() {
    calculate();
  });

  function confirm_sale() {
    $.ajax({
      url: 'confirm_sale.php',
      type: 'POST',
      data: '',
      dataType: 'html'
    })
    .done(function(data) {
      $('#c1').html(data);
    })
    .fail(function() {
      $('#c1').html('');
    });
  }

  if (window.KTouchKeyboard) {
    window.KTouchKeyboard.init($('#load'), {
      inputSelector: '[data-vk-layout]',
      hostSelector: '[data-vk-host]',
      title: 'Payment Keyboard'
    });
  }
</script>
