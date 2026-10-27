<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
require_once __DIR__ . '/../kug/funcs.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$job = $_SESSION['job'];
$purchase_settings = function_exists('kp_purchase_settings')
  ? kp_purchase_settings($connection)
  : ['front_purchase_enabled' => 1];
$front_purchase_allowed = (int)($purchase_settings['front_purchase_enabled'] ?? 0) === 1;
$shift = isset($_SESSION['shift']) ? trim((string)$_SESSION['shift']) : '';
$get_shift = mysqli_query($connection,"SELECT * FROM kp_shift") or die("Could not get the shift");
$gs = mysqli_fetch_assoc($get_shift);
$activeShift = isset($gs['shift']) ? trim((string)$gs['shift']) : '';
if ($shift === '' || $shift === '0') $shift = $activeShift !== '' ? $activeShift : date('Y-m-d');
$take = $gs['stock_status'];
$counter_id = $user_id ? get_column('kp_users', 'counter', "user_id='$user_id'") : 0;
$counter_name = $counter_id ? get_counter($counter_id) : 'No Counter';
$stock_added = ['purchase_qty'=>0, 'purchase_amount'=>0, 'request_qty'=>0, 'unit_qty'=>0];
$stock_added_items = [];
$shiftEsc = mysqli_real_escape_string($connection, (string)$shift);
$tableExists = function($table) use ($connection) { $tableEsc = mysqli_real_escape_string($connection, $table); $q = mysqli_query($connection, "SHOW TABLES LIKE '{$tableEsc}'"); return $q && mysqli_num_rows($q) > 0; };
$columnExists = function($table, $column) use ($connection) { $tableEsc = mysqli_real_escape_string($connection, $table); $columnEsc = mysqli_real_escape_string($connection, $column); $q = mysqli_query($connection, "SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$columnEsc}'"); return $q && mysqli_num_rows($q) > 0; };
if ($tableExists('kp_grn_items')) {
  $purchaseDateFilter = $columnExists('kp_grn_items','day') ? "gi.day='{$shiftEsc}'" : ($columnExists('kp_grn_items','created_at') ? "DATE(gi.created_at)='{$shiftEsc}'" : '1=0');
  $canMatchTrackedPurchase = $tableExists('kp_stock_track') && $columnExists('kp_stock_track','grn_id') && $columnExists('kp_stock_track','item_id') && $columnExists('kp_stock_track','stock_counter');
  /* Stock is counter-owned. Do not fall back to a date-only purchase query,
     because that would expose another counter's purchases. */
  $trackedPurchaseStatus = $columnExists('kp_stock_track','status') ? " AND UPPER(st.status) IN ('IN','RECEIVED')" : '';
  $purchaseFilter = ($canMatchTrackedPurchase && $counter_id > 0)
    ? "EXISTS (SELECT 1 FROM kp_stock_track st WHERE st.grn_id=gi.grn_id AND st.item_id=gi.item_id AND st.stock_counter='" . (int)$counter_id . "'{$trackedPurchaseStatus})"
    : '1=0';
  $counterQtyColumn = 'cntr' . (int)$counter_id . '_qty';
  $counterQtySql = $counter_id > 0 && $columnExists('kp_items', $counterQtyColumn) ? "COALESCE(i.`{$counterQtyColumn}`,0)" : '0';
  $counterGroupSql = $counter_id > 0 && $columnExists('kp_items', $counterQtyColumn) ? ", i.`{$counterQtyColumn}`" : '';
  $q = mysqli_query($connection, "SELECT COALESCE(SUM(gi.qty),0) qty, COALESCE(SUM(gi.total),0) amount FROM kp_grn_items gi WHERE {$purchaseFilter}");
  if ($q && ($r=mysqli_fetch_assoc($q))) { $stock_added['purchase_qty']=(float)$r['qty']; $stock_added['purchase_amount']=(float)$r['amount']; }
  $itemsQ = mysqli_query($connection, "SELECT gi.item_id, COALESCE(i.name, CONCAT('Item #',gi.item_id)) item_name, COALESCE(SUM(gi.qty),0) qty, COALESCE(SUM(gi.total),0) amount, {$counterQtySql} current_qty FROM kp_grn_items gi LEFT JOIN kp_items i ON i.item_id=gi.item_id WHERE {$purchaseFilter} GROUP BY gi.item_id, i.name{$counterGroupSql} ORDER BY item_name ASC");
  if ($itemsQ) { while ($itemRow=mysqli_fetch_assoc($itemsQ)) { $stock_added_items[]=['source'=>'PURCHASE','name'=>(string)$itemRow['item_name'],'qty'=>(float)$itemRow['qty'],'amount'=>(float)$itemRow['amount'],'current_qty'=>(float)$itemRow['current_qty']]; } }
}
if ($tableExists('kp_req_items') && $columnExists('kp_req_items','req_qty')) {
  $requestDateFilter = $columnExists('kp_req_items','rec_date') ? "rec_date='{$shiftEsc}'" : ($columnExists('kp_req_items','issue_date') ? "issue_date='{$shiftEsc}'" : ($columnExists('kp_req_items','req_shift') ? "req_shift='{$shiftEsc}'" : ($columnExists('kp_req_items','created_at') ? "DATE(created_at)='{$shiftEsc}'" : '1=0')));
  $requestQty = $columnExists('kp_req_items','rec_qty') ? 'rec_qty' : ($columnExists('kp_req_items','issue_qty') ? 'issue_qty' : 'req_qty');
  $q = mysqli_query($connection, "SELECT COALESCE(SUM({$requestQty}),0) qty FROM kp_req_items WHERE {$requestDateFilter}");
  if ($q && ($r=mysqli_fetch_assoc($q))) { $stock_added['request_qty']=(float)$r['qty']; }
}
if ($tableExists('kp_tot_items')) { $q = mysqli_query($connection, "SELECT COALESCE(SUM(tots),0) qty FROM kp_tot_items WHERE shift='{$shiftEsc}'"); if ($q && ($r=mysqli_fetch_assoc($q))) { $stock_added['unit_qty']=(float)$r['qty']; } }
/* Requisitions and generated units are recorded in stock tracking on newer servers. */
if ($tableExists('kp_stock_track') && $columnExists('kp_stock_track','item_id') && $columnExists('kp_stock_track','qty') && $counter_id > 0 && $columnExists('kp_stock_track','stock_counter')) {
  $trackWhere = [];
  if ($columnExists('kp_stock_track','shift')) $trackWhere[] = "st.shift='{$shiftEsc}'";
  elseif ($columnExists('kp_stock_track','created_at')) $trackWhere[] = "DATE(st.created_at)='{$shiftEsc}'";
  $trackWhere[] = "st.stock_counter='".(int)$counter_id."'";
  if ($columnExists('kp_stock_track','status')) $trackWhere[] = "UPPER(st.status) IN ('IN','RECEIVED')";
  if ($columnExists('kp_stock_track','trans')) $trackWhere[] = "UPPER(st.trans) IN ('REQ','UNIT_GEN')";
  if ($trackWhere) {
    $trackSql = "SELECT st.item_id, COALESCE(i.name, CONCAT('Item #',st.item_id)) item_name, COALESCE(SUM(st.qty),0) qty, 0 amount, 0 current_qty FROM kp_stock_track st LEFT JOIN kp_items i ON i.item_id=st.item_id WHERE ".implode(' AND ',$trackWhere)." GROUP BY st.item_id,i.name ORDER BY item_name ASC";
    $trackQ = mysqli_query($connection,$trackSql);
    if ($trackQ) while ($row=mysqli_fetch_assoc($trackQ)) $stock_added_items[]=['source'=>'STOCK_TRACK','name'=>(string)$row['item_name'],'qty'=>(float)$row['qty'],'amount'=>0,'current_qty'=>0];
  }
}
$stock_added_total = $stock_added['purchase_qty'] + $stock_added['request_qty'] + $stock_added['unit_qty'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Stock</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
  <style>
    body {
      background: url("../assets/img/bg/33.jpg") no-repeat center center fixed;
      -webkit-background-size: cover;
      -moz-background-size: cover;
      -o-background-size: cover;
      background-size: cover;
    }
    .desktop-shell { display: block; }
    .mobile-shell { display: none; }
    .btn-landing {
      font-family: 'comic sans ms';
      margin-top: 20px;
      font-size: 25px;
      font-weight: bold;
      color: #6F9F07 !important;
      padding: 5px;
      border-radius: 15px 0 15px 0;
      background-color: #FFF;
      border: 5px solid #61b846;
      width: 300px;
    }
    .cont {
      background-color: #B104214D;
      width: 360px;
      height: auto;
      border-radius: 15px;
      margin-left: 40px;
      box-shadow: 0 0 6px 0 rgba(157, 96, 212, 0.5);
      border: solid 4px transparent;
      background-image: linear-gradient(rgba(255,255,255,0), rgba(255,255,255,0)), linear-gradient(101deg, #f36c26, #61b846);
      background-origin: border-box;
      box-shadow: 2px 1000px 1px #B104214D inset;
    }
    .stock-app {
      max-width: 540px;
      margin: 0 auto;
      padding: 0 8px 22px;
    }
    .stock-topbar {
      position: sticky;
      top: 0;
      z-index: 20;
      margin-bottom: 12px;
      padding: 14px 16px;
      border-radius: 0 0 20px 20px;
      background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
      color: #fff8ea;
      box-shadow: 0 16px 28px rgba(74,37,11,0.18);
    }
    .stock-topbar h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 900;
      text-transform: uppercase;
    }
    .stock-topbar p {
      margin: 5px 0 0;
      font-size: 12px;
      font-weight: 700;
    }
    .stock-top-meta {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 6px;
      margin-top: 10px;
    }
    .stock-top-chip {
      padding: 8px 6px;
      border-radius: 10px;
      background: rgba(255,255,255,0.14);
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.16);
      text-align: center;
      min-width: 0;
    }
    .stock-top-chip strong {
      display: block;
      font-size: 9px;
      font-weight: 800;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      opacity: 0.9;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .stock-top-chip span {
      display: block;
      margin-top: 4px;
      font-size: 13px;
      font-weight: 900;
      line-height: 1.15;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .stock-top-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-top: 8px;
    }
    .stock-top-actions a {
      min-height: 42px;
      border-radius: 10px;
      background: rgba(255,255,255,0.16);
      color: #fff !important;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none !important;
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
    }
    .btn-added{background:linear-gradient(135deg,#7e2014,#d16a28)!important;color:#fff!important;border-color:#f1b15b!important}.stock-added-modal-content{border:0;border-radius:16px;overflow:hidden}.stock-added-modal-content .modal-header{padding:18px 20px;color:#fff;background:linear-gradient(135deg,#7e2014,#d16a28)}.stock-added-modal-content .modal-title{font-weight:900}.stock-added-modal-content .modal-header p{margin:4px 0 0;font-size:11px;opacity:.9}.stock-added-total{padding:14px;border-radius:12px;background:#fff1df;text-align:center;color:#7e3a20}.stock-added-total small{display:block;font-size:10px;font-weight:900}.stock-added-total strong{display:block;margin-top:4px;font-size:28px}.stock-added-grid{display:grid;grid-template-columns:1fr;gap:8px;margin-top:12px}.stock-added-grid>div{display:grid;grid-template-columns:1fr auto;gap:4px;padding:10px 12px;border:1px solid #ecd9c8;border-radius:10px;background:#fffaf5}.stock-added-grid small{font-weight:900;color:#7e3a20}.stock-added-grid b{text-align:right;color:#a33b1e;font-size:16px}.stock-added-grid em{grid-column:1/-1;color:#8d7a70;font-size:10px;font-style:normal}.stock-added-close,.stock-added-link{border-radius:7px;font-weight:800}.stock-added-link{background:#8f301f;color:#fff!important}
    .stock-added-items{margin-top:14px}.stock-added-items h5{margin:0 0 7px;color:#7e3a20;font-size:11px;font-weight:900;letter-spacing:.08em}.stock-added-items table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #ecd9c8;border-radius:5px;overflow:hidden;background:#fff;font-size:11px}.stock-added-items th{padding:7px;background:#fff4e8;color:#7e3a20;text-align:left}.stock-added-items td{padding:7px;border-top:1px solid #f0e5dc}.stock-added-items th:nth-child(2),.stock-added-items th:nth-child(3),.stock-added-items td:nth-child(2),.stock-added-items td:nth-child(3){text-align:right}.stock-added-empty{padding:12px;border:1px dashed #dfc6b6;border-radius:5px;color:#927c70;text-align:center;font-size:11px}
    .stock-added-dialog{width:calc(100vw - 80px);max-width:1100px}.stock-added-modal-content{font-size:13px}.stock-added-items table{table-layout:auto}.stock-added-items th,.stock-added-items td{vertical-align:middle}.stock-added-items td:first-child{font-weight:700;min-width:220px}.stock-added-items th:nth-child(n+2),.stock-added-items td:nth-child(n+2){white-space:nowrap;text-align:right}.stock-added-items td:nth-child(3){color:#176b35;font-weight:800}
    @media (max-width:767px){.stock-added-dialog{width:calc(100vw - 18px);max-width:none;margin:9px auto}.stock-added-modal-content .modal-header{padding:12px 14px}.stock-added-modal-content .modal-title{font-size:15px;line-height:1.25}.stock-added-modal-content .modal-body{padding:10px}.stock-added-items{overflow-x:hidden}.stock-added-items table{width:100%;min-width:0;table-layout:fixed;font-size:10px}.stock-added-items th,.stock-added-items td{padding:7px 3px;white-space:normal!important;word-break:break-word;overflow-wrap:anywhere}.stock-added-items th:first-child,.stock-added-items td:first-child{width:43%;min-width:0;text-align:left}.stock-added-items th:nth-child(2),.stock-added-items td:nth-child(2){width:18%;text-align:right}.stock-added-items th:nth-child(3),.stock-added-items td:nth-child(3){width:19%;text-align:right}.stock-added-items th:nth-child(4),.stock-added-items td:nth-child(4){width:20%;text-align:right}.stock-added-items td strong{font-size:10px}.stock-added-modal-content .modal-footer{padding:9px 12px}}
    .stock-info-card,
    .stock-menu-card {
      background: rgba(255,255,255,0.92);
      border-radius: 18px;
      box-shadow: 0 14px 28px rgba(0,0,0,0.08);
      padding: 14px;
      margin-bottom: 12px;
    }
    .stock-section-title {
      margin: 0 0 12px;
      font-size: 13px;
      font-weight: 900;
      letter-spacing: 0.08em;
      color: #7e3a20;
      text-transform: uppercase;
    }
    .stock-stats {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    .stock-stat {
      padding: 12px;
      border-radius: 16px;
      background: linear-gradient(180deg, #fffdfa 0%, #f6edd7 100%);
      border: 1px solid rgba(146,110,52,0.12);
    }
    .stock-stat strong {
      display: block;
      font-size: 11px;
      color: #785832;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .stock-stat span {
      display: block;
      margin-top: 6px;
      font-size: 24px;
      font-weight: 900;
      color: #332411;
    }
    .stock-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    .stock-btn {
      min-height: 104px;
      border-radius: 20px;
      padding: 14px 12px;
      text-decoration: none !important;
      color: #fff !important;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 14px 24px rgba(64,45,15,0.16);
      position: relative;
      overflow: hidden;
    }
    .stock-btn i,
    .stock-btn .glyphicon {
      position: relative;
      z-index: 1;
      font-size: 22px;
      margin-bottom: 8px;
      opacity: 0.95;
    }
    .stock-btn:before {
      content: "";
      position: absolute;
      top: -20px;
      right: -18px;
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: rgba(255,255,255,0.14);
    }
    .stock-btn span {
      display: block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      opacity: 0.9;
    }
    .stock-btn b {
      position: relative;
      z-index: 1;
      font-size: 18px;
      line-height: 1.15;
      font-weight: 900;
    }
    .btn-buy { background: linear-gradient(135deg, #7d3521 0%, #cb6337 100%); }
    .btn-po-receive { background: linear-gradient(135deg, #5f4c12 0%, #cf9f2d 100%); }
    .btn-req { background: linear-gradient(135deg, #355f16 0%, #58a03f 100%); }
    .btn-transfer { background: linear-gradient(135deg, #22515e 0%, #3e8f9e 100%); }
    .btn-receive { background: linear-gradient(135deg, #4b6e1a 0%, #7bb23c 100%); }
    .btn-tots { background: linear-gradient(135deg, #7b2048 0%, #c14f77 100%); }
    .btn-kitchen { background: linear-gradient(135deg, #744b0e 0%, #d89320 100%); }
    .stock-note {
      margin-top: 12px;
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(255,255,255,0.72);
      color: #6e5126;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      text-align: center;
    }
    .stock-confirm-shell .modal-content {
      border-radius: 18px;
      overflow: hidden;
      border: 0;
      box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
    }
    .stock-confirm-shell .modal-header {
      border-bottom: 0;
      padding: 14px 16px;
      background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
      color: #fff8ea;
    }
    .stock-confirm-shell .close {
      color: #fff;
      opacity: 1;
    }
    .stock-confirm-shell .modal-title {
      font-size: 20px;
      font-weight: 900;
      text-transform: uppercase;
    }
    .stock-confirm-body {
      background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
      padding: 18px 16px 16px;
      color: #4f3618;
    }
    .stock-confirm-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 34px;
      padding: 0 12px;
      border-radius: 999px;
      background: #fff;
      color: #8a1232;
      font-size: 11px;
      font-weight: 900;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      box-shadow: 0 8px 16px rgba(86, 61, 24, 0.1);
    }
    .stock-confirm-body h4 {
      margin: 12px 0 8px;
      font-size: 22px;
      font-weight: 900;
      color: #382819;
    }
    .stock-confirm-body p {
      margin: 0;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.5;
    }
    .stock-confirm-points {
      margin-top: 14px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
    }
    .stock-confirm-point {
      padding: 10px 12px;
      border-radius: 12px;
      background: rgba(255,255,255,0.85);
      color: #6a4d00;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      border: 1px solid rgba(146,110,52,0.12);
    }
    .stock-confirm-shell .modal-footer {
      border-top: 0;
      background: #fffdf8;
      padding: 12px 14px 14px;
      display: flex;
      gap: 10px;
    }
    .stock-confirm-btn {
      min-height: 46px;
      border: 0;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 900;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none !important;
      flex: 1 1 0;
    }
    .stock-confirm-close {
      background: #ead6d6;
      color: #7e2014 !important;
    }
    .stock-confirm-go {
      background: linear-gradient(135deg, #355f16 0%, #58a03f 100%);
      color: #fff !important;
    }
    #modal .modal-dialog {
      width: 92vw;
      max-width: 420px;
      margin: 24px auto;
    }
    @media (max-width: 991px) {
      body {
        min-height: 100vh;
        background: radial-gradient(circle at top, rgba(246,208,137,0.22), transparent 32%), linear-gradient(180deg, #f6f0d7 0%, #efe3bf 36%, #d3b67d 100%);
      }
      .desktop-shell { display: none; }
      .mobile-shell { display: block; }
      .stock-app {
        padding-left: 5px;
        padding-right: 5px;
      }
      .stock-grid {
        grid-template-columns: 1fr 1fr;
      }
      .stock-top-meta {
        grid-template-columns: 1fr 1fr 1fr;
      }
      #modal .modal-dialog {
        width: 92vw;
        max-width: 380px;
        margin: 16px auto;
      }
      .stock-confirm-shell .modal-footer {
        flex-direction: column;
      }
      .stock-btn {
        min-height: 96px;
      }
    }
    @media (max-width: 420px) {
      .stock-top-actions {
        grid-template-columns: 1fr 1fr;
      }
    }
  </style>
</head>
<body class="fixed-sn deep-orange-skin">
  <div class="desktop-shell">
    <header>
      <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
        <ul class="nav navbar-nav navbar-left">
          <li class="nav-item" style="margin-left: 10px; margin-top: 10px;">
            <a href="../dash/dash.php" name="open" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;">HOME</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
          <li class="nav-item">
            <a href="../log_out.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
          </li>
        </ul>
      </nav>
    </header>
    <main class="container-fluid">
      <div class="container" style="margin-top: 80px;">
        <div class="row">
          <div class="col-xs-4"></div>
          <div class="col-md-3 col-sm-3 col-xs-3 cont">
            <div style="margin-left: 8px; margin-right: 8px; margin-top: 10px; margin-bottom: 20px;">
              <?php if ($front_purchase_allowed) { ?>
              <a href="#" class="btn btn-sm btn-success btn-landing stock-buy-trigger" data-toggle="modal" data-target="#modal">BUY STOCK</a>
              <?php } ?>
              <a data-toggle="modal" data-target="#modal" class="btn btn-sm btn-success btn-landing" id="counter-req">REQUISITION</a>
              <a data-toggle="modal" data-target="#modal" id="trans" class="btn btn-sm btn-success btn-landing">STOCK TRANSFER</a>
              <a href="#" class="btn btn-sm btn-success btn-landing stock-confirm-trigger" data-title="Receive Issued Stock" data-badge="Receive" data-message="Open the stock receive page?" data-point-one="Review stock issued to this counter before receiving" data-point-two="Continue to the receive screen" data-href="../rec/rec_pending.php">RECEIVE</a>
              <a href="#" class="btn btn-sm btn-success btn-landing stock-confirm-trigger" data-title="Generate Units" data-badge="Units" data-message="Open the units generation page?" data-point-one="Prepare the stock units process for the current session" data-point-two="Continue to the units screen" data-href="../tot/tot_init.php">GENERATE UNITS</a>
              <a href="#" class="btn btn-sm btn-success btn-landing stock-confirm-trigger" data-title="Kitchen Receive" data-badge="Kitchen" data-message="Open the kitchen receive page?" data-point-one="Review kitchen-issued stock before posting the receipt" data-point-two="Continue to the kitchen receive screen" data-href="../kit/kit_init.php">KITCHEN RECEIVE</a>
              <a href="#stock-added-modal" data-toggle="modal" class="btn btn-sm btn-success btn-landing stock-added-trigger">ADDED TO STOCK</a>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div class="mobile-shell">
    <div class="stock-app">
      <div class="stock-topbar">
        <h1>Stock</h1> 
        <div class="stock-top-meta">
          <div class="stock-top-chip">
            <strong>Counter</strong>
            <span><?php echo $counter_name; ?></span>
          </div>
          <div class="stock-top-chip">
            <strong>Shift</strong>
            <span><?php echo !empty($shift) ? $shift : 'Closed'; ?></span>
          </div>
          <div class="stock-top-chip">
            <strong>Stock Mode</strong>
            <span><?php echo (int)$take === 1 ? 'Open' : 'Ready'; ?></span>
          </div>
        </div>
        <div class="stock-top-actions">
          <a href="../dash/dash.php">Home</a>
          <a href="../log_out.php">Log Out</a>
        </div>
      </div>

      <div class="stock-menu-card">
        <p class="stock-section-title">Stock Actions</p>
        <div class="stock-grid">
          <?php if ($front_purchase_allowed) { ?>
          <a href="#" class="stock-btn btn-buy stock-buy-trigger" data-toggle="modal" data-target="#modal"><span class="glyphicon glyphicon-shopping-cart"></span><span>Purchase</span><b>Buy Stock</b></a>
          <?php } ?>
          <a data-toggle="modal" data-target="#modal" id="counter-req-mobile" class="stock-btn btn-req"><span class="glyphicon glyphicon-export"></span><span>Counter</span><b>Requisition</b></a>
          <a data-toggle="modal" data-target="#modal" id="trans-mobile" class="stock-btn btn-transfer"><span class="glyphicon glyphicon-transfer"></span><span>Move</span><b>Stock Transfer</b></a>
          <a href="#" class="stock-btn btn-receive stock-confirm-trigger" data-title="Receive Issued Stock" data-badge="Receive" data-message="Open the stock receive page?" data-point-one="Review stock issued to this counter before receiving" data-point-two="Continue to the receive screen" data-href="../rec/rec_pending.php"><span class="glyphicon glyphicon-download-alt"></span><span>Receive</span><b>Issued Stock</b></a>
          <a href="#" class="stock-btn btn-tots stock-confirm-trigger" data-title="Generate Units" data-badge="Units" data-message="Open the units generation page?" data-point-one="Prepare the stock units process for the current session" data-point-two="Continue to the units screen" data-href="../tot/tot_init.php"><span class="glyphicon glyphicon-stats"></span><span>Report</span><b>Generate Units</b></a>
          <a href="#" class="stock-btn btn-kitchen stock-confirm-trigger" data-title="Kitchen Receive" data-badge="Kitchen" data-message="Open the kitchen receive page?" data-point-one="Review kitchen-issued stock before posting the receipt" data-point-two="Continue to the kitchen receive screen" data-href="../kit/kit_init.php"><span class="glyphicon glyphicon-cutlery"></span><span>Kitchen</span><b>Kitchen Receive</b></a>
          <a href="#stock-added-modal" data-toggle="modal" class="stock-btn btn-added"><span class="glyphicon glyphicon-stats"></span><span>Summary</span><b>Added to Stock</b></a>
        </div>
      </div>

      <div class="stock-note">Desktop keeps the old stock dashboard. Mobile uses this touch version.</div>
    </div>
  </div>

  <div id="stock-added-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg stock-added-dialog"><div class="modal-content stock-added-modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><span class="glyphicon glyphicon-stats"></span> Items Added to Main Counter Today</h4><p>Added quantity, current counter stock, and value</p></div><div class="modal-body"><div class="stock-added-items"><h5>ITEMS ADDED TODAY</h5><?php if (!$stock_added_items) { ?><div class="stock-added-empty">No items added to the main counter today.</div><?php } else { ?><table><thead><tr><th>Item</th><th>Added Qty</th><th>Current Stock</th><th>Amount</th></tr></thead><tbody><?php foreach ($stock_added_items as $addedItem) { ?><tr><td><?php echo htmlspecialchars($addedItem['name']); ?></td><td><?php echo number_format($addedItem['qty'], 3); ?></td><td><strong><?php echo number_format((float)($addedItem['current_qty'] ?? 0), 3); ?></strong></td><td>KES <?php echo number_format($addedItem['amount'], 2); ?></td></tr><?php } ?></tbody></table><?php } ?></div></div><div class="modal-footer"><button type="button" class="btn stock-added-close" data-dismiss="modal">Close</button></div></div></div>
  </div>
  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" id="load"></div>
  </div>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function() {
    function openTransferModal() {
      $.ajax({
        url: '../trans/API/trans_api.php',
        type: 'POST',
        data: { action: 'counter_picker_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html('');
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    }

    function openReqModal() {
      $.ajax({
        url: '../gwitia/counter_req.php',
        typr: 'POST',
        data: '',
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html('');
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    }

    function openBuyConfirmModal() {
      $('#load').html(
        '<div class="stock-confirm-shell">' +
          '<div class="modal-content">' +
            '<div class="modal-header">' +
              '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
              '<h4 class="modal-title">Start Purchase</h4>' +
            '</div>' +
            '<div class="stock-confirm-body">' +
              '<span class="stock-confirm-badge">Buy Stock</span>' +
              '<h4>Open the purchase screen?</h4>' +
              '<p>This will prepare the stock purchase draft, then take you into the purchase page.</p>' +
              '<div class="stock-confirm-points">' +
                '<div class="stock-confirm-point">Creates or resumes your active purchase draft</div>' +
                '<div class="stock-confirm-point">Continues to the stock buying screen</div>' +
              '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
              '<button type="button" class="stock-confirm-btn stock-confirm-close" data-dismiss="modal">Close</button>' +
              '<a href="../kug/buy_init.php" class="stock-confirm-btn stock-confirm-go">Continue</a>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
      $('#modal').modal('show');
    }

    function openStockConfirmModal(options) {
      var title = options.title || 'Continue';
      var badge = options.badge || 'Action';
      var message = options.message || 'Open this page?';
      var pointOne = options.pointOne || 'Continue to the next screen';
      var pointTwo = options.pointTwo || 'Proceed with this stock action';
      var href = options.href || '#';
      $('#load').html(
        '<div class="stock-confirm-shell">' +
          '<div class="modal-content">' +
            '<div class="modal-header">' +
              '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
              '<h4 class="modal-title">' + title + '</h4>' +
            '</div>' +
            '<div class="stock-confirm-body">' +
              '<span class="stock-confirm-badge">' + badge + '</span>' +
              '<h4>' + message + '</h4>' +
              '<div class="stock-confirm-points">' +
                '<div class="stock-confirm-point">' + pointOne + '</div>' +
                '<div class="stock-confirm-point">' + pointTwo + '</div>' +
              '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
              '<button type="button" class="stock-confirm-btn stock-confirm-close" data-dismiss="modal">Close</button>' +
              '<a href="' + href + '" class="stock-confirm-btn stock-confirm-go">Continue</a>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
      $('#modal').modal('show');
    }

    $(document).on('click','#trans, #trans-mobile', function(){ openTransferModal(); });
    $(document).on('click','#counter-req, #counter-req-mobile', function(){ openReqModal(); });
    $(document).on('click','.stock-buy-trigger', function(e){
      e.preventDefault();
      openBuyConfirmModal();
    });
    $(document).on('click','.stock-confirm-trigger', function(e){
      e.preventDefault();
      openStockConfirmModal({
        title: $(this).data('title'),
        badge: $(this).data('badge'),
        message: $(this).data('message'),
        pointOne: $(this).data('point-one'),
        pointTwo: $(this).data('point-two'),
        href: $(this).data('href')
      });
    });
  });
</script>
