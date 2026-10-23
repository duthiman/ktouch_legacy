<?php
require_once __DIR__ . '/../../inc/connection.php';
require_once __DIR__ . '/funcs.php';
header('Content-Type: application/json; charset=utf-8');

$counter = (int)($_POST['counter'] ?? $_GET['counter'] ?? 0);
$shift = trim((string)($_POST['shift'] ?? $_GET['shift'] ?? ''));
if ($counter < 1 || $shift === '') {
    echo json_encode(['ok' => false, 'message' => 'Counter and shift are required.']);
    exit;
}

$table = 'kp_cntr' . $counter . '_stock_take';
$tableSafe = '`' . str_replace('`', '', $table) . '`';
$shiftSql = mysqli_real_escape_string($connection, $shift);
$result = mysqli_query($connection, "SELECT take_id,item_id,opening,counted,closing,status FROM {$tableSafe} WHERE shift='{$shiftSql}'");
if (!$result) {
    echo json_encode(['ok' => false, 'message' => 'Could not load the variance report.']);
    exit;
}

$items = [];
$varianceNet = 0.0;
$varianceAbs = 0.0;
$varianceCount = 0;
$pendingCount = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $itemId = (int)$row['item_id'];
    $variance = (float)$row['counted'] - (float)$row['opening'];
    $status = (int)$row['status'];
    if ($variance != 0) $varianceCount++;
    if ($variance != 0 && $status === 3) $pendingCount++;
    $varianceNet += $variance;
    $varianceAbs += abs($variance);
    $items[] = [
        'item' => get_item($itemId),
        'expected' => (float)$row['opening'],
        'counted' => (float)$row['counted'],
        'variance' => $variance,
        'closing' => (float)$row['closing'],
        'status' => $status === 3 ? 'Pending' : ($variance == 0 ? 'OK' : 'Variance'),
    ];
}
if (!$items) {
    echo json_encode(['ok' => false, 'message' => 'No variance report exists for this counter and shift.']);
    exit;
}

$counterName = get_counter($counter);
$userId = (int)($_SESSION['user_id'] ?? 0);
$userName = $userId > 0 ? (string)get_user($userId) : '';
$company = mysqli_query($connection, 'SELECT company_name,company_tag,address,phone1,phone2,image_path,thermal_logo_path FROM kp_settings_gen LIMIT 1');
$companyRow = $company ? (mysqli_fetch_assoc($company) ?: []) : [];
$header = [
    'company_name' => (string)($companyRow['company_name'] ?? ''),
    'company_tagline' => (string)($companyRow['company_tag'] ?? ''),
    'company_address' => (string)($companyRow['address'] ?? ''),
    'company_phone' => trim((string)($companyRow['phone1'] ?? '') . ' ' . (string)($companyRow['phone2'] ?? '')),
    'logo_src' => (string)($companyRow['thermal_logo_path'] ?? $companyRow['image_path'] ?? ''),
    'counter_id' => $counter,
    'counter_name' => $counterName,
    'shift' => $shift,
    'shift_date' => $shift,
    'counted_by' => $userName,
    'print_time' => date('Y-m-d H:i:s'),
];
$payload = json_encode([
    'payload_version' => 1,
    'document_type' => 'SVP',
    'title' => 'STOCK VARIANCE PRINT',
    'header' => $header,
    'variance_summary' => [
        'items' => count($items),
        'variance_items' => $varianceCount,
        'pending_items' => $pendingCount,
        'net_variance' => $varianceNet,
        'absolute_variance' => $varianceAbs,
    ],
    'variance_items' => $items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($payload === false) {
    echo json_encode(['ok' => false, 'message' => 'Could not build the variance print payload.']);
    exit;
}

$payloadSql = mysqli_real_escape_string($connection, $payload);
$label = mysqli_real_escape_string($connection, 'SVP');
$shiftSql = mysqli_real_escape_string($connection, $shift);
$requestedBy = $userId;
$itemsJsonColumn = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE 'items_json'");
if ($itemsJsonColumn && mysqli_num_rows($itemsJsonColumn) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_print_queue ADD COLUMN items_json LONGTEXT NULL AFTER payload_json");
}
$itemsJsonSql = mysqli_real_escape_string($connection, json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$ok = mysqli_query($connection, "INSERT INTO kp_print_queue
    (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,print_attempts,last_error,shift,payload_json,items_json)
    VALUES (0,0,'SVP','{$label}','{$counter}','','','{$requestedBy}',0,0,0,'','{$shiftSql}','{$payloadSql}','{$itemsJsonSql}')");
if (!$ok) {
    echo json_encode(['ok' => false, 'message' => 'Could not queue the variance report.']);
    exit;
}
echo json_encode(['ok' => true, 'message' => 'Variance report queued for printing.', 'queue_id' => (int)mysqli_insert_id($connection)]);
