<?php
session_start();
include_once('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include_once('../../back/counter/funcs.php');

if (!function_exists('thermal_money')) {
    function thermal_money($amount) {
        return number_format((float)$amount, 2);
    }
}

if (!function_exists('kp_local_table_exists')) {
    function kp_local_table_exists($connection, $table) {
        $table_esc = mysqli_real_escape_string($connection, $table);
        $res = mysqli_query($connection, "SHOW TABLES LIKE '$table_esc'");
        return $res && mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('kp_local_column_exists')) {
    function kp_local_column_exists($connection, $table, $column) {
        $table = preg_replace('/[^A-Za-z0-9_]/', '', (string)$table);
        $column = mysqli_real_escape_string($connection, (string)$column);
        $result = mysqli_query($connection, "SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kp_counter_stock_take_summary')) {
    function kp_counter_stock_take_summary($connection, $counter, $shift) {
        $table = "kp_cntr" . (int)$counter . "_stock_take";
        $summary = array(
            'available' => false,
            'taken' => false,
            'ok_count' => 0,
            'variance_count' => 0,
            'variance_qty' => 0.0,
            'status' => 'NOT TAKEN'
        );

        if (!kp_local_table_exists($connection, $table)) {
            return $summary;
        }

        $summary['available'] = true;
        $shift_esc = mysqli_real_escape_string($connection, $shift);
        $rows = mysqli_query($connection, "SELECT opening, counted FROM `$table` WHERE shift='$shift_esc'");
        if (!$rows || mysqli_num_rows($rows) < 1) {
            return $summary;
        }

        $summary['taken'] = true;
        while ($row = mysqli_fetch_assoc($rows)) {
            $opening = (float)($row['opening'] ?? 0);
            $counted = (float)($row['counted'] ?? 0);
            $variance = $opening - $counted;
            if (abs($variance) > 0.0001) {
                $summary['variance_count']++;
                $summary['variance_qty'] += abs($variance);
            } else {
                $summary['ok_count']++;
            }
        }

        if ($summary['variance_count'] > 0) {
            $summary['status'] = 'VARIANCE';
        } elseif ($summary['taken']) {
            $summary['status'] = 'OK';
        }

        return $summary;
    }
}

if (!function_exists('kp_counter_transfer_rows')) {
    function kp_counter_transfer_rows($connection, $counter, $shift) {
        $counter = (int)$counter;
        $shift_esc = mysqli_real_escape_string($connection, $shift);
        $rows = array();
        $sql = "SELECT ti.trans_id, ti.item_id, SUM(ti.qty) AS qty
                FROM kp_counter_trans_items ti
                INNER JOIN kp_counter_trans t ON t.trans_id = ti.trans_id
                WHERE t.shift='$shift_esc' AND (t.cntr_from='$counter' OR t.cntr_to='$counter')
                GROUP BY ti.trans_id, ti.item_id
                ORDER BY ti.trans_id DESC, ti.item_id ASC";
        $result = mysqli_query($connection, $sql);
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $trans_id = (int)$row['trans_id'];
            $transfer = mysqli_query($connection, "SELECT cntr_from, cntr_to, user_from, user_to, status FROM kp_counter_trans WHERE trans_id='$trans_id' LIMIT 1");
            $meta = $transfer ? mysqli_fetch_assoc($transfer) : array();
            $rows[] = array(
                'trans_id' => $trans_id,
                'item_id' => (int)($row['item_id'] ?? 0),
                'qty' => (float)($row['qty'] ?? 0),
                'cntr_from' => (int)($meta['cntr_from'] ?? 0),
                'cntr_to' => (int)($meta['cntr_to'] ?? 0),
                'user_from' => (int)($meta['user_from'] ?? 0),
                'user_to' => (int)($meta['user_to'] ?? 0),
                'status' => (int)($meta['status'] ?? 0)
            );
        }

        return $rows;
    }
}

if (!function_exists('kp_counter_kpesa_totals')) {
    function kp_counter_kpesa_totals($connection, $counter, $shift) {
        $response = array(
            'enabled' => false,
            'mpesa_total' => 0.0,
            'card_total' => 0.0,
            'mpesa_modes' => array(),
            'card_modes' => array()
        );

        $kpesa_active = (int)get_column('kp_settings_print', 'kpesa_active', "print_doc=1");
        if ($kpesa_active === 2) {
            return $response;
        }

        $response['enabled'] = true;
        $counter = (int)$counter;
        $shift_esc = mysqli_real_escape_string($connection, $shift);
        $has_kpesa_profile_id = false;
        $has_default_mpesa = false;
        $has_default_card = false;
        $kpesa_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE 'kpesa_profile_id'");
        if ($kpesa_col && mysqli_num_rows($kpesa_col) > 0) {
            $has_kpesa_profile_id = true;
        }
        $mpesa_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE 'is_default_mpesa'");
        if ($mpesa_col && mysqli_num_rows($mpesa_col) > 0) {
            $has_default_mpesa = true;
        }
        $card_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE 'is_default_card'");
        if ($card_col && mysqli_num_rows($card_col) > 0) {
            $has_default_card = true;
        }
        $selectParts = array(
            'pay_id',
            'name',
            $has_kpesa_profile_id ? 'kpesa_profile_id' : '0 AS kpesa_profile_id',
            $has_default_mpesa ? 'is_default_mpesa' : '2 AS is_default_mpesa',
            $has_default_card ? 'is_default_card' : '2 AS is_default_card',
        );
        $modes_q = mysqli_query($connection, "SELECT " . implode(', ', $selectParts) . " FROM kp_pay_mode WHERE 1");
        if (!$modes_q) {
            return $response;
        }

        while ($mode = mysqli_fetch_assoc($modes_q)) {
            $mode_id = (int)$mode['pay_id'];
            $mode_name = trim((string)($mode['name'] ?? ''));
            $mode_total = (float)column_total('kp_sale_payment', 'mode' . $mode_id, "pay_date='$shift_esc' AND counter='$counter'");
            $is_mpesa = ((int)($mode['is_default_mpesa'] ?? 2) === 1) || (int)($mode['kpesa_profile_id'] ?? 0) > 0 || stripos($mode_name, 'mpesa') !== false;
            $is_card = ((int)($mode['is_default_card'] ?? 2) === 1) || stripos($mode_name, 'card') !== false || stripos($mode_name, 'pdq') !== false;

            if ($is_mpesa) {
                $response['mpesa_total'] += $mode_total;
                $response['mpesa_modes'][] = array('name' => $mode_name, 'amount' => $mode_total);
            }
            if ($is_card) {
                $response['card_total'] += $mode_total;
                $response['card_modes'][] = array('name' => $mode_name, 'amount' => $mode_total);
            }
        }

        return $response;
    }
}

$summary_id = (int)($_GET['summary_id'] ?? 0);
$counter = (int)($_GET['cntr_id'] ?? $_GET['id'] ?? 0);
$shift = mysqli_real_escape_string($connection, $_GET['shift'] ?? '');
$closeSummaryRow = null;
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
    exit('Invalid counter');
}
if ($shift === '') {
    $shift = (string)get_column('kp_shift', 'shift', '1');
}

$openingStaffId = (int)($closeSummaryRow['opening_staff_id'] ?? $closeSummaryRow['open_by'] ?? get_column('kp_counter_hist', 'open_by', "shift='$shift'"));
$closingStaffId = (int)($closeSummaryRow['closing_staff_id'] ?? $closeSummaryRow['close_by'] ?? get_column('kp_counter_hist', 'close_by', "shift='$shift'"));
$openingAt = (string)($closeSummaryRow['opening_time'] ?? $closeSummaryRow['opened_at'] ?? get_column('kp_counter_hist', 'opening', "shift='$shift'"));
$closingAt = (string)($closeSummaryRow['closing_time'] ?? $closeSummaryRow['closed_at'] ?? get_column('kp_counter_hist', 'closing', "shift='$shift'"));
$counterTotalSales = (float)($closeSummaryRow['total_sales'] ?? column_total('kp_sales', 'amount', "shift='$shift' AND counter='$counter"));

$nos = 45;
$today = $shift;
$counterName = get_counter($counter);
$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);
$stockTakeSummary = function_exists('kp_counter_stock_take_snapshot_summary')
    ? kp_counter_stock_take_snapshot_summary($connection, $counter, $shift, $summary_id)
    : kp_counter_stock_take_summary($connection, $counter, $shift);
$transferRows = kp_counter_transfer_rows($connection, $counter, $shift);
$kpesaTotals = kp_counter_kpesa_totals($connection, $counter, $shift);
$thermalCssPath = realpath(__DIR__ . '/../assets/css/thermal_print.css');
$thermalCss = ($thermalCssPath && is_file($thermalCssPath)) ? file_get_contents($thermalCssPath) : '';
$direct_print = (int)($_GET['direct_print'] ?? 0) === 1;
$return_to = trim((string)($_GET['return_to'] ?? '../dash/dash.php'));
$today_requests_total = (int)($closeSummaryRow['today_requests_total'] ?? column_count('kp_counter_req', 'req_id', "req_shift='$shift' AND counter='$counter'"));
$today_returns_accepted_total = (int)($closeSummaryRow['today_returns_accepted_total'] ?? column_count('kp_ret_items', 'item_id', "shift='$shift' AND stock_counter='$counter' AND confirm_status=1"));
$today_returns_rejected_total = (int)($closeSummaryRow['today_returns_rejected_total'] ?? column_count('kp_ret_items', 'item_id', "shift='$shift' AND stock_counter='$counter' AND confirm_status=3"));
$today_voided_items_total = (int)($closeSummaryRow['today_voided_items_total'] ?? 0);
if ($today_voided_items_total < 1) {
    $voidTableExists = mysqli_query($connection, "SHOW TABLES LIKE 'kp_void_items'");
    if ($voidTableExists && mysqli_num_rows($voidTableExists) > 0) {
        $today_voided_items_total = (int)column_count('kp_void_items', 'item_id', "shift='$shift' AND stock_counter='$counter'");
    }
}
$today_items_sold_total = (float)($closeSummaryRow['today_items_sold_total'] ?? column_total('kp_sale_items', 'qty', "shift='$shift' AND stock_counter='$counter'"));
$today_transfer_to_total = (int)($closeSummaryRow['today_transfer_to_total'] ?? column_count('kp_counter_trans', 'trans_id', "shift='$shift' AND cntr_to='$counter'"));
$today_transfer_from_total = (int)($closeSummaryRow['today_transfer_from_total'] ?? column_count('kp_counter_trans', 'trans_id', "shift='$shift' AND cntr_from='$counter'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Counter Summary #<?php echo $counter; ?></title>
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
    .meta span { white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
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
    .category-heading td {
      font-size: 15px;
      font-weight: 900;
      text-align: left;
      padding-top: 7px;
      padding-bottom: 3px;
      border-top: 2px solid #000;
      letter-spacing: .04em;
    }
    .category-total td {
      font-size: 12px;
      font-weight: 900;
      border-top: 1px dashed #555;
      padding-top: 3px;
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
    /* Keep every summary table compact and use the full receipt width. */
    #receipt-body > table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
    #receipt-body > table th, #receipt-body > table td,
    #bord table th, #bord table td { padding: 3px 4px !important; vertical-align: middle; white-space: normal !important; overflow: visible; text-overflow: clip; overflow-wrap: anywhere; word-break: break-word; }
    #bord table { width:100%; max-width:100%; table-layout:auto; }
    #bord table td.text-right, #bord table th.text-right { width:1%; white-space:nowrap !important; overflow:visible; }
    #bord table td.text-center, #bord table th.text-center { width:1%; }
    #bord table td:first-child, #bord table th:first-child { overflow-wrap:anywhere; word-break:break-word; }
    #bord table.returned-items-table { table-layout:fixed; }
    #bord table.returned-items-table td:first-child, #bord table.returned-items-table th:first-child { width:52%; }
    #bord table.returned-items-table td:nth-child(2), #bord table.returned-items-table th:nth-child(2) { width:12%; white-space:nowrap !important; }
    #bord table.returned-items-table td:nth-child(3), #bord table.returned-items-table th:nth-child(3) { width:16%; white-space:nowrap !important; }
    #bord table.returned-items-table td:nth-child(4), #bord table.returned-items-table th:nth-child(4) { width:20%; }
    #bord table.req-items-table td:first-child { font-size:14px !important; font-weight:700; line-height:1.25; }
    #bord table.req-items-table .category-heading td { font-size:15px !important; }
    #bord .category-heading td { font-size:16px !important; line-height:1.2; font-weight:900 !important; text-align:left !important; white-space:normal !important; border-top:2px solid #000; border-bottom:1px solid #000; padding:7px 4px !important; letter-spacing:.05em; }
    #bord .category-total td { font-size:13px !important; font-weight:900 !important; white-space:normal !important; border-top:1px dashed #000; padding:5px 4px !important; }
    #receipt-body > table td.compact-right, #receipt-body > table th.compact-right { text-align: right !important; white-space: nowrap; }
    #receipt-body > table td.compact-center, #receipt-body > table th.compact-center { text-align: center !important; }
    #receipt-body > table:nth-of-type(1) td:first-child { width: 72%; }
    #receipt-body > table:nth-of-type(1) td:last-child { width: 28%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(2) th:nth-child(1), #receipt-body > table:nth-of-type(2) td:nth-child(1) { width: 8%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(2), #receipt-body > table:nth-of-type(2) td:nth-child(2) { width: 42%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(3), #receipt-body > table:nth-of-type(2) td:nth-child(3) { width: 25%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(4), #receipt-body > table:nth-of-type(2) td:nth-child(4) { width: 15%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(5), #receipt-body > table:nth-of-type(2) td:nth-child(5) { width: 10%; }
    #receipt-body > table:nth-of-type(3) td:first-child, #receipt-body > table:nth-of-type(6) td:first-child { width: 72%; }
    #receipt-body > table:nth-of-type(3) td:last-child, #receipt-body > table:nth-of-type(6) td:last-child { width: 28%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(4) th:first-child, #receipt-body > table:nth-of-type(4) td:first-child,
    #receipt-body > table:nth-of-type(5) th:first-child, #receipt-body > table:nth-of-type(5) td:first-child { width: 43%; }
    #receipt-body > table:nth-of-type(4) th:nth-child(2), #receipt-body > table:nth-of-type(4) td:nth-child(2),
    #receipt-body > table:nth-of-type(5) th:nth-child(2), #receipt-body > table:nth-of-type(5) td:nth-child(2) { width: 28%; }
    #receipt-body > table:nth-of-type(4) th:last-child, #receipt-body > table:nth-of-type(4) td:last-child,
    #receipt-body > table:nth-of-type(5) th:last-child, #receipt-body > table:nth-of-type(5) td:last-child { width: 29%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(7) th:first-child, #receipt-body > table:nth-of-type(7) td:first-child { width: 14%; }
    #receipt-body > table:nth-of-type(7) th:nth-child(2), #receipt-body > table:nth-of-type(7) td:nth-child(2) { width: 50%; }
    #receipt-body > table:nth-of-type(7) th:nth-child(3), #receipt-body > table:nth-of-type(7) td:nth-child(3) { width: 14%; }
    #receipt-body > table:nth-of-type(7) th:last-child, #receipt-body > table:nth-of-type(7) td:last-child { width: 22%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(8) th:first-child, #receipt-body > table:nth-of-type(8) td:first-child { width: 18%; }
    #receipt-body > table:nth-of-type(8) th:nth-child(2), #receipt-body > table:nth-of-type(8) td:nth-child(2) { width: 57%; }
    #receipt-body > table:nth-of-type(8) th:last-child, #receipt-body > table:nth-of-type(8) td:last-child { width: 25%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(9) td:first-child { width: 65%; }
    #receipt-body > table:nth-of-type(9) td:last-child { width: 35%; text-align: right; }
    /* Use the complete thermal paper width without outer whitespace. */
    html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; overflow-x: hidden; }
    #receipt { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; box-sizing: border-box !important; }
    #bord { margin: 0 !important; width: 100% !important; max-width: none !important; box-sizing: border-box !important; }
    #receipt-body, #receipt-body > table, .items-tbl, .totals-tbl, .open-demo-summary { margin-left: 0 !important; margin-right: 0 !important; width: 100% !important; max-width: 100% !important; }
    @media print {
      html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; }
      #receipt { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; box-sizing: border-box !important; }
    }

    /* Final thermal receipt settings: 79mm paper, flush to the printer edge. */
    @page { size: 79mm auto; margin: 0 !important; }
    html, body {
      width: 79mm !important;
      min-width: 79mm !important;
      max-width: 79mm !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
      overflow-x: hidden !important;
    }
    #receipt {
      width: 79mm !important;
      min-width: 79mm !important;
      max-width: 79mm !important;
      margin: 0 !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
    #bord, #receipt-body, #receipt-body > table, .items-tbl, .totals-tbl {
      width: 100% !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      box-sizing: border-box !important;
    }
    @media print {
      @page { size: 79mm auto; margin: 0 !important; }
      html, body, #receipt { width: 79mm !important; min-width: 79mm !important; max-width: 79mm !important; margin: 0 !important; padding: 0 !important; }
    }
  </style>
  <?php if ($direct_print) { ?>
  <script type="text/javascript">
    (function () {
      var redirected = false;
      function afterPrintRedirect() {
        if (redirected) {
          return;
        }
        redirected = true;
        window.location.replace(<?php echo json_encode($return_to); ?>);
      }
      window.addEventListener('load', function () {
        setTimeout(function () {
          window.print();
        }, 250);
      });
      window.addEventListener('afterprint', function () {
        setTimeout(afterPrintRedirect, 150);
      });
      document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
          setTimeout(afterPrintRedirect, 300);
        }
      });
    })();
  </script>
  <?php } ?>
</head>
<body>
<div id="receipt">
  <div id="bord">
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;">
      <b><?php echo date('l jS F Y', strtotime($shift)); ?><br><?php echo $counterName; ?> SHIFT SUMMARY</b>
    </h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>

    <table class="table">
      <tbody>
        <tr>
          <td><b>OPENING</b></td>
          <td><?php echo htmlspecialchars(get_user($openingStaffId)); ?></td>
          <td colspan="2">
            <?php
            $new = $openingAt !== '' ? date('l h:i:sa d-m-Y', strtotime($openingAt)) : 'NOT RECORDED';
            echo $new;
            ?>
          </td>
        </tr>
        <tr>
          <td><b>CLOSING</b></td>
          <td><?php echo htmlspecialchars(get_user($closingStaffId)); ?></td>
          <td colspan="2"><?php echo $closingAt !== '' ? htmlspecialchars(date('l h:i:sa d-m-Y', strtotime($closingAt))) : 'NOT RECORDED'; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>TOTAL SALES</b></td>
          <td colspan="2"><b><?php echo thermal_money($counterTotalSales); ?></b></td>
        </tr>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>STOCK OUT<br>COUNTER STOCK TRANSFERS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table"><tbody>
      <tr><td><b>ITEM</b></td><td class="text-right"><b>QTY</b></td><td><b>TO</b></td></tr>
      <?php
      $stockOutTransferCount = 0;
      foreach ($transferRows as $transferRow) {
          if ((int)$transferRow['cntr_from'] !== $counter) continue;
          $stockOutTransferCount++;
          echo '<tr><td>' . htmlspecialchars(get_item($transferRow['item_id'])) . '</td><td class="text-right">' . thermal_money($transferRow['qty']) . '</td><td>' . htmlspecialchars(get_counter($transferRow['cntr_to'])) . '</td></tr>';
      }
      if ($stockOutTransferCount < 1) echo '<tr><td colspan="3">No stock transfers out for this counter.</td></tr>';
      ?>
    </tbody></table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>STOCK OUT<br>UNIT GENERATED</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table"><tbody>
      <tr><td><b>ITEM</b></td><td class="text-right"><b>QTY</b></td><td><b>USER</b></td></tr>
      <?php
      $unitRows = false;
      if (kp_local_table_exists($connection, 'kp_stock_track')) {
          $unitRows = mysqli_query($connection, "SELECT item_id, user_id, SUM(qty) AS qty FROM kp_stock_track WHERE shift='$shift' AND stock_counter='$counter' AND `trans`='UNIT_GEN' GROUP BY item_id, user_id ORDER BY item_id ASC");
      }
      if (!$unitRows || mysqli_num_rows($unitRows) < 1) {
          echo '<tr><td colspan="3">No generated units recorded for this counter.</td></tr>';
      } else {
          while ($unit = mysqli_fetch_assoc($unitRows)) {
              echo '<tr><td>' . htmlspecialchars(get_item((int)$unit['item_id'])) . '</td><td class="text-right">' . thermal_money($unit['qty']) . '</td><td>' . htmlspecialchars(get_user((int)$unit['user_id'])) . '</td></tr>';
          }
      }
      ?>
    </tbody></table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b><?php echo $counterName; ?><br>STOCK OUT<br>SOLD ITEMS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <tr><td><b>ITEM</b></td><td class="text-right"><b>QTY</b></td><td class="text-right"><b>TOTAL</b></td></tr>
        <?php
        $soldItems = mysqli_query($connection, "SELECT si.item_id, i.cat_id, SUM(si.qty) AS qty, SUM(si.total) AS amount FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id=si.item_id WHERE si.shift='$shift' AND si.stock_counter='$counter' GROUP BY si.item_id, i.cat_id ORDER BY i.cat_id ASC, amount DESC");
        if (!$soldItems || mysqli_num_rows($soldItems) < 1) {
            echo '<tr><td colspan="3">No sold items recorded for this counter.</td></tr>';
        } else {
            $lastCategory = null;
            $categoryQty = 0.0;
            $categoryAmount = 0.0;
            while ($sold = mysqli_fetch_assoc($soldItems)) {
                $categoryId = (int)($sold['cat_id'] ?? 0);
                if ($lastCategory !== $categoryId) {
                    if ($lastCategory !== null) {
                        echo '<tr class="category-total"><td colspan="2"><b>CATEGORY</b> ' . htmlspecialchars($lastCategory > 0 ? get_cat($lastCategory) : 'UNCATEGORISED') . '</td><td class="text-right"><b>' . thermal_money($categoryAmount) . '</b></td></tr>';
                    }
                    echo '<tr class="category-heading"><td colspan="3">' . htmlspecialchars($categoryId > 0 ? get_cat($categoryId) : 'UNCATEGORISED') . '</td></tr>';
                    $lastCategory = $categoryId;
                    $categoryQty = 0.0;
                    $categoryAmount = 0.0;
                }
                $categoryQty += (float)$sold['qty'];
                $categoryAmount += (float)$sold['amount'];
                echo '<tr><td>' . htmlspecialchars(get_item((int)$sold['item_id'])) . '</td><td class="text-right">' . thermal_money($sold['qty']) . '</td><td class="text-right">' . thermal_money($sold['amount']) . '</td></tr>';
            }
            if ($lastCategory !== null) {
                echo '<tr class="category-total"><td colspan="2"><b>CATEGORY</b> ' . htmlspecialchars($lastCategory > 0 ? get_cat($lastCategory) : 'UNCATEGORISED') . '</td><td class="text-right"><b>' . thermal_money($categoryAmount) . '</b></td></tr>';
            }
        }
        ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>STOCK IN<br>REQUISITIONS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table req-items-table">
      <tbody>
        <tr><td><b>ITEM</b></td><td class="text-right"><b>QTY</b></td><td><b>STATUS</b></td><td><b>ISSUER</b></td></tr>
        <?php
        $reqRows = mysqli_query($connection, "SELECT ri.item_id, i.cat_id, r.issue_user, r.issue_status, r.rec_status, SUM(COALESCE(ri.issue_qty, ri.req_qty, 0)) AS qty FROM kp_counter_req r INNER JOIN kp_counter_req_items ri ON ri.req_id=r.req_id LEFT JOIN kp_items i ON i.item_id=ri.item_id WHERE r.req_shift='$shift' AND r.counter='$counter' GROUP BY ri.item_id, i.cat_id, r.issue_user, r.issue_status, r.rec_status ORDER BY r.issue_status ASC, i.cat_id ASC, ri.item_id ASC");
        if (!$reqRows || mysqli_num_rows($reqRows) < 1) {
            echo '<tr><td colspan="4">No requisitions recorded for this counter.</td></tr>';
        } else {
            $lastReqStatus = null;
            $lastReqCategory = null;
            while ($req = mysqli_fetch_assoc($reqRows)) {
                $reqStatus = ((int)$req['issue_status'] === 1) ? 'ISSUED' : 'PENDING';
                if ($lastReqStatus !== $reqStatus) {
                    echo '<tr class="category-heading"><td colspan="4">' . $reqStatus . '</td></tr>';
                    $lastReqStatus = $reqStatus;
                    $lastReqCategory = null;
                }
                $catName = ((int)($req['cat_id'] ?? 0) > 0) ? get_cat((int)$req['cat_id']) : 'UNCATEGORISED';
                if ($lastReqCategory !== $catName) {
                    echo '<tr class="category-heading"><td colspan="4">' . htmlspecialchars($catName) . '</td></tr>';
                    $lastReqCategory = $catName;
                }
                $issuerId = (int)($req['issue_user'] ?? 0);
                if ($reqStatus === 'PENDING') {
                    echo '<tr><td>' . htmlspecialchars(get_item((int)$req['item_id'])) . '</td><td class="text-right">' . thermal_money($req['qty']) . '</td><td colspan="2">PENDING</td></tr>';
                } else {
                    echo '<tr><td>' . htmlspecialchars(get_item((int)$req['item_id'])) . '</td><td class="text-right">' . thermal_money($req['qty']) . '</td><td>' . $reqStatus . '</td><td>' . htmlspecialchars($issuerId > 0 ? get_user($issuerId) : '-') . '</td></tr>';
                }
            }
        }
        ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>BILLS SUMMARY</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <tr>
          <td class="text-center"><b>TOTAL BILLS</b></td>
          <td class="text-center" colspan="2"><h3><?php echo column_count('kp_sales', 'sale_id', "shift='$shift' AND counter='$counter'"); ?></h3></td>
        </tr>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>DELETED BILLS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table returned-items-table">
      <tbody>
        <?php
        $deletedCounterWhere = kp_local_column_exists($connection, 'kp_del_orders', 'counter') ? " AND counter='$counter'" : (kp_local_column_exists($connection, 'kp_del_orders', 'cntr_id') ? " AND cntr_id='$counter'" : '');
        $delete = mysqli_query($connection, "SELECT * FROM kp_del_orders WHERE shift='$shift'" . $deletedCounterWhere) or die('Error fetching the staff data.:' . mysqli_error($connection));
        if (mysqli_num_rows($delete) < 1) {
            echo "<tr><td colspan='4'>No orders have been deleted Today</td></tr>";
        } else {
            ?>
            <tr>
              <td class="text-center"><b>Ord No</b></td>
              <td class="text-center"><b>Ord By</b></td>
              <td class="text-center"><b>App By</b></td>
              <td class="text-center"><b>AMT</b></td>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc($delete)) {
                ?>
                <tr>
                  <td><?php echo $row['sale_id']; ?></td>
                  <td><?php echo get_user($row['user_id']); ?></td>
                  <td><?php echo get_user($row['appr_by']); ?></td>
                  <td><?php echo $row['amount']; ?></td>
                </tr>
                <?php
            }
        }
        ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>USER SALES BY ORDER / TOKENS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <?php
        $userSalesTotal = 0;
        $userTokenTotal = 0;
        $userSales = mysqli_query($connection, "SELECT user_id, COUNT(sale_id) AS orders_count, SUM(amount) AS sales_total FROM kp_sales WHERE shift='$shift' AND counter='$counter' GROUP BY user_id ORDER BY sales_total DESC") or die(mysqli_error($connection));
        if (mysqli_num_rows($userSales) < 1) {
            echo "<tr><td colspan='4'>No sales recorded for this counter.</td></tr>";
        } else {
            ?>
            <tr>
              <td class="text-center"><b>USER</b></td>
              <td class="text-center"><b>ORDERS</b></td>
              <td class="text-center"><b>SALES</b></td>
              <td class="text-center"><b>TOKENS</b></td>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc($userSales)) {
                $salesUserId = (int)$row['user_id'];
                $salesTotal = (float)($row['sales_total'] ?? 0);
                $tokenTotal = function_exists('get_user_shift_token_total') ? (float)get_user_shift_token_total($salesUserId, $shift) : 0;
                $userSalesTotal += $salesTotal;
                $userTokenTotal += $tokenTotal;
                ?>
                <tr>
                  <td><?php echo htmlspecialchars(get_user($salesUserId)); ?></td>
                  <td><?php echo (int)$row['orders_count']; ?></td>
                  <td><?php echo thermal_money($salesTotal); ?></td>
                  <td><?php echo thermal_money($tokenTotal); ?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
              <td><b>TOTAL</b></td>
              <td><b><?php echo (int)column_count('kp_sales', 'sale_id', "shift='$shift' AND counter='$counter'"); ?></b></td>
              <td><b><?php echo thermal_money($userSalesTotal); ?></b></td>
              <td><b><?php echo thermal_money($userTokenTotal); ?></b></td>
            </tr>
            <?php
        }
        ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b><?php echo $counterName; ?><br>RETURNED ITEMS BY USER</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <?php
        $delete = mysqli_query($connection, "SELECT user_id, item_id, confirm_by, confirm_status, SUM(qty) AS qty FROM kp_ret_items WHERE shift='$shift' AND stock_counter='$counter' GROUP BY user_id, item_id, confirm_by, confirm_status ORDER BY user_id ASC, confirm_status ASC, item_id ASC") or die('Error fetching the staff data.:' . mysqli_error($connection));
        if (mysqli_num_rows($delete) < 1) {
            echo "<tr><td colspan='4'>No orders have been Returned Today</td></tr>";
        } else {
            ?>
            <tr>
              <td class="text-center"><b>ITEM</b></td>
              <td class="text-center"><b>QTY</b></td>
              <td class="text-center"><b>STATUS</b></td>
              <td class="text-center"><b>APPR BY</b></td>
            </tr>
            <?php
            $lastReturnUser = null;
            while ($row = mysqli_fetch_assoc($delete)) {
                $returnUserId = (int)($row['user_id'] ?? 0);
                if ($lastReturnUser !== $returnUserId) {
                    echo '<tr class="category-heading"><td colspan="4">USER: ' . htmlspecialchars(get_user($returnUserId)) . '</td></tr>';
                    $lastReturnUser = $returnUserId;
                }
                $confirmStatus = (int)($row['confirm_status'] ?? 0);
                $returnStatus = $confirmStatus === 1 ? 'APPROVED' : ($confirmStatus === 3 ? 'REJECTED' : 'PENDING');
                ?>
                <tr>
                  <td><?php echo htmlspecialchars(get_item((int)$row['item_id'])); ?></td>
                  <td><?php echo thermal_money($row['qty']); ?></td>
                  <td><?php echo $returnStatus; ?></td>
                  <td><?php echo htmlspecialchars(get_user((int)($row['confirm_by'] ?? 0))); ?></td>
                </tr>
                <?php
            }
        }
        ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>STOCK IN<br>STOCK TRANSFER</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <?php if (empty($transferRows)) { ?>
          <tr><td colspan="4">No transfers for this counter in this shift.</td></tr>
        <?php } else { ?>
          <tr>
            <td class="text-center"><b>ITEM</b></td>
            <td class="text-center"><b>QTY</b></td>
            <td class="text-center"><b>COUNTER</b></td>
            <td class="text-center"><b>USER</b></td>
          </tr>
          <?php $lastTransferDirection = null; $lastTransferCategory = null; foreach ($transferRows as $transferRow) {
            $transferDirection = ((int)$transferRow['cntr_to'] === $counter) ? 'STOCK IN' : 'STOCK OUT';
            if ($transferDirection !== 'STOCK IN') { continue; }
            $transferCategory = (int)get_column('kp_items', 'cat_id', "item_id='" . (int)$transferRow['item_id'] . "'");
            if ($lastTransferDirection !== $transferDirection) { echo '<tr class="category-heading"><td colspan="4">' . $transferDirection . '</td></tr>'; $lastTransferDirection = $transferDirection; $lastTransferCategory = null; }
            if ($lastTransferCategory !== $transferCategory) { echo '<tr class="category-heading"><td colspan="4">' . htmlspecialchars($transferCategory > 0 ? get_cat($transferCategory) : 'UNCATEGORISED') . '</td></tr>'; $lastTransferCategory = $transferCategory; }
          ?>
            <tr>
              <td><?php echo htmlspecialchars(get_item($transferRow['item_id'])); ?></td>
              <td><?php echo thermal_money($transferRow['qty']); ?></td>
              <td><?php echo htmlspecialchars(get_counter($transferRow['cntr_from'])); ?></td>
              <td><?php echo htmlspecialchars(get_user($transferRow['user_from'])); ?></td>
            </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b><?php echo $counterName; ?> STOCK OUT<br>STAFF SUMMARY</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <?php
        $tt = 0;
        $purchase = mysqli_query($connection, "SELECT SUM(total) as stotal,user_id FROM kp_sale_items WHERE shift='$shift' AND counter='$counter' GROUP BY user_id ORDER BY stotal DESC") or die('Error fetching the purchases.:' . mysqli_error($connection));
        if (mysqli_num_rows($purchase) < 1) {
            echo "<tr><td colspan='4'>No Tokes Available today</td></tr>";
        } else {
            ?>
            <tr>
              <td class="text-center"><b>STAFF</b></td>
              <td class="text-center"><b>BAR</b></td>
              <td class="text-center"><b>KITCHEN</b></td>
              <td class="text-center"><b>AMOUNT</b></td>
            </tr>
            <?php
            while ($pow = mysqli_fetch_assoc($purchase)) {
                $user_id = $pow['user_id'];
                $sales = $pow['stotal'];
                $bar_amount = column_total('kp_sale_items', 'total', "shift='$shift' AND stock_counter='$counter' AND user_id='$user_id' AND dpt=1");
                $kitchen_amount = column_total('kp_sale_items', 'total', "shift='$shift' AND stock_counter='$counter' AND user_id='$user_id' AND dpt=2");
                $tt += $sales;
                ?>
                <tr>
                  <td><b><?php echo get_user($user_id); ?></b></td>
                  <td><b><?php echo $bar_amount; ?></b></td>
                  <td><b><?php echo $kitchen_amount; ?></b></td>
                  <td><b><?php echo $sales; ?></b></td>
                </tr>
                <?php
            }
            ?>
            <tr>
              <td colspan="3" class="text-center"><b>TOTAL</b></td>
              <td><b><?php echo $tt; ?></b></td>
            </tr>
            <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
