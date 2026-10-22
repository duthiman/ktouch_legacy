<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$sale_id = (int)($_GET['sale_id'] ?? 0);
if ($sale_id < 1) {
    exit('Invalid sale');
}

$sale_r = mysqli_query($connection, "SELECT * FROM kp_sales WHERE sale_id='$sale_id'");
$sale = ($sale_r && mysqli_num_rows($sale_r) > 0) ? mysqli_fetch_assoc($sale_r) : null;
if (!$sale) {
    $fallback_waiter = (int)get_column('kp_sale_items', 'user_id', "sale_id='$sale_id'");
    if ($fallback_waiter < 1) {
        $fallback_waiter = (int)get_column('kp_sale_print', 'user_id', "sale_id='$sale_id'");
    }
    $fallback_shift = (string)get_column('kp_sale_items', 'shift', "sale_id='$sale_id'");
    if ($fallback_shift === '' || $fallback_shift === '0') {
        $fallback_shift = (string)get_column('kp_sale_print', 'shift', "sale_id='$sale_id'");
    }
    if ($fallback_waiter < 1 && column_count('kp_sale_items', 'item_id', "sale_id='$sale_id'") < 1) {
        exit('Sale not found');
    }
    $sale = [
        'user_id' => $fallback_waiter,
        'shift' => $fallback_shift,
    ];
}
$shift = $sale['shift'];
$waiter = $sale['user_id'];
$thermalCssPath = realpath(__DIR__ . '/../assets/css/thermal_print.css');
$thermalCss = ($thermalCssPath && is_file($thermalCssPath)) ? file_get_contents($thermalCssPath) : '';
$nos = 50;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GOT #<?php echo $sale_id; ?></title>
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
      <h1 style="margin:0;">GRILL ORDER TICKET</h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;"><u>WAITER: <?php echo strtoupper(get_column('kp_users', 'username', "user_id='$waiter'")); ?></u></h2>
      <h2 style="margin:0;">Order No: <?php echo $sale_id; ?></h2>
      <span><strong>SHIFT: <?php echo $shift; ?></strong></span><br>
      <span><strong>PRINT TIME: <?php echo totime(); ?></strong></span>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <div id="receipt-body">
      <table>
        <thead>
          <tr><th>Name</th><th>Qty</th><th>Price</th><th>Total</th></tr>
        </thead>
        <tbody>
          <?php
          $r = mysqli_query($connection,
            "SELECT SUM(qty) AS sqty, price, item_id, attr_id
             FROM kp_sale_items
             WHERE sale_id='$sale_id' AND dpt=3
             GROUP BY item_id, attr_id"
          );
          $sum = 0;
          while ($r && ($o = mysqli_fetch_assoc($r))) {
              $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
              if (!empty($o['attr_id'])) {
                  $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
              }
              $line = $o['sqty'] * $o['price'];
              $sum += $line;
              echo '<tr><td>' . $iname . '</td><td>' . $o['sqty'] . '</td><td>' . number_format((float)$o['price'], 2) . '</td><td>' . number_format($line, 2) . '</td></tr>';
          }
          ?>
        </tbody>
        <tfoot>
          <tr><th colspan="4" class="text-center"><b style="font-size:22px;">TOTAL: <?php echo number_format($sum, 2); ?></b></th></tr>
        </tfoot>
      </table>
    </div>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <br>
  </div>
</div>
</body>
</html>
