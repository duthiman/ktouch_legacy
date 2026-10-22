<?php
/**
 * JSON aggregates for director dashboard: sales, purchases, expenses, profit by day/week/month.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

include __DIR__ . '/../inc/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$end = date('Y-m-d');
$start = date('Y-m-d', strtotime('-120 days'));
$startEsc = mysqli_real_escape_string($connection, $start);
$endEsc = mysqli_real_escape_string($connection, $end);

$salesByDay = [];
$q = mysqli_query(
    $connection,
    "SELECT shift AS d,
            SUM(amount) AS sales_gross,
            SUM(CASE WHEN pay_status = 1 THEN amount ELSE 0 END) AS sales_paid,
            COUNT(*) AS order_cnt
     FROM kp_sales
     WHERE shift >= '$startEsc' AND shift <= '$endEsc'
     GROUP BY shift"
);
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $d = (string) $row['d'];
        $salesByDay[$d] = [
            'sales_gross' => (float) $row['sales_gross'],
            'sales_paid' => (float) $row['sales_paid'],
            'orders' => (int) $row['order_cnt'],
        ];
    }
}

$purchByDay = [];
$chkPo = mysqli_query($connection, "SHOW TABLES LIKE 'kp_po'");
if ($chkPo && mysqli_num_rows($chkPo) > 0) {
    $q2 = mysqli_query(
        $connection,
        "SELECT shift AS d, SUM(amount) AS purchases
         FROM kp_po
         WHERE shift >= '$startEsc' AND shift <= '$endEsc'
         GROUP BY shift"
    );
    if ($q2) {
        while ($row = mysqli_fetch_assoc($q2)) {
            $purchByDay[(string) $row['d']] = (float) $row['purchases'];
        }
    }
}

$expByDay = [];
$q3 = mysqli_query(
    $connection,
    "SELECT shift_date AS d, SUM(amount) AS expenses
     FROM kp_shop_expenses
     WHERE shift_date >= '$startEsc' AND shift_date <= '$endEsc'
     GROUP BY shift_date"
);
if ($q3) {
    while ($row = mysqli_fetch_assoc($q3)) {
        $expByDay[(string) $row['d']] = (float) $row['expenses'];
    }
}

/** @return array<string,array<string,float|int>> */
function dir_day_bucket(array $salesByDay, array $purchByDay, array $expByDay, string $d): array
{
    $s = $salesByDay[$d] ?? ['sales_gross' => 0.0, 'sales_paid' => 0.0, 'orders' => 0];
    $p = $purchByDay[$d] ?? 0.0;
    $e = $expByDay[$d] ?? 0.0;
    $profit = (float) $s['sales_paid'] - $p - $e;
    return [
        'date' => $d,
        'sales_gross' => (float) $s['sales_gross'],
        'sales_paid' => (float) $s['sales_paid'],
        'purchases' => (float) $p,
        'expenses' => (float) $e,
        'profit' => (float) $profit,
        'orders' => (int) $s['orders'],
    ];
}

/** @param array<string,array<string,mixed>> $map */
function dir_sum_days(array $map): array
{
    $g = $pd = $pu = $e = $pr = 0.0;
    $o = 0;
    foreach ($map as $row) {
        $g += (float) $row['sales_gross'];
        $pd += (float) $row['sales_paid'];
        $pu += (float) $row['purchases'];
        $e += (float) $row['expenses'];
        $pr += (float) $row['profit'];
        $o += (int) $row['orders'];
    }
    return [
        'sales_gross' => $g,
        'sales_paid' => $pd,
        'purchases' => $pu,
        'expenses' => $e,
        'profit' => $pr,
        'orders' => $o,
    ];
}

$allDays = [];
$cursor = strtotime($start);
$endTs = strtotime($end);
while ($cursor <= $endTs) {
    $d = date('Y-m-d', $cursor);
    $allDays[$d] = dir_day_bucket($salesByDay, $purchByDay, $expByDay, $d);
    $cursor = strtotime('+1 day', $cursor);
}

$byDayList = [];
for ($i = 30; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    if (isset($allDays[$d])) {
        $row = $allDays[$d];
        $row['label'] = date('D j M', strtotime($d));
        $byDayList[] = $row;
    }
}

$byWeekMap = [];
foreach ($allDays as $d => $row) {
    $dt = new DateTimeImmutable($d);
    $y = (int) $dt->format('o');
    $w = (int) $dt->format('W');
    $key = sprintf('%04d-W%02d', $y, $w);
    if (!isset($byWeekMap[$key])) {
        $byWeekMap[$key] = [
            'period' => $key,
            'label' => 'Week ' . $w . ', ' . $y,
            'sales_gross' => 0.0,
            'sales_paid' => 0.0,
            'purchases' => 0.0,
            'expenses' => 0.0,
            'profit' => 0.0,
            'orders' => 0,
        ];
    }
    $byWeekMap[$key]['sales_gross'] += $row['sales_gross'];
    $byWeekMap[$key]['sales_paid'] += $row['sales_paid'];
    $byWeekMap[$key]['purchases'] += $row['purchases'];
    $byWeekMap[$key]['expenses'] += $row['expenses'];
    $byWeekMap[$key]['profit'] += $row['profit'];
    $byWeekMap[$key]['orders'] += $row['orders'];
}
$weekKeys = array_keys($byWeekMap);
rsort($weekKeys, SORT_STRING);
$byWeekList = [];
foreach (array_slice($weekKeys, 0, 10) as $k) {
    $byWeekList[] = $byWeekMap[$k];
}
$byWeekList = array_reverse($byWeekList);

$byMonthMap = [];
foreach ($allDays as $d => $row) {
    $ym = substr($d, 0, 7);
    if (!isset($byMonthMap[$ym])) {
        $byMonthMap[$ym] = [
            'period' => $ym,
            'label' => date('M Y', strtotime($ym . '-01')),
            'sales_gross' => 0.0,
            'sales_paid' => 0.0,
            'purchases' => 0.0,
            'expenses' => 0.0,
            'profit' => 0.0,
            'orders' => 0,
        ];
    }
    $byMonthMap[$ym]['sales_gross'] += $row['sales_gross'];
    $byMonthMap[$ym]['sales_paid'] += $row['sales_paid'];
    $byMonthMap[$ym]['purchases'] += $row['purchases'];
    $byMonthMap[$ym]['expenses'] += $row['expenses'];
    $byMonthMap[$ym]['profit'] += $row['profit'];
    $byMonthMap[$ym]['orders'] += $row['orders'];
}
$monthKeys = array_keys($byMonthMap);
rsort($monthKeys, SORT_STRING);
$byMonthList = [];
foreach (array_slice($monthKeys, 0, 12) as $k) {
    $byMonthList[] = $byMonthMap[$k];
}
$byMonthList = array_reverse($byMonthList);

$last7 = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    if (isset($allDays[$d])) {
        $last7[$d] = $allDays[$d];
    }
}
$last30 = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    if (isset($allDays[$d])) {
        $last30[$d] = $allDays[$d];
    }
}

echo json_encode([
    'ok' => true,
    'range' => ['start' => $start, 'end' => $end],
    'totals_last_7' => dir_sum_days($last7),
    'totals_last_30' => dir_sum_days($last30),
    'by_day' => $byDayList,
    'by_week' => $byWeekList,
    'by_month' => $byMonthList,
    'note' => 'Profit = paid sales − purchases − expenses (by calendar day). Gross sales includes unpaid bills.',
], JSON_UNESCAPED_UNICODE);
