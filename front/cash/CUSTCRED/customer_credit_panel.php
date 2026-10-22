<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (file_exists('../../../inc/connection.php')) {
  include('../../../inc/connection.php');
} elseif (file_exists('../../inc/connection.php')) {
  include('../../inc/connection.php');
}

if (file_exists('../funcs.php')) {
  require_once('../funcs.php');
} elseif (file_exists('../../funcs.php')) {
  require_once('../../funcs.php');
}

$shift = isset($_GET['shift']) ? $_GET['shift'] : date('Y-m-d');
$today = date('Y-m-d');
$full = isset($_GET['full']) ? (int)$_GET['full'] : 0;

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payments (
  credit_pay_id INT NOT NULL AUTO_INCREMENT,
  cust_id INT NOT NULL DEFAULT 0,
  user_id INT NOT NULL DEFAULT 0,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  pay_mode_id INT NOT NULL DEFAULT 0,
  reference_no VARCHAR(120) NOT NULL DEFAULT '',
  note TEXT NULL,
  shift_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (credit_pay_id)
)") or die(mysqli_error($connection));

$limit = $full ? 50 : 10;
$customers = mysqli_query($connection,"SELECT cust_id,name,phone,bal FROM kp_customers WHERE bal > 0 ORDER BY bal DESC LIMIT $limit") or die(mysqli_error($connection));
?>
<style type="text/css">
  .cash-credit-table-wrap {
    max-height: <?php echo $full ? '420px' : '250px'; ?>;
    overflow-y: auto;
    border-radius: 14px;
    border: 1px solid rgba(146,110,52,0.12);
    background: #fff;
  }
  .cash-credit-table {
    margin: 0;
  }
  .cash-credit-table th,
  .cash-credit-table td {
    vertical-align: middle !important;
    font-size: 12px;
  }
  .cash-credit-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fbf4de;
    color: #7e3a20;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .cash-credit-balance {
    font-size: 14px;
    font-weight: 900;
    color: #0f766e;
    white-space: nowrap;
  }
  .cash-credit-pay {
    min-height: 30px;
    padding: 0 10px;
    border: 0;
    border-radius: 8px;
    background: #355f16;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-credit-empty {
    padding: 14px;
    border-radius: 14px;
    background: #fff7dc;
    color: #8a6a3a;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
  }
</style>
<?php if (mysqli_num_rows($customers) < 1) { ?>
  <div class="cash-credit-empty">No customers are currently on credit.</div>
<?php } else { ?>
  <div class="cash-credit-table-wrap">
    <table class="table table-striped cash-credit-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Phone</th>
          <th>Balance</th>
          <th>Today Credit</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($customers)) {
          $cust_id = (int)$row['cust_id'];
          $today_credit = column_total('kp_cust_hist','amount',"cust_id='$cust_id' AND trans='SALE' AND trans_date='$today'");
          ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
            <td><?php echo !empty($row['phone']) ? htmlspecialchars($row['phone']) : '-'; ?></td>
            <td><span class="cash-credit-balance"><?php echo number_format((float)$row['bal'], 2); ?></span></td>
            <td><?php echo number_format((float)$today_credit, 2); ?></td>
            <td><button type="button" class="cash-credit-pay" data-id="<?php echo $cust_id; ?>">Open</button></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php } ?>
