<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$sale_id = (int)($_GET['sale_id'] ?? 0);
if ($sale_id < 1) { exit('Invalid sale'); }

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
$shift   = $sale['shift'];
$waiter  = $sale['user_id'];
$counter = $sale['counter'];
$thermalCssPath = realpath(__DIR__ . '/../assets/css/thermal_print.css');
$thermalCss = ($thermalCssPath && is_file($thermalCssPath)) ? file_get_contents($thermalCssPath) : '';
$delta_only = (int)($_GET['delta_only'] ?? 0) === 1;

function kp_ticket_attribute_summary($json) {
  $decoded = json_decode((string)$json, true);
  if (!is_array($decoded)) return [];
  foreach (['attributes', 'accompaniments', 'selections', 'items'] as $list_key) {
    if (isset($decoded[$list_key]) && is_array($decoded[$list_key])) {
      $decoded = $decoded[$list_key];
      break;
    }
  }
  // Also accept one attribute stored as a single object.
  if (isset($decoded['name']) || isset($decoded['label']) || isset($decoded['title'])) {
    $decoded = [$decoded];
  }
  $counts = [];
  foreach ($decoded as $attribute) {
    if (is_string($attribute)) { $name = trim($attribute); $key = strtolower($name); }
    elseif (is_array($attribute)) { $name = trim((string)($attribute['name'] ?? $attribute['label'] ?? $attribute['title'] ?? '')); $key = (string)($attribute['id'] ?? $attribute['attr_id'] ?? strtolower($name)); }
    else continue;
    if ($name === '') continue;
    if (!isset($counts[$key])) $counts[$key] = ['name' => $name, 'qty' => 0];
    $attribute_qty = is_array($attribute) ? (float)($attribute['qty'] ?? 1) : 1;
    $counts[$key]['qty'] += $attribute_qty > 0 ? $attribute_qty : 1;
  }
  return $counts;
}
$batch_id = (int)($_GET['batch_id'] ?? 0);

$nos = 50;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>BOT #<?php echo $sale_id; ?></title>
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
    .item-attributes {
      display: block;
      font-size: 11px;
      font-weight: 600;
      line-height: 1.2;
      margin-top: 1px;
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
    .item-attributes {
      display: block;
      font-size: 11px;
      font-weight: 600;
      line-height: 1.2;
      margin-top: 1px;
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
    /* Compact BOT item area to reduce thermal-paper usage. */
    #receipt-body table { margin: 0 !important; border-spacing: 0 !important; }
    #receipt-body table th { padding: 1px 2px !important; font-size: 11px !important; line-height: 1 !important; }
    #receipt-body table td { padding: 1px 2px !important; font-size: 12px !important; line-height: 1.08 !important; }
    #receipt-body table td:nth-child(1) { font-size: 13px !important; line-height: 1.08 !important; }
    #receipt-body .item-attributes { margin: 0 !important; font-size: 11px !important; line-height: 1.05 !important; }
    #receipt-body .bot-attribute-row td { font-size: 10px !important; line-height: 1 !important; padding: 1px 2px !important; border: 1px solid #000 !important; }
    #receipt-body .bot-attribute-row td:first-child { padding-left: 7px !important; font-weight: 600; }
    #receipt-body .bot-attribute-row td:nth-child(2) { text-align: right; font-weight: 700; }
    #receipt-body table tfoot th b { font-size: 17px !important; line-height: 1.05 !important; }
    #receipt-header {
      display: block !important;
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
      line-height: 1 !important;
      box-sizing: border-box !important;
    }
    #receipt-header h1 { font-size: 18px !important; line-height: 1 !important; margin: 0 !important; }
    #receipt-header h2 { font-size: 14px !important; line-height: 1 !important; margin: 0 !important; }
    #receipt-header span { display: block; font-size: 11px !important; line-height: 1 !important; margin: 0 !important; }
    #receipt-header .receipt-divider { margin: 0 !important; line-height: .9 !important; font-size: 10px !important; }

    /* BOT layout: emphasize the counter/waiter and give the item name the
       available width while keeping quantity compact. */
    #receipt-header .bot-meta {
      font-size: 25px !important;
      font-weight: 900 !important;
      line-height: 1.05 !important;
      margin: 1px 0 !important;
    }
    #receipt-header .bot-waiter,
    #receipt-header .bot-shift {
      font-size: 22px !important;
      font-weight: 900 !important;
      line-height: 1.08 !important;
      margin: 1px 0 !important;
    }
    #receipt-header .bot-details {
      display: block !important;
      font-size: 14px !important;
      font-weight: 800 !important;
      line-height: 1.25 !important;
      margin: 3px 0 !important;
    }
    .items-tbl th:nth-child(1),
    .items-tbl td:nth-child(1) { width: 59% !important; }
    .items-tbl th:nth-child(2),
    .items-tbl td:nth-child(2) { width: 9% !important; }
    .items-tbl th:nth-child(3),
    .items-tbl td:nth-child(3),
    .items-tbl th:nth-child(4),
    .items-tbl td:nth-child(4) { width: 16% !important; }
    .items-tbl {
      table-layout: auto !important;
    }
    .items-tbl th:nth-child(1),
    .items-tbl td:nth-child(1) {
      width: auto !important;
    }
    .items-tbl th:nth-child(2),
    .items-tbl td:nth-child(2),
    .items-tbl th:nth-child(3),
    .items-tbl td:nth-child(3),
    .items-tbl th:nth-child(4),
    .items-tbl td:nth-child(4) {
      width: auto !important;
      white-space: nowrap;
    }
    .items-tbl .item-attributes {
      display: flex !important;
      align-items: baseline;
      justify-content: space-between;
      gap: 6px;
      width: 100%;
      padding-left: 8px;
      font-size: 10px !important;
      font-weight: 600;
    }
    .items-tbl .item-attributes::before {
      content: '\2022';
      margin-left: -7px;
      margin-right: 2px;
    }
    .items-tbl .item-attributes .attribute-name {
      flex: 1 1 auto;
      text-align: left;
    }
    .items-tbl .item-attributes strong {
      flex: 0 0 auto;
      min-width: 16px;
      text-align: right;
    }
    #receipt-header .receipt-divider {
      display: block !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
    }
  </style>
</head>
<body>
<div id="receipt">
  <div id="bord">
    
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
  
    <div id="receipt-header" class="text-center"> 
      <h1 class="bot-meta" style="font-size:22px;"><?php echo get_counter($counter); ?> BOT</h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 class="bot-waiter">WAITER: <?php echo strtoupper(get_column('kp_users', 'username', "user_id='$waiter'")); ?></h2>
      <h2 class="bot-shift">SHIFT: <?php echo $shift; ?></h2>
      <div class="bot-details">ORDER NO: <?php echo $sale_id; ?></div>
      <div class="bot-details">PRINT: <?php echo totime(); ?></div>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <div id="receipt-body">
      <table class="items-tbl">
        <thead>
          <tr>
            <th>Name</th><th>Qty</th><th>Price</th><th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($delta_only && $batch_id > 0) {
            $r = mysqli_query($connection,
              "SELECT *
               FROM kp_print_ticket_items
               WHERE batch_id='$batch_id' AND sale_id='$sale_id' AND dpt=1
               ORDER BY id ASC"
            );
            if (!$r || mysqli_num_rows($r) < 1) {
              $r = mysqli_query($connection,
                "SELECT *
                 FROM kp_sale_items
                 WHERE sale_id='$sale_id' AND dpt=1
                 ORDER BY id ASC"
              );
            }
          } else {
            $sourceTable = 'kp_sale_items';
            $r = mysqli_query($connection,
              "SELECT *
               FROM $sourceTable
               WHERE sale_id='$sale_id' AND dpt=1
               ORDER BY id ASC"
            );
          }
          $sum = 0;
          $grouped = [];
          while ($o = mysqli_fetch_assoc($r)) {
            $key = (int)$o['item_id'] . ':' . (int)($o['attr_id'] ?? 0);
            if (!isset($grouped[$key])) $grouped[$key] = ['item_id' => (int)$o['item_id'], 'attr_id' => (int)($o['attr_id'] ?? 0), 'qty' => 0, 'price' => (float)$o['price'], 'total' => 0, 'attrs' => []];
            $grouped[$key]['qty'] += (float)$o['qty'];
            $grouped[$key]['total'] += (float)$o['total'];
            $attr_summary = kp_ticket_attribute_summary($o['accompaniment_json'] ?? '');
            // attr_id is the selected item attribute/variant. Keep it visible
            // even when the accompaniment JSON is empty or unavailable.
            $row_attr_id = (int)($o['attr_id'] ?? 0);
            if ($row_attr_id > 0) {
              $row_attr_name = trim((string)get_column('kp_items_attributes', 'name', "id='$row_attr_id'"));
              if ($row_attr_name !== '') {
                $row_attr_key = 'id:' . $row_attr_id;
                $row_attr_exists = false;
                foreach ($attr_summary as $existing_attr) {
                  if (strcasecmp(trim((string)($existing_attr['name'] ?? '')), $row_attr_name) === 0) {
                    $row_attr_exists = true;
                    break;
                  }
                }
                if (!$row_attr_exists && !isset($attr_summary[$row_attr_key])) {
                  $attr_summary[$row_attr_key] = ['name' => $row_attr_name, 'qty' => (float)$o['qty']];
                }
              }
            }
            foreach ($attr_summary as $attr_key => $attr_value) {
              if (!isset($grouped[$key]['attrs'][$attr_key])) $grouped[$key]['attrs'][$attr_key] = ['name' => $attr_value['name'], 'qty' => 0];
              $grouped[$key]['attrs'][$attr_key]['qty'] += (int)$attr_value['qty'];
            }
          }
          foreach ($grouped as $o):
            $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
            if ($o['attr_id'] > 0) $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
            $line = $o['total'] > 0 ? $o['total'] : $o['qty'] * $o['price'];
            $sum += $line;
          ?>
          <tr>
            <td>
              <span><?php echo htmlspecialchars($iname, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php foreach ($o['attrs'] as $attribute): ?>
                <span class="item-attributes"><span class="attribute-name"><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></span><strong><?php echo htmlspecialchars($attribute['qty'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
              <?php endforeach; ?>
            </td>
            <td><?php echo $o['qty']; ?></td>
            <td><?php echo number_format((float)$o['price'], 2); ?></td>
            <td><?php echo number_format($line, 2); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-center">
              <b style="font-size:22px;">TOTAL: <?php echo number_format($sum, 2); ?></b>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
   
  </div>
</div>
</body>
</html>
