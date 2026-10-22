<?php
declare(strict_types=1);

include('../../inc/connection.php');

date_default_timezone_set('Africa/Nairobi');

if (!isset($connection) || !($connection instanceof mysqli)) {
    http_response_code(500);
    exit('Database connection failed.');
}

function bdr_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function bdr_money(float $amount): string
{
    return 'KES ' . number_format($amount, 2);
}

function bdr_num(float $amount): string
{
    return number_format($amount, 0);
}

function bdr_period(string $preset, ?string $fromRaw, ?string $toRaw): array
{
    $today = new DateTimeImmutable('today');
    $preset = strtolower(trim($preset));

    switch ($preset) {
        case 'yesterday':
            $from = $today->modify('-1 day');
            $to = $today->modify('-1 day');
            $label = 'Yesterday';
            break;
        case 'this_week':
            $from = $today->modify('-6 days');
            $to = $today;
            $label = 'Last 7 Days';
            break;
        case 'this_month':
            $from = $today->modify('first day of this month');
            $to = $today;
            $label = 'This Month';
            break;
        case 'this_year':
            $from = $today->setDate((int) $today->format('Y'), 1, 1);
            $to = $today;
            $label = 'This Year';
            break;
        case 'custom':
            $from = DateTimeImmutable::createFromFormat('Y-m-d', (string) $fromRaw) ?: $today;
            $to = DateTimeImmutable::createFromFormat('Y-m-d', (string) $toRaw) ?: $from;
            if ($from > $to) {
                [$from, $to] = [$to, $from];
            }
            $label = 'Custom Dates';
            break;
        case 'today':
        default:
            $preset = 'today';
            $from = $today;
            $to = $today;
            $label = 'Today';
            break;
    }

    return [
        'preset' => $preset,
        'label' => $label,
        'from' => $from,
        'to' => $to,
        'from_sql' => $from->format('Y-m-d'),
        'to_sql' => $to->format('Y-m-d'),
        'from_human' => $from->format('d M Y'),
        'to_human' => $to->format('d M Y'),
        'days' => ((int) $from->diff($to)->format('%a')) + 1,
    ];
}

function bdr_previous_period(array $period): array
{
    $days = max(1, (int) ($period['days'] ?? 1));
    $currentFrom = $period['from'];
    if (!$currentFrom instanceof DateTimeImmutable) {
        $currentFrom = new DateTimeImmutable((string) ($period['from_sql'] ?? 'today'));
    }

    $previousTo = $currentFrom->modify('-1 day');
    $previousFrom = $previousTo->modify('-' . ($days - 1) . ' days');

    return [
        'from_sql' => $previousFrom->format('Y-m-d'),
        'to_sql' => $previousTo->format('Y-m-d'),
    ];
}

function bdr_one(mysqli $connection, string $sql, string $types = '', array $params = []): array
{
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        return [];
    }

    if ($types !== '' && $params !== []) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : [];
    mysqli_stmt_close($stmt);

    return is_array($row) ? $row : [];
}

function bdr_all(mysqli $connection, string $sql, string $types = '', array $params = []): array
{
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        return [];
    }

    if ($types !== '' && $params !== []) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    mysqli_stmt_close($stmt);

    return $rows;
}

function bdr_slot_names(mysqli $connection): array
{
    $rows = bdr_all($connection, "SELECT pay_id, name FROM kp_pay_mode WHERE del_status=2 ORDER BY pay_id ASC");
    $slots = [];
    foreach ($rows as $row) {
        $slots[(int) ($row['pay_id'] ?? 0)] = ucfirst(strtolower(trim((string) ($row['name'] ?? 'Mode'))));
    }

    if (!isset($slots[1])) {
        $slots[1] = 'Cash';
    }
    if (!isset($slots[2])) {
        $slots[2] = 'Mpesa';
    }
    if (!isset($slots[3])) {
        $slots[3] = 'Card';
    }
    if (!isset($slots[4])) {
        $slots[4] = 'Bank';
    }

    return $slots;
}

function bdr_line_key(int $saleId, int $itemId, int $attrId): string
{
    return $saleId . ':' . $itemId . ':' . $attrId;
}

$period = bdr_period(
    (string) ($_GET['range'] ?? 'today'),
    isset($_GET['from']) ? (string) $_GET['from'] : null,
    isset($_GET['to']) ? (string) $_GET['to'] : null
);
$previous = bdr_previous_period($period);
$stockDay = $period['to_sql'];

$sales = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_sales
     WHERE shift BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$salesPaidCurrent = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(paid_total), 0) AS total_amount
     FROM kp_sale_payment
     WHERE pay_date BETWEEN ? AND ? AND ord_date BETWEEN ? AND ?",
    'ssss',
    [$period['from_sql'], $period['to_sql'], $period['from_sql'], $period['to_sql']]
);

$salesPaidPrevious = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(paid_total), 0) AS total_amount
     FROM kp_sale_payment
     WHERE pay_date BETWEEN ? AND ? AND ord_date < ?",
    'sss',
    [$period['from_sql'], $period['to_sql'], $period['from_sql']]
);

$expenses = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_shop_expenses
     WHERE shift_date BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$purchases = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_po
     WHERE po_date BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$purchasesPaidCurrent = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(paid), 0) AS total_amount
     FROM kp_grn
     WHERE pay_day BETWEEN ? AND ? AND day BETWEEN ? AND ?",
    'ssss',
    [$period['from_sql'], $period['to_sql'], $period['from_sql'], $period['to_sql']]
);

$purchasesPaidPrevious = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(paid), 0) AS total_amount
     FROM kp_grn
     WHERE pay_day BETWEEN ? AND ? AND day < ?",
    'sss',
    [$period['from_sql'], $period['to_sql'], $period['from_sql']]
);

$bills = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_sales
     WHERE pay_status = 3 AND shift BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$billsPaidCurrent = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_customer_credit_payment_items
     WHERE pay_date BETWEEN ? AND ? AND ord_date BETWEEN ? AND ?",
    'ssss',
    [$period['from_sql'], $period['to_sql'], $period['from_sql'], $period['to_sql']]
);

$billsPaidPrevious = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_customer_credit_payment_items
     WHERE pay_date BETWEEN ? AND ? AND ord_date < ?",
    'sss',
    [$period['from_sql'], $period['to_sql'], $period['from_sql']]
);

$stockPurchased = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS total_count,
        COALESCE(SUM(gi.qty), 0) AS qty_total,
        COALESCE(SUM(gi.total), 0) AS total_amount
     FROM kp_grn_items gi
     WHERE gi.day BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$stockPurchasedItems = bdr_all(
    $connection,
    "SELECT
        gi.item_id,
        COALESCE(SUM(gi.qty), 0) AS qty_total
     FROM kp_grn_items gi
     WHERE gi.day BETWEEN ? AND ?
     GROUP BY gi.item_id",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$stockOpening = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS line_count,
        COALESCE(SUM(t.opening), 0) AS qty_total,
        COALESCE(SUM(t.opening * COALESCE(i.b_price, 0)), 0) AS value_total
     FROM kp_store_stock_take t
     LEFT JOIN kp_items i ON i.item_id = t.item_id
     WHERE t.shift = ?",
    's',
    [$stockDay]
);

$stockClosingTake = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS line_count,
        COALESCE(SUM(t.closing), 0) AS qty_total,
        COALESCE(SUM(t.closing * COALESCE(i.b_price, 0)), 0) AS value_total,
        COALESCE(SUM(t.closing * COALESCE(i.price, 0)), 0) AS sale_value_total
     FROM kp_store_stock_take t
     LEFT JOIN kp_items i ON i.item_id = t.item_id
     WHERE t.shift = ?",
    's',
    [$stockDay]
);

$stockClosingSnapshot = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS line_count,
        COALESCE(SUM(cs.qty), 0) AS qty_total,
        COALESCE(SUM(cs.qty * COALESCE(i.b_price, 0)), 0) AS value_total,
        COALESCE(SUM(cs.qty * COALESCE(i.price, 0)), 0) AS sale_value_total
     FROM kp_store_closing_stock cs
     LEFT JOIN kp_items i ON i.item_id = cs.item_id
     WHERE cs.shift = ?",
    's',
    [$stockDay]
);

$currentStockLive = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS line_count,
        COALESCE(SUM(i.cntr1_qty), 0) AS qty_total,
        COALESCE(SUM(i.cntr1_qty * COALESCE(i.b_price, 0)), 0) AS value_total,
        COALESCE(SUM(i.cntr1_qty * COALESCE(i.price, 0)), 0) AS sale_value_total
     FROM kp_items i
     WHERE i.del_status = 2 AND i.stock = 1",
    '',
    []
);

$currentStockItems = bdr_all(
    $connection,
    "SELECT
        i.item_id,
        i.name,
        COALESCE(i.cntr1_qty, 0) AS current_qty,
        COALESCE(i.buying, 0) AS buy_price,
        COALESCE(i.price, 0) AS sale_price
     FROM kp_items i
     WHERE i.del_status = 2 AND i.stock = 1",
    '',
    []
);

$stockIn = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS total_count,
        COALESCE(SUM(st.qty), 0) AS qty_total,
        COALESCE(SUM(st.qty * COALESCE(i.b_price, 0)), 0) AS value_total
     FROM kp_store_stock_track st
     LEFT JOIN kp_items i ON i.item_id = st.item_id
     WHERE st.status = 'IN' AND st.shift BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$stockOut = bdr_one(
    $connection,
    "SELECT
        COUNT(*) AS total_count,
        COALESCE(SUM(st.qty), 0) AS qty_total,
        COALESCE(SUM(st.qty * COALESCE(i.b_price, 0)), 0) AS value_total
     FROM kp_store_stock_track st
     LEFT JOIN kp_items i ON i.item_id = st.item_id
     WHERE st.status = 'OUT' AND st.shift BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$soldSaleLines = bdr_all(
    $connection,
    "SELECT
        si.sale_id,
        si.item_id,
        COALESCE(si.attr_id, 0) AS attr_id,
        i.name AS item_name,
        COALESCE(i.recipe, 0) AS recipe,
        COALESCE(SUM(si.qty), 0) AS qty_total,
        COALESCE(SUM(si.total), 0) AS value_total
     FROM kp_sale_items si
     LEFT JOIN kp_items i ON i.item_id = si.item_id
     WHERE si.shift BETWEEN ? AND ?
     GROUP BY si.sale_id, si.item_id, COALESCE(si.attr_id, 0), i.name, i.recipe
     ORDER BY si.sale_id DESC, i.name ASC",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$soldTrackLines = bdr_all(
    $connection,
    "SELECT
        st.sale_id,
        st.item_id,
        COALESCE(st.attr_id, 0) AS attr_id,
        i.name AS item_name,
        COALESCE(i.recipe, 0) AS recipe,
        COALESCE(SUM(st.qty), 0) AS qty_total
     FROM kp_stock_track st
     LEFT JOIN kp_items i ON i.item_id = st.item_id
     WHERE st.status = 'OUT' AND st.sale_id > 0 AND st.shift BETWEEN ? AND ?
     GROUP BY st.sale_id, st.item_id, COALESCE(st.attr_id, 0), i.name, i.recipe
     ORDER BY st.sale_id DESC, i.name ASC",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$voids = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_del_orders
     WHERE shift BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$cancels = bdr_one(
    $connection,
    "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_cancelled
     WHERE shift BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$currentCreditList = bdr_all(
    $connection,
    "SELECT
        c.cust_id,
        c.name,
        c.phone,
        c.location,
        c.bal,
        COALESCE(COUNT(s.sale_id), 0) AS unpaid_bills,
        COALESCE(SUM(s.amount), 0) AS unpaid_sales_total
     FROM kp_customers c
     LEFT JOIN kp_sales s ON s.cust_id = c.cust_id AND s.pay_status = 3
     WHERE c.del_status = 2 AND c.bal > 0
     GROUP BY c.cust_id, c.name, c.phone, c.location, c.bal
     ORDER BY c.bal DESC, c.name ASC"
);

$periodCreditList = bdr_all(
    $connection,
    "SELECT
        c.cust_id,
        c.name,
        c.phone,
        c.location,
        COALESCE(COUNT(s.sale_id), 0) AS credit_bills,
        COALESCE(SUM(s.amount), 0) AS credit_total
     FROM kp_customers c
     INNER JOIN kp_sales s ON s.cust_id = c.cust_id
     WHERE c.del_status = 2
       AND s.pay_status = 3
       AND s.shift BETWEEN ? AND ?
     GROUP BY c.cust_id, c.name, c.phone, c.location
     ORDER BY credit_total DESC, c.name ASC",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);

$slotNames = bdr_slot_names($connection);
$payRows = bdr_all(
    $connection,
    "SELECT
        COALESCE(SUM(mode1), 0) AS mode1_total,
        COALESCE(SUM(mode2), 0) AS mode2_total,
        COALESCE(SUM(mode3), 0) AS mode3_total,
        COALESCE(SUM(mode4), 0) AS mode4_total
     FROM kp_sale_payment
     WHERE pay_date BETWEEN ? AND ?",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);
$paymentModes = [];
$baseModes = isset($payRows[0]) ? $payRows[0] : [];
for ($slot = 1; $slot <= 4; $slot++) {
    $amount = (float) ($baseModes['mode' . $slot . '_total'] ?? 0);
    if ($amount > 0) {
        $name = $slotNames[$slot] ?? ('Mode ' . $slot);
        $paymentModes[$name] = ($paymentModes[$name] ?? 0) + $amount;
    }
}

$creditModeRows = bdr_all(
    $connection,
    "SELECT pay_mode_id, COALESCE(SUM(amount), 0) AS total_amount
     FROM kp_customer_credit_payments
     WHERE shift_date BETWEEN ? AND ?
     GROUP BY pay_mode_id
     ORDER BY total_amount DESC",
    'ss',
    [$period['from_sql'], $period['to_sql']]
);
foreach ($creditModeRows as $row) {
    $modeId = (int) ($row['pay_mode_id'] ?? 0);
    $amount = (float) ($row['total_amount'] ?? 0);
    if ($amount <= 0) {
        continue;
    }
    $name = $slotNames[$modeId] ?? ('Mode ' . $modeId);
    $paymentModes[$name] = ($paymentModes[$name] ?? 0) + $amount;
}
arsort($paymentModes);

$paymentModeRows = [];
$paymentMax = 0.0;
$moneyInTotal = 0.0;
foreach ($paymentModes as $name => $amount) {
    $paymentModeRows[] = ['name' => (string) $name, 'amount' => (float) $amount];
    $paymentMax = max($paymentMax, (float) $amount);
    $moneyInTotal += (float) $amount;
}

$closingStock = $currentStockLive;
$openingStockValue = (float) ($stockOpening['value_total'] ?? 0);
$openingStockQty = (float) ($stockOpening['qty_total'] ?? 0);
$stockInValue = (float) ($stockIn['value_total'] ?? 0);
$stockInQty = (float) ($stockIn['qty_total'] ?? 0);
$stockOutValue = (float) ($stockOut['value_total'] ?? 0);
$stockOutQty = (float) ($stockOut['qty_total'] ?? 0);
$currentStockCostValue = (float) ($closingStock['value_total'] ?? 0);
$currentStockValue = (float) ($closingStock['sale_value_total'] ?? $closingStock['value_total'] ?? 0);
$currentStockQty = (float) ($closingStock['qty_total'] ?? 0);
$salesValue = (float) ($sales['total_amount'] ?? 0);
$expenseValue = (float) ($expenses['total_amount'] ?? 0);
$stockAvailableValue = $openingStockValue + $stockInValue;
$stockAvailableQty = $openingStockQty + $stockInQty;
$stockUsedValue = max(0, $stockAvailableValue - $currentStockCostValue);
$stockUsedQty = max(0, $stockAvailableQty - $currentStockQty);
$stockGrossProfit = $salesValue - $stockUsedValue;
$netProfit = $stockGrossProfit - $expenseValue;
$stockMovementGap = $stockOutValue - $stockUsedValue;
$saleLineQtyTotal = 0.0;
$saleLineValueTotal = 0.0;
$trackSaleQtyTotal = 0.0;
$trackSaleValueTotal = 0.0;
$saleLineMap = [];
$trackLineMap = [];
$stockDiscrepancyRows = [];

foreach ($soldSaleLines as $row) {
    $saleId = (int) ($row['sale_id'] ?? 0);
    $itemId = (int) ($row['item_id'] ?? 0);
    $attrId = (int) ($row['attr_id'] ?? 0);
    $qty = (float) ($row['qty_total'] ?? 0);
    $value = (float) ($row['value_total'] ?? 0);
    $unitPrice = $qty > 0 ? ($value / $qty) : 0.0;
    $key = bdr_line_key($saleId, $itemId, $attrId);
    $saleLineMap[$key] = [
        'sale_id' => $saleId,
        'item_id' => $itemId,
        'attr_id' => $attrId,
        'item_name' => (string) ($row['item_name'] ?? ('Item ' . $itemId)),
        'recipe' => (int) ($row['recipe'] ?? 0),
        'sale_qty' => $qty,
        'sale_value' => $value,
        'sale_unit_price' => $unitPrice,
    ];
    $saleLineQtyTotal += $qty;
    $saleLineValueTotal += $value;
}

foreach ($soldTrackLines as $row) {
    $saleId = (int) ($row['sale_id'] ?? 0);
    $itemId = (int) ($row['item_id'] ?? 0);
    $attrId = (int) ($row['attr_id'] ?? 0);
    $qty = (float) ($row['qty_total'] ?? 0);
    $key = bdr_line_key($saleId, $itemId, $attrId);
    $trackLineMap[$key] = [
        'sale_id' => $saleId,
        'item_id' => $itemId,
        'attr_id' => $attrId,
        'item_name' => (string) ($row['item_name'] ?? ('Item ' . $itemId)),
        'recipe' => (int) ($row['recipe'] ?? 0),
        'track_qty' => $qty,
    ];
    $trackSaleQtyTotal += $qty;
}

$allKeys = array_unique(array_merge(array_keys($saleLineMap), array_keys($trackLineMap)));
foreach ($allKeys as $key) {
    $saleRow = $saleLineMap[$key] ?? null;
    $trackRow = $trackLineMap[$key] ?? null;
    $saleQty = (float) ($saleRow['sale_qty'] ?? 0);
    $saleValue = (float) ($saleRow['sale_value'] ?? 0);
    $unitPrice = (float) ($saleRow['sale_unit_price'] ?? 0);
    $trackQty = (float) ($trackRow['track_qty'] ?? 0);
    $trackValue = $unitPrice > 0 ? ($trackQty * $unitPrice) : 0.0;
    $trackSaleValueTotal += $trackValue;
    $qtyDiff = $saleQty - $trackQty;
    $valueDiff = $saleValue - $trackValue;
    $recipeFlag = (int) ($saleRow['recipe'] ?? $trackRow['recipe'] ?? 0);

    if (abs($qtyDiff) < 0.01 && abs($valueDiff) < 0.01) {
        continue;
    }

    $issue = 'Quantity mismatch';
    if ($saleRow && !$trackRow) {
        $issue = 'Sale line has no stock-out movement';
    } elseif (!$saleRow && $trackRow) {
        $issue = $recipeFlag === 1 ? 'Recipe or raw-material stock-out without direct sale line' : 'Stock-out movement has no active sale line';
    } elseif ($recipeFlag === 1) {
        $issue = 'Recipe item or raw-material conversion difference';
    }

    $stockDiscrepancyRows[] = [
        'sale_id' => (int) ($saleRow['sale_id'] ?? $trackRow['sale_id'] ?? 0),
        'item_name' => (string) ($saleRow['item_name'] ?? $trackRow['item_name'] ?? 'Item'),
        'sale_qty' => $saleQty,
        'track_qty' => $trackQty,
        'sale_value' => $saleValue,
        'track_value' => $trackValue,
        'value_diff' => $valueDiff,
        'issue' => $issue,
    ];
}

usort($stockDiscrepancyRows, static function (array $a, array $b): int {
    return abs((float) $b['value_diff']) <=> abs((float) $a['value_diff']);
});

$voidTotal = (float) ($voids['total_amount'] ?? 0);
$cancelTotal = (float) ($cancels['total_amount'] ?? 0);
$stockSaleGapValue = $saleLineValueTotal - $trackSaleValueTotal;
$stockSaleGapQty = $saleLineQtyTotal - $trackSaleQtyTotal;
$stockDiscrepancyCount = count($stockDiscrepancyRows);
$creditGivenValue = (float) ($bills['total_amount'] ?? 0);
$creditGivenCount = (float) ($bills['total_count'] ?? 0);
$creditPaidValue = (float) ($billsPaidCurrent['total_amount'] ?? 0);
$creditPaidCount = (float) ($billsPaidCurrent['total_count'] ?? 0);

$stockOutValue = $trackSaleValueTotal;
$stockOutQty = $trackSaleQtyTotal;
$stockMovementGap = $stockOutValue - $saleLineValueTotal;
$currentCreditTotal = 0.0;
foreach ($currentCreditList as $row) {
    $currentCreditTotal += (float) ($row['bal'] ?? 0);
}

$soldQtyByItem = [];
foreach ($soldSaleLines as $row) {
    $itemId = (int) ($row['item_id'] ?? 0);
    $soldQtyByItem[$itemId] = ($soldQtyByItem[$itemId] ?? 0.0) + (float) ($row['qty_total'] ?? 0);
}

$purchasedQtyByItem = [];
foreach ($stockPurchasedItems as $row) {
    $itemId = (int) ($row['item_id'] ?? 0);
    $purchasedQtyByItem[$itemId] = ($purchasedQtyByItem[$itemId] ?? 0.0) + (float) ($row['qty_total'] ?? 0);
}

$derivedOpeningQty = 0.0;
$derivedOpeningBuyValue = 0.0;
$derivedOpeningSellValue = 0.0;
$derivedClosingQty = 0.0;
$derivedClosingBuyValue = 0.0;
$derivedClosingSellValue = 0.0;
foreach ($currentStockItems as $row) {
    $itemId = (int) ($row['item_id'] ?? 0);
    $currentQty = (float) ($row['current_qty'] ?? 0);
    $buyPrice = (float) ($row['buy_price'] ?? 0);
    $salePrice = (float) ($row['sale_price'] ?? 0);
    $soldQty = (float) ($soldQtyByItem[$itemId] ?? 0.0);
    $purchasedQty = (float) ($purchasedQtyByItem[$itemId] ?? 0.0);
    $openingQtyExact = $currentQty + $soldQty - $purchasedQty;

    $derivedClosingQty += $currentQty;
    $derivedClosingBuyValue += $currentQty * $buyPrice;
    $derivedClosingSellValue += $currentQty * $salePrice;
    $derivedOpeningQty += $openingQtyExact;
    $derivedOpeningBuyValue += $openingQtyExact * $buyPrice;
    $derivedOpeningSellValue += $openingQtyExact * $salePrice;
}
$currentStockBuyingCost = $derivedClosingBuyValue;
$currentStockSellingCost = $derivedClosingSellValue;
$currentStockExpectedGrossProfit = $salesValue + $derivedClosingBuyValue - $derivedOpeningBuyValue - (float) ($stockPurchased['total_amount'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Director Report</title>
    <link rel="stylesheet" href="../../assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        :root {
            --bg: #f4f4f1;
            --card: #ffffff;
            --ink: #25211b;
            --muted: #6f6454;
            --line: #e6dfd3;
            --primary: #8a2d1f;
            --deep: #5f1f16;
            --accent: #c68a2b;
            --green: #4d7d27;
            --teal: #2a6975;
            --purple: #6e3f88;
            --shadow: 0 18px 34px rgba(74, 57, 33, 0.08);
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(138, 45, 31, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(198, 138, 43, 0.12), transparent 28%),
                linear-gradient(180deg, #fbfaf7 0%, var(--bg) 100%);
            color: var(--ink);
            font-family: "Trebuchet MS", Verdana, sans-serif;
        }

        .report-shell {
            width: min(1360px, calc(100% - 24px));
            margin: 0 auto;
            padding: 18px 0 36px;
        }

        .hero,
        .block,
        .metric-card,
        .table-shell,
        .bar-shell {
            background: var(--card);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }

        .hero {
            border-radius: 28px;
            padding: 24px;
            background: linear-gradient(135deg, var(--deep) 0%, var(--primary) 54%, var(--accent) 100%);
            color: #fff7eb;
        }

        .db-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }

        .db-hero-title {
            margin: 12px 0 8px;
            font-size: clamp(2rem, 4vw, 3.1rem);
            line-height: 1;
            font-weight: 900;
        }

        .db-hero-sub {
            margin: 0;
            max-width: 760px;
            line-height: 1.7;
            color: rgba(255, 247, 235, 0.92);
        }

        .db-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .db-kpi {
            position: relative;
            border-radius: 24px;
            padding: 18px;
            background: var(--card);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }

        .db-kpi-accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            border-radius: 24px 24px 0 0;
        }

        .db-kpi-label {
            margin: 6px 0 10px;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
        }

        .db-kpi-val {
            margin: 0;
            font-size: clamp(1.3rem, 2vw, 2rem);
            font-weight: 900;
            line-height: 1.1;
        }

        .db-kpi-button {
            width: 100%;
            border: 0;
            text-align: left;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .db-kpi-button:hover,
        .db-kpi-button:focus {
            transform: translateY(-2px);
            outline: none;
            box-shadow: 0 22px 38px rgba(74, 57, 33, 0.14);
        }

        .db-kpi-sub {
            margin-top: 10px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .db-main-grid-3 {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }

        .db-main-span-6 {
            grid-column: span 6;
        }

        .db-main-span-3 {
            grid-column: span 3;
        }

        .db-card {
            height: 100%;
            border-radius: 24px;
            background: var(--card);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }

        .db-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 18px 18px 12px;
        }

        .db-card-title {
            font-size: 1rem;
            font-weight: 800;
        }

        .db-card-badge {
            padding: 8px 10px;
            border-radius: 999px;
            background: #f7efe2;
            color: var(--deep);
            font-size: 12px;
            font-weight: 800;
        }

        .db-card-body {
            padding: 0 18px 18px;
        }

        .db-story {
            display: grid;
            gap: 12px;
        }

        .db-story-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: #fbf7f1;
            border: 1px solid var(--line);
            line-height: 1.7;
        }

        .db-story-item strong {
            display: block;
            margin-bottom: 4px;
        }

        .eyebrow {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.15);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.10em;
        }

        .hero h1 {
            margin: 12px 0 8px;
            font-size: clamp(2rem, 4vw, 3.4rem);
            line-height: 1;
            font-weight: 900;
        }

        .hero p {
            margin: 0;
            max-width: 760px;
            line-height: 1.7;
            color: rgba(255, 247, 235, 0.92);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .hero-pill,
        .preset-link {
            border-radius: 999px;
        }

        .hero-pill {
            padding: 10px 14px;
            background: rgba(255,255,255,0.15);
            font-size: 14px;
        }

        .filters {
            margin-top: 18px;
            border-radius: 24px;
            padding: 18px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .preset-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preset-link {
            display: inline-block;
            padding: 10px 14px;
            border: 1px solid var(--line);
            background: #fbf7f1;
            color: var(--deep);
            text-decoration: none;
            font-weight: 700;
        }

        .preset-link.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .field input,
        .field button {
            width: 100%;
            height: 46px;
            border-radius: 14px;
            border: 1px solid var(--line);
            padding: 0 14px;
            font-size: 15px;
        }

        .field button {
            border: 0;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--deep) 100%);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .metric-card {
            border-radius: 24px;
            padding: 18px;
        }

        .metric-card .label {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
        }

        .metric-card .value {
            margin: 0;
            font-size: clamp(1.3rem, 2vw, 2rem);
            font-weight: 900;
            line-height: 1.1;
        }

        .metric-card .sub {
            margin-top: 10px;
            color: var(--muted);
            font-size: 13px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 18px;
            margin-top: 18px;
        }

        .block {
            border-radius: 24px;
            padding: 22px;
        }

        .block h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
        }

        .lead-copy {
            margin: 8px 0 16px;
            color: var(--muted);
            line-height: 1.7;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .mini-box {
            padding: 16px;
            border-radius: 18px;
            background: linear-gradient(180deg, #fffdfa 0%, #f7efe2 100%);
            border: 1px solid var(--line);
        }

        .mini-box strong {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .mini-box span {
            font-size: 20px;
            font-weight: 900;
        }

        .note-list {
            display: grid;
            gap: 12px;
        }

        .note-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: #fbf7f1;
            border: 1px solid var(--line);
            line-height: 1.7;
        }

        .note-item strong {
            display: block;
            margin-bottom: 4px;
        }

        .table-shell {
            border-radius: 24px;
            padding: 22px;
            margin-top: 18px;
        }

        .table-wrap {
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th, td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        thead th {
            background: #faf5ed;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .bars {
            display: grid;
            gap: 12px;
        }

        .bar-shell {
            border-radius: 18px;
            padding: 14px 16px;
        }

        .bar-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .bar-track {
            width: 100%;
            height: 14px;
            border-radius: 999px;
            background: #efe5d7;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 999px;
        }

        .fill-cash { background: linear-gradient(135deg, var(--green) 0%, #83b744 100%); }
        .fill-mpesa { background: linear-gradient(135deg, var(--teal) 0%, #5eabbc 100%); }
        .fill-card { background: linear-gradient(135deg, var(--primary) 0%, #d36e52 100%); }
        .fill-bank { background: linear-gradient(135deg, var(--accent) 0%, #e4b75d 100%); }
        .fill-generic { background: linear-gradient(135deg, var(--purple) 0%, #af7bcb 100%); }

        .positive { color: var(--green); }
        .negative { color: #b13030; }

        .credit-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(23, 18, 13, 0.55);
        }

        .credit-modal.show {
            display: flex;
        }

        .detail-modal {
            position: fixed;
            inset: 0;
            z-index: 9998;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(23, 18, 13, 0.55);
        }

        .detail-modal.show {
            display: flex;
        }

        .credit-modal-card {
            width: min(1180px, 100%);
            max-height: min(86vh, 900px);
            background: #fff;
            border-radius: 24px;
            border: 1px solid var(--line);
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .credit-modal-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 18px 20px 14px;
            border-bottom: 1px solid var(--line);
            background: #faf5ed;
        }

        .credit-modal-head h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--ink);
        }

        .credit-modal-head p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .credit-close {
            border: 0;
            border-radius: 999px;
            width: 42px;
            height: 42px;
            background: var(--primary);
            color: #fff;
            font-size: 1.2rem;
            font-weight: 800;
            cursor: pointer;
        }

        .credit-modal-body {
            padding: 18px 20px 20px;
            overflow: auto;
        }

        .credit-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .credit-tab {
            border: 1px solid var(--line);
            background: #fbf7f1;
            color: var(--deep);
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .credit-tab.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .credit-panel {
            display: none;
        }

        .credit-panel.active {
            display: block;
        }

        .detail-hint {
            display: inline-block;
            margin-top: 8px;
            color: var(--deep);
            font-weight: 700;
            font-size: 0.88rem;
        }

        @media (max-width: 1200px) {
            .metrics-grid,
            .db-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .main-grid {
                grid-template-columns: 1fr;
            }
            .db-main-span-6,
            .db-main-span-3 {
                grid-column: span 6;
            }
        }

        @media (max-width: 860px) {
            .filters-grid,
            .mini-grid {
                grid-template-columns: 1fr;
            }
            .db-main-grid-3 {
                grid-template-columns: 1fr;
            }
            .db-main-span-6,
            .db-main-span-3 {
                grid-column: span 1;
            }
        }

        @media (max-width: 720px) {
            .report-shell {
                width: min(100% - 14px, 100%);
                padding-top: 10px;
            }
            .metrics-grid,
            .db-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .preset-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
            .hero,
            .filters,
            .metric-card,
            .block,
            .table-shell,
            .credit-modal-card {
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <section class="hero">
            <div class="db-hero-top">
                <div>
                    <span class="eyebrow">Backend Director Report</span>
                    <h1 class="db-hero-title">Director stock and profit summary</h1>
                    
                </div>
            </div>
            <div class="hero-meta">
                <span class="hero-pill"><?php echo bdr_h($period['label']); ?></span>
                <span class="hero-pill"><?php echo bdr_h($period['from_human']); ?> to <?php echo bdr_h($period['to_human']); ?></span>
                <span class="hero-pill">Stock day: <?php echo bdr_h(date('d M Y', strtotime($stockDay))); ?></span>
            </div>
        </section>

        <section class="block filters">
            <form method="get">
                <input type="hidden" name="range" value="custom">
                <div class="filters-grid">
                    <div class="field">
                        <label>Quick Search</label>
                        <div class="preset-row">
                            <?php
                            $presets = [
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'this_week' => 'This Week',
                                'this_month' => 'This Month',
                                'this_year' => 'This Year',
                            ];
                            foreach ($presets as $key => $label):
                            ?>
                                <a class="preset-link <?php echo $period['preset'] === $key ? 'active' : ''; ?>" href="?range=<?php echo bdr_h($key); ?>">
                                    <?php echo bdr_h($label); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="field">
                        <label for="from">From Date</label>
                        <input type="date" id="from" name="from" value="<?php echo bdr_h($period['from_sql']); ?>">
                    </div>
                    <div class="field">
                        <label for="to">To Date</label>
                        <input type="date" id="to" name="to" value="<?php echo bdr_h($period['to_sql']); ?>">
                    </div>
                    <div class="field">
                        <label>&nbsp;</label>
                        <button type="submit">Apply Dates</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="db-kpi-grid">
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:#ef4444;"></div>
                <p class="db-kpi-label">Sales Raised</p>
                <p class="db-kpi-val"><?php echo bdr_money($salesValue); ?></p>
                <div class="db-kpi-sub"><?php echo bdr_num((float) ($sales['total_count'] ?? 0)); ?> sales or bills were raised in the selected period.</div>
            </article>
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:#0f766e;"></div>
                <p class="db-kpi-label">Stock Purchased</p>
                <p class="db-kpi-val"><?php echo bdr_money((float) ($stockPurchased['total_amount'] ?? 0)); ?></p>
                <div class="db-kpi-sub"><?php echo bdr_num((float) ($stockPurchased['qty_total'] ?? 0)); ?> purchased items were received through GRN in the selected period.</div>
            </article>
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:#2563eb;"></div>
                <p class="db-kpi-label">Opening Stock</p>
                <p class="db-kpi-val"><?php echo bdr_money($derivedOpeningBuyValue); ?></p>
                <div class="db-kpi-sub">Buying Value: <?php echo bdr_money($derivedOpeningBuyValue); ?>. Selling Value: <?php echo bdr_money($derivedOpeningSellValue); ?>. Items: <?php echo bdr_num($derivedOpeningQty); ?>.</div>
            </article>
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:<?php echo $currentStockExpectedGrossProfit >= 0 ? '#16a34a' : '#dc2626'; ?>;"></div>
                <p class="db-kpi-label">Gross Profit</p>
                <p class="db-kpi-val <?php echo $currentStockExpectedGrossProfit >= 0 ? 'positive' : 'negative'; ?>"><?php echo bdr_money($currentStockExpectedGrossProfit); ?></p>
                <div class="db-kpi-sub">Gross Profit = Sales + Closing Stock - Opening Stock - Purchases. Green is above zero, red is below zero.</div>
            </article>
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:#1d4ed8;"></div>
                <p class="db-kpi-label">Closing Stock</p>
                <p class="db-kpi-val"><?php echo bdr_money($currentStockSellingCost); ?></p>
                <div class="db-kpi-sub">Buying Cost: <?php echo bdr_money($currentStockBuyingCost); ?>. Selling Cost: <?php echo bdr_money($currentStockSellingCost); ?>. Items: <?php echo bdr_num($derivedClosingQty); ?>.</div>
            </article>
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:#7c3aed;"></div>
                <p class="db-kpi-label">Expected Gross Profit</p>
                <p class="db-kpi-val"><?php echo bdr_money($currentStockExpectedGrossProfit); ?></p>
                <div class="db-kpi-sub">Gross Profit = Selling Cost - Buying Cost, before any expense.</div>
            </article>
            <article class="db-kpi">
                <div class="db-kpi-accent" style="background:#d97706;"></div>
                <p class="db-kpi-label">Total Expenses</p>
                <p class="db-kpi-val"><?php echo bdr_money($expenseValue); ?></p>
                <div class="db-kpi-sub"><?php echo bdr_num((float) ($expenses['total_count'] ?? 0)); ?> expense entries were recorded in the selected period.</div>
            </article>
            <button type="button" class="db-kpi db-kpi-button" id="open-credit-modal" aria-haspopup="dialog" aria-controls="credit-modal">
                <div class="db-kpi-accent" style="background:#4d7d27;"></div>
                <p class="db-kpi-label">Total Credit</p>
                <p class="db-kpi-val"><?php echo bdr_money($creditGivenValue); ?></p>
                <div class="db-kpi-sub"><?php echo bdr_num($creditGivenCount); ?> credit bills were raised in the selected period. Tap to view customers with credit.</div>
            </button>
        </section>

        <section class="table-shell">
            <h2>Simple Summary</h2>
            <p class="lead-copy">This stays simple for now and only shows the two figures we have added so far.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Measure</th>
                            <th>Value</th>
                            <th>Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Sales Raised</strong></td>
                            <td><?php echo bdr_money($salesValue); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Number of Sales</strong></td>
                            <td><?php echo bdr_num((float) ($sales['total_count'] ?? 0)); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Stock Purchased</strong></td>
                            <td><?php echo bdr_money((float) ($stockPurchased['total_amount'] ?? 0)); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Purchased Items Received</strong></td>
                            <td><?php echo bdr_num((float) ($stockPurchased['qty_total'] ?? 0)); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Opening Stock Buying Value</strong></td>
                            <td><?php echo bdr_money($derivedOpeningBuyValue); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Opening Stock Items</strong></td>
                            <td><?php echo bdr_num($derivedOpeningQty); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Opening Stock Selling Value</strong></td>
                            <td><?php echo bdr_money($derivedOpeningSellValue); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Closing Stock Buying Value</strong></td>
                            <td><?php echo bdr_money($currentStockBuyingCost); ?></td>
                            <td>Current stock buying value</td>
                        </tr>
                        <tr>
                            <td><strong>Closing Stock Items</strong></td>
                            <td><?php echo bdr_num($derivedClosingQty); ?></td>
                            <td>Live closing stock from `cntr1_qty`</td>
                        </tr>
                        <tr>
                            <td><strong>Closing Stock Selling Value</strong></td>
                            <td><?php echo bdr_money($currentStockSellingCost); ?></td>
                            <td>Current stock selling value</td>
                        </tr>
                        <tr>
                            <td><strong>Gross Profit</strong></td>
                            <td><?php echo bdr_money($currentStockExpectedGrossProfit); ?></td>
                            <td>Sales + Closing Stock - Opening Stock - Purchases.</td>
                        </tr>
                        <tr>
                            <td><strong>Total Expenses</strong></td>
                            <td><?php echo bdr_money($expenseValue); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Expense Entries</strong></td>
                            <td><?php echo bdr_num((float) ($expenses['total_count'] ?? 0)); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Credit</strong></td>
                            <td><?php echo bdr_money($creditGivenValue); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Credit Bills Raised</strong></td>
                            <td><?php echo bdr_num($creditGivenCount); ?></td>
                            <td><?php echo bdr_h($period['from_human'] . ' to ' . $period['to_human']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="credit-modal" id="credit-modal" aria-hidden="true">
        <div class="credit-modal-card" role="dialog" aria-modal="true" aria-labelledby="credit-modal-title">
            <div class="credit-modal-head">
                <div>
                    <h3 id="credit-modal-title">Customers with credit</h3>
                    <p>Customer list for the current credit view in the system.</p>
                </div>
                <button type="button" class="credit-close" id="close-credit-modal" aria-label="Close credit list">×</button>
            </div>
            <div class="credit-modal-body">
                <div class="credit-tabs">
                    <button type="button" class="credit-tab active" data-target="period-credit-panel">Credit This Period</button>
                    <button type="button" class="credit-tab" data-target="all-credit-panel">All Credit</button>
                </div>

                <div class="credit-panel active" id="period-credit-panel">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Credit Bills</th>
                                    <th>Credit Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($periodCreditList as $row): ?>
                                    <tr>
                                        <td><?php echo bdr_h(trim((string) ($row['name'] ?? 'Customer'))); ?></td>
                                        <td><?php echo bdr_h((string) ($row['phone'] ?? '')); ?></td>
                                        <td><?php echo bdr_h((string) ($row['location'] ?? '')); ?></td>
                                        <td><?php echo bdr_num((float) ($row['credit_bills'] ?? 0)); ?></td>
                                        <td><strong><?php echo bdr_money((float) ($row['credit_total'] ?? 0)); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($periodCreditList === []): ?>
                                    <tr>
                                        <td colspan="5">There are no credit customers in the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="credit-panel" id="all-credit-panel">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Open Credit Bills</th>
                                    <th>Credit Sales Total</th>
                                    <th>Current Unpaid Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currentCreditList as $row): ?>
                                    <tr>
                                        <td><?php echo bdr_h(trim((string) ($row['name'] ?? 'Customer'))); ?></td>
                                        <td><?php echo bdr_h((string) ($row['phone'] ?? '')); ?></td>
                                        <td><?php echo bdr_h((string) ($row['location'] ?? '')); ?></td>
                                        <td><?php echo bdr_num((float) ($row['unpaid_bills'] ?? 0)); ?></td>
                                        <td><?php echo bdr_money((float) ($row['unpaid_sales_total'] ?? 0)); ?></td>
                                        <td><strong><?php echo bdr_money((float) ($row['bal'] ?? 0)); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($currentCreditList === []): ?>
                                    <tr>
                                        <td colspan="6">There are no customers with credit currently.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var openBtn = document.getElementById('open-credit-modal');
            var closeBtn = document.getElementById('close-credit-modal');
            var modal = document.getElementById('credit-modal');
            var tabs = document.querySelectorAll('.credit-tab');
            var panels = document.querySelectorAll('.credit-panel');

            if (!openBtn || !closeBtn || !modal) {
                return;
            }

            function openModal() {
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
            Array.prototype.forEach.call(tabs, function (tab) {
                tab.addEventListener('click', function () {
                    var targetId = tab.getAttribute('data-target');
                    Array.prototype.forEach.call(tabs, function (item) {
                        item.classList.remove('active');
                    });
                    Array.prototype.forEach.call(panels, function (panel) {
                        panel.classList.remove('active');
                    });
                    tab.classList.add('active');
                    var panel = document.getElementById(targetId);
                    if (panel) {
                        panel.classList.add('active');
                    }
                });
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('show')) {
                    closeModal();
                }
            });
        }());
    </script>
</body>
</html>
