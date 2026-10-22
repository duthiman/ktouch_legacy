<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../../inc/connection.php');
require_once('../funcs.php');
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}
$cust_id = isset($_REQUEST['cust_id']) ? (int)$_REQUEST['cust_id'] : 0;
$customers = mysqli_query($connection,"SELECT cust_id,name,phone,bal FROM kp_customers WHERE bal > 0 ORDER BY name ASC") or die(mysqli_error($connection));
?>
<style type="text/css">
  .cash-credit-shell .modal-content {
    width: 800px ;
    max-width: 800px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .cash-credit-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #14532d 0%, #277246 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .cash-credit-shell .modal-title {
    margin: 0;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-credit-shell .modal-body {
    padding: 16px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .cash-credit-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
  }
  .cash-credit-note {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff;
    color: #14532d;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
  }
  .cash-credit-toolbar {
    display: grid;
    grid-template-columns: 1.4fr 0.8fr auto;
    gap: 10px;
    margin-bottom: 14px;
  }
  .cash-credit-toolbar .form-control {
    height: 44px;
    border-radius: 10px;
  }
  .cash-credit-empty {
    padding: 16px;
    border-radius: 14px;
    background: #fff;
    color: #6b5b36;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
    border: 1px dashed rgba(20,83,45,0.18);
  }
  .cash-credit-statement {
    min-height: 44px;
    border: 0;
    border-radius: 12px;
    padding: 0 14px;
    background: #fff;
    color: #14532d;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    box-shadow: inset 0 0 0 2px rgba(20,83,45,0.12);
  }
  @media (max-width: 840px) {
    .cash-credit-shell .modal-content {
      max-width: 100%;
    }
    .cash-credit-toolbar {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="cash-credit-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Customer Credit Payment Desk</h4>
    </div>
    <div class="modal-body">
      <p class="cash-credit-note">Select a customer first, review open credit invoices, then take payment using one or more modes.</p>
      <div class="cash-credit-toolbar">
        <select id="credit-pay-customer" class="form-control">
          <option value="0">Select customer on credit</option>
          <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
            <option value="<?php echo $customer['cust_id']; ?>" <?php echo $cust_id === (int)$customer['cust_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($customer['name'].' | '.$customer['phone'].' | Bal '.number_format((float)$customer['bal'],2)); ?>
            </option>
          <?php } ?>
        </select>
        <input type="date" id="credit-statement-from" class="form-control" value="<?php echo $shift; ?>">
        <button type="button" id="print-credit-statement" class="cash-credit-statement">Print Statement</button>
      </div>
      <div id="customer-credit-workspace">
        <div class="cash-credit-empty">Choose a customer to load outstanding invoices and payment options.</div>
      </div>
    </div>
    <div class="modal-footer">
      <span></span>
      <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  function loadCustomerCreditWorkspace(custId) {
    if (!custId || custId === '0') {
      $('#customer-credit-workspace').html('<div class="cash-credit-empty">Choose a customer to load outstanding invoices and payment options.</div>');
      return;
    }
    $('#customer-credit-workspace').html('<div class="cash-credit-empty">Loading customer credit invoices...</div>');
    $.ajax({
      url: 'CUSTCRED/customer_credit_workbench.php',
      type: 'GET',
      data: { cust_id: custId },
      dataType: 'html'
    })
    .done(function(data){
      $('#customer-credit-workspace').html(data);
    })
    .fail(function(){
      $('#customer-credit-workspace').html('<div class="cash-credit-empty">Could not load customer credit invoices.</div>');
    });
  }

  $(document).off('change.cashCreditSelect').on('change.cashCreditSelect', '#credit-pay-customer', function(){
    loadCustomerCreditWorkspace($(this).val());
  });

  $(document).off('click.cashCreditStatement').on('click.cashCreditStatement', '#print-credit-statement', function(){
    var custId = $('#credit-pay-customer').val();
    var fromDate = $('#credit-statement-from').val();
    if (!custId || custId === '0') {
      alert('Select customer first.');
      return;
    }
    window.open('CUSTCRED/customer_statement.php?cust_id=' + encodeURIComponent(custId) + '&from=' + encodeURIComponent(fromDate), '_blank');
  });

  loadCustomerCreditWorkspace($('#credit-pay-customer').val());
</script>
