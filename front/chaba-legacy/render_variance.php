<?php
session_start();
include_once('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include_once('../../back/counter/funcs.php');

if (!function_exists('kp_variance_embed_logo')) {
  function kp_variance_embed_logo($src) {
    $src = trim((string)$src);
    if ($src === '' || stripos($src, 'data:') === 0) {
      return $src;
    }
    $path = realpath(__DIR__ . '/../../' . ltrim(str_replace('\\', '/', $src), '/'));
    if (!$path || !is_file($path) || !is_readable($path)) {
      return $src;
    }
    $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'image/png';
    $data = @file_get_contents($path);
    if ($data === false) {
      return $src;
    }
    return 'data:' . $mime . ';base64,' . base64_encode($data);
  }
}

$summary_id = (int)($_GET['summary_id'] ?? 0);
$counter = (int)($_GET['cntr_id'] ?? 0);
$shift = trim((string)($_GET['shift'] ?? ''));
if ($summary_id > 0) {
  $closeSummaryQ = mysqli_query($connection, "SELECT * FROM kp_counter_close_summary WHERE close_sum_id='$summary_id' LIMIT 1");
  $closeSummaryRow = $closeSummaryQ ? mysqli_fetch_assoc($closeSummaryQ) : null;
  if ($closeSummaryRow) {
    if ($counter < 1) {
      $counter = (int)($closeSummaryRow['cntr_id'] ?? 0);
    }
    if ($shift === '') {
      $shift = (string)($closeSummaryRow['shift_date'] ?? '');
    }
  }
}
if ($counter < 1) {
  $user_id = (int)($_SESSION['user_id'] ?? 0);
  $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
}
if ($counter < 1) {
  exit('Invalid counter');
}
if ($shift === '') {
  $shift = (string)get_column('kp_shift', 'shift', "1");
}
$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);
$thermalCssPath = realpath(__DIR__ . '/../assets/css/thermal_print.css');
$thermalCss = ($thermalCssPath && is_file($thermalCssPath)) ? file_get_contents($thermalCssPath) : '';
$spr = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1");
$printRow = $spr ? mysqli_fetch_assoc($spr) : [];
$gen = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_gen WHERE id=1"));
$show_logo = !empty($printRow['show_logo']) && (int)$printRow['show_logo'] === 1;
$thermal_logo = !empty($gen['thermal_logo_path']) ? $gen['thermal_logo_path'] : ($gen['image_path'] ?? '');
$thermal_logo_src = kp_variance_embed_logo($thermal_logo);
$thermal_logo_height = isset($printRow['thermal_logo_height']) ? (int)$printRow['thermal_logo_height'] : 60;
if ($thermal_logo_height < 30) { $thermal_logo_height = 30; }
if ($thermal_logo_height > 180) { $thermal_logo_height = 180; }

$varianceRows = [];
$varianceTotal = 0.0;
$varianceCount = 0;
$varianceSourceRows = function_exists('kp_counter_stock_take_snapshot_rows')
  ? kp_counter_stock_take_snapshot_rows($connection, $counter, $shift, $summary_id, 'variance')
  : array();
foreach ($varianceSourceRows as $row) {
  $variance = (float)($row['variance'] ?? 0);
  $varianceRows[] = [
    'item' => (string)($row['item'] ?? ''),
    'opening' => (float)($row['opening'] ?? 0),
    'counted' => (float)($row['counted'] ?? 0),
    'variance' => $variance,
  ];
  $varianceTotal += abs($variance);
  $varianceCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Variance #<?php echo $counter; ?></title>
  <style>
    <?php echo $thermalCss; ?>
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

    /* Match the gpints variance print header and report styling. */
    body, #receipt, #bord, table, #receipt-header, #receipt-body, #receipt-footer {
      background: #fff !important;
    }
    #receipt-header .head-copy h1,
    #receipt-header .head-copy h2,
    #receipt-header .head-copy h3,
    #receipt-header .head-copy p {
      margin: 0;
    }
    #receipt-header .head-copy h1 { font-size: 26px; }
    #receipt-header .head-copy h2 { font-size: 18px; }
    #receipt-header .head-copy h3 { font-size: 15px; }
    #receipt-header .head-copy p { font-size: 12px; font-weight: 800; }
    .summary-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
      padding: 6px 4px;
    }
    .summary-box {
      border: 1px solid #000;
      padding: 5px 4px;
      text-align: center;
    }
    .summary-box strong {
      display: block;
      font-size: 10px;
      text-transform: uppercase;
    }
    .summary-box span {
      display: block;
      margin-top: 2px;
      font-size: 14px;
      font-weight: 900;
    }
    #receipt-body table {
      width: 78mm;
      max-width: 79mm;
      margin: 0 auto 6px;
      border-collapse: collapse;
      table-layout: fixed;
      background: #fff;
    }
    #receipt-body th,
    #receipt-body td {
      border: 1px solid #000 !important;
      padding: 3px 2px;
      font-size: 11px;
    }
    #receipt-body th {
      font-size: 11px;
      font-weight: 900;
      text-transform: uppercase;
    }
    #receipt-body td:first-child,
    #receipt-body th:first-child {
      width: 34%;
      word-break: break-word;
    }
    #receipt-body td:nth-child(n+2),
    #receipt-body th:nth-child(n+2) {
      width: 16%;
      text-align: right;
    }
    .variance-up { color: #0d6b2f; font-weight: 900; }
    .variance-down { color: #9c1f1f; font-weight: 900; }
    .empty-copy {
      padding: 12px 8px;
      text-align: center;
      font-size: 12px;
      font-weight: 800;
    }
    @media print {
      body { margin: 0 !important; padding: 0 !important; width: 80mm !important; }
      #receipt { width: 79mm !important; max-width: 79mm !important; padding: .5mm 0 !important; }
      #bord { width: 79mm !important; max-width: 80mm !important; }
      #receipt-body table { width: 78mm !important; max-width: 79mm !important; }
    }

    /* Final thermal override: print variance flush-left at 79mm. */
    @page { size: 79mm auto; margin: 0 !important; }
    html, body {
      width: 79mm !important;
      max-width: 79mm !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    #receipt, #bord {
      width: 79mm !important;
      max-width: 79mm !important;
      margin: 0 !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    #receipt-body table {
      width: 79mm !important;
      max-width: 79mm !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
  </style>
</head>
<body>
  <div id="receipt">
    <div id="bord">
      <div id="receipt-header" class="text-center">
        <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
        <?php if ($show_logo && $thermal_logo_src !== '') { ?>
          <div class="text-center" style="padding-top:4px;">
            <img src="<?php echo htmlspecialchars($thermal_logo_src); ?>" alt="Logo" style="max-width:100%;max-height:<?php echo (int)$thermal_logo_height; ?>px;">
          </div>
        <?php } ?>
        <div class="head-copy">
          <h2><?php echo strtoupper(get_counter($counter)); ?> VARIANCE REPORT</h2>
          <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
          <p>SHIFT: <?php echo htmlspecialchars($shift); ?></p>
          <p>PRINT TIME: <?php echo htmlspecialchars(totime()); ?></p>
        </div>
        <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      </div>

      <div id="receipt-body">
        <div class="summary-row">
          <div class="summary-box">
            <strong>Variance Items</strong>
            <span><?php echo (int)$varianceCount; ?></span>
          </div>
          <div class="summary-box">
            <strong>Total Variance</strong>
            <span><?php echo number_format($varianceTotal, 2); ?></span>
          </div>
        </div>

        <?php if (count($varianceRows) < 1) { ?>
          <div class="empty-copy">All counted items are balanced. No variance found.</div>
        <?php } else { ?>
          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th>Open</th>
                <th>Count</th>
                <th>Var</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($varianceRows as $row) { ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['item']); ?></td>
                  <td><?php echo rtrim(rtrim(number_format($row['opening'], 2, '.', ''), '0'), '.'); ?></td>
                  <td><?php echo rtrim(rtrim(number_format($row['counted'], 2, '.', ''), '0'), '.'); ?></td>
                  <td class="<?php echo $row['variance'] >= 0 ? 'variance-up' : 'variance-down'; ?>">
                    <?php echo ($row['variance'] > 0 ? '+' : '') . rtrim(rtrim(number_format($row['variance'], 2, '.', ''), '0'), '.'); ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </div>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
  </div>
</body>
</html>
