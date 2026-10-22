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
    $fallback_shift = (string)get_column('kp_sale_items', 'shift', "sale_id='$sale_id'");
    $fallback_counter = (int)get_column('kp_sale_items', 'counter', "sale_id='$sale_id'");
    if ($fallback_waiter < 1 && $fallback_counter < 1 && column_count('kp_sale_items', 'item_id', "sale_id='$sale_id'") < 1) {
        exit('Sale not found');
    }
    $sale = [
        'user_id' => $fallback_waiter,
        'shift' => $fallback_shift,
        'counter' => $fallback_counter,
    ];
}
$shift = $sale['shift'];
$waiter = $sale['user_id'];
$counter = $sale['counter'];
$thermalCssPath = realpath(__DIR__ . '/../assets/css/thermal_print.css');
$thermalCss = ($thermalCssPath && is_file($thermalCssPath)) ? file_get_contents($thermalCssPath) : '';
$delta_only = (int)($_GET['delta_only'] ?? 0) === 1;
$batch_id = (int)($_GET['batch_id'] ?? 0);
$nos = 50;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cashier Order #<?php echo $sale_id; ?></title>
   <style>
    <?php
    $print_template = get_print_template();
     if ($print_template === 2): ?>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background: #fff;
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      color: #000;
      width: 80mm;
    }
    #receipt {
      margin: 0 auto;
      width: 79mm;
      max-width: 79mm;
      background: #fff;
      padding: 1.5mm 0;
    }
    #bord {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      border: 2px solid #000 !important;
    }
    .sep-eq { border: none; border-top: 2px solid #000; margin: 3px 0; }
    .sep-dot { border: none; border-top: 1px dashed #555; margin: 3px 0; }
    .text-center { text-align: center !important; }
    .receipt-divider { clear: both; display: block; margin: 3px 0 0; line-height: 1.1; white-space: nowrap; overflow: hidden; text-align: center; font-weight: 700; letter-spacing: .06em; }
    .meta {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin: 1px 0;
    }
    .meta span { white-space: nowrap; }
    .meta.center { justify-content: center; text-align: center; }
    h1.title {
      font-size: 22px;
      margin: 4px 0 2px;
      letter-spacing: 2px;
    }
    .items-tbl,
    .totals-tbl {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      margin: 2px 0;
      table-layout: fixed;
    }
    .items-tbl th {
      font-size: 11px;
      border-top: 1px solid #000;
      border-bottom: 1px solid #000;
      padding: 2px 1px;
      text-transform: uppercase;
    }
    .items-tbl th:nth-child(1),
    .items-tbl td:nth-child(1) { width: 12%; }
    .items-tbl th:nth-child(2),
    .items-tbl td:nth-child(2) { width: 46%; }
    .items-tbl th:nth-child(3),
    .items-tbl td:nth-child(3),
    .items-tbl th:nth-child(4),
    .items-tbl td:nth-child(4) { width: 21%; }
    .items-tbl td,
    .totals-tbl td {
      font-size: 12px;
      padding: 2px 1px;
      border: none;
    }
    .items-tbl td:nth-child(2) {
      font-size: 13px;
      font-weight: 700;
      line-height: 1.2;
      word-break: break-word;
    }
    .items-tbl td:nth-child(3),
    .items-tbl td:nth-child(4) {
      font-size: 12px;
      font-weight: 600;
    }
    .items-tbl th:first-child,
    .items-tbl td:first-child,
    .totals-tbl td:first-child { text-align: left; }
    .items-tbl th:not(:first-child),
    .items-tbl td:not(:first-child),
    .totals-tbl td:last-child { text-align: right; }
    .sec-label td {
      font-size: 11px;
      font-weight: bold;
      text-decoration: underline;
      padding-top: 4px;
    }
    .grand td {
      font-size: 16px;
      font-weight: bold;
      padding-top: 3px;
    }
    .mpesa-line {
      text-align: center;
      font-size: 13px;
      font-weight: bold;
      letter-spacing: 1px;
      margin: 3px 0;
    }
    .open-customer-box {
      margin: 6px 0 8px;
      padding: 6px 0 2px;
      border-top: 2px solid #000;
      border-bottom: 2px solid #000;
      width: 100%;
    }
    .open-customer-head {
      margin: 0 0 4px;
      text-align: center;
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: .08em;
    }
    .open-customer-line {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin: 2px 0;
      font-size: 12px;
      font-weight: 700;
    }
    .open-customer-line strong:last-child {
      text-align: right;
    }
    .open-customer-total {
      margin-top: 5px;
      padding-top: 4px;
      border-top: 1px dashed #555;
      font-size: 15px;
      font-weight: 900;
    }
    .open-demo-summary {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      margin: 4px 0;
      table-layout: fixed;
    }
    .open-demo-summary th {
      padding: 3px 1px;
      text-align: center;
      border: none;
      font-weight: 900;
    }
    .open-demo-summary .bill-no th {
      font-size: 16px;
    }
    .open-demo-summary .line-label th,
    .open-demo-summary .line-value th {
      font-size: 14px;
    }
    .open-demo-summary .prev-bill th {
      font-size: 12px;
    }
    .open-demo-summary .total-label th,
    .open-demo-summary .total-value th {
      font-size: 22px;
    }
    .qr-wrap {
      text-align: center;
      margin: 6px 0;
    }
    .qr-wrap img,
    .qr-wrap canvas {
      display: block;
      margin: 0 auto;
      background: #fff;
      max-width: 100%;
      height: auto;
      image-rendering: pixelated;
    }
    .qr-box {
      display: inline-block;
      padding: 6px;
      background: #fff;
      border: 1px solid #000;
    }
    .qr-caption {
      margin: 3px 0 0;
      font-size: 10px;
      text-align: center;
    }
    @media print {
      body {
        margin: 0 !important;
        padding: 0 !important;
        width: 80mm !important;
        background: #fff;
      }
      #receipt {
        width: 79mm !important;
        max-width: 79mm !important;
        padding: 1.5mm 0 !important;
        background: #fff;
      }
      #bord {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
      }
    }
    <?php else: ?>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background: #fff;
      color: #000;
      font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
      width: 80mm;
    }
    #receipt {
      padding: 1.5mm 0;
      margin: 0 auto;
      width: 79mm;
      max-width: 79mm;
      background: #fff;
    }
    #bord {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      border: 2px solid #000 !important;
    }
    .text-center { text-align: center !important; }
    .receipt-divider { margin: 0; line-height: 1.1; white-space: nowrap; overflow: hidden; text-align: center; font-weight: 700; letter-spacing: .06em; }
    .pull-left { float: left !important; }
    .pull-right { float: right !important; }
    h1.title { font-size: 26px; margin-bottom: 0; }
    table {
      padding: 0;
      margin: 0 auto;
      width: 100%;
      max-width: 100%;
      background: #fff;
      border-collapse: collapse;
      table-layout: fixed;
    }
    td, th {
      font-size: 12px;
      border: 1px solid #000 !important;
    }
    th {
      font-size: 13px;
      padding: 2px;
      margin: 2px;
    }
    #deSo th:nth-child(1),
    #deSo td:nth-child(1) { width: 12%; }
    #deSo th:nth-child(2),
    #deSo td:nth-child(2) { width: 46%; }
    #deSo th:nth-child(3),
    #deSo td:nth-child(3),
    #deSo th:nth-child(4),
    #deSo td:nth-child(4) { width: 21%; }
    #deSo td:nth-child(2) {
      font-size: 13px;
      font-weight: bold;
      line-height: 1.2;
      word-break: break-word;
    }
    #deSo td:nth-child(3),
    #deSo td:nth-child(4) {
      font-size: 12px;
      font-weight: 600;
    }
    .open-customer-box {
      margin: 6px auto 8px;
      padding: 6px 4px;
      width: 100%;
      border: 2px solid #000;
        background: #fff;
    }
    .open-customer-head {
      margin: 0 0 5px;
      text-align: center;
      font-size: 13px;
      font-weight: 900;
      text-transform: uppercase;
    }
    .open-customer-line {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin: 3px 0;
      font-size: 13px;
      font-weight: 800;
    }
    .open-customer-total {
      margin-top: 5px;
      padding-top: 5px;
      border-top: 2px solid #000;
      font-size: 16px;
      font-weight: 900;
    }
    .open-demo-summary {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      margin: 4px 0;
      background: #fff;
      table-layout: fixed;
    }
    .open-demo-summary th {
      padding: 3px 1px;
      text-align: center;
      border: 1px solid #000 !important;
      font-weight: 900;
    }
    .open-demo-summary .bill-no th {
      font-size: 20px;
    }
    .open-demo-summary .line-label th,
    .open-demo-summary .line-value th {
      font-size: 14px;
    }
    .open-demo-summary .total-label th,
    .open-demo-summary .total-value th {
      font-size: 22px;
    }
    .qr-wrap {
      text-align: center;
      margin: 6px 0;
    }
    .qr-wrap img,
    .qr-wrap canvas {
      display: block;
      margin: 0 auto;
      background: #fff;
      max-width: 100%;
      height: auto;
      image-rendering: pixelated;
    }
    .qr-box {
      display: inline-block;
      padding: 6px;
      background: #fff;
      border: 1px solid #000;
    }
    .qr-caption {
      margin: 3px 0 0;
      font-size: 10px;
      text-align: center;
    }
    @media print {
      body {
        margin: 0 !important;
        padding: 0 !important;
        width: 80mm !important;
        background: #fff;
      }
      #receipt {
        width: 79mm !important;
        max-width: 79mm !important;
        padding: 0.5mm 0 !important;
        background: #fff;
      }
      #bord {
        width: 79mm !important;
        max-width: 80mm !important;
        box-sizing: border-box !important;
      }
      table {
        width: 78mm !important;
        max-width: 79mm !important;
      }
      td, th { font-size: 13px; border: 1.5px solid #000 !important; }
    }
    <?php endif; ?>
  </style>
</head>
<body>
<div id="receipt">
  <div id="bord">
    <div id="receipt-header" class="text-center">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h1 style="margin:0;"><?php echo get_counter($counter); ?></h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">CASHIER ORDER TICKET</h2>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">WAITER: <?php echo strtoupper(get_column('kp_users', 'username', "user_id='$waiter'")); ?></h2>
      <h2 style="margin:0;">Order No: <?php echo $sale_id; ?></h2>
      <h2 style="margin:0;"><strong>SHIFT: <?php echo $shift; ?></strong></h2>
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
          $sections = [
              1 => 'BAR ITEMS',
              2 => 'KITCHEN ITEMS',
          ];
          $sum = 0;
          foreach ($sections as $dpt => $label) {
              if ($delta_only && $batch_id > 0) {
                  $r = mysqli_query($connection,
                      "SELECT SUM(qty) AS sqty, price, item_id, attr_id
                       FROM kp_print_ticket_items
                       WHERE batch_id='$batch_id' AND sale_id='$sale_id' AND dpt='$dpt'
                       GROUP BY item_id, attr_id"
                  );
                  if (!$r || mysqli_num_rows($r) < 1) {
                      $r = mysqli_query($connection,
                          "SELECT SUM(qty) AS sqty, price, item_id, attr_id
                           FROM kp_sale_items
                           WHERE sale_id='$sale_id' AND dpt='$dpt'
                           GROUP BY item_id, attr_id"
                      );
                  }
              } else {
                  $sourceTable = 'kp_sale_items';
                  $r = mysqli_query($connection,
                      "SELECT SUM(qty) AS sqty, price, item_id, attr_id
                       FROM $sourceTable
                       WHERE sale_id='$sale_id' AND dpt='$dpt'
                       GROUP BY item_id, attr_id"
                  );
              }
              if (!$r || mysqli_num_rows($r) < 1) {
                  continue;
              }
              echo '<tr><td colspan="4"><b>' . htmlspecialchars($label) . '</b></td></tr>';
              while ($o = mysqli_fetch_assoc($r)) {
                  $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
                  if (!empty($o['attr_id'])) {
                      $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
                  }
                  $line = $o['sqty'] * $o['price'];
                  $sum += $line;
                  echo '<tr><td>' . $iname . '</td><td>' . $o['sqty'] . '</td><td>' . number_format((float)$o['price'], 2) . '</td><td>' . number_format($line, 2) . '</td></tr>';
              }
          }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-center"><b style="font-size:22px;">TOTAL: <?php echo number_format($sum, 2); ?></b></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <br>
  </div>
</div>
</body>
</html>
