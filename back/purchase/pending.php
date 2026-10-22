<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

if (!function_exists('kp_menu_column_exists')) {
  function kp_menu_column_exists($connection, $column) {
    $column = mysqli_real_escape_string($connection, $column);
    $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_menu LIKE '$column'");
    return $check && mysqli_num_rows($check) > 0;
  }
}

if (!function_exists('kp_menu_find_by_link')) {
  function kp_menu_find_by_link($connection, $link) {
    $link = mysqli_real_escape_string($connection, $link);
    $q = mysqli_query($connection, "SELECT menu_id FROM kp_menu WHERE link='$link' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
      $row = mysqli_fetch_assoc($q);
      return (int)$row['menu_id'];
    }
    return 0;
  }
}

if (!function_exists('kp_menu_next_id')) {
  function kp_menu_next_id($connection) {
    $q = mysqli_query($connection, "SELECT MAX(menu_id) AS last_id FROM kp_menu");
    $row = $q ? mysqli_fetch_assoc($q) : null;
    return (int)($row['last_id'] ?? 0) + 1;
  }
}

if (!function_exists('kp_menu_insert_dynamic')) {
  function kp_menu_insert_dynamic($connection, array $data) {
    $columns = [];
    $values = [];
    foreach ($data as $column => $value) {
      if (!kp_menu_column_exists($connection, $column)) {
        continue;
      }
      $columns[] = "`$column`";
      $values[] = "'" . mysqli_real_escape_string($connection, (string)$value) . "'";
    }
    if (!$columns) {
      return 0;
    }
    mysqli_query($connection, "INSERT INTO kp_menu (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")");
    return (int)mysqli_insert_id($connection);
  }
}

if (!function_exists('kp_find_stock_menu_parent')) {
  function kp_find_stock_menu_parent($connection) {
    $candidates = [
      ['name' => 'Stock', 'link' => ''],
      ['name' => 'Store', 'link' => ''],
      ['name' => 'Inventory', 'link' => ''],
      ['name' => '', 'link' => 'stock/store_dash.php'],
      ['name' => '', 'link' => '../stock/store_dash.php'],
      ['name' => '', 'link' => 'stock'],
    ];
    foreach ($candidates as $candidate) {
      $where = [];
      if ($candidate['name'] !== '') {
        $name = mysqli_real_escape_string($connection, $candidate['name']);
        $where[] = "name='$name'";
      }
      if ($candidate['link'] !== '') {
        $link = mysqli_real_escape_string($connection, $candidate['link']);
        $where[] = "link='$link'";
      }
      if (!$where) {
        continue;
      }
      $q = mysqli_query($connection, "SELECT menu_id FROM kp_menu WHERE parent=0 AND (" . implode(' OR ', $where) . ") ORDER BY menu_id ASC LIMIT 1");
      if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        return (int)$row['menu_id'];
      }
    }
    return 0;
  }
}

if (!function_exists('kp_ensure_purchase_menu_under_stock')) {
  function kp_ensure_purchase_menu_under_stock($connection) {
    $targetLink = 'purchase/pending.php';
    if (kp_menu_find_by_link($connection, $targetLink) > 0) {
      return;
    }

    $stockParentId = kp_find_stock_menu_parent($connection);
    if ($stockParentId < 1) {
      return;
    }

    $approvalParentId = 0;
    $q = mysqli_query($connection, "SELECT menu_id FROM kp_menu WHERE parent='$stockParentId' AND name='Purchases' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
      $row = mysqli_fetch_assoc($q);
      $approvalParentId = (int)$row['menu_id'];
    }

    if ($approvalParentId < 1) {
      $approvalParentId = kp_menu_next_id($connection);
      kp_menu_insert_dynamic($connection, [
        'menu_id' => $approvalParentId,
        'name' => 'Purchases',
        'link' => 'kugura/pending.php',
        'icon' => 'fa fa-shopping-basket',
        'parent' => $stockParentId,
        'level' => 2,
        'sub' => 1,
      ]);
    }

    if (kp_menu_find_by_link($connection, $targetLink) < 1) {
      kp_menu_insert_dynamic($connection, [
        'menu_id' => kp_menu_next_id($connection),
        'name' => 'Unapproved Purchases',
        'link' => $targetLink,
        'icon' => 'fa fa-check-square-o',
        'parent' => $approvalParentId,
        'level' => 3,
        'sub' => 0,
      ]);
    }
  }
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id < 1) {
  header('Location:../log_out.php');
  exit;
}

kp_ensure_purchase_menu_under_stock($connection);

$pending = mysqli_query($connection, "SELECT * FROM kp_po WHERE COALESCE(rec_status,0) <> 1 OR COALESCE(stock_status,0) <> 1 ORDER BY id DESC") or die(mysqli_error($connection));
$msg = trim((string)($_GET['msg'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pending Purchases</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body { margin:0; min-height:100vh; background:linear-gradient(180deg,#f8f1df 0%,#efdcb8 100%); color:#2d2217; }
    .pending-shell { max-width:1100px; margin:0 auto; padding:18px 12px 28px; }
    .pending-head { padding:18px; border-radius:24px; background:linear-gradient(135deg,#7e2014 0%,#b94828 50%,#e7a33a 100%); color:#fff8ea; box-shadow:0 18px 34px rgba(74,37,11,.18); }
    .pending-head h1 { margin:0; font-size:28px; font-weight:900; text-transform:uppercase; }
    .pending-head p { margin:6px 0 0; font-size:13px; font-weight:700; }
    .pending-actions { display:flex; gap:10px; margin-top:12px; flex-wrap:wrap; }
    .pending-actions a { min-height:42px; padding:0 16px; border-radius:12px; background:rgba(255,255,255,.14); color:#fff !important; text-decoration:none !important; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:900; text-transform:uppercase; }
    .pending-list { margin-top:16px; display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; }
    .pending-card { background:rgba(255,255,255,.94); border-radius:20px; box-shadow:0 16px 28px rgba(0,0,0,.08); padding:16px; border:1px solid rgba(126,58,32,.08); }
    .pending-badge { display:inline-flex; min-height:30px; padding:0 12px; border-radius:999px; align-items:center; justify-content:center; background:#fff3d8; color:#8a4f00; font-size:11px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .pending-card h3 { margin:10px 0 6px; font-size:24px; font-weight:900; color:#3b2818; }
    .pending-meta { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:12px; }
    .pending-meta div { padding:10px 12px; border-radius:14px; background:#faf5ea; }
    .pending-meta strong,.pending-meta span { display:block; }
    .pending-meta strong { font-size:10px; font-weight:900; letter-spacing:.08em; color:#88653b; text-transform:uppercase; }
    .pending-meta span { margin-top:4px; font-size:15px; font-weight:900; color:#2e2217; }
    .pending-card .btn { margin-top:14px; min-height:46px; border-radius:14px; font-weight:900; text-transform:uppercase; font-size:13px; width:100%; background:linear-gradient(135deg,#355f16 0%,#58a03f 100%); border:0; color:#fff; }
    .pending-empty { margin-top:16px; padding:20px; border-radius:18px; background:rgba(255,255,255,.94); text-align:center; font-size:16px; font-weight:800; color:#7a5a2c; box-shadow:0 14px 24px rgba(0,0,0,.07); }
  </style>
</head>
<body>
  <div class="pending-shell">
    <div class="pending-head">
      <h1>Unapproved Purchases</h1>
      <p>Review front-raised purchase drafts before stock is posted into the store.</p>
      <div class="pending-actions">
        <a href="../dash/">Back to Dashboard</a>
        <a href="../../front/stock/store_dash.php">Stock Panel</a>
      </div>
    </div>

    <?php if ($msg === 'approved') { ?>
      <div class="alert alert-success" style="margin-top:14px; border-radius:16px; font-weight:800;">Purchase approved and stock posted successfully.</div>
    <?php } ?>

    <?php if (mysqli_num_rows($pending) < 1) { ?>
      <div class="pending-empty">There are no unapproved purchases right now.</div>
    <?php } else { ?>
      <div class="pending-list">
        <?php while ($row = mysqli_fetch_assoc($pending)) { ?>
          <?php
            $po_id = (int)$row['po_id'];
            $supplier = (int)$row['sup_id'] > 0 ? get_sup($row['sup_id']) : 'Supplier not set';
            $counter_name = (int)$row['req_id'] > 0 ? get_counter($row['req_id']) : 'No Counter';
            $raised_by = (int)$row['ord_user'] > 0 ? get_user($row['ord_user']) : 'Unknown';
            $item_count = (int)column_count('kp_po_items', 'id', "po_id='$po_id'");
          ?>
          <div class="pending-card">
            <span class="pending-badge">Awaiting Approval</span>
            <h3>PO #<?php echo $po_id; ?></h3>
            <div style="font-size:14px; font-weight:800; color:#77552a;"><?php echo htmlspecialchars($supplier); ?></div>
            <div class="pending-meta">
              <div><strong>Counter</strong><span><?php echo htmlspecialchars($counter_name); ?></span></div>
              <div><strong>Raised By</strong><span><?php echo htmlspecialchars($raised_by); ?></span></div>
              <div><strong>Items</strong><span><?php echo $item_count; ?></span></div>
              <div><strong>Amount</strong><span><?php echo number_format((float)$row['amount'], 2); ?></span></div>
            </div>
            <a class="btn btn-default" href="approve.php?po_id=<?php echo $po_id; ?>">Open For Approval</a>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</body>
</html>
