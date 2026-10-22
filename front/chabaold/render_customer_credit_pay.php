<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
require_once(__DIR__ . '/render_css.php');

$creditPayId = (int)($_GET['credit_pay_id'] ?? $_GET['id'] ?? 0);
if ($creditPayId < 1) {
    exit('Invalid credit payment');
}

$paymentQ = mysqli_query($connection, "SELECT * FROM kp_customer_credit_payments WHERE credit_pay_id='$creditPayId' LIMIT 1");
if (!$paymentQ || mysqli_num_rows($paymentQ) < 1) {
    exit('Credit payment not found');
}
$payment = mysqli_fetch_assoc($paymentQ);
$custId = (int)($payment['cust_id'] ?? 0);
$userId = (int)($payment['user_id'] ?? 0);
$amount = (float)($payment['amount'] ?? 0);
$shiftDate = trim((string)($payment['shift_date'] ?? ''));
$note = trim((string)($payment['note'] ?? ''));

$gen = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_gen WHERE id=1"));
$spr = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1"));
$print_template = kp_render_print_template_id();
$thermal_logo = !empty($gen['thermal_logo_path']) ? $gen['thermal_logo_path'] : ($gen['image_path'] ?? '');
$thermal_logo_height = isset($spr['thermal_logo_height']) ? (int)$spr['thermal_logo_height'] : 60;
if ($thermal_logo_height < 30) { $thermal_logo_height = 30; }
if ($thermal_logo_height > 180) { $thermal_logo_height = 180; }

$modes = [];
$modeQ = mysqli_query($connection, "SELECT pay_mode_id, amount, reference_no FROM kp_customer_credit_payment_modes WHERE credit_pay_id='$creditPayId' ORDER BY credit_mode_id ASC");
while ($modeQ && ($mode = mysqli_fetch_assoc($modeQ))) {
    $modes[] = $mode;
}

$items = [];
$itemQ = mysqli_query($connection, "SELECT sale_id, ord_date, pay_date, amount FROM kp_customer_credit_payment_items WHERE credit_pay_id='$creditPayId' ORDER BY credit_item_id ASC");
while ($itemQ && ($item = mysqli_fetch_assoc($itemQ))) {
    $items[] = $item;
}

date_default_timezone_set('Africa/Nairobi');
$printTime = date('d-M-Y h:i:a');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Credit Payment Voucher #<?php echo $creditPayId; ?></title>
  <style>
    <?php echo kp_render_print_template_css($print_template); ?>
    @page { size: 80mm auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      width: 80mm;
      background: #fff;
      color: #000;
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
    }
    #receipt {
      width: 76mm;
      max-width: 76mm;
      margin: 0 auto;
      padding: 2mm 1.5mm;
      background: #fff;
    }
    .text-center { text-align: center; }
    .title { font-size: 18px; margin: 4px 0 2px; letter-spacing: 1px; }
    .sep { border: none; border-top: 1px dashed #555; margin: 4px 0; }
    .sep-strong { border: none; border-top: 2px solid #000; margin: 4px 0; }
    .meta { display: flex; justify-content: space-between; gap: 8px; margin: 2px 0; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 3px 0; }
    th, td { padding: 2px 1px; font-size: 11px; vertical-align: top; }
    th { border-top: 1px solid #000; border-bottom: 1px solid #000; text-transform: uppercase; }
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { text-align: right; }
    .section-label { font-weight: 900; text-transform: uppercase; margin-top: 5px; }
    .grand { font-size: 15px; font-weight: 900; }
    @media print {
      body { margin: 0 !important; padding: 0 !important; width: 80mm !important; }
      #receipt { width: 76mm !important; max-width: 76mm !important; padding: 2mm 1.5mm !important; }
    }
  </style>
</head>
<body>
<div id="receipt">
  <div class="text-center">
    <?php if (!empty($spr['show_logo']) && (int)$spr['show_logo'] === 1 && !empty($thermal_logo)): ?>
      <img src="<?php echo htmlspecialchars($thermal_logo); ?>" style="height:<?php echo $thermal_logo_height; ?>px;width:auto;max-width:100%;"><br>
    <?php endif; ?>
    <div class="title"><?php echo strtoupper(htmlspecialchars($gen['company_name'] ?? '')); ?></div>
    <div><strong>CUSTOMER CREDIT PAYMENT</strong></div>
    <hr class="sep-strong">
  </div>

  <div class="meta"><span>Voucher No</span><span><strong>#<?php echo $creditPayId; ?></strong></span></div>
  <div class="meta"><span>Customer</span><span><strong><?php echo htmlspecialchars(get_cust($custId)); ?></strong></span></div>
  <div class="meta"><span>Cashier</span><span><strong><?php echo htmlspecialchars(get_user($userId)); ?></strong></span></div>
  <div class="meta"><span>Shift Date</span><span><strong><?php echo htmlspecialchars($shiftDate); ?></strong></span></div>
  <div class="meta"><span>Print Time</span><span><strong><?php echo htmlspecialchars($printTime); ?></strong></span></div>

  <hr class="sep-strong">
  <div class="section-label">Payment Modes</div>
  <table>
    <thead>
      <tr><th>Mode</th><th>Amount</th></tr>
    </thead>
    <tbody>
      <?php foreach ($modes as $mode): ?>
      <tr>
        <td>
          <?php echo htmlspecialchars(get_pay_name((int)$mode['pay_mode_id'])); ?>
          <?php if (trim((string)$mode['reference_no']) !== ''): ?>
            <div style="font-size:10px;">Ref: <?php echo htmlspecialchars($mode['reference_no']); ?></div>
          <?php endif; ?>
        </td>
        <td><?php echo number_format((float)$mode['amount'], 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="section-label">Invoices Cleared</div>
  <table>
    <thead>
      <tr><th>Sale</th><th>Amount</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr>
        <td>
          #<?php echo (int)$item['sale_id']; ?>
          <div style="font-size:10px;">Ord: <?php echo htmlspecialchars((string)$item['ord_date']); ?></div>
        </td>
        <td><?php echo number_format((float)$item['amount'], 2); ?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td class="grand">TOTAL PAID</td>
        <td class="grand"><?php echo number_format($amount, 2); ?></td>
      </tr>
    </tbody>
  </table>

  <?php if ($note !== ''): ?>
    <hr class="sep">
    <div class="section-label">Note</div>
    <div><?php echo nl2br(htmlspecialchars($note)); ?></div>
  <?php endif; ?>

  <hr class="sep-strong">
  <div class="text-center">Thank you</div>
</div>
</body>
</html>
