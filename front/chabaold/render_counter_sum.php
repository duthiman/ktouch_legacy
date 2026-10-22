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

$nos = 50;
$today = $shift;
$counterName = get_counter($counter);
$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);
$stockTakeSummary = function_exists('kp_counter_stock_take_snapshot_summary')
    ? kp_counter_stock_take_snapshot_summary($connection, $counter, $shift, $summary_id)
    : kp_counter_stock_take_summary($connection, $counter, $shift);
$transferRows = kp_counter_transfer_rows($connection, $counter, $shift);
$kpesaTotals = kp_counter_kpesa_totals($connection, $counter, $shift);
require_once(__DIR__ . '/render_css.php');
$print_template = kp_render_print_template_id();
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
    <?php echo kp_render_print_template_css($print_template); ?>
    body, #receipt, table, #receipt-header, #receipt-body, #receipt-footer {
      background: #fff !important;
    }
    .receipt-divider {
      margin: 0;
      line-height: 1.1;
      white-space: nowrap;
      overflow: hidden;
      text-align: center;
      font-weight: 700;
      letter-spacing: .06em;
    }
    .status-pill {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .04em;
    }
    .status-pill.ok {
      background: #e6f6ea;
      color: #0d6b2f;
    }
    .status-pill.bad {
      background: #fde9e9;
      color: #9c1f1f;
    }
    .status-pill.neutral {
      background: #f1f1f1;
      color: #444;
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
          <td><b>OPENING:</b></td>
          <td><?php echo get_user(get_column('kp_counter_hist', 'open_by', "shift='$shift'")); ?></td>
          <td colspan="2">
            <?php
            $new = date('l h:i:sa d-m-Y', strtotime(get_column('kp_counter_hist', 'opening', "shift='$shift'")));
            echo $new;
            ?>
          </td>
        </tr>
        <tr>
          <td><b>CLOSING:</b></td>
          <td><?php echo get_user(get_column('kp_counter_hist', 'close_by', "shift='$shift'")); ?></td>
          <td colspan="2"><?php today(); ?></td>
        </tr>
        <tr>
          <td colspan="4"><b>ORDER NUMBER</b></td>
        </tr>
        <tr>
          <td colspan="2"><b>START NUMBER</b></td>
          <td colspan="2"><?php echo get_column('kp_counter_hist', 'open_ord', "shift='$shift'"); ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>END NUMBER</b></td>
          <td colspan="2"><?php echo get_column('kp_sale_id', 'sale_id', "1") - 1; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>TODAY REQUESTS</b></td>
          <td colspan="2"><?php echo $today_requests_total; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>RETURNS ACCEPTED</b></td>
          <td colspan="2"><?php echo $today_returns_accepted_total; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>RETURNS REJECTED</b></td>
          <td colspan="2"><?php echo $today_returns_rejected_total; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>VOIDED ITEMS</b></td>
          <td colspan="2"><?php echo $today_voided_items_total; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>ITEMS SOLD</b></td>
          <td colspan="2"><?php echo rtrim(rtrim(number_format($today_items_sold_total, 2, '.', ''), '0'), '.'); ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>TRANSFER TO COUNTER</b></td>
          <td colspan="2"><?php echo $today_transfer_to_total; ?></td>
        </tr>
        <tr>
          <td colspan="2"><b>TRANSFER FROM COUNTER</b></td>
          <td colspan="2"><?php echo $today_transfer_from_total; ?></td>
        </tr>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>STOCK TAKE STATUS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <tr>
          <td><b>STATUS</b></td>
          <td colspan="3">
            <?php
            $stockStatusClass = 'neutral';
            if ($stockTakeSummary['status'] === 'OK') {
                $stockStatusClass = 'ok';
            } elseif ($stockTakeSummary['status'] === 'VARIANCE') {
                $stockStatusClass = 'bad';
            }
            ?>
            <span class="status-pill <?php echo $stockStatusClass; ?>"><?php echo htmlspecialchars($stockTakeSummary['status']); ?></span>
          </td>
        </tr>
        <tr>
          <td><b>OK ITEMS</b></td>
          <td><?php echo (int)$stockTakeSummary['ok_count']; ?></td>
          <td><b>VARIANCE ITEMS</b></td>
          <td><?php echo (int)$stockTakeSummary['variance_count']; ?></td>
        </tr>
        <tr>
          <td><b>VARIANCE QTY</b></td>
          <td><?php echo thermal_money($stockTakeSummary['variance_qty']); ?></td>
          <td><b>TAKEN</b></td>
          <td><?php echo $stockTakeSummary['taken'] ? 'YES' : 'NO'; ?></td>
        </tr>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b><?php echo $counterName; ?> STOCK SALES SUMMARY BY DEPARTMENT</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>

    <table class="table">
      <tbody>
        <tr>
          <td class="text-center"><b>DEPARTMENT</b></td>
          <td class="text-center"><b>SALES</b></td>
        </tr>
        <tr>
          <td>BAR:</td>
          <td><?php $drinks = column_total('kp_sale_items', 'total', "dpt=1 AND shift='$shift' AND stock_counter='$counter'"); echo $drinks; ?></td>
        </tr>
        <tr>
          <td>KITCHEN:</td>
          <td><?php $food = column_total('kp_sale_items', 'total', "dpt=2 AND shift='$shift' AND stock_counter='$counter'"); echo $food; ?></td>
        </tr>
        <tr>
          <td><b>TOTAL</b></td>
          <td><b><?php echo $food + $drinks; ?></b></td>
        </tr>
        <tr>
          <td><b>CORRECTION</b></td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>PAYMENT SUMMARY</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table table-bordered">
      <thead>
        <tr class="text-center">
          <th colspan="3"><b><u><?php echo htmlspecialchars($today); ?> CASH BOX SUMMARY</u></b></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sum = 0;
        $get_mode_r = mysqli_query($connection, "SELECT * FROM kp_pay_mode") or die("Could not fetch the payment mode");
        while ($o = mysqli_fetch_assoc($get_mode_r)) {
            $mode_id = $o['pay_id'];
            $name = $o['name'];
            $col = "mode" . $mode_id;
            $mode_total = column_total('kp_sale_payment', $col, "pay_date='$shift' AND counter='$counter'");
            $sum += $mode_total;
            ?>
            <tr>
              <td><?php echo $mode_id; ?></td>
              <td><?php echo $name; ?></td>
              <td><?php echo $mode_total; ?></td>
            </tr>
            <?php
        }
        ?>
        <tr>
          <td colspan="2" class="text-center"><b>TOTAL</b></td>
          <td><b><?php echo $sum; ?></b></td>
        </tr>
      </tbody>
    </table>

    <?php if ($kpesaTotals['enabled']) { ?>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b>K-PESA TOTALS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <tr>
          <td><b>M-PESA</b></td>
          <td class="text-right"><b><?php echo thermal_money($kpesaTotals['mpesa_total']); ?></b></td>
        </tr>
        <?php foreach ($kpesaTotals['mpesa_modes'] as $modeRow) { ?>
          <tr>
            <td><?php echo htmlspecialchars($modeRow['name']); ?></td>
            <td class="text-right"><?php echo thermal_money($modeRow['amount']); ?></td>
          </tr>
        <?php } ?>
        <tr>
          <td><b>CARD / PDQ</b></td>
          <td class="text-right"><b><?php echo thermal_money($kpesaTotals['card_total']); ?></b></td>
        </tr>
        <?php foreach ($kpesaTotals['card_modes'] as $modeRow) { ?>
          <tr>
            <td><?php echo htmlspecialchars($modeRow['name']); ?></td>
            <td class="text-right"><?php echo thermal_money($modeRow['amount']); ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } ?>

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
    <table class="table">
      <tbody>
        <?php
        $delete = mysqli_query($connection, "SELECT * FROM kp_del_orders WHERE shift='$shift'") or die('Error fetching the staff data.:' . mysqli_error($connection));
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
    <h4 style="margin:0;text-align:center;"><b><?php echo $counterName; ?><br>RETURNED ITEMS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <?php
        $delete = mysqli_query($connection, "SELECT * FROM kp_ret_items WHERE shift='$shift' AND stock_counter='$counter'") or die('Error fetching the staff data.:' . mysqli_error($connection));
        if (mysqli_num_rows($delete) < 1) {
            echo "<tr><td colspan='6'>No orders have been Returned Today</td></tr>";
        } else {
            ?>
            <tr>
              <td class="text-center"><b>Id</b></td>
              <td class="text-center"><b>Item</b></td>
              <td class="text-center"><b>Ord By</b></td>
              <td class="text-center"><b>App By</b></td>
              <td class="text-center"><b>Qty</b></td>
              <td class="text-center"><b>AMT</b></td>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc($delete)) {
                ?>
                <tr>
                  <td><?php echo $row['sale_id']; ?></td>
                  <td><?php echo get_item($row['item_id']); ?></td>
                  <td><?php echo get_user($row['user_id']); ?></td>
                  <td><?php echo get_user($row['confirm_by']); ?></td>
                  <td><?php echo $row['qty']; ?></td>
                  <td><?php echo $row['amount']; ?></td>
                </tr>
                <?php
            }
        }
        ?>
      </tbody>
    </table>

    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <h4 style="margin:0;text-align:center;"><b><?php echo $counterName; ?><br>STOCK TRANSFERS</b></h4>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <table class="table">
      <tbody>
        <?php if (empty($transferRows)) { ?>
          <tr><td colspan="4">No transfers for this counter in this shift.</td></tr>
        <?php } else { ?>
          <tr>
            <td class="text-center"><b>ITEM</b></td>
            <td class="text-center"><b>QTY</b></td>
            <td class="text-center"><b>FROM</b></td>
            <td class="text-center"><b>TO</b></td>
          </tr>
          <?php foreach ($transferRows as $transferRow) { ?>
            <tr>
              <td><?php echo htmlspecialchars(get_item($transferRow['item_id'])); ?></td>
              <td><?php echo thermal_money($transferRow['qty']); ?></td>
              <td><?php echo htmlspecialchars(get_counter($transferRow['cntr_from'])); ?><br><small><?php echo htmlspecialchars(get_user($transferRow['user_from'])); ?></small></td>
              <td><?php echo htmlspecialchars(get_counter($transferRow['cntr_to'])); ?><br><small><?php echo htmlspecialchars(get_user($transferRow['user_to'])); ?></small></td>
            </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>

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
