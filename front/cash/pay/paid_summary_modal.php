<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}

$today_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift' AND ord_date='$shift'");
$previous_paid = (float)column_total('kp_sales_paid', 'paid_total', "ord_date!=pay_date AND pay_date='$shift'");
$total_paid = (float)column_total('kp_sale_payment', 'paid_total', "pay_date='$shift'");
$payments = mysqli_query($connection, "SELECT * FROM kp_sale_payment WHERE pay_date='$shift' ORDER BY pay_id DESC LIMIT 200") or die(mysqli_error($connection));
?>
<div class="modal-dialog modal-lg" role="document" style="max-width:1120px;">
  <div class="modal-content" style="border-radius:18px; overflow:hidden;">
    <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
      <h5 class="modal-title" style="font-weight:900; color:#7a491b;">Paid Summary</h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body" style="background:#fffdf9;">
      <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:10px; margin-bottom:14px;">
        <div style="border:1px solid #eadfce; border-radius:14px; background:#fff; padding:12px;">
          <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Paid Today</strong>
          <span style="display:block; margin-top:6px; font-size:22px; font-weight:900;"><?php echo number_format($today_paid, 2); ?></span>
        </div>
        <div style="border:1px solid #eadfce; border-radius:14px; background:#fff; padding:12px;">
          <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Previous Paid</strong>
          <span style="display:block; margin-top:6px; font-size:22px; font-weight:900;"><?php echo number_format($previous_paid, 2); ?></span>
        </div>
        <div style="border:1px solid #eadfce; border-radius:14px; background:#fff; padding:12px;">
          <strong style="display:block; font-size:11px; color:#9a6a33; text-transform:uppercase;">Total Paid</strong>
          <span style="display:block; margin-top:6px; font-size:22px; font-weight:900;"><?php echo number_format($total_paid, 2); ?></span>
        </div>
      </div>
      <div style="border:1px solid #eadfce; border-radius:16px; overflow:hidden; background:#fff;">
        <div style="padding:12px 14px; background:#fff8ef; border-bottom:1px solid #eadfce;">
          <strong style="color:#6b4b25;">Paid List for Shift <?php echo htmlspecialchars($shift); ?></strong>
        </div>
        <div style="max-height:60vh; overflow:auto;">
          <table class="table table-striped" style="margin-bottom:0;">
            <thead>
              <tr>
                <th>Pay ID</th>
                <th>Bills</th>
                <th>Cashier</th>
                <th>Type</th>
                <th>Order Date</th>
                <th class="text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$payments || mysqli_num_rows($payments) < 1) { ?>
                <tr><td colspan="6">No paid items found for this shift.</td></tr>
              <?php } else { ?>
                <?php while ($payment = mysqli_fetch_assoc($payments)) { ?>
                  <?php
                  $typeLabel = ((string)$payment['ord_date'] === (string)$payment['pay_date']) ? 'Today' : 'Previous';
                  $cashier = get_column('kp_users', 'username', "user_id='" . (int)$payment['user_id'] . "'");
                  ?>
                  <tr>
                    <td><strong><?php echo (int)$payment['pay_id']; ?></strong></td>
                    <td style="min-width:220px;"><?php echo htmlspecialchars((string)$payment['sale_id']); ?></td>
                    <td><?php echo htmlspecialchars((string)$cashier); ?></td>
                    <td><?php echo $typeLabel; ?></td>
                    <td><?php echo htmlspecialchars((string)$payment['ord_date']); ?></td>
                    <td class="text-right"><?php echo number_format((float)$payment['paid_total'], 2); ?></td>
                  </tr>
                <?php } ?>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
