<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../../inc/connection.php');
require_once('../funcs.php');

function ensure_credit_sales_columns() {
  global $connection;
  $columns = [
    "cust_id INT NOT NULL DEFAULT 0",
    "sale_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
    "cf_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
    "type INT NOT NULL DEFAULT 0"
  ];
  foreach ($columns as $definition) {
    $parts = explode(' ', $definition, 2);
    $column = $parts[0];
    $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE '$column'") or die(mysqli_error($connection));
    if (mysqli_num_rows($check) < 1) {
      mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN $definition") or die(mysqli_error($connection));
    }
  }
}

$cust_id = isset($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}
$today = date('Y-m-d');

ensure_credit_sales_columns();

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

$customer_q = mysqli_query($connection,"SELECT * FROM kp_customers WHERE cust_id='$cust_id' LIMIT 1") or die(mysqli_error($connection));
$customer = mysqli_num_rows($customer_q) > 0 ? mysqli_fetch_assoc($customer_q) : null;
$sales = $customer ? mysqli_query($connection,"SELECT sale_id,shift,amount,sale_bal,counter FROM kp_sales WHERE cust_id='$cust_id' AND pay_status=3 AND sale_bal>0 ORDER BY shift ASC, sale_id ASC") : false;
$has_sales_result = ($sales instanceof mysqli_result);
$sales_error = (!$has_sales_result && $customer) ? mysqli_error($connection) : '';
$pay_modes = mysqli_query($connection,"SELECT * FROM kp_pay_mode WHERE del_status=2 ORDER BY name ASC") or die(mysqli_error($connection));
?>
<style type="text/css">
  .credit-workbench-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
    gap: 14px;
    align-items: start;
  }
  .credit-workbench-card {
    padding: 14px;
    border-radius: 16px;
    background: #fff;
    border: 1px solid rgba(146,110,52,0.12);
    min-width: 0;
  }
  .credit-workbench-summary {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px;
  }
  .credit-workbench-pill {
    padding: 12px;
    border-radius: 14px;
    background: linear-gradient(180deg, #fffdfa 0%, #f6edd7 100%);
    border: 1px solid rgba(146,110,52,0.12);
  }
  .credit-workbench-pill strong,
  .credit-workbench-pill span {
    display: block;
  }
  .credit-workbench-pill strong {
    font-size: 11px;
    font-weight: 900;
    color: #7e3a20;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .credit-workbench-pill span {
    margin-top: 6px;
    font-size: 22px;
    font-weight: 900;
    color: #332411;
  }
  .credit-workbench-dual {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 8px;
  }
  .credit-workbench-dual strong,
  .credit-workbench-dual span {
    display: block;
  }
  .credit-workbench-dual strong {
    font-size: 10px;
    font-weight: 900;
    color: #7e3a20;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .credit-workbench-dual span {
    margin-top: 4px;
    font-size: 18px;
    font-weight: 900;
    color: #332411;
  }
  .credit-workbench-table-wrap {
    max-height: 380px;
    overflow-y: auto;
    border-radius: 14px;
    border: 1px solid rgba(146,110,52,0.12);
  }
  .credit-workbench-table {
    margin: 0;
  }
  .credit-workbench-table th,
  .credit-workbench-table td {
    vertical-align: middle !important;
    font-size: 12px;
  }
  .credit-workbench-table th {
    position: sticky;
    top: 0;
    background: #fbf4de;
    z-index: 1;
    color: #7e3a20;
    font-weight: 900;
  }
  .credit-workbench-mode {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 12px;
    background: #faf6ea;
  }
  .credit-workbench-mode .form-control {
    height: 40px;
    border-radius: 10px;
    margin-top: 8px;
  }
  .credit-workbench-status {
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff7dc;
    color: #8a6a3a;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 12px;
  }
  .credit-workbench-actions {
    display: flex;
    gap: 10px;
  }
  .credit-workbench-pay-btn {
    min-height: 46px;
    border: 0;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    flex: 1 1 0;
  }
  .credit-workbench-pay-btn.primary {
    background: linear-gradient(135deg, #14532d 0%, #277246 100%);
    color: #fff;
  }
  .credit-workbench-pay-btn.secondary {
    background: #ead6d6;
    color: #7e2014;
  }
  @media (max-width: 840px) {
    .credit-workbench-grid {
      grid-template-columns: 1fr;
    }
    .credit-workbench-summary {
      grid-template-columns: 1fr;
    }
  }
</style>
<?php if (!$customer) { ?>
  <div class="credit-workbench-status">Customer not found.</div>
<?php } else { ?>
  <?php
    $today_credit = column_total('kp_cust_hist','amount',"cust_id='$cust_id' AND trans='SALE' AND trans_date='$today'");
    $today_paid = column_total('kp_customer_credit_payment_items','amount',"cust_id='$cust_id' AND pay_date='$shift' AND ord_date='$shift'");
    $previous_paid = column_total('kp_customer_credit_payment_items','amount',"cust_id='$cust_id' AND pay_date='$shift' AND ord_date!='$shift'");
  ?>
  <div class="credit-workbench-summary">
    <div class="credit-workbench-pill"><strong>Credit Today</strong><span><?php echo number_format((float)$today_credit, 2); ?></span></div>
    <div class="credit-workbench-pill">
      <strong>Paid Credit</strong>
      <div class="credit-workbench-dual">
        <div>
          <strong>Today</strong>
          <span><?php echo number_format((float)$today_paid, 2); ?></span>
        </div>
        <div>
          <strong>Previous</strong>
          <span><?php echo number_format((float)$previous_paid, 2); ?></span>
        </div>
      </div>
    </div>
  </div>
  <div class="credit-workbench-grid">
    <div class="credit-workbench-card">
      <div class="credit-workbench-status">Outstanding credit invoices for <?php echo htmlspecialchars($customer['name']); ?>. Select the invoices being paid now.</div>
      <div class="credit-workbench-table-wrap">
        <table class="table table-striped credit-workbench-table">
          <thead>
            <tr>
              <th>Select</th>
              <th>Bill</th>
              <th>Shift</th>
              <th>Counter</th>
              <th>Balance</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$has_sales_result || mysqli_num_rows($sales) < 1) { ?>
              <tr><td colspan="5">No outstanding credit invoices for this customer.</td></tr>
            <?php } else { ?>
              <?php while ($sale = mysqli_fetch_assoc($sales)) { ?>
                <tr>
                  <td><input type="checkbox" class="credit-bill" value="<?php echo $sale['sale_id']; ?>" data-amount="<?php echo (float)$sale['sale_bal']; ?>" data-date="<?php echo htmlspecialchars($sale['shift']); ?>"></td>
                  <td><?php echo (int)$sale['sale_id']; ?></td>
                  <td><?php echo htmlspecialchars($sale['shift']); ?></td>
                  <td><?php echo htmlspecialchars(get_column('kp_counters','name',"cntr_id='".(int)$sale['counter']."'")); ?></td>
                  <td><?php echo number_format((float)$sale['sale_bal'], 2); ?></td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="credit-workbench-card">
      <div id="credit-pay-results"></div>
      <?php if (!empty($sales_error)) { ?>
        <div class="alert alert-warning">Could not load credit invoices fully: <?php echo htmlspecialchars($sales_error); ?></div>
      <?php } ?>
      <form id="customer-credit-pay-form">
        <input type="hidden" name="cust_id" value="<?php echo $cust_id; ?>">
        <input type="hidden" name="invoices" id="credit-pay-invoices" value="">
        <input type="hidden" name="tendered" id="credit-pay-tendered" value="0">
        <div class="credit-workbench-pill" style="margin-bottom: 10px;">
          <strong>Selected Balance</strong>
          <span id="credit-selected-total">0.00</span>
        </div>
        <div class="credit-workbench-pill" style="margin-bottom: 10px;">
          <strong>Payment Total</strong>
          <span id="credit-tendered-total">0.00</span>
        </div>
        <div class="credit-workbench-pill" style="margin-bottom: 10px;">
          <strong>Customer Balance</strong>
          <span><?php echo number_format((float)$customer['bal'], 2); ?></span>
        </div>
        <?php while ($mode = mysqli_fetch_assoc($pay_modes)) { ?>
          <div class="credit-workbench-mode">
            <label style="font-weight: 900;">
              <input type="checkbox" class="credit-mode-check" value="<?php echo $mode['pay_id']; ?>" data-ref="<?php echo (int)$mode['has_ref']; ?>">
              <?php echo htmlspecialchars($mode['name']); ?>
            </label>
            <?php if ((int)$mode['has_ref'] === 1) { ?>
              <input type="text" class="form-control credit-mode-ref" data-mode="<?php echo $mode['pay_id']; ?>" name="ref<?php echo $mode['pay_id']; ?>" placeholder="Reference / code" style="display: none;">
            <?php } ?>
            <input type="number" step="0.01" min="0" class="form-control credit-mode-amount" data-mode="<?php echo $mode['pay_id']; ?>" name="name<?php echo $mode['pay_id']; ?>" value="0" style="display: none;">
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
    $('.credit-bill:checked').each(function(){
      invoices.push($(this).val());
      total += parseFloat($(this).data('amount')) || 0;
    });
    $('#credit-pay-invoices').val(invoices.join(','));
    $('#credit-selected-total').text(total.toFixed(2));
    recalcCustomerCreditTendered();
  }

  function recalcCustomerCreditTendered() {
    var total = 0;
    var modes = [];
    $('.credit-mode-check:checked').each(function(){
      var mode = $(this).val();
      modes.push(mode);
      total += parseFloat($('.credit-mode-amount[data-mode="' + mode + '"]').val()) || 0;
    });
    $('#credit-pay-tendered').val(total.toFixed(2));
    $('#credit-tendered-total').text(total.toFixed(2));
    var selected = parseFloat($('#credit-selected-total').text()) || 0;
    if (selected > 0 && total > 0 && total <= selected && modes.length > 0) {
      $('#submit-credit-payment').show();
    } else {
      $('#submit-credit-payment').hide();
    }
  }

  $(document).off('change.creditBills').on('change.creditBills', '.credit-bill', function(){
    recalcCustomerCreditTotal();
  });

  $(document).off('change.creditModes').on('change.creditModes', '.credit-mode-check', function(){
    var mode = $(this).val();
    var ref = $(this).data('ref');
    if (this.checked) {
      $('.credit-mode-amount[data-mode="' + mode + '"]').show().val('0');
      if (parseInt(ref, 10) === 1) {
        $('.credit-mode-ref[data-mode="' + mode + '"]').show().val('');
      }
    } else {
      $('.credit-mode-amount[data-mode="' + mode + '"]').hide().val('0');
      $('.credit-mode-ref[data-mode="' + mode + '"]').hide().val('');
    }
    recalcCustomerCreditTendered();
  });

  $(document).off('keyup.creditAmounts change.creditAmounts').on('keyup.creditAmounts change.creditAmounts', '.credit-mode-amount', function(){
    recalcCustomerCreditTendered();
  });

  $(document).off('click.submitCreditPayment').on('click.submitCreditPayment', '#submit-credit-payment', function(e){
    e.preventDefault();
    $.ajax({
      url: 'CUSTCRED/customer_credit_pay.php',
      type: 'POST',
      data: $('#customer-credit-pay-form').serialize() + '&pay-mode[]=' + $('.credit-mode-check:checked').map(function(){ return $(this).val(); }).get().join('&pay-mode[]='),
      dataType: 'json'
    })
    .done(function(data){
      $('#credit-pay-results').html('<div class="alert alert-' + (data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'danger')) + '">' + data.message + '</div>');
      if (data.status === 'success') {
        if (typeof loadCreditPanel === 'function') {
          loadCreditPanel();
        }
        if (typeof loadCashLive === 'function') {
          loadCashLive();
        }
        setTimeout(function(){
          $('#modal').modal('hide');
          if (data.print_select_url) {
            window.location.href = data.print_select_url;
            return;
          }
          window.location.reload();
        }, 700);
      }
    })
    .fail(function(){
      $('#credit-pay-results').html('<div class="alert alert-danger">Could not record customer credit payment.</div>');
    });
  });
</script>
