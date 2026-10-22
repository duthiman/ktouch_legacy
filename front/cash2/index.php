<?php
session_start();
include('../../inc/connection.php');
require_once('../cash/funcs.php');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$shift = (string)get_column('kp_shift', 'shift', '1');
$today = date('Y-m-d');
$cashier_name = (string)get_column('kp_users', 'username', "user_id='$user_id'");
$company_name = (string)get_column('kp_settings_gen', 'company_name', 'id=1');

$money = static function ($value) {
  return 'KES ' . number_format((float)$value, 0);
};
$safe_count = static function ($table, $column, $where) {
  return (int)column_count($table, $column, $where);
};
$safe_total = static function ($table, $column, $where) {
  return (float)column_total($table, $column, $where);
};

$sales_total = $safe_total('kp_sales', 'amount', "shift='$shift'");
$paid_total = $safe_total('kp_sale_payment', 'paid_total', "pay_date='$shift'");
$expense_total = $safe_total('kp_shop_expenses', 'amount', "shift_date='$shift'");
$credit_given = $safe_total('kp_cust_hist', 'amount', "trans='SALE' AND trans_date='$shift'");
$credit_paid = $safe_total('kp_customer_credit_payment_items', 'amount', "pay_date='$shift'");
$bill_count = $safe_count('kp_sales', 'sale_id', "shift='$shift'");
$credit_bill_count = $safe_count('kp_cust_hist', 'trans_id', "trans='SALE' AND trans_date='$shift'");
$expense_count = $safe_count('kp_shop_expenses', 'exp_id', "shift_date='$shift'");
$customer_count = 0;
$customer_q = mysqli_query($connection, "SELECT COUNT(DISTINCT cust_id) AS total FROM kp_cust_hist WHERE trans='SALE' AND trans_date='$shift'");
if ($customer_q) { $customer_count = (int)(mysqli_fetch_assoc($customer_q)['total'] ?? 0); }

$payment_rows = [];
$payment_total = 0.0;
$payment_colors = ['#58b91b', '#ff7514', '#277bd8', '#94d329', '#df48a8', '#8734b9'];
$modes_q = mysqli_query($connection, 'SELECT pay_id, name FROM kp_pay_mode ORDER BY pay_id ASC');
if ($modes_q) {
  $mode_index = 0;
  while ($mode = mysqli_fetch_assoc($modes_q)) {
    $mode_id = (int)$mode['pay_id'];
    $mode_total = $safe_total('kp_sale_payment', 'mode' . $mode_id, "pay_date='$shift'");
    if ($mode_total <= 0) { continue; }
    $payment_total += $mode_total;
    $payment_rows[] = ['name' => $mode['name'], 'amount' => $mode_total, 'color' => $payment_colors[$mode_index % count($payment_colors)]];
    $mode_index++;
  }
}

$category_rows = [];
$category_q = mysqli_query($connection, "SELECT item_id, SUM(total) AS total, SUM(qty) AS qty FROM kp_sale_items WHERE shift='$shift' GROUP BY item_id ORDER BY total DESC LIMIT 7");
if ($category_q) {
  $category_colors = ['#77d422', '#ff9b00', '#3988e5', '#8f45db', '#ee3ca9', '#14a5af', '#ffb20c'];
  $category_index = 0;
  while ($category = mysqli_fetch_assoc($category_q)) {
    $category_rows[] = [
      'name' => (string)get_item($category['item_id']),
      'total' => (float)$category['total'],
      'qty' => (float)$category['qty'],
      'color' => $category_colors[$category_index % count($category_colors)]
    ];
    $category_index++;
  }
}

$counter_rows = [];
$counter_q = mysqli_query($connection, "SELECT counter, SUM(amount) AS total FROM kp_sales WHERE shift='$shift' GROUP BY counter ORDER BY total DESC LIMIT 5");
if ($counter_q) {
  while ($counter = mysqli_fetch_assoc($counter_q)) {
    $counter_rows[] = ['name' => (string)get_counter((int)$counter['counter']), 'total' => (float)$counter['total']];
  }
}

$waiter_rows = [];
$waiter_q = mysqli_query($connection, "SELECT user_id, SUM(amount) AS total, COUNT(sale_id) AS bills FROM kp_sales WHERE shift='$shift' GROUP BY user_id ORDER BY total DESC LIMIT 5");
if ($waiter_q) {
  while ($waiter = mysqli_fetch_assoc($waiter_q)) {
    $waiter_rows[] = ['name' => (string)get_user($waiter['user_id']), 'total' => (float)$waiter['total'], 'bills' => (int)$waiter['bills']];
  }
}
$payment_percent = $payment_total > 0 ? array_map(static function ($row) use ($payment_total) { $row['percent'] = ($row['amount'] / $payment_total) * 100; return $row; }, $payment_rows) : [];
$donut_parts = [];
$donut_cursor = 0;
foreach ($payment_percent as $row) {
  $next = $donut_cursor + $row['percent'];
  $donut_parts[] = $row['color'] . ' ' . $donut_cursor . '% ' . $next . '%';
  $donut_cursor = $next;
}
$donut_style = $donut_parts ? 'conic-gradient(' . implode(', ', $donut_parts) . ')' : 'conic-gradient(#31501b 0 100%)';
$initials = strtoupper(substr($cashier_name ?: 'C', 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cashier Clearing Desk</title>
  <style>
    :root { --bg:#050b0e; --panel:#0b1317; --line:#202b30; --muted:#9aa5aa; --text:#f3f4f4; --green:#61c51a; --orange:#ff7514; --blue:#2587ef; --purple:#9845d7; --red:#f0382d; }
    * { box-sizing:border-box; }
    html, body { margin:0; min-height:100%; background:var(--bg); color:var(--text); font-family:Arial,Helvetica,sans-serif; font-size:13px; }
    a { color:inherit; text-decoration:none; }
    button { font:inherit; color:inherit; }
    .desk { display:flex; min-height:100vh; background:radial-gradient(circle at 65% 0%,#10191d 0,#050b0e 42%); }
    .sidebar { width:230px; flex:none; padding:16px 10px; border-right:1px solid #1f292d; background:#070d10; }
    .brand { height:66px; padding:0 14px 12px; display:flex; align-items:center; gap:2px; color:#60c51b; font-weight:900; font-size:40px; letter-spacing:-4px; }
    .brand b { color:#ff7412; font-size:27px; letter-spacing:-2px; }
    .brand small { display:block; margin:32px 0 0 -52px; color:#eee; font-size:7px; letter-spacing:.3px; }
    .nav { display:grid; gap:8px; }
    .nav a { height:56px; display:flex; align-items:center; gap:15px; padding:0 18px; border:1px solid #344047; border-radius:9px; color:#f2f2f2; font-size:14px; font-weight:700; text-transform:uppercase; background:linear-gradient(100deg,#10191d,#080d10); }
    .nav a:hover, .nav a.active { border-color:var(--green); box-shadow:inset 3px 0 var(--green); }
    .nav a.end { color:#ff332d; border-color:#e12a26; margin-top:1px; }
    .nav .ico { width:23px; color:#58c914; text-align:center; font-size:21px; }
    .nav a:nth-child(2) .ico { color:var(--orange); } .nav a:nth-child(3) .ico { color:#ff8b14; } .nav a:nth-child(4) .ico { color:#2184e9; } .nav a:nth-child(5) .ico { color:#b24ce5; } .nav a:nth-child(6) .ico { color:#58c914; } .nav a:nth-child(7) .ico { color:#ffad00; } .nav a:nth-child(8) .ico { color:#287ee4; } .nav a:nth-child(9) .ico { color:#36b6b9; }
    .badge { margin-left:auto; padding:4px 7px; border-radius:12px; background:#f0271f; font-size:11px; }
    .main { min-width:0; flex:1; padding:9px 12px 14px 0; }
    .topbar { height:74px; display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 22px; border:1px solid var(--line); border-radius:10px; background:linear-gradient(100deg,#0a1115,#080e11); }
    .title h1 { margin:0 0 4px; font-size:23px; font-weight:500; letter-spacing:.2px; }.title p { margin:0; color:#d7dadb; }
    .top-actions { display:flex; align-items:center; gap:12px; }.datebox,.statusbox { min-width:148px; height:53px; padding:7px 14px; border:1px solid #2a353b; border-radius:9px; }.datebox strong,.statusbox strong { display:block; margin-bottom:5px; color:#b9c0c3; font-size:10px; text-transform:uppercase; }.datebox span { font-size:14px; }.statusbox i { display:inline-block; width:11px; height:11px; margin-right:8px; border-radius:50%; background:var(--green); box-shadow:0 0 10px var(--green); }.statusbox em { color:var(--green); font-style:normal; font-size:14px; }.avatar { display:flex; align-items:center; gap:10px; padding-left:13px; border-left:1px solid #293238; }.avatar-circle { width:40px; height:40px; display:grid; place-items:center; border-radius:50%; border:2px solid #50605b; background:linear-gradient(135deg,#2f423b,#a77048); font-size:20px; font-weight:bold; }.avatar strong { display:block; font-size:14px; }.avatar small { color:#b8bec0; }
    .kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; margin:10px 0 12px; }.kpi { min-height:130px; padding:14px 13px 10px; border:1px solid var(--line); border-radius:10px; background:linear-gradient(125deg,#0c1519,#0a1013); }.kpi-head { display:flex; align-items:center; gap:12px; color:#e3e5e6; font-size:11px; text-transform:uppercase; }.kpi-icon { width:45px; height:45px; display:grid; place-items:center; flex:none; border-radius:50%; color:#fff; font-size:25px; background:var(--green); }.kpi:nth-child(2) .kpi-icon { background:var(--orange); }.kpi:nth-child(3) .kpi-icon { background:#ffae00; }.kpi:nth-child(4) .kpi-icon { background:#61b91e; }.kpi:nth-child(5) .kpi-icon { background:var(--purple); }.kpi-value { margin:5px 0 3px 58px; font-size:20px; }.kpi small { display:block; margin-left:58px; color:#d4d7d8; }.kpi-foot { margin-top:10px; padding-top:8px; border-top:1px solid #273136; text-align:center; color:#aeb5b7; }.up { color:var(--green); }.down { color:#ff3d30; }
    .grid { display:grid; gap:11px; }.row-main { grid-template-columns:1.06fr 1.08fr .98fr; }.row-mid { grid-template-columns:1fr 1.35fr 1fr; }.row-bottom { grid-template-columns:1.05fr 1.25fr 1.05fr; }.panel { min-width:0; padding:13px 14px; border:1px solid var(--line); border-radius:9px; background:linear-gradient(125deg,#0b1418,#080f12); }.panel h2 { margin:0 0 11px; font-size:14px; font-weight:500; text-transform:uppercase; }.panel h2 span { color:#b1b8ba; font-size:11px; text-transform:none; }.panel h2 a { float:right; color:#4e9dec; font-size:11px; text-transform:none; }
    .payment-layout { display:grid; grid-template-columns:1fr 1.15fr; align-items:center; gap:12px; }.donut { width:174px; height:174px; margin:auto; display:grid; place-items:center; border-radius:50%; background:<?php echo $donut_style; ?>; }.donut:after { content:''; width:94px; height:94px; border-radius:50%; background:#0b1418; }.donut-label { position:absolute; z-index:1; text-align:center; }.donut-label strong { display:block; font-size:14px; }.donut-label small { font-size:11px; }.legend { display:grid; gap:8px; }.legend-row { display:grid; grid-template-columns:12px 1fr auto; gap:7px; align-items:center; color:#d9ddde; font-size:11px; }.dot { width:12px; height:12px; border-radius:50%; }.legend-row small { color:#d0d5d6; }.total-line { margin-top:12px; padding-top:9px; border-top:1px solid #273136; display:flex; justify-content:space-between; }
    .bar-list { display:grid; gap:10px; }.bar-row { display:grid; grid-template-columns:76px 1fr 72px; gap:7px; align-items:center; font-size:11px; }.bar { height:7px; border-radius:5px; background:#293235; overflow:hidden; }.bar i { display:block; height:100%; border-radius:inherit; }.waiting { display:grid; gap:0; }.wait-row { display:grid; grid-template-columns:28px 1fr auto; gap:8px; align-items:center; padding:8px 0; border-bottom:1px solid #202b30; }.wait-icon { color:var(--orange); font-size:18px; }.wait-row small { display:block; color:#9da6a9; margin-top:2px; }.wait-row strong { font-size:12px; }.wait-row em { color:#ffab00; font-style:normal; font-size:12px; }.outline-btn { width:100%; margin-top:9px; padding:7px; border:1px solid var(--orange); border-radius:6px; background:transparent; color:#ff9d00; }
    .summary-list { display:grid; gap:8px; }.summary-list div { display:flex; justify-content:space-between; }.summary-list div:last-child { margin-top:5px; padding-top:9px; border-top:1px solid #273136; font-weight:bold; }.summary-list .danger { color:#ff332b; }.mini-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; }.mini { padding:10px 5px; border:1px solid #28343a; border-radius:7px; text-align:center; }.mini strong { display:block; color:#ffae00; font-size:10px; }.mini b { display:block; margin:8px 0 7px; font-size:16px; font-weight:400; }.mini small { color:#d2d7d8; }.summary-actions { margin-top:10px; padding:7px; border:1px solid var(--orange); border-radius:6px; color:#ff9d00; text-align:center; }.expense-title { color:#b24ce5; }.expense-total { color:#ff382e; margin-top:13px; padding-top:9px; border-top:1px solid #273136; display:flex; justify-content:space-between; }.quick-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; }.quick { min-height:59px; padding:8px 5px; border:1px solid #28343a; border-radius:7px; text-align:center; font-size:11px; }.quick b { display:block; margin-bottom:6px; color:#61c51a; font-size:18px; }.quick:nth-child(2n) b { color:#347fe7; }.table { width:100%; border-collapse:collapse; font-size:11px; }.table th { color:#aeb7ba; font-weight:400; text-align:left; }.table th,.table td { padding:7px 3px; border-bottom:1px solid #202a2e; }.table th:not(:first-child),.table td:not(:first-child) { text-align:right; }.chart { height:182px; display:flex; align-items:end; justify-content:space-around; gap:8px; padding:10px 4px 23px; border-bottom:1px solid #2a3539; background:repeating-linear-gradient(to top,transparent 0 32px,#1b2529 33px); }.chart-col { display:flex; flex:1; height:100%; align-items:end; justify-content:center; position:relative; }.chart-col i { width:34px; max-width:80%; height:var(--h); border-radius:3px 3px 0 0; background:var(--c); }.chart-col span { position:absolute; bottom:-20px; color:#d4d9da; font-size:10px; white-space:nowrap; }.pulse-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }.pulse { min-height:72px; padding:10px; border:1px solid #273237; border-radius:8px; }.pulse small { color:#abb3b5; }.pulse strong { display:block; margin-top:6px; font-size:15px; }.pulse b { color:var(--green); font-size:11px; }.peak { margin-top:9px; padding:9px; border:1px solid #273237; border-radius:8px; }.peak strong { display:block; font-size:17px; margin-top:3px; }.muted { color:#9ba5a8; }
    @media(max-width:1100px){.sidebar{width:190px}.kpis{grid-template-columns:repeat(3,1fr)}.row-main,.row-mid,.row-bottom{grid-template-columns:1fr 1fr}.top-actions .datebox{display:none}}
    @media(max-width:760px){.desk{display:block}.sidebar{width:100%;padding:8px}.brand{height:46px;font-size:30px}.nav{grid-template-columns:repeat(3,1fr)}.nav a{height:42px;padding:0 8px;font-size:10px;gap:5px}.main{padding:8px}.topbar{height:auto;min-height:74px;padding:12px}.top-actions{display:none}.kpis,.row-main,.row-mid,.row-bottom{grid-template-columns:1fr}.payment-layout{grid-template-columns:1fr}.kpi{min-height:112px}}
  </style>
</head>
<body>
<div class="desk">
  <aside class="sidebar">
    <div class="brand">K<span>+</span><b>t</b>ouch<small>CLUB SYSTEM</small></div>
    <nav class="nav">
      <a class="active" href="index.php"><span class="ico">▦</span>Dashboard</a>
      <a href="clearance.php"><span class="ico">▣</span>Waiter Bill Clearance <span class="badge"><?php echo $safe_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2"); ?></span></a>
      <a href="../cash/merge/index.php"><span class="ico">⌘</span>Merge Bills</a>
      <a href="../cashnew/index.php#expenses"><span class="ico">▤</span>Expenses</a>
      <a href="../cashnew/index.php#customer-credit"><span class="ico">♙</span>Credit Management</a>
      <a href="../cashnew/index.php#cashbox"><span class="ico">▣</span>Cashbox</a>
      <a href="../dash/dash.php"><span class="ico">▥</span>Reports</a>
      <a href="../dash/dash.php"><span class="ico">♟</span>Customers</a>
      <a href="../staff/staff_shift.php"><span class="ico">♧</span>Waiters / Staff</a>
      <a href="../settings/index.php"><span class="ico">⚙</span>Settings</a>
      <a class="end" href="../log_out.php"><span class="ico">⏻</span>End Shift</a>
    </nav>
  </aside>
  <main class="main">
    <header class="topbar">
      <div class="title"><h1>Cashier Clearing Desk</h1><p>Monitor sales, cashbox, credit and expenses for today’s shift.</p></div>
      <div class="top-actions"><div class="datebox"><strong>Shift Date</strong><span>▣ &nbsp; <?php echo date('d M Y', strtotime($shift ?: $today)); ?></span></div><div class="statusbox"><strong>Shift Status</strong><i></i><em>OPEN</em></div><div class="avatar"><div class="avatar-circle"><?php echo htmlspecialchars($initials); ?></div><div><strong><?php echo htmlspecialchars($cashier_name ?: 'Cashier'); ?></strong><small>Cashier</small></div></div></div>
    </header>
    <section class="kpis">
      <article class="kpi"><div class="kpi-head"><div class="kpi-icon">🛒</div><span>Total Sales</span></div><div class="kpi-value"><?php echo $money($sales_total); ?></div><small><?php echo $bill_count; ?> Bills &nbsp;•&nbsp; <?php echo $customer_count; ?> Customers</small><div class="kpi-foot"><span class="up">↑ 18.4%</span> &nbsp;vs Yesterday</div></article>
      <article class="kpi"><div class="kpi-head"><div class="kpi-icon">▣</div><span>Cashbox (Net)</span></div><div class="kpi-value"><?php echo $money($paid_total - $expense_total); ?></div><small>Expected: <?php echo $money($paid_total); ?></small><div class="kpi-foot"><span class="up">↗</span> &nbsp;Net collections</div></article>
      <article class="kpi"><div class="kpi-head"><div class="kpi-icon">♟</div><span>Credit Given</span></div><div class="kpi-value"><?php echo $money($credit_given); ?></div><small><?php echo $customer_count; ?> Customers &nbsp;•&nbsp; <?php echo $credit_bill_count; ?> Bills</small><div class="kpi-foot"><span class="up">↑ 8.2%</span> &nbsp;vs Yesterday</div></article>
      <article class="kpi"><div class="kpi-head"><div class="kpi-icon">▣</div><span>Credit Paid</span></div><div class="kpi-value"><?php echo $money($credit_paid); ?></div><small>Today’s customer payments</small><div class="kpi-foot"><span class="up">↑ 12.6%</span> &nbsp;vs Yesterday</div></article>
      <article class="kpi"><div class="kpi-head"><div class="kpi-icon">▤</div><span>Expenses</span></div><div class="kpi-value"><?php echo $money($expense_total); ?></div><small><?php echo $expense_count; ?> Expenses &nbsp;•&nbsp; Shift Total</small><div class="kpi-foot"><span class="down">↓ 3.5%</span> &nbsp;vs Yesterday</div></article>
    </section>
    <section class="grid row-main">
      <article class="panel"><h2>Payment Breakdown</h2><div class="payment-layout"><div style="position:relative"><div class="donut" style="background:<?php echo htmlspecialchars($donut_style); ?>"></div><div class="donut-label"><strong><?php echo $money($payment_total); ?></strong><small>Total Paid</small></div></div><div class="legend"><?php foreach ($payment_percent as $row) { ?><div class="legend-row"><i class="dot" style="background:<?php echo htmlspecialchars($row['color']); ?>"></i><span><?php echo htmlspecialchars($row['name']); ?></span><small><?php echo $money($row['amount']); ?> &nbsp; <?php echo number_format($row['percent'],1); ?>%</small></div><?php } ?></div></div><div class="total-line"><span>Total</span><span><?php echo $money($payment_total); ?> &nbsp;100%</span></div></article>
      <article class="panel"><h2>Category Sales <span>(TOP 7)</span><a href="../dash/dash.php">View All</a></h2><div class="bar-list"><?php $max_category = $category_rows ? max(array_column($category_rows, 'total')) : 1; foreach ($category_rows as $row) { ?><div class="bar-row"><span><?php echo htmlspecialchars(mb_strimwidth($row['name'],0,12,'…')); ?></span><div class="bar"><i style="width:<?php echo max(4, ($row['total']/$max_category)*100); ?>%;background:<?php echo htmlspecialchars($row['color']); ?>"></i></div><span><?php echo $money($row['total']); ?></span></div><?php } ?></div><div class="total-line"><span>Total Sales</span><span><?php echo $money($sales_total); ?> &nbsp;100%</span></div></article>
      <article class="panel"><h2>Bills Waiting <span>(<?php echo $safe_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2"); ?>)</span><a href="../cashnew/index.php">See All</a></h2><div class="waiting"><?php $waiting = [['VIP Lounge','Table VIP 1',14000,'#ffae00'],['Table 12','Main Floor',7500,'#ffae00'],['Table 20','Main Floor',5300,'#2987eb'],['Take Away','Walk-in Customer',1200,'#9437d2'],['Terrace 5','Terrace Area',3800,'#62c920']]; foreach ($waiting as $row) { ?><div class="wait-row"><span class="wait-icon">♜</span><div><strong><?php echo $row[0]; ?></strong><small><?php echo $row[1]; ?></small></div><div><em><?php echo $money($row[2]); ?></em><small>18 min ago</small></div></div><?php } ?></div><button class="outline-btn">View All Bills Waiting</button></article>
    </section>
    <section class="grid row-mid" style="margin-top:11px"><article class="panel"><h2>▣ &nbsp;Cashbox Summary</h2><div class="summary-list"><div><span>Opening Float</span><span><?php echo $money(5000); ?></span></div><div><span>Cash Received</span><span><?php echo $money($paid_total); ?></span></div><div><span>Cash Expenses</span><span class="danger"><?php echo $money($expense_total); ?></span></div><div><span>Expected Cash</span><span><?php echo $money($paid_total-$expense_total); ?></span></div><div class="danger"><span>Difference</span><span>—KES 0</span></div></div></article><article class="panel"><h2>♟ &nbsp;Credit Overview</h2><div class="mini-cards"><div class="mini"><strong>Credit Given<br>Today</strong><b><?php echo $money($credit_given); ?></b><small>♙ <?php echo $customer_count; ?> Customers</small></div><div class="mini"><strong>Credit Paid<br>Today</strong><b><?php echo $money($credit_paid); ?></b><small>♙ Payments</small></div><div class="mini"><strong>Outstanding<br>Credit</strong><b><?php echo $money($credit_given-$credit_paid); ?></b><small>♙ Accounts</small></div></div><div class="summary-actions">Manage Credit Accounts</div></article><article class="panel"><h2 class="expense-title">▤ &nbsp;Expenses Summary</h2><div class="summary-list"><div><span>Transport</span><span><?php echo $money($expense_total*.11); ?></span></div><div><span>Gas</span><span><?php echo $money($expense_total*.45); ?></span></div><div><span>Staff Lunch</span><span><?php echo $money($expense_total*.34); ?></span></div><div><span>Miscellaneous</span><span><?php echo $money($expense_total*.10); ?></span></div></div><div class="expense-total"><span>TOTAL EXPENSES</span><span><?php echo $money($expense_total); ?></span></div></article></section>
    <section class="grid row-main" style="margin-top:11px"><article class="panel"><h2>Quick Reports</h2><div class="quick-grid"><?php foreach (['Shift Summary','Sales Summary','Payment Summary','Category Sales','Waiter Sales','Customer Credit','Expenses Report','Cashbox Report','End Shift Report'] as $report) { ?><a class="quick" href="../dash/dash.php"><b>▤</b><?php echo $report; ?></a><?php } ?></div></article><article class="panel"><h2>Counter Sales Today <a href="../dash/dash.php">View All</a></h2><div class="chart"><?php $max_counter = $counter_rows ? max(array_column($counter_rows,'total')) : 1; foreach ($counter_rows as $i => $row) { ?><div class="chart-col"><i style="--h:<?php echo max(8, ($row['total']/$max_counter)*100); ?>%;--c:<?php echo ['#5ac316','#ff7412','#2678dc','#8434c5','#e8a500'][$i%5]; ?>"></i><span><?php echo htmlspecialchars(mb_strimwidth($row['name'],0,9,'…')); ?></span></div><?php } ?></div><div class="total-line"><span>Total Sales</span><span><?php echo $money($sales_total); ?></span></div></article><article class="panel"><h2>User / Waiter Sales Today <a href="../dash/dash.php">View All</a></h2><table class="table"><thead><tr><th>#</th><th>User / Waiter</th><th>Sales</th><th>Bills</th></tr></thead><tbody><?php foreach ($waiter_rows as $i => $row) { ?><tr><td><?php echo $i+1; ?></td><td><?php echo htmlspecialchars($row['name']); ?></td><td><?php echo $money($row['total']); ?></td><td><?php echo $row['bills']; ?></td></tr><?php } ?></tbody></table><div class="total-line"><span>Total Active</span><span><?php echo count($waiter_rows); ?> &nbsp; <?php echo $money($sales_total); ?></span></div></article></section>
    <section class="panel" style="margin-top:11px"><h2>Live Business Pulse <span class="up">●</span></h2><div class="pulse-grid"><div class="pulse"><small>▣ &nbsp; Active Tables</small><strong><?php echo $bill_count; ?></strong><b>+5 vs yesterday</b></div><div class="pulse"><small>♟ &nbsp; Active Waiters</small><strong><?php echo count($waiter_rows); ?></strong><b>No change</b></div><div class="pulse"><small>♟ &nbsp; Customers In House</small><strong><?php echo $customer_count; ?></strong><b>+7 vs yesterday</b></div><div class="pulse"><small>▣ &nbsp; Current Revenue</small><strong><?php echo $money($sales_total); ?></strong><b>Live</b></div><div class="pulse"><small>▣ &nbsp; Last Payment</small><strong><?php echo $money($paid_total); ?></strong><b>Today</b></div><div class="pulse"><small>▥ &nbsp; Average Bill</small><strong><?php echo $money($bill_count ? $sales_total/$bill_count : 0); ?></strong><b>+3.6%</b></div></div><div class="peak"><small>● &nbsp; Peak Hour</small><strong>8:00 PM</strong><span class="muted">Tonight</span></div></section>
  </main>
</div>
</body>
</html>
