<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../../inc/connection.php');
require_once('../funcs.php');

$invoices = isset($_REQUEST['invoices']) ? trim($_REQUEST['invoices']) : '';
$amount = isset($_REQUEST['amount']) ? (float)$_REQUEST['amount'] : 0;
$customers = mysqli_query($connection,"SELECT cust_id,name,phone,bal FROM kp_customers ORDER BY name ASC") or die(mysqli_error($connection));
?>
<style type="text/css">
  .cash-credit-assign-shell .modal-content {
    width: 100%;
    max-width: 560px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .cash-credit-assign-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #14532d 0%, #277246 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .cash-credit-assign-shell .modal-title {
    margin: 0;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-credit-assign-shell .modal-body {
    padding: 16px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .cash-credit-assign-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    gap: 10px;
  }
  .cash-credit-assign-shell .form-control {
    height: 44px;
    border-radius: 10px;
    margin-bottom: 10px;
  }
  .cash-credit-assign-card {
    margin-bottom: 14px;
    padding: 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid rgba(146,110,52,0.12);
  }
  .cash-credit-assign-card strong,
  .cash-credit-assign-card span {
    display: block;
  }
  .cash-credit-assign-card strong {
    margin-bottom: 4px;
    color: #14532d;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .cash-credit-assign-card span {
    color: #332411;
    font-size: 15px;
    font-weight: 800;
  }
  .cash-credit-assign-note {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff;
    color: #14532d;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
  }
  .cash-credit-assign-btn {
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
  .cash-credit-assign-close {
    background: #ead6d6;
    color: #7e2014;
  }
  .cash-credit-assign-save {
    background: linear-gradient(135deg, #14532d 0%, #277246 100%);
    color: #fff;
  }
  .cash-credit-existing-info {
    margin-bottom: 12px;
  }
  .cash-credit-existing-card {
    margin-bottom: 10px;
    padding: 12px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid rgba(20,83,45,0.12);
  }
  .cash-credit-existing-card strong,
  .cash-credit-existing-card span {
    display: block;
  }
  .cash-credit-existing-card strong {
    margin-bottom: 4px;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    color: #14532d;
    letter-spacing: 0.05em;
  }
  .cash-credit-existing-bills {
    max-height: 150px;
    overflow-y: auto;
  }
  .cash-credit-existing-empty {
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff7dc;
    color: #8a6a3a;
    font-size: 12px;
    font-weight: 800;
  }
  .cash-credit-new-fields {
    border-top: 1px dashed rgba(20,83,45,0.18);
    padding-top: 12px;
  }
</style>
<div class="cash-credit-assign-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Send Bills To Customer Credit</h4>
    </div>
    <div class="modal-body">
      <p class="cash-credit-assign-note">Pick an existing customer or add a new one before moving the selected bills to credit.</p>
      <div id="credit-assign-results"></div>
      <div class="cash-credit-assign-card">
        <strong>Selected Bills</strong>
        <span><?php echo htmlspecialchars($invoices); ?></span>
      </div>
      <div class="cash-credit-assign-card">
        <strong>Total Amount</strong>
        <span><?php echo number_format($amount, 2); ?></span>
      </div>
      <form id="credit-assign-form">
        <input type="hidden" name="invoices" value="<?php echo htmlspecialchars($invoices, ENT_QUOTES); ?>">
        <div class="form-group">
          <label>Select Customer</label>
          <select name="cust_id" id="credit-customer-select" class="form-control">
            <option value="0">Select existing customer</option>
            <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
              <option value="<?php echo $customer['cust_id']; ?>"><?php echo htmlspecialchars($customer['name'].' | '.$customer['phone'].' | Bal '.number_format((float)$customer['bal'],2)); ?></option>
            <?php } ?>
          </select>
        </div>
        <div id="credit-existing-info" class="cash-credit-existing-info" style="display: none;"></div>
        <div id="credit-new-fields" class="cash-credit-new-fields">
        <div class="form-group">
          <label>New Customer Name</label>
          <input type="text" name="name" id="credit-new-name" class="form-control" placeholder="Customer name">
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" id="credit-new-phone" class="form-control" placeholder="07xx or +254...">
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="loc" class="form-control" placeholder="Location">
        </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="cash-credit-assign-btn cash-credit-assign-close" data-dismiss="modal">Close</button>
      <button type="button" class="cash-credit-assign-btn cash-credit-assign-save" id="save-credit-assignment">Send To Credit</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  function toggleCreditCustomerMode() {
    var custId = $('#credit-customer-select').val();
    if (custId && custId !== '0') {
      $('#credit-new-fields').hide();
      $('#credit-new-name').prop('required', false).val('');
      $('#credit-new-phone').prop('required', false).val('');
      $('#credit-existing-info').show().html('<div class="cash-credit-existing-empty">Loading customer balance and recent bills...</div>');
      $.ajax({
        url: 'customer_credit_lookup.php',
        type: 'GET',
        data: { cust_id: custId },
        dataType: 'html'
      })
      .done(function(data){
        $('#credit-existing-info').html(data);
      })
      .fail(function(){
        $('#credit-existing-info').html('<div class="cash-credit-existing-empty">Could not load customer credit details.</div>');
      });
    } else {
      $('#credit-existing-info').hide().html('');
      $('#credit-new-fields').show();
      $('#credit-new-name').prop('required', true);
      $('#credit-new-phone').prop('required', true);
    }
  }

  $(document).off('click.cashCreditAssign').on('click.cashCreditAssign', '#save-credit-assignment', function(e){
    e.preventDefault();
    $.ajax({
      url: 'CUSTCRED/credit_assign.php',
      type: 'POST',
      data: $('#credit-assign-form').serialize(),
      dataType: 'json'
    })
    .done(function(data){
      $('#credit-assign-results').html('<div class="alert alert-' + (data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'danger')) + '">' + data.message + '</div>');
      if (data.status === 'success') {
        setTimeout(function(){
          $('#modal').modal('hide');
          window.location.reload();
        }, 700);
      }
    })
    .fail(function(){
      $('#credit-assign-results').html('<div class="alert alert-danger">Could not send bills to credit.</div>');
    });
  });
  $(document).off('change.cashCreditCustomer').on('change.cashCreditCustomer', '#credit-customer-select', function(){
    toggleCreditCustomerMode();
  });
  toggleCreditCustomerMode();
</script>
