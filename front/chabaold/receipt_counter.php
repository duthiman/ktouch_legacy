<?php
session_start();
include('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include('funcs.php');

if (!isset($_SESSION['user_id'])) {
    header('Location:../log_out.php');
    exit;
}

ensure_chaba_print_system($connection);

$print_mode = (int)get_print_mode();
$type = strtolower(trim((string)($_GET['type'] ?? $_POST['type'] ?? '')));
$ref_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$sale_id = (int)($_GET['sale_id'] ?? $_POST['sale_id'] ?? $ref_id);
$return_to = trim((string)($_GET['return_to'] ?? $_POST['return_to'] ?? ''));
$extra = [];
foreach (['user_id', 'cntr_id', 'shift', 'summary_id'] as $extraKey) {
    $val = trim((string)($_GET[$extraKey] ?? $_POST[$extraKey] ?? ''));
    if ($val !== '') {
        $extra[$extraKey] = $val;
    }
}

$routes = [
    'bill' => ['title' => 'Select Receipt Counter For Bill', 'copy' => 'Choose the printer closest to where the customer will collect the bill.', 'file' => 'render_bill.php', 'ref_key' => 'sale_id', 'direct' => 'render_bill.php?sale_id=', 'direct_suffix' => '&auto_print=1'],
    'cashier_order' => ['title' => 'Select Printer For Cashier Order', 'copy' => 'Choose the counter/printer where the combined cashier order ticket should come out.', 'file' => 'render_cashier_order.php', 'ref_key' => 'sale_id'],
    'bot' => ['title' => 'Select Printer For BOT', 'copy' => 'Choose the printer where the bar order ticket should be collected.', 'file' => 'render_bot.php', 'ref_key' => 'sale_id'],
    'kot' => ['title' => 'Select Printer For KOT', 'copy' => 'Choose the printer where the kitchen order ticket should be collected.', 'file' => 'render_kot.php', 'ref_key' => 'sale_id'],
    'got' => ['title' => 'Select Printer For Grill Ticket', 'copy' => 'Choose the printer where the grill order ticket should be collected.', 'file' => 'render_got.php', 'ref_key' => 'sale_id'],
    'ret' => ['title' => 'Select Receipt Counter For Return', 'copy' => 'Choose the counter where the return receipt should be collected.', 'file' => 'render_ret.php', 'ref_key' => 'sale_id', 'direct' => 'render_ret.php?sale_id=', 'direct_suffix' => '&auto_print=1'],
    'kug' => ['title' => 'Select Receipt Counter For Purchase', 'copy' => 'Choose the counter where the purchase receipt should be collected.', 'file' => 'render_kug.php', 'ref_key' => 'sale_id', 'direct' => 'render_kug.php?sale_id=', 'direct_suffix' => '&auto_print=1'],
    'gwitia' => ['title' => 'Select Printer For Requisition', 'copy' => 'Choose the printer where the stock transfer requisition should be collected.', 'file' => 'render_req.php', 'ref_key' => 'id'],
    'trans' => ['title' => 'Select Printer For Transfer', 'copy' => 'Choose the printer where the stock transfer slip should be collected.', 'file' => 'render_trans.php', 'ref_key' => 'trans_id'],
    'tots' => ['title' => 'Select Printer For Units Generated', 'copy' => 'Choose the printer where the generated units slip should be collected.', 'file' => 'render_tots.php', 'ref_key' => 'id'],
    'variance' => ['title' => 'Select Printer For Variance', 'copy' => 'Choose the printer where the variance report should be collected.', 'file' => 'render_variance.php', 'ref_key' => 'cntr_id'],
    'summary' => ['title' => 'Select Printer For Cashier Summary', 'copy' => 'Choose the printer where the cashier summary should be collected.', 'file' => 'render_shift_sum.php', 'ref_key' => 'id'],
    'shift_sum' => ['title' => 'Select Printer For Shift Summary', 'copy' => 'Choose the printer where the shift summary should be collected.', 'file' => 'render_shift_sum.php', 'ref_key' => 'id'],
    'staff_sum' => ['title' => 'Select Printer For Staff Summary', 'copy' => 'Choose the printer where the staff summary should be collected.', 'file' => 'render_staff_summary.php', 'ref_key' => 'id'],
    'counter_sum' => ['title' => 'Select Printer For Counter Summary', 'copy' => 'Choose the printer where the counter close summary should be collected.', 'file' => 'render_counter_sum.php', 'ref_key' => 'summary_id'],
    'cust_credit_pay' => ['title' => 'Select Printer For Credit Payment Voucher', 'copy' => 'Choose the counter where the customer credit payment voucher should be collected.', 'file' => 'render_customer_credit_pay.php', 'ref_key' => 'credit_pay_id'],
];

if (!isset($routes[$type]) || $ref_id < 1) {
    header('Location:../dash/dash.php');
    exit;
}

$route = $routes[$type];
if ($return_to === '') {
    $return_to = '../dash/dash.php';
}

if ($print_mode === 1) {
    $direct = $route['direct'] ?? '';
    if ($direct !== '') {
        if (substr($direct, -1) === '=') {
            $direct_suffix = (string)($route['direct_suffix'] ?? '');
            header('Location:' . $direct . $ref_id . $direct_suffix);
        } else {
            header('Location:' . $direct);
        }
    } else {
        $queryParams = array_merge($extra, [$route['ref_key'] => $ref_id]);
        header('Location:' . $route['file'] . '?' . kp_print_query_string($queryParams));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cntr_id'])) {
    $cntr_id = (int)$_POST['cntr_id'];
    if ($cntr_id > 0) {
        $queryParams = array_merge($extra, [$route['ref_key'] => $ref_id]);
        $queue_id = kp_print_enqueue($connection, [
            'sale_id' => $sale_id,
            'ref_id' => $ref_id,
            'print_type' => $type,
            'label' => strtoupper(str_replace('_', ' ', $type)),
            'cntr_id' => $cntr_id,
            'route_path' => $route['file'],
            'route_query' => kp_print_query_string($queryParams),
            'requested_by' => (int)$_SESSION['user_id'],
        ]);
        if ($queue_id > 0) {
            header('Location:' . $return_to);
            exit;
        }
    }
}

$my_counter = (int)get_column('kp_users', 'counter', "user_id='" . (int)$_SESSION['user_id'] . "'");
$counters = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, cntr_id ASC") or die(mysqli_error($connection));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($route['title']); ?></title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css">
  <style>
    body {
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(180deg, #fbf5ea 0%, #f4e6cd 58%, #efe0c4 100%);
      color: #2f2418;
    }
    .receipt-shell { max-width: 760px; margin: 0 auto; padding: 24px 14px 34px; }
    .receipt-card {
      border-radius: 22px;
      overflow: hidden;
      background: rgba(255,255,255,0.96);
      box-shadow: 0 18px 34px rgba(73, 67, 25, 0.12);
      border: 1px solid rgba(97, 184, 70, 0.10);
    }
    .receipt-head {
      padding: 16px 18px;
      background: linear-gradient(135deg, #7e2014 0%, #b94828 40%, #e0a128 100%);
      color: #fff8ea;
    }
    .receipt-head h1 { margin: 0; font-size: 24px; font-weight: 900; text-transform: uppercase; }
    .receipt-head p { margin: 6px 0 0; font-size: 13px; font-weight: 700; opacity: 0.94; }
    .receipt-body { padding: 18px; background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%); }
    .receipt-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .receipt-btn {
      width: 100%; min-height: 96px; border: 2px solid #d8c3a2; border-radius: 18px; background: #fff;
      color: #6b4218; text-align: center; padding: 12px 10px; box-shadow: 0 14px 24px rgba(79, 58, 24, 0.08); font-weight: 900;
    }
    .receipt-btn.active { border-color: #2f7a31; background: linear-gradient(135deg, #eef8e9 0%, #fff2e6 100%); color: #235f25; }
    .receipt-btn strong { display: block; font-size: 28px; line-height: 1; }
    .receipt-btn span { display: block; margin-top: 10px; font-size: 12px; text-transform: uppercase; }
    .receipt-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
    @media (max-width: 575px) { .receipt-actions { flex-direction: column; } }
  </style>
</head>
<body>
  <div class="receipt-shell">
    <div class="receipt-card">
      <div class="receipt-head">
        <h1><?php echo htmlspecialchars($route['title']); ?></h1>
        <p><?php echo htmlspecialchars($route['copy']); ?></p>
      </div>
      <div class="receipt-body">
        <form method="post">
          <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
          <input type="hidden" name="id" value="<?php echo (int)$ref_id; ?>">
          <input type="hidden" name="sale_id" value="<?php echo (int)$sale_id; ?>">
          <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to); ?>">
          <?php foreach ($extra as $extraKey => $extraValue) { ?>
            <input type="hidden" name="<?php echo htmlspecialchars($extraKey); ?>" value="<?php echo htmlspecialchars($extraValue); ?>">
          <?php } ?>
          <div class="receipt-grid">
            <?php while ($counter = mysqli_fetch_assoc($counters)) { ?>
              <?php $active = ((int)$counter['cntr_id'] === $my_counter) ? ' active' : ''; ?>
              <button type="submit" class="receipt-btn<?php echo $active; ?>" name="cntr_id" value="<?php echo (int)$counter['cntr_id']; ?>">
                <strong><?php echo (int)$counter['cntr_id']; ?></strong>
                <span><?php echo htmlspecialchars($counter['name']); ?><?php echo !empty($counter['dpt']) ? ' - DPT ' . (int)$counter['dpt'] : ''; ?></span>
              </button>
            <?php } ?>
          </div>
        </form>
        <div class="receipt-actions">
          <a href="<?php echo htmlspecialchars($return_to); ?>" class="btn btn-outline-danger">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
