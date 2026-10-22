<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../inc/connection.php');
require_once('funcs.php');

$cust_id = isset($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;
if ($cust_id < 1) {
  echo '<div class="cash-credit-existing-empty">Select a customer to view credit details.</div>';
  exit;
}

$customer_q = mysqli_query($connection,"SELECT cust_id,name,phone,bal FROM kp_customers WHERE cust_id='$cust_id' LIMIT 1") or die(mysqli_error($connection));
if (mysqli_num_rows($customer_q) < 1) {
  echo '<div class="cash-credit-existing-empty">Customer not found.</div>';
  exit;
}
$customer = mysqli_fetch_assoc($customer_q);
$recent = mysqli_query($connection,"SELECT trans_id,amount,trans_date,cf_bal FROM kp_cust_hist WHERE cust_id='$cust_id' AND trans='SALE' ORDER BY trans_date DESC, trans_id DESC LIMIT 8") or die(mysqli_error($connection));
?>
<div class="cash-credit-existing-card">
  <strong>Current Balance</strong>
  <span><?php echo number_format((float)$customer['bal'], 2); ?></span>
</div>
<div class="cash-credit-existing-card">
  <strong>Customer</strong>
  <span><?php echo htmlspecialchars($customer['name']); ?><?php if (!empty($customer['phone'])) { ?> | <?php echo htmlspecialchars($customer['phone']); ?><?php } ?></span>
</div>
<div class="cash-credit-existing-card cash-credit-existing-bills">
  <strong>Recent Credit Bills</strong>
  <?php if (mysqli_num_rows($recent) < 1) { ?>
    <div class="cash-credit-existing-empty">No previous credit bills for this customer.</div>
  <?php } else { ?>
    <table class="table table-striped" style="margin-bottom: 0;">
      <thead>
        <tr>
          <th>Bill</th>
          <th>Date</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($recent)) { ?>
          <tr>
            <td><?php echo (int)$row['trans_id']; ?></td>
            <td><?php echo htmlspecialchars($row['trans_date']); ?></td>
            <td><?php echo number_format((float)$row['amount'], 2); ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
</div>
