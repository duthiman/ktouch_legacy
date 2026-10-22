<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (file_exists('../../../inc/connection.php')) {
  include('../../../inc/connection.php');
} elseif (file_exists('../../inc/connection.php')) {
  include('../../inc/connection.php');
}
require_once('../funcs.php');

$cust_id = isset($_REQUEST['cust_id']) ? (int)$_REQUEST['cust_id'] : 0;
$customer_q = mysqli_query($connection,"SELECT * FROM kp_customers WHERE cust_id='$cust_id' LIMIT 1") or die(mysqli_error($connection));
$customer = mysqli_num_rows($customer_q) > 0 ? mysqli_fetch_assoc($customer_q) : null;
$pay_modes = mysqli_query($connection,"SELECT pay_id,name FROM kp_pay_mode WHERE del_status=2 ORDER BY name ASC") or die(mysqli_error($connection));
?>
<style type="text/css">
  .cash-credit-pay-shell .modal-content {
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .cash-credit-pay-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #14532d 0%, #277246 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .cash-credit-pay-shell .modal-title {
    margin: 0;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-credit-pay-shell .modal-body {
    padding: 16px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .cash-credit-pay-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    gap: 10px;
  }
  .cash-credit-pay-shell .form-control {
    height: 44px;
    border-radius: 10px;
    margin-bottom: 10px;
  }
  .cash-credit-pay-shell textarea.form-control {
    height: auto;
    min-height: 90px;
  }
  .cash-credit-pay-card {
    margin-bottom: 14px;
    padding: 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid rgba(146,110,52,0.12);
  }
  .cash-credit-pay-card strong,
  .cash-credit-pay-card span {
    display: block;
  }
  .cash-credit-pay-card strong {
    margin-bottom: 4px;
    color: #14532d;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .cash-credit-pay-card span {
    color: #332411;
    font-size: 15px;
    font-weight: 800;
  }
  .cash-credit-pay-btn {
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
  .cash-credit-pay-close {
    background: #ead6d6;
    color: #7e2014;
  }
  .cash-credit-pay-save {
    background: linear-gradient(135deg, #14532d 0%, #277246 100%);
    color: #fff;
  }
</style>
<div class="cash-credit-pay-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Pay Customer Credit</h4>
    </div>
    <div class="modal-body">
      <div id="customer-credit-pay-results"></div>
      <?php if (!$customer) { ?>
        <div class="alert alert-warning">Customer not found. Refresh and try again.</div>
      <?php } else { ?>
        <div class="cash-credit-pay-card">
          <strong>Customer</strong>
          <span><?php echo htmlspecialchars($customer['name']); ?></span>
        </div>
        <div class="cash-credit-pay-card">
          <strong>Current Balance</strong>
          <span><?php echo number_format((float)$customer['bal'], 2); ?></span>
        </div>
        <form id="customer-credit-pay-form">
          <input type="hidden" name="cust_id" value="<?php echo $cust_id; ?>">
          <div class="form-group">
            <label>Amount</label>
            <input type="number" step="0.01" min="0" max="<?php echo (float)$customer['bal']; ?>" name="amount" class="form-control" placeholder="Enter amount">
          </div>
          <div class="form-group">
            <label>Payment Mode</label>
            <select name="pay_mode_id" class="form-control">
              <option value="0">Select mode</option>
              <?php while ($mode = mysqli_fetch_assoc($pay_modes)) { ?>
                <option value="<?php echo $mode['pay_id']; ?>"><?php echo htmlspecialchars($mode['name']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label>Reference</label>
            <input type="text" name="reference_no" class="form-control" placeholder="Receipt / code / ref">
          </div>
          <div class="form-group">
            <label>Note</label>
            <textarea name="note" class="form-control" placeholder="Short note"></textarea>
          </div>
        </form>
      <?php } ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="cash-credit-pay-btn cash-credit-pay-close" data-dismiss="modal">Close</button>
      <?php if ($customer) { ?>
        <button type="button" class="cash-credit-pay-btn cash-credit-pay-save" id="save-customer-credit-pay">Record Payment</button>
      <?php } ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).off('click.cashCustomerCreditPay').on('click.cashCustomerCreditPay', '#save-customer-credit-pay', function(e){
    e.preventDefault();
    $.ajax({
      url: 'CUSTCRED/customer_credit_pay.php',
      type: 'POST',
      data: $('#customer-credit-pay-form').serialize(),
      dataType: 'json'
    })
    .done(function(data){
      $('#customer-credit-pay-results').html('<div class="alert alert-' + (data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'danger')) + '">' + data.message + '</div>');
      if (data.status === 'success') {
        if (typeof loadCreditPanel === 'function') {
          loadCreditPanel();
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
      $('#customer-credit-pay-results').html('<div class="alert alert-danger">Could not record customer credit payment.</div>');
    });
  });
</script>
