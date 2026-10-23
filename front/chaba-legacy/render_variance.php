<?php
session_start();
require_once __DIR__ . '/../../inc/connection.php';
require_once __DIR__ . '/../../inc/gen_funcs.php';
require_once __DIR__ . '/../../back/counter/funcs.php';

$summaryId = (int)($_GET['summary_id'] ?? 0);
$counter = (int)($_GET['cntr_id'] ?? 0);
$shift = trim((string)($_GET['shift'] ?? ''));

if ($summaryId > 0) {
    $summaryQuery = mysqli_query($connection, "SELECT * FROM kp_counter_close_summary WHERE close_sum_id='{$summaryId}' LIMIT 1");
    $summaryRow = $summaryQuery ? mysqli_fetch_assoc($summaryQuery) : null;
    if ($summaryRow) {
        $counter = $counter > 0 ? $counter : (int)($summaryRow['cntr_id'] ?? 0);
        $shift = $shift !== '' ? $shift : (string)($summaryRow['shift_date'] ?? '');
    }
}

if ($counter < 1) {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $counter = (int)get_column('kp_users', 'counter', "user_id='{$userId}'");
}
if ($counter < 1) exit('Invalid counter');
if ($shift === '') $shift = (string)get_column('kp_shift', 'shift', '1');

$rows = [];
$sourceRows = function_exists('kp_counter_stock_take_snapshot_rows')
    ? kp_counter_stock_take_snapshot_rows($connection, $counter, $shift, $summaryId, 'variance')
    : [];

foreach ($sourceRows as $row) {
    $variance = (float)($row['variance'] ?? 0);
    if ($variance == 0.0) continue;
    $rows[] = [
        'item' => (string)($row['item'] ?? ''),
        'opening' => (float)($row['opening'] ?? 0),
        'counted' => (float)($row['counted'] ?? 0),
        'variance' => $variance,
    ];
}

$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$formatNumber = static fn($value) => rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
$counterName = strtoupper((string)get_counter($counter));
$varianceTotal = array_reduce($rows, static fn($total, $row) => $total + abs($row['variance']), 0.0);
$printTime = function_exists('totime') ? totime() : date('Y-m-d H:i:s');

$settingsQuery = mysqli_query($connection, 'SELECT thermal_logo_path,image_path FROM kp_settings_gen WHERE id=1 LIMIT 1');
$settings = $settingsQuery ? (mysqli_fetch_assoc($settingsQuery) ?: []) : [];
$logo = (string)($settings['thermal_logo_path'] ?? $settings['image_path'] ?? '');
if ($logo !== '' && stripos($logo, 'data:') !== 0) {
    $logoPath = realpath(__DIR__ . '/../../' . ltrim(str_replace('\\', '/', $logo), '/'));
    if ($logoPath && is_file($logoPath)) {
        $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
        $logo = 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($logoPath));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Variance</title>
    <style>
        @page{size:79mm auto;margin:0!important}*{box-sizing:border-box}
        html,body{width:79mm!important;max-width:79mm!important;margin:0!important;padding:0!important;background:#fff;color:#000}
        body{font-family:Arial,"Helvetica Neue",Helvetica,sans-serif;font-size:12px}
        .receipt{width:79mm;max-width:79mm;margin:0;padding:.5mm 0}.border{width:79mm;max-width:79mm;border:2px solid #000}
        .center{text-align:center!important}.divider{clear:both;display:block;margin:3px 0;line-height:1.1;white-space:nowrap;overflow:hidden;text-align:center;font-weight:700;letter-spacing:.06em}
        .header h2{margin:0;font-size:18px}.header p{margin:0;font-size:12px;font-weight:800}.logo{display:block;max-width:100%;max-height:60px;margin:4px auto;object-fit:contain}
        .summary{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:6px 4px}.summary-box{padding:5px 4px;border:1px solid #000;text-align:center}
        .summary-box strong{display:block;font-size:10px;text-transform:uppercase}.summary-box span{display:block;margin-top:2px;font-size:14px;font-weight:900}
        .items{width:calc(100% - 2px);max-width:calc(100% - 2px);margin:1px;border-collapse:collapse;table-layout:fixed;background:#fff}
        .items th,.items td{padding:3px 2px;border:1px solid #000!important;font-family:Arial,"Helvetica Neue",Helvetica,sans-serif;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:clip}
        .items th{font-size:11px;font-weight:900;text-transform:uppercase}.items td:first-child,.items th:first-child{width:34%;text-align:left}
        .items td:first-child{white-space:normal;overflow-wrap:break-word;word-break:break-word}.items td:nth-child(n+2),.items th:nth-child(n+2){width:16%;text-align:right}
        .up{color:#0d6b2f}.down{color:#9c1f1f}.empty{padding:12px 8px;text-align:center;font-size:12px;font-weight:800}
    </style>
</head>
<body>
<main class="receipt"><div class="border"><header class="header center">
    <div class="divider">***********************************************************</div>
    <?php if ($logo !== ''): ?><img class="logo" src="<?= $escape($logo) ?>" alt="Logo"><?php endif; ?>
    <h2><?= $escape($counterName . ' VARIANCE REPORT') ?></h2>
    <div class="divider">***********************************************************</div>
    <p>SHIFT: <?= $escape($shift) ?></p><p>PRINT TIME: <?= $escape($printTime) ?></p>
    <div class="divider">***********************************************************</div>
</header>
<section class="summary"><div class="summary-box"><strong>Variance Items</strong><span><?= count($rows) ?></span></div><div class="summary-box"><strong>Total Variance</strong><span><?= $formatNumber($varianceTotal) ?></span></div></section>
<?php if (!$rows): ?><div class="empty">All counted items are balanced. No variance found.</div><?php else: ?><table class="items"><thead><tr><th>Item</th><th>Open</th><th>Count</th><th>Var</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= $escape($row['item']) ?></td><td><?= $formatNumber($row['opening']) ?></td><td><?= $formatNumber($row['counted']) ?></td><td class="<?= $row['variance'] >= 0 ? 'up' : 'down' ?>"><?= $row['variance'] > 0 ? '+' : '' ?><?= $formatNumber($row['variance']) ?></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?><div class="divider">***********************************************************</div></div></main>
</body>
</html>
