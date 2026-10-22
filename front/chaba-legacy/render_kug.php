<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
include('../kug/funcs.php');

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$po_id = (int)($_GET['sale_id'] ?? $_GET['id'] ?? 0);
if ($po_id < 1) {
  exit('Invalid purchase receipt');
}

$po_q = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
$po = mysqli_fetch_assoc($po_q);
if (!$po) {
  exit('Purchase order not found');
}

$shift = get_column('kp_shift', 'shift', "1");
$sup_id = (int)$po['sup_id'];
$supplier_name = $sup_id > 0 ? get_sup($sup_id) : 'NOT SELECTED YET';
$counter_name = get_counter((int)$po['req_id']);
$thermalCssPath = realpath(__DIR__ . '/../assets/css/thermal_print.css');
$thermalCss = ($thermalCssPath && is_file($thermalCssPath)) ? file_get_contents($thermalCssPath) : '';
$grand_total = 0;
$total_qty = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchase Receipt #<?php echo $po_id; ?></title>
  <style>
    <?php echo $thermalCss; ?>
    .receipt-divider { margin:0; line-height:1.1; white-space:nowrap; overflow:hidden; text-align:center; font-weight:700; letter-spacing:.06em; }
    body, #receipt, table, #receipt-header, #receipt-body, #receipt-footer {
      background: #fff !important;
    }
  </style>
</head>
<body>
<div id="receipt">
  <div id="bord">
    <div id="receipt-header" class="text-center">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h1 style="margin:0;"><?php echo htmlspecialchars($counter_name); ?></h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">BUY STOCK ITEMS</h2>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">RAISED BY: <?php echo strtoupper(get_user($po['ord_user'])); ?></h2>
      <h2 style="margin:0;">PO No: <?php echo $po_id; ?></h2>
      <h2 style="margin:0;"><strong>SHIFT: <?php echo $shift; ?></strong></h2>
      <span><strong>PRINT TIME: <?php echo totime(); ?></strong></span><br>
      <span><strong>SUPPLIER: <?php echo htmlspecialchars($supplier_name); ?></strong></span><br>
      <span><strong>STATUS: PENDING BACKEND APPROVAL</strong></span>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <div id="receipt-body">
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Qty</th><th>Price</th><th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $items = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id' ORDER BY id ASC") or die(mysqli_error($connection));
          while ($row = mysqli_fetch_assoc($items)) {
            $grand_total += (float)$row['total'];
            $total_qty += (float)$row['qty'];
          ?>
          <tr>
            <td><?php echo htmlspecialchars(get_item($row['item_id'])); ?></td>
            <td><?php echo htmlspecialchars($row['qty']); ?></td>
            <td><?php echo number_format((float)$row['price'], 2); ?></td>
            <td><?php echo number_format((float)$row['total'], 2); ?></td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-center"><b style="font-size:18px;">TOTAL QTY: <?php echo number_format($total_qty, 2); ?></b></th>
          </tr>
          <tr>
            <th colspan="4" class="text-center"><b style="font-size:22px;">GRAND TOTAL: <?php echo number_format($grand_total, 2); ?></b></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <br>
  </div>
</div>
</body>
</html>
