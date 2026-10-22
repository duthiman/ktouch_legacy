<?php
session_start();
require_once __DIR__ . '/../../inc/connection.php';
require_once __DIR__ . '/funcs.php';

$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}

$purchase = mysqli_query($connection, "SELECT * FROM kp_pay_mode WHERE 1 ORDER BY name ASC") or die(mysqli_error($connection));
$today_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift' AND ord_date='$shift'");
$previous_paid = (float)column_total('kp_sales_paid', 'paid_total', "ord_date!=pay_date AND pay_date='$shift'");
$total_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift'");
?>
<style type="text/css">
  .cashbox-shell {
    border: 1px solid #eadfce;
    border-radius: 18px;
    background: #fffdf9;
    overflow: hidden;
  }
  .cashbox-head {
    padding: 16px 18px;
    border-bottom: 1px solid #efe2d3;
    background: linear-gradient(135deg, #fff5e8 0%, #fffdfa 100%);
  }
  .cashbox-head h3 {
    margin: 0 0 4px;
    color: #744416;
    font-weight: 900;
  }
  .cashbox-head p {
    margin: 0;
    color: #816c57;
  }
  .cashbox-body {
    padding: 16px 18px 18px;
  }
  .cashbox-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
  }
  .cashbox-stat {
    border: 1px solid #eee2d6;
    border-radius: 14px;
    padding: 12px;
    background: #fff;
  }
  .cashbox-stat strong,
  .cashbox-stat span {
    display: block;
  }
  .cashbox-stat strong {
    color: #9c6b33;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cashbox-stat span {
    margin-top: 6px;
    color: #2d2419;
    font-size: 24px;
    font-weight: 900;
  }
  .cashbox-table-wrap {
    max-height: 520px;
    overflow: auto;
    border-radius: 16px;
    border: 1px solid #eee2d6;
    background: #fff;
  }
  .cashbox-table {
    margin: 0;
  }
  .cashbox-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fff7ec;
    color: #5c4330;
  }
  @media (max-width: 767px) {
    .cashbox-stats {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="cashbox-shell">
  <div class="cashbox-head">
    <h3>Cash Box</h3>
    <p>Today's payment dashboard by payment mode for shift <?php echo htmlspecialchars($shift); ?>.</p>
  </div>
  <div class="cashbox-body">
    <div class="cashbox-stats">
      <div class="cashbox-stat"><strong>Today's Paid</strong><span><?php echo number_format($today_paid, 2); ?></span></div>
      <div class="cashbox-stat"><strong>Previous Bills Paid</strong><span><?php echo number_format($previous_paid, 2); ?></span></div>
      <div class="cashbox-stat"><strong>Total Paid</strong><span><?php echo number_format($total_paid, 2); ?></span></div>
    </div>
    <div class="cashbox-table-wrap">
      <table class="table table-striped cashbox-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Action</th>
            <th>Mode</th>
            <th class="text-right">Today</th>
            <th class="text-right">Previous</th>
            <th class="text-right">Total</th>
            <th class="text-right">Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 0;
          if (!$purchase || mysqli_num_rows($purchase) < 1) {
              echo "<tr><td colspan='7'>No payment modes found.</td></tr>";
          } else {
              while ($pow = mysqli_fetch_assoc($purchase)) {
                  $pay_id = (int)$pow['pay_id'];
                  $name = $pow['name'];
                  $has_colle = (int)$pow['has_colle'];
                  $accbal = $pow['accbal'];
                  $col = "mode" . $pay_id;
                  $amount = (float)column_total('kp_sale_payment', $col, "pay_date='$shift' AND ord_date='$shift'");
                  $prevamount = (float)column_total('kp_sale_payment', $col, "pay_date='$shift' AND ord_date!='$shift'");
                  $total = (float)column_total('kp_sale_payment', $col, "pay_date='$shift'");
                  $no++;
                  ?>
                  <tr>
                    <td><strong><?php echo $no; ?></strong></td>
                    <td>
                      <?php if ($has_colle == 1) { ?>
                        <button id="colle" class="btn btn-xs btn-outline-primary" data-id="<?php echo $pay_id; ?>">Collect</button>
                      <?php } else { ?>
                        <span class="text-muted">-</span>
                      <?php } ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                    <td class="text-right"><?php echo number_format($amount, 2); ?></td>
                    <td class="text-right"><?php echo number_format($prevamount, 2); ?></td>
                    <td class="text-right"><?php echo number_format($total, 2); ?></td>
                    <td class="text-right"><?php echo number_format((float)$accbal, 2); ?></td>
                  </tr>
                  <?php
              }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
