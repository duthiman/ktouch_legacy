<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
include_once('funcs.php');

if (!function_exists('kp_bill_embed_image_src')) {
  function kp_bill_embed_image_src($src) {
    $src = trim((string)$src);
    if ($src === '' || stripos($src, 'data:') === 0) {
      return $src;
    }

    $remoteSrc = $src;
    $urlBits = @parse_url($src);
    if (is_array($urlBits) && !empty($urlBits['path'])) {
      $src = (string)$urlBits['path'];
    }

    $roots = array(
      realpath(__DIR__),
      realpath(__DIR__ . '/../../'),
      isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false
    );

    $candidates = array();
    $normalized = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, ltrim($src, '/\\'));
    foreach ($roots as $root) {
      if (!$root) {
        continue;
      }
      $directCandidate = realpath($root . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $src));
      if ($directCandidate) {
        $candidates[] = $directCandidate;
      }
      $candidates[] = $root . DIRECTORY_SEPARATOR . $normalized;
      if ($src !== $normalized) {
        $candidates[] = $root . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $src);
      }
    }

    foreach (array_unique($candidates) as $candidate) {
      if (!is_file($candidate) || !is_readable($candidate)) {
        continue;
      }
      $mime = function_exists('mime_content_type') ? mime_content_type($candidate) : '';
      if (strpos((string)$mime, 'image/') !== 0) {
        $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        $mimeMap = array(
          'png' => 'image/png',
          'jpg' => 'image/jpeg',
          'jpeg' => 'image/jpeg',
          'gif' => 'image/gif',
          'webp' => 'image/webp',
          'bmp' => 'image/bmp',
          'svg' => 'image/svg+xml'
        );
        $mime = $mimeMap[$ext] ?? 'application/octet-stream';
      }
      $data = @file_get_contents($candidate);
      if ($data === false) {
        continue;
      }
      return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    if (preg_match('/^https?:\/\//i', $remoteSrc)) {
      $remoteData = @file_get_contents($remoteSrc);
      if ($remoteData !== false) {
        return 'data:image/png;base64,' . base64_encode($remoteData);
      }
    }

    return $src;
  }
}

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$sale_id = (int)($_GET['sale_id'] ?? 0);
if ($sale_id < 1) { exit('Invalid sale'); }

// Chaba queue printing reads from the saved sale row in `kp_sales`.
// By the time this page renders, the live front-session row may already be cleared,
// so continued-bill detection must come from persisted sale fields / open-bill tables.
$sale_r = mysqli_query($connection, "SELECT * FROM kp_sales WHERE sale_id='$sale_id'");
$sale = ($sale_r && mysqli_num_rows($sale_r) > 0) ? mysqli_fetch_assoc($sale_r) : null;
if (!$sale) {
  $fallback_user = (int)get_column('kp_sale_items', 'user_id', "sale_id='$sale_id'");
  if ($fallback_user < 1) {
    $fallback_user = (int)get_column('kp_sale_print', 'user_id', "sale_id='$sale_id'");
  }
  $fallback_shift = (string)get_column('kp_sale_items', 'shift', "sale_id='$sale_id'");
  if ($fallback_shift === '' || $fallback_shift === '0') {
    $fallback_shift = (string)get_column('kp_sale_print', 'shift', "sale_id='$sale_id'");
  }
  $fallback_counter = (int)get_column('kp_sale_items', 'counter', "sale_id='$sale_id'");
  if ($fallback_counter < 1) {
    $fallback_counter = (int)get_column('kp_sale_print', 'counter', "sale_id='$sale_id'");
  }
  $fallback_amount = (float)column_total('kp_sale_items', 'total', "sale_id='$sale_id'");
  if ($fallback_amount <= 0) {
    $fallback_amount = (float)column_total('kp_sale_print', 'total', "sale_id='$sale_id'");
  }
  if ($fallback_user < 1 && $fallback_amount <= 0) {
    exit('Sale not found');
  }
  $sale = [
    'sale_id' => $sale_id,
    'user_id' => $fallback_user,
    'shift' => $fallback_shift,
    'amount' => $fallback_amount,
    'counter' => $fallback_counter,
    'parent' => 0,
    'parent_sale' => 0,
    'print_cntr_id' => 0,
  ];
}
$sale     = $sale;
$user_id  = $sale['user_id'];
$shift    = $sale['shift'];
$amount   = (float)$sale['amount'];
$bill_context = function_exists('kp_chaba_bill_context')
  ? kp_chaba_bill_context($connection, $sale_id, $sale, '')
  : [
      'parent_bill' => (int)($sale['parent'] ?? 0),
      'has_parent_sale_col' => false,
      'has_sales_open_table' => false,
      'open_state' => '',
      'closed_parent_final' => false,
      'open_display_bill_no' => 0,
    ];
$parent_bill = (int)$bill_context['parent_bill'];
$has_parent_sale_col = !empty($bill_context['has_parent_sale_col']);
$has_sales_open_table = !empty($bill_context['has_sales_open_table']);
$vat      = round((16/116) * $amount, 2, PHP_ROUND_HALF_UP);
$ctl      = round((2/102)  * $amount, 2, PHP_ROUND_HALF_UP);
$open_state = (string)$bill_context['open_state'];
$open_bill_number = 0;
$open_previous_total = 0.0;
$open_current_total = 0.0;
$open_combined_total = 0.0;
$open_display_previous = 0.0;
$open_display_bill_no = (int)$bill_context['open_display_bill_no'];
$closed_parent_final = !empty($bill_context['closed_parent_final']);
$is_continuation_child = ($parent_bill > 0 && !$closed_parent_final && $sale_id !== $parent_bill);
if ($is_continuation_child) {
  // For continued bills, "previous total" is the parent bill plus all earlier children.
  $open_current_total = $amount;
  $open_previous_total = (float)get_column('kp_sales', 'amount', "sale_id='$parent_bill'");
  $child_parent_where = "parent='$parent_bill'";
  if ($has_parent_sale_col) {
    $child_parent_where = "(parent='$parent_bill' OR parent_sale='$parent_bill')";
  }
  $open_previous_children = mysqli_query($connection, "SELECT SUM(amount) AS prev_total, COUNT(sale_id) AS prev_count FROM kp_sales WHERE $child_parent_where AND sale_id!='$sale_id'");
  if ($open_previous_children) {
    $prev_row = mysqli_fetch_assoc($open_previous_children);
    $open_previous_total += (float)($prev_row['prev_total'] ?? 0);
    $open_bill_number = ((int)($prev_row['prev_count'] ?? 0)) + 2;
  } else {
    $open_bill_number = 2;
  }
  $open_combined_total = $open_previous_total + $open_current_total;
  $open_display_previous = $open_previous_total;
  $open_display_bill_no = $open_bill_number;
  if ($has_sales_open_table) {
    // The open row status tells us whether the printed child was marked as the last bill.
    $open_row = mysqli_query($connection, "SELECT status, last_bill, bill_count FROM kp_sales_open WHERE sale_id='$parent_bill' LIMIT 1");
    if ($open_row && mysqli_num_rows($open_row) > 0) {
      $open_data = mysqli_fetch_assoc($open_row);
      $open_state = ((int)($open_data['status'] ?? 1) === 2 && (int)($open_data['last_bill'] ?? 0) === $sale_id) ? 'last' : 'continue';
      $open_display_bill_no = (int)($open_data['bill_count'] ?? $open_bill_number);
      if ($open_display_bill_no < 2) {
        $open_display_bill_no = $open_bill_number;
      }
      $has_cf_amount_col = false;
      $cf_amount_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'cf_amount'");
      if ($cf_amount_col && mysqli_num_rows($cf_amount_col) > 0) {
        $has_cf_amount_col = true;
      }
      if ($open_display_bill_no == 2) {
        $open_display_previous = (float)get_column('kp_sales', 'amount', "sale_id='$parent_bill'");
      } elseif ($has_cf_amount_col) {
        $last_open_bill = (int)($open_data['last_bill'] ?? 0);
        if ($last_open_bill > 0 && $last_open_bill !== $sale_id) {
          $cf_prev = (float)get_column('kp_sales', 'cf_amount', "sale_id='$last_open_bill'");
          if ($cf_prev > 0) {
            $open_display_previous = $cf_prev;
          }
        }
      }
    }
  }
}
$spr = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1");
$row = $spr ? mysqli_fetch_assoc($spr) : [];
$gen = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_gen WHERE id=1"));
$thermal_logo = !empty($gen['thermal_logo_path']) ? $gen['thermal_logo_path'] : ($gen['image_path'] ?? '');
$thermal_logo_src = kp_bill_embed_image_src($thermal_logo);
$print_template = get_print_template();
$show_logo = !empty($row['show_logo']) && (int)$row['show_logo'] === 1;
$show_qr = !empty($row['show_qr']) && (int)$row['show_qr'] === 1;
$thermal_logo_height = isset($row['thermal_logo_height']) ? (int)$row['thermal_logo_height'] : 60;
if ($thermal_logo_height < 30) { $thermal_logo_height = 30; }
if ($thermal_logo_height > 180) { $thermal_logo_height = 180; }

date_default_timezone_set("Africa/Nairobi");
$time = date("h:i:a");
$today = date("d-M-Y");
$mpesa_source = strtoupper((string)($row['mpesa_source'] ?? 'MAIN')) === 'COUNTER' ? 'COUNTER' : 'MAIN';
$bill_counter = (int)($sale['counter'] ?? 0);
$counter_mpesa = ($mpesa_source === 'COUNTER' && $bill_counter > 0) ? mysqli_fetch_assoc(mysqli_query($connection, "SELECT mpesa_type, mpesa_shortcode, mpesa_account FROM kp_counters WHERE cntr_id='{$bill_counter}' LIMIT 1")) : [];
$mpesa_type = strtoupper((string)($counter_mpesa['mpesa_type'] ?? (($row['mpesa'] ?? 1) == 2 ? 'PAYBILL' : 'TILL')));
$mpesa_shortcode = $mpesa_source === 'COUNTER' && isset($counter_mpesa['mpesa_shortcode']) ? $counter_mpesa['mpesa_shortcode'] : ($gen['mpesa_no'] ?? '');
$mpesa_account = $mpesa_source === 'COUNTER' && isset($counter_mpesa['mpesa_account']) ? $counter_mpesa['mpesa_account'] : ($gen['mpesa_account'] ?? '');
$mpesa_label = $mpesa_type === 'PAYBILL'
    ? 'PAYBILL ' . $mpesa_shortcode . ' ACCOUNT: ' . $mpesa_account
    : 'TILL ' . $mpesa_shortcode;
$show_continue_summary = ($parent_bill > 0 && (int)$sale_id !== (int)$parent_bill);
if ($show_continue_summary) {
    $open_display_previous = (float)($bill_context['previous_balance'] ?? ($open_display_previous ?? 0));
    $open_current_total = (float)($bill_context['current_total'] ?? ($open_current_total ?? 0));
    $open_balance_total = (float)($bill_context['current_balance'] ?? ($open_display_previous + $open_current_total));
    if (empty($bill_context['open_display_bill_no'])) {
        $bill_context['open_display_bill_no'] = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>BILL #<?php echo $sale_id; ?></title>
  <style>
    <?php if ($print_template === 2): ?>
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
    .meta span { white-space: nowrap; }
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
  </style>
</head>
<body>
<div id="receipt">
  <div id="bord">
    <?php if ($print_template === 2): ?>
    <div class="text-center">
      <?php if ($show_logo && !empty($thermal_logo_src)): ?>
        <img src="<?php echo htmlspecialchars($thermal_logo_src); ?>" style="height:<?php echo $thermal_logo_height; ?>px;width:auto;margin-top:10px;"><br>
      <?php endif; ?>
      <h1 class="title"><?php echo strtoupper(htmlspecialchars($gen['company_name'] ?? '')); ?></h1>
      <?php if (!empty($gen['company_tag'])): ?>
        <p style="margin:0;"><em><?php echo htmlspecialchars($gen['company_tag']); ?></em></p>
      <?php endif; ?>
      <hr class="sep-dot">
      <?php if (!empty($gen['address'])): ?>
        <p style="margin:0;"><?php echo htmlspecialchars($gen['address']); ?></p>
      <?php endif; ?>
      <p style="margin:0;">Cell: <?php echo htmlspecialchars($gen['phone1'] ?? ''); ?> | <?php echo htmlspecialchars($gen['phone2'] ?? ''); ?></p>
      <?php if (!empty($row['pin']) && (int)$row['pin'] === 1): ?>
        <p style="margin:0;text-align:center;">PIN: <?php echo htmlspecialchars($gen['kra_pin'] ?? ''); ?></p>
      <?php endif; ?>
    </div>

    <hr class="sep-eq">

    <div class="meta center"><span>BILL NO: <b><?php echo $sale_id; ?></b></span></div>
    <div class="meta center"><span>SERVED BY: <b><?php echo strtoupper(get_column('kp_users', 'username', "user_id='$user_id'")); ?></b></span></div>
    <div class="meta"><span>SHIFT: <b><?php echo htmlspecialchars($shift); ?></b></span><span>TIME: <b><?php echo $time; ?></b></span></div>
    <?php if ($show_continue_summary): ?>
    <div class="meta"><span>CONTINUATION</span><span>#<?php echo $parent_bill; ?></span></div>
    <div class="meta"><span>OPEN STATE</span><span>STILL CONTINUING</span></div>
    <?php endif; ?>

    <hr class="sep-eq">

    <table class="items-tbl">
      <thead>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $r1 = mysqli_query($connection,
          "SELECT SUM(qty) AS sqty, item_id, price, attr_id
           FROM kp_sale_items
           WHERE sale_id='$sale_id' AND hour=1
           GROUP BY item_id, attr_id"
        );
        while ($o = mysqli_fetch_assoc($r1)):
          $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
          if (!empty($o['attr_id'])) {
            $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
          }
        ?>
        <tr>
          <td><?php echo htmlspecialchars($iname); ?></td>
          <td><?php echo (float)$o['sqty']; ?></td>
          <td><?php echo number_format((float)$o['price'], 2); ?></td>
          <td><?php echo number_format($o['sqty'] * $o['price'], 2); ?></td>
        </tr>
        <?php endwhile; ?>

        <?php
        $r2 = mysqli_query($connection,
          "SELECT SUM(qty) AS sqty, item_id, price, attr_id
           FROM kp_sale_items
           WHERE sale_id='$sale_id' AND hour=2
           GROUP BY item_id, attr_id"
        );
        if (mysqli_num_rows($r2) > 0):
        ?>
        <tr class="sec-label">
          <td colspan="4">HAPPY HOUR ORDER</td>
        </tr>
        <?php
        while ($o = mysqli_fetch_assoc($r2)):
          $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
          if (!empty($o['attr_id'])) {
            $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
          }
        ?>
        <tr>
          <td><?php echo htmlspecialchars($iname); ?></td>
          <td><?php echo (float)$o['sqty']; ?></td>
          <td><?php echo number_format((float)$o['price'], 2); ?></td>
          <td><?php echo number_format($o['sqty'] * $o['price'], 2); ?></td>
        </tr>
        <?php endwhile; endif; ?>
      </tbody>
    </table>
    
      <?php if ($show_continue_summary): ?>
      <hr>
      <table>
        <thead>
          <tr>
            <th colspan="5"  ><h2 style="margin: 0px 0px 0px 0px; font-size:16px;">BILL NO. <?php
             echo  (int)$open_display_bill_no; ?></h2></th> 
          </tr>
          <tr>
            <th colspan="4"><b style="font-size:12px;">PREVIOUS BILL NO. <?php echo (int)($bill_context['previous_bill_sale_id'] ?? 0); ?></b></th>
          </tr>
          <tr>
            <th colspan="2"  >
              <B style=" font-size: 14px;">PREVIOUS TOTAL: </B>
            </th> 
            <th colspan="2" class="text-center">
              <B style=" font-size: 14px;"><?php  echo number_format((float)$open_display_previous,2); ?>&nbsp;</B>
            </th>
          </tr>
          <tr>
            <th colspan="2"  >
              <B style=" font-size: 14px;">CURRENT BILL: </B>
            </th> 
            <th colspan="2" class="text-center">
              <B style=" font-size: 14px;"><?php  echo number_format((float)$open_current_total,2); ?>&nbsp;</B>
            </th>
          </tr>
          <tr>
            <th colspan="2" class="text-center">
              <B style=" font-size: 22px;"> BALANCE: </B>
            </th> 
            <th colspan="2" class="text-center">
              <B style=" font-size: 22px;"><?php  echo number_format((float)$open_display_previous + (float)$open_current_total,2); ?>&nbsp;</B>
            </th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <?php endif; ?>

    <table class="totals-tbl">
      <tr class="grand">
        <td><strong>TOTAL</strong></td>
        <td><strong><?php echo number_format($amount, 2); ?></strong></td>
      </tr>
      <?php if ((int)get_column('kp_settings_print', 'tax', 'print_doc=1') === 1): ?>
      <tr>
        <td>Gross</td>
        <td><?php echo number_format($amount, 2); ?></td>
      </tr>
      <tr>
        <td>V.A.T (16%)</td>
        <td><?php echo number_format($vat, 2); ?></td>
      </tr>
      <tr>
        <td>Cat Levy (2%)</td>
        <td><?php echo number_format($ctl, 2); ?></td>
      </tr>
      <?php endif; ?>
    </table>
    <hr class="sep-dot">
    <p class="text-center" style="margin:0;"><b>THANK YOU</b></p>
    <p class="text-center" style="margin:0;"><?php echo htmlspecialchars($row['thankyou_note'] ?? ''); ?></p>
    <hr class="sep-dot">
    <div class="mpesa-line"><?php echo htmlspecialchars($mpesa_label); ?></div>
    <hr class="sep-dot">
    <?php if ($show_qr): ?>
    <div class="qr-wrap">
      <div class="qr-box"><div id="bill-qr"></div></div>
      <p class="qr-caption">Bill #<?php echo $sale_id; ?></p>
    </div>
    <hr class="sep-dot">
    <?php endif; ?>
    <p class="text-center" style="margin:0;font-size:11px;">
      K-TOUCH POS Designed &amp; Distributed by K-TEN<br>
      +254716100335 | +254735100335
    </p>
    <?php else: ?>
    <div id="receipt-header" class="text-center">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php if ($show_logo && !empty($thermal_logo_src)): ?>
        <img src="<?php echo htmlspecialchars($thermal_logo_src); ?>" style="height:<?php echo $thermal_logo_height; ?>px;width:auto;margin-top:10px;"><br>
      <?php endif; ?>
      <h1 class="title"><?php echo strtoupper(htmlspecialchars($gen['company_name'] ?? '')); ?></h1>
      <?php if (!empty($gen['company_tag'])): ?>
        <p style="font-size:18px;margin:0;">(<?php echo htmlspecialchars($gen['company_tag']); ?>)</p>
      <?php endif; ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php if (!empty($gen['address'])): ?>
        <p style="margin:0;"><?php echo htmlspecialchars($gen['address']); ?></p>
      <?php endif; ?>
      <p style="margin:0;">Cell: <?php echo htmlspecialchars($gen['phone1'] ?? ''); ?> | <?php echo htmlspecialchars($gen['phone2'] ?? ''); ?></p>
      <?php if (!empty($row['pin']) && $row['pin'] == 1): ?>
        <p style="margin:0;text-align:center;">PIN: <?php echo htmlspecialchars($gen['kra_pin'] ?? ''); ?></p>
      <?php endif; ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <div style="display:flex;justify-content:space-between;gap:8px;font-weight:bold;">
        <span style="display:block;width:100%;text-align:center;">BILL No: <?php echo $sale_id; ?></span>
      </div>
      <div style="text-align:center;font-weight:bold;">
        <span>SERVED BY: <?php echo strtoupper(get_column('kp_users', 'username', "user_id='$user_id'")); ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;gap:8px;font-weight:bold;">
        <span>SHIFT: <?php echo htmlspecialchars($shift); ?></span>
        <span>TIME: <?php echo $time; ?></span>
      </div>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>

    <div id="receipt-body">
      <table class="table table-bordered" id="deSo">
        <thead>
          <tr>
            <th>Qty</th>
            <th>Item</th>
            <th>Amount</th>
            <th>Total</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th colspan="4" class="text-center">
              <b style="font-size:22px;">Total: <?php echo number_format($amount, 2); ?></b>
            </th>
          </tr>
        </tfoot>
        <tbody>
          <?php
          $r1 = mysqli_query($connection,
            "SELECT SUM(qty) AS sqty, item_id, price, attr_id
             FROM kp_sale_items
             WHERE sale_id='$sale_id' AND hour=1
             GROUP BY item_id, attr_id"
          );
          while ($o = mysqli_fetch_assoc($r1)):
            $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
            if (!empty($o['attr_id'])) {
              $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
            }
          ?>
          <tr>
            <td><?php echo $o['sqty']; ?></td>
            <td><?php echo htmlspecialchars($iname); ?></td>
            <td><?php echo number_format((float)$o['price'], 2); ?></td>
            <td><?php echo number_format($o['sqty'] * $o['price'], 2); ?></td>
          </tr>
          <?php endwhile; ?>

          <?php
          $r2 = mysqli_query($connection,
            "SELECT SUM(qty) AS sqty, item_id, price, attr_id
             FROM kp_sale_items
             WHERE sale_id='$sale_id' AND hour=2
             GROUP BY item_id, attr_id"
          );
          if (mysqli_num_rows($r2) > 0): ?>
          <tr>
            <td colspan="4"><b>HAPPY HOUR ORDER</b></td>
          </tr>
          <?php
            while ($o = mysqli_fetch_assoc($r2)):
              $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
              if (!empty($o['attr_id'])) {
                $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
              }
          ?>
          <tr>
            <td><?php echo $o['sqty']; ?></td>
            <td><?php echo htmlspecialchars($iname); ?></td>
            <td><?php echo number_format((float)$o['price'], 2); ?></td>
            <td><?php echo number_format($o['sqty'] * $o['price'], 2); ?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
      <?php if ($show_continue_summary): ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <table class="open-demo-summary">
        <thead>
          <tr class="bill-no">
            <th colspan="4"><h2 style="margin:0;font-size:16px;">BILL NO. <?php echo (int)$open_display_bill_no; ?></h2></th>
          </tr>
          <tr class="prev-bill">
            <th colspan="4"><b>PREVIOUS BILL NO. <?php echo (int)($bill_context['previous_bill_sale_id'] ?? 0); ?></b></th>
          </tr>
          <tr class="line-label">
            <th colspan="2"><b>PREVIOUS TOTAL</b></th>
            <th colspan="2"><b><?php echo number_format((float)$open_display_previous, 2); ?></b></th>
          </tr>
          <tr class="line-value">
            <th colspan="2"><b>CURRENT BILL</b></th>
            <th colspan="2"><b><?php echo number_format((float)$open_current_total, 2); ?></b></th>
          </tr>
          <tr class="total-value">
            <th colspan="2"><b>BALANCE</b></th>
            <th colspan="2"><b><?php echo number_format((float)$open_display_previous + (float)$open_current_total, 2); ?></b></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <?php endif; ?>
    </div>

    <div id="receipt-footer">
      <?php if ((int)get_column('kp_settings_print', 'tax', 'print_doc=1') === 1): ?>
        <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
        <span class="pull-left">Gross: <?php echo $amount; ?></span>
        <span class="pull-right">V.A.T(16%): <?php echo $vat; ?></span><br>
        <span class="pull-right">Cat Levy(2%): <?php echo $ctl; ?></span><br>
      <?php endif; ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <p class="text-center" style="margin:0;"><b>THANK YOU</b></p>
      <p class="text-center" style="margin:0;"><?php echo htmlspecialchars($row['thankyou_note'] ?? ''); ?></p>
      <?php if ((int)get_column('kp_settings_print', 'tax', 'print_doc=1') === 1): ?>
        <p class="text-center" style="margin:0;">This is not an ETR. Request one.</p>
      <?php endif; ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 class="text-center" style="margin:0;font-size:20px;font-weight:bold;letter-spacing:3px;">
        <?php echo htmlspecialchars($mpesa_label); ?>
      </h2>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
        <?php if ($show_qr): ?>
        <div class="qr-wrap">
          <div class="qr-box"><div id="bill-qr"></div></div>
          <p class="qr-caption">Bill #<?php echo $sale_id; ?></p>
        </div>
        <?php endif; ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <p class="text-center" style="margin:0;font-size:11px;">
        K-TOUCH POS Designed &amp; Distributed by K-TEN<br>
        +254716100335 | +254735100335
      </p>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php if ($show_qr): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  (function() {
    var qrMount = document.getElementById('bill-qr');
    if (!qrMount || typeof QRCode === 'undefined') {
      return;
    }
    new QRCode(qrMount, {
      text: 'BILL-<?php echo $sale_id; ?>',
      width: 132,
      height: 132,
      correctLevel: QRCode.CorrectLevel.H
    });
  })();
</script>
<?php endif; ?>
</body>
</html>
