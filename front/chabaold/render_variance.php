<?php
session_start();
include_once('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include_once('../take/funcs.php');
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

require_once(__DIR__ . '/render_css.php');

$summary_id = (int)($_GET['summary_id'] ?? 0);
$auto_print = (int)($_GET['auto_print'] ?? 0) === 1;
$return_to = trim((string)($_GET['return_to'] ?? ''));
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
$print_template = kp_render_print_template_id();
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
    <?php echo kp_render_print_template_css($print_template); ?>
    .receipt-divider { margin:0; line-height:1.1; white-space:nowrap; overflow:hidden; text-align:center; font-weight:700; letter-spacing:.06em; }
    body, #receipt, table, #receipt-header, #receipt-body, #receipt-footer {
      background: #fff !important;
    }
    .head-copy h1,
    .head-copy h2,
    .head-copy h3,
    .head-copy p {
      margin: 0;
    }
    .head-copy h1 { font-size: 26px; }
    .head-copy h2 { font-size: 18px; }
    .head-copy h3 { font-size: 15px; }
    .head-copy p { font-size: 12px; font-weight: 800; }
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
    table {
      width: 78mm;
      max-width: 79mm;
      margin: 0 auto 6px;
      border-collapse: collapse;
      table-layout: fixed;
      background: #fff;
    }
    th, td {
      border: 1px solid #000 !important;
      padding: 3px 2px;
      font-size: 11px;
    }
    th {
      font-size: 11px;
      font-weight: 900;
      text-transform: uppercase;
    }
    td:first-child, th:first-child {
      width: 34%;
      word-break: break-word;
    }
    td:nth-child(2), th:nth-child(2),
    td:nth-child(3), th:nth-child(3),
    td:nth-child(4), th:nth-child(4) {
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
          <h1><?php echo strtoupper(get_counter($counter)); ?></h1>
          <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
          <h2>VARIANCE REPORT</h2>
          <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
          <h3><?php echo strtoupper((string)get_column("kp_settings_gen", "company_name", "id=1")); ?></h3>
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
<?php if ($auto_print): ?>
<script>
  window.addEventListener('load', function () {
    setTimeout(function () {
      window.print();
    }, 250);
  });
  <?php if ($return_to !== ''): ?>
  window.addEventListener('afterprint', function () {
    window.location.replace(<?php echo json_encode($return_to); ?>);
  });
  <?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
