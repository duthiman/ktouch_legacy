<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../../../inc/connection.php');
require_once('../funcs.php');

$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}
$today = date('Y-m-d');
$mode = trim($_GET['mode'] ?? 'today');

$title = 'Credit Today';
$note = 'Customers who were moved to credit today.';
$rows = [];

if ($mode === 'paid_today') {
    $title = 'Paid Credit Today';
    $note = 'Credit invoices raised today and paid in this shift.';
    $q = mysqli_query($connection, "
        SELECT i.sale_id,i.ord_date,i.pay_date,i.amount,p.cust_id,c.name AS customer_name,m.name AS pay_mode_name
        FROM kp_customer_credit_payment_items i
        LEFT JOIN kp_customer_credit_payments p ON p.credit_pay_id=i.credit_pay_id
        LEFT JOIN kp_customers c ON c.cust_id=i.cust_id
        LEFT JOIN kp_pay_mode m ON m.pay_id=p.pay_mode_id
        WHERE i.pay_date='$shift' AND i.ord_date='$shift'
        ORDER BY i.credit_item_id DESC
        LIMIT 80
    ") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($q)) {
        $rows[] = $row;
    }
} elseif ($mode === 'paid_previous') {
    $title = 'Paid Previous Credit';
    $note = 'Earlier credit invoices paid during this shift.';
    $q = mysqli_query($connection, "
        SELECT i.sale_id,i.ord_date,i.pay_date,i.amount,p.cust_id,c.name AS customer_name,m.name AS pay_mode_name
        FROM kp_customer_credit_payment_items i
        LEFT JOIN kp_customer_credit_payments p ON p.credit_pay_id=i.credit_pay_id
        LEFT JOIN kp_customers c ON c.cust_id=i.cust_id
        LEFT JOIN kp_pay_mode m ON m.pay_id=p.pay_mode_id
        WHERE i.pay_date='$shift' AND i.ord_date!='$shift'
        ORDER BY i.credit_item_id DESC
        LIMIT 80
    ") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($q)) {
        $rows[] = $row;
    }
} else {
    $mode = 'today';
    $q = mysqli_query($connection, "
        SELECT h.trans_id,h.amount,h.trans_date,h.cust_id,c.name AS customer_name,c.phone
        FROM kp_cust_hist h
        LEFT JOIN kp_customers c ON c.cust_id=h.cust_id
        WHERE h.trans='SALE' AND h.trans_date='$today'
        ORDER BY h.trans_date DESC, h.trans_id DESC
        LIMIT 80
    ") or die(mysqli_error($connection));
    while ($row = mysqli_fetch_assoc($q)) {
        $rows[] = $row;
    }
}
?>
<style type="text/css">
  .credit-snapshot-shell {
    border: 1px solid #eadfce;
    border-radius: 18px;
    background: #fffdf9;
    overflow: hidden;
  }
  .credit-snapshot-head {
    padding: 16px 18px;
    border-bottom: 1px solid #efe2d3;
    background: linear-gradient(135deg, #fff7e8 0%, #fffdfa 100%);
  }
  .credit-snapshot-head h3 {
    margin: 0 0 4px;
    color: #744416;
    font-weight: 900;
  }
  .credit-snapshot-head p {
    margin: 0;
    color: #816c57;
  }
  .credit-snapshot-body {
    padding: 16px 18px 18px;
  }
  .credit-snapshot-table-wrap {
    max-height: 560px;
    overflow: auto;
    border: 1px solid #eee2d6;
    border-radius: 16px;
    background: #fff;
  }
  .credit-snapshot-table {
    margin: 0;
  }
  .credit-snapshot-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fff8ef;
  }
  .credit-snapshot-empty {
    padding: 20px;
    color: #84715d;
  }
</style>
<div class="credit-snapshot-shell">
  <div class="credit-snapshot-head">
    <h3><?php echo htmlspecialchars($title); ?></h3>
    <p><?php echo htmlspecialchars($note); ?></p>
  </div>
  <div class="credit-snapshot-body">
    <div class="credit-snapshot-table-wrap">
      <?php if (count($rows) < 1) { ?>
        <div class="credit-snapshot-empty">No records found for this view.</div>
      <?php } else { ?>
        <table class="table table-striped credit-snapshot-table">
          <thead>
            <tr>
              <?php if ($mode === 'today') { ?>
                <th>Customer</th>
                <th>Phone</th>
                <th>Bill</th>
                <th>Date</th>
                <th class="text-right">Amount</th>
              <?php } else { ?>
                <th>Customer</th>
                <th>Bill</th>
                <th>Original Date</th>
                <th>Paid Date</th>
                <th>Mode</th>
                <th class="text-right">Amount</th>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row) { ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($row['customer_name'] ?: 'Walk-in'); ?></strong></td>
                <?php if ($mode === 'today') { ?>
                  <td><?php echo !empty($row['phone']) ? htmlspecialchars($row['phone']) : '-'; ?></td>
                  <td><?php echo (int)$row['trans_id']; ?></td>
                  <td><?php echo htmlspecialchars($row['trans_date']); ?></td>
                  <td class="text-right"><?php echo number_format((float)$row['amount'], 2); ?></td>
                <?php } else { ?>
                  <td><?php echo (int)$row['sale_id']; ?></td>
                  <td><?php echo htmlspecialchars($row['ord_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['pay_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['pay_mode_name'] ?: 'Not Set'); ?></td>
                  <td class="text-right"><?php echo number_format((float)$row['amount'], 2); ?></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php } ?>
    </div>
  </div>
</div>
