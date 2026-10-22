<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../inc/connection.php';

function kug_value($connection, $sql, $fallback = 0) {
    $result = mysqli_query($connection, $sql);
    if (!$result) {
        throw new RuntimeException(mysqli_error($connection));
    }
    $row = mysqli_fetch_assoc($result);
    return $row ? ($row['value'] ?? $fallback) : $fallback;
}

try {
    $monthStart = date('Y-m-01');
    $nextMonth = date('Y-m-01', strtotime('+1 month'));

    $totalOrders = (int)kug_value($connection, "SELECT COUNT(*) AS value FROM kp_po WHERE DATE(po_date) = CURRENT_DATE");
    $pendingApproval = (int)kug_value($connection, "SELECT COUNT(*) AS value FROM kp_po WHERE rec_status = 2");
    $pendingReceiving = (int)kug_value($connection, "SELECT COUNT(*) AS value FROM kp_po WHERE stock_status = 2");
    $activeOrdering = (int)kug_value($connection, "SELECT COUNT(*) AS value FROM kp_po_status WHERE po_status = 2");
    $monthSpend = (float)kug_value($connection, "SELECT COALESCE(SUM(amount), 0) AS value FROM kp_po WHERE DATE(po_date) >= '{$monthStart}' AND DATE(po_date) < '{$nextMonth}'");
    $allTimeSpend = (float)kug_value($connection, "SELECT COALESCE(SUM(amount), 0) AS value FROM kp_po");
    $receivingValue = (float)kug_value($connection, "SELECT COALESCE(SUM(amount), 0) AS value FROM kp_po WHERE stock_status = 2");
    $latestPurchases = [];
    $latestResult = mysqli_query(
        $connection,
        "SELECT p.po_id, p.po_date, p.amount, p.rec_status, p.stock_status,
                COALESCE(s.sup_name, 'Unknown supplier') AS supplier
         FROM kp_po AS p
         LEFT JOIN kp_suppliers AS s ON s.sup_id = p.sup_id
         ORDER BY p.po_date DESC, p.id DESC
         LIMIT 5"
    );
    if (!$latestResult) {
        throw new RuntimeException(mysqli_error($connection));
    }
    while ($row = mysqli_fetch_assoc($latestResult)) {
        $status = ((int)$row['stock_status'] === 1)
            ? 'Received'
            : (((int)$row['rec_status'] === 2) ? 'Pending' : 'Processing');
        $latestPurchases[] = [
            'po_id' => (int)$row['po_id'],
            'supplier' => (string)$row['supplier'],
            'date' => (string)$row['po_date'],
            'amount' => (float)$row['amount'],
            'status' => $status,
        ];
    }
    $trend = [];
    $trendResult = mysqli_query(
        $connection,
        "SELECT DATE_FORMAT(po_date, '%Y-%m') AS period,
                COUNT(*) AS order_count,
                COALESCE(SUM(amount), 0) AS total
         FROM kp_po
         WHERE po_date >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH), '%Y-%m-01')
         GROUP BY DATE_FORMAT(po_date, '%Y-%m')
         ORDER BY period ASC"
    );
    if (!$trendResult) {
        throw new RuntimeException(mysqli_error($connection));
    }
    while ($row = mysqli_fetch_assoc($trendResult)) {
        $trend[$row['period']] = [
            'count' => (int)$row['order_count'],
            'total' => (float)$row['total'],
        ];
    }

    $trendRows = [];
    for ($offset = 5; $offset >= 0; $offset--) {
        $period = date('Y-m', strtotime('-' . $offset . ' months'));
        $trendRows[] = [
            'period' => $period,
            'label' => date('M y', strtotime($period . '-01')),
            'count' => $trend[$period]['count'] ?? 0,
            'total' => $trend[$period]['total'] ?? 0,
        ];
    }

    echo json_encode([
        'ok' => true,
        'metrics' => [
            'total_orders' => $totalOrders,
            'pending_approval' => $pendingApproval,
            'ordering' => $activeOrdering,
            'packaging' => 0,
            'shipping' => 0,
            'pending_receiving' => $pendingReceiving,
            'month_spend' => $monthSpend,
            'international_value' => 0,
            'shipping_cost' => 0,
            'all_time_spend' => $allTimeSpend,
            'receiving_value' => $receivingValue,
        ],
        'trend' => $trendRows,
        'latest_purchases' => $latestPurchases,
    ]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not load dashboard data.']);
}
