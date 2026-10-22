<?php
include('../../inc/connection.php');
include('funcs.php');

$shift = mysqli_real_escape_string($connection, $_GET['shift'] ?? '');
$counter = mysqli_real_escape_string($connection, $_GET['counter'] ?? '');
$counterName = get_counter($counter);
$table = "kp_cntr" . $counter . "_stock_take";
$sales = mysqli_query($connection, "SELECT * FROM $table WHERE shift='$shift'");

if (!$sales || mysqli_num_rows($sales) < 1) {
  ?>
  <div style="padding:24px;background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.08);">
    <h3 style="margin-top:0;">Counter Take Report</h3>
    <p style="margin-bottom:0;">Stock was not taken for this shift.</p>
  </div>
  <?php
  return;
}

$rows = array();
$no = 0;
$salesTotal = 0;
$profitTotal = 0;
$buyingValueTotal = 0;
$varianceNetTotal = 0;
$varianceAbsTotal = 0;
$okCount = 0;
$varianceCount = 0;
$pendingCount = 0;

while ($sa = mysqli_fetch_assoc($sales)) {
  $take_id = (int)$sa['take_id'];
  $item_id = (int)$sa['item_id'];
  $opening = (float)$sa['opening'];
  $counted = (float)$sa['counted'];
  $closing = (float)$sa['closing'];
  $status = (int)$sa['status'];
  $variance = $counted - $opening;

  if ($variance == 0 && $status == 2) {
    $color = '#dff6e5';
    $statusLabel = 'OK';
    $okCount++;
  } elseif ($variance != 0 && $status == 2) {
    $color = '#ffe0e0';
    $statusLabel = 'Variance';
    $varianceCount++;
  } elseif ($variance != 0 && $status == 3) {
    $color = '#fff2d8';
    $statusLabel = 'Pending';
    $pendingCount++;
  } else {
    $color = '#f7f9fc';
    $statusLabel = ($variance == 0) ? 'OK' : 'Variance';
    if ($variance == 0) {
      $okCount++;
    } else {
      $varianceCount++;
    }
  }

  $total_out = mysqli_query($connection, "SELECT SUM(qty) AS out_qty FROM kp_stock_track WHERE status='OUT' AND item_id='$item_id' AND shift='$shift'") or die("Could not fetch the quantity out");
  $rowout = mysqli_fetch_assoc($total_out);
  $totalout = (float)($rowout['out_qty'] ?? 0);

  $total_in = mysqli_query($connection, "SELECT SUM(qty) AS in_qty FROM kp_stock_track WHERE status='IN' AND item_id='$item_id' AND shift='$shift'") or die("Could not fetch the total quantity in");
  $rowin = mysqli_fetch_assoc($total_in);
  $totalin = (float)($rowin['in_qty'] ?? 0);

  $stock_total = $counted + $totalin;
  $price = (float)get_column('kp_items', 'price', "item_id='$item_id'");

  $get_item = mysqli_query($connection, "SELECT * FROM kp_items WHERE item_id='$item_id'");
  $iow = mysqli_fetch_assoc($get_item);
  $buyPrice = (float)($iow['b_price'] ?? 0);
  $stock = (float)($iow['qty'] ?? 0);
  $lineSales = $totalout * $price;
  $lineProfit = $lineSales - ($totalout * $buyPrice);

  $salesTotal += $lineSales;
  $profitTotal += $lineProfit;
  $buyingValueTotal += ($buyPrice * $stock);
  $varianceNetTotal += $variance;
  $varianceAbsTotal += abs($variance);

  $rows[] = array(
    'no' => ++$no,
    'take_id' => $take_id,
    'item_name' => get_item($item_id),
    'opening' => $opening,
    'counted' => $counted,
    'variance' => $variance,
    'issues' => $totalin,
    'total' => $stock_total,
    'closing' => $closing,
    'total_out' => $totalout,
    'price' => $price,
    'sales' => $lineSales,
    'status_label' => $statusLabel,
    'row_color' => $color,
  );
}

$itemCount = count($rows);
?>
<style>
  .ctr-report {
    background: #f4f7fb;
    color: #16202a;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 18px 48px rgba(15, 23, 42, 0.12);
  }
  .ctr-report-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
  }
  .ctr-report-title {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #14324a;
    letter-spacing: .02em;
  }
  .ctr-report-meta {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 13px;
  }
  .ctr-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    background: #e6eef8;
    color: #21415d;
    border-radius: 999px;
    font-weight: 700;
  }
  .ctr-print-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    background: #1f8f5f;
    color: #fff !important;
    font-weight: 700;
    text-decoration: none !important;
  }
  .ctr-summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
  }
  .ctr-summary-card {
    padding: 14px 16px;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border: 1px solid #e7edf5;
  }
  .ctr-summary-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #60758c;
    margin-bottom: 6px;
  }
  .ctr-summary-value {
    display: block;
    font-size: 26px;
    font-weight: 900;
    color: #14293d;
    line-height: 1.1;
  }
  .ctr-summary-note {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #6a7c8f;
  }
  .ctr-summary-card.ok-card .ctr-summary-value { color: #1f8f5f; }
  .ctr-summary-card.variance-card .ctr-summary-value { color: #c74634; }
  .ctr-summary-card.pending-card .ctr-summary-value { color: #d1821b; }
  .ctr-summary-card.sales-card .ctr-summary-value { color: #184d8a; font-size: 22px; }
  .ctr-summary-card.net-card .ctr-summary-value { color: #4e2ea8; font-size: 22px; }
  .ctr-table-wrap {
    background: #fff;
    border-radius: 16px;
    padding: 12px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    overflow-x: auto;
  }
  .ctr-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }
  .ctr-table th,
  .ctr-table td {
    border: 1px solid #dbe5ef;
    padding: 8px 7px;
    font-size: 12px;
    vertical-align: middle !important;
  }
  .ctr-table thead th {
    background: #17324d;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .ctr-table td:nth-child(2) {
    font-weight: 700;
  }
  .ctr-table td:not(:nth-child(2)),
  .ctr-table th:not(:nth-child(2)) {
    text-align: right;
  }
  .ctr-table td:nth-child(1),
  .ctr-table th:nth-child(1) {
    width: 48px;
    text-align: center;
  }
  .ctr-table td:nth-child(2),
  .ctr-table th:nth-child(2) {
    width: 240px;
    text-align: left;
  }
  .ctr-table tfoot td {
    font-weight: 800;
    background: #eef4fa;
  }
  .ctr-status-badge {
    display: inline-block;
    min-width: 70px;
    padding: 5px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    text-align: center;
    text-transform: uppercase;
  }
  .ctr-status-ok { background: #d9f4e3; color: #13643f; }
  .ctr-status-variance { background: #ffe1de; color: #9f281f; }
  .ctr-status-pending { background: #fff0d1; color: #a56409; }
  @media (max-width: 1100px) {
    .ctr-summary-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media print {
    @page {
      size: A4 landscape;
      margin: 9mm;
    }
    html, body {
      background: #fff !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    .ctr-report {
      background: #fff !important;
      box-shadow: none !important;
      border-radius: 0 !important;
      padding: 0 !important;
    }
    .hidden-print {
      display: none !important;
    }
    .ctr-summary-grid {
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 8px;
      margin-bottom: 12px;
    }
    .ctr-summary-card,
    .ctr-table-wrap {
      box-shadow: none !important;
      border: 1px solid #d0d8e2;
    }
    .ctr-table-wrap {
      padding: 0;
      overflow: visible !important;
    }
    .ctr-table {
      table-layout: auto;
    }
    .ctr-table th,
    .ctr-table td {
      font-size: 10px;
      padding: 5px 4px;
      color: #000 !important;
    }
    .ctr-table thead th {
      background: #e8edf2 !important;
      color: #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .ctr-table tr {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .ctr-summary-card,
    .ctr-status-badge {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
  }
</style>

<div class="ctr-report">
  <div class="ctr-report-header">
    <div>
      <h3 class="ctr-report-title"><?php echo htmlspecialchars($counterName); ?> Counter Take Report</h3>
      <div class="ctr-report-meta">
        <span class="ctr-meta-pill">Counter: <?php echo htmlspecialchars($counterName); ?></span>
        <span class="ctr-meta-pill">Shift: <?php echo htmlspecialchars($shift); ?></span>
        <span class="ctr-meta-pill">Items: <?php echo number_format($itemCount); ?></span>
      </div>
    </div>
    <div class="hidden-print">
      <a href="javascript:;" onclick="window.print()" class="ctr-print-btn"><i class="fa fa-print"></i> Print A4 Report</a>
    </div>
  </div>

  <div class="ctr-summary-grid">
    <div class="ctr-summary-card ok-card">
      <span class="ctr-summary-label">OK Items</span>
      <span class="ctr-summary-value"><?php echo number_format($okCount); ?></span>
      <span class="ctr-summary-note">Items balanced correctly</span>
    </div>
    <div class="ctr-summary-card variance-card">
      <span class="ctr-summary-label">Variance Items</span>
      <span class="ctr-summary-value"><?php echo number_format($varianceCount); ?></span>
      <span class="ctr-summary-note">Items with non-zero variance</span>
    </div>
    <div class="ctr-summary-card pending-card">
      <span class="ctr-summary-label">Pending Review</span>
      <span class="ctr-summary-value"><?php echo number_format($pendingCount); ?></span>
      <span class="ctr-summary-note">Variance rows still pending</span>
    </div>
    <div class="ctr-summary-card sales-card">
      <span class="ctr-summary-label">Sales Total</span>
      <span class="ctr-summary-value"><?php echo number_format($salesTotal, 2); ?></span>
      <span class="ctr-summary-note">Total sales from this take</span>
    </div>
    <div class="ctr-summary-card net-card">
      <span class="ctr-summary-label">Variance Totals</span>
      <span class="ctr-summary-value"><?php echo number_format($varianceNetTotal, 2); ?></span>
      <span class="ctr-summary-note">Absolute: <?php echo number_format($varianceAbsTotal, 2); ?></span>
    </div>
  </div>

  <div class="ctr-table-wrap">
    <table class="ctr-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Item</th>
          <th>Prev Closing</th>
          <th>Counted</th>
          <th>Variance</th>
          <th>Issues</th>
          <th>Total</th>
          <th>Closing</th>
          <th>Total Out</th>
          <th>Sell Price</th>
          <th>Sales</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <?php
          $badgeClass = 'ctr-status-variance';
          if ($row['status_label'] === 'OK') {
            $badgeClass = 'ctr-status-ok';
          } elseif ($row['status_label'] === 'Pending') {
            $badgeClass = 'ctr-status-pending';
          }
          ?>
          <tr style="background-color: <?php echo htmlspecialchars($row['row_color']); ?>;">
            <td><?php echo number_format($row['no']); ?></td>
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td><?php echo number_format($row['opening'], 2); ?></td>
            <td><?php echo number_format($row['counted'], 2); ?></td>
            <td><?php echo number_format($row['variance'], 2); ?></td>
            <td><?php echo number_format($row['issues'], 2); ?></td>
            <td><?php echo number_format($row['total'], 2); ?></td>
            <td><?php echo number_format($row['closing'], 2); ?></td>
            <td><?php echo number_format($row['total_out'], 2); ?></td>
            <td><?php echo number_format($row['price'], 2); ?></td>
            <td><?php echo number_format($row['sales'], 2); ?></td>
            <td style="text-align:center;">
              <span class="ctr-status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['status_label']); ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="10" style="text-align:right;">Sales Total</td>
          <td><?php echo number_format($salesTotal, 2); ?></td>
          <td style="text-align:center;">&nbsp;</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
