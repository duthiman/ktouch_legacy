<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

$req_id = isset($_GET['req']) ? $_GET['req'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$counter = $user_id ? user_counter($user_id) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Receive Requisition</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <link href="../../assets/plugins/font-awesome/5.1/css/all.css" rel="stylesheet" />
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
  <style>
    body {
      background:
        radial-gradient(circle at top left, rgba(231,163,58,0.18), transparent 26%),
        radial-gradient(circle at bottom right, rgba(126,32,20,0.14), transparent 24%),
        linear-gradient(180deg, #f7f2e1 0%, #ead6a6 100%);
    }
    .desktop-shell {
      display: block;
    }
    .mobile-shell {
      display: none;
    }
    .btn {
      cursor: pointer;
    }
    .info {
      border-color: #2196F3;
      color: dodgerblue;
    }
    .info:hover {
      background: #2196F3;
      color: white;
    }
    .success {
      border-color: #04AA6D;
      color: green;
    }
    .success:hover {
      background-color: #04AA6D;
      color: white;
    }
    .danger {
      border-color: #ff0505;
      color: red;
    }
    .danger:hover {
      background-color: #ff0505;
      color: white;
    }
    .rec-page-shell {
      max-width: 1180px;
      margin: 0 auto;
      padding: 0 18px 24px;
    }
    .rec-desktop-hero {
      display: flex;
      justify-content: space-between;
      gap: 18px;
      align-items: center;
      padding: 18px 22px;
      border-radius: 18px;
      background: linear-gradient(135deg, rgba(126,32,20,0.96) 0%, rgba(185,72,40,0.94) 48%, rgba(231,163,58,0.92) 100%);
      box-shadow: 0 24px 44px rgba(91, 46, 17, 0.22);
      color: #fff8ea;
      margin-bottom: 18px;
    }
    .rec-desktop-title {
      margin: 0;
      font-size: 26px;
      font-weight: 900;
      letter-spacing: 0.02em;
    }
    .rec-desktop-sub {
      margin-top: 4px;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      opacity: 0.92;
    }
    .rec-desktop-metrics {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .rec-metric {
      min-width: 110px;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(255,255,255,0.16);
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.15);
      text-align: center;
    }
    .rec-metric strong {
      display: block;
      font-size: 10px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }
    .rec-metric span {
      display: block;
      margin-top: 4px;
      font-size: 22px;
      font-weight: 900;
    }
    .rec-panel {
      width: 100%;
      background: rgba(255,255,255,0.95);
      border-radius: 18px;
      box-shadow: 0 18px 36px rgba(77,55,21,0.12);
      overflow: hidden;
      border: 1px solid rgba(110, 75, 24, 0.10);
    }
    .rec-panel-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      padding: 16px 20px;
      background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(247,241,227,0.95));
      border-bottom: 1px solid rgba(110, 75, 24, 0.08);
    }
    .rec-panel-head h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 900;
      color: #6d2f16;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }
    .rec-panel-pill {
      min-width: 54px;
      padding: 6px 10px;
      border-radius: 999px;
      background: #fff0c7;
      color: #8a4b16;
      font-size: 11px;
      font-weight: 900;
      text-align: center;
    }
    .rec-panel-body {
      padding: 18px;
    }
    .rec-table-wrap {
      overflow-x: auto;
    }
    .rec-table th,
    .rec-table td {
      white-space: nowrap;
      vertical-align: middle !important;
    }
    .rec-table thead th {
      border-top: 0 !important;
      color: #8a6a3a;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.07em;
    }
    .rec-table tbody td {
      font-size: 13px;
      color: #382819;
      font-weight: 700;
    }
    .rec-desktop-action {
      min-height: 42px;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
      text-decoration: none !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
      padding: 0 14px;
      border: 0;
    }
    .rec-desktop-action.receive {
      background: linear-gradient(135deg, #0f4c81 0%, #2196F3 100%);
      color: #fff !important;
    }
    .rec-desktop-action.stock {
      background: linear-gradient(135deg, #2f5d17 0%, #58a03f 100%);
      color: #fff !important;
    }
    .rec-desktop-foot {
      display: flex;
      justify-content: center;
      padding: 18px;
      border-top: 1px solid rgba(110, 75, 24, 0.08);
      background: rgba(255,250,240,0.8);
    }
    .rec-mobile-shell {
      max-width: 540px;
      margin: 0 auto;
      padding: 0 5px 24px;
    }
    .rec-topbar {
      position: sticky;
      top: 10px;
      z-index: 20;
      margin-bottom: 12px;
      padding: 10px 14px 14px;
      border-radius: 10px;
      background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
      box-shadow: 0 18px 30px rgba(74, 37, 11, 0.18);
      color: #fff8ea;
    }
    .rec-brand {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 10px;
      align-items: center;
    }
    .rec-brand-main {
      min-width: 0;
    }
    .rec-counter-line,
    .rec-sub-line {
      display: block;
      margin: 0;
      color: #fff8ea;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .rec-sub-line {
      margin-top: 4px;
      opacity: 0.92;
    }
    .rec-badge {
      min-width: 86px;
      padding: 7px 9px;
      border-radius: 10px;
      text-align: center;
      background: rgba(255,255,255,0.15);
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
    }
    .rec-badge strong {
      display: block;
      font-size: 9px;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .rec-badge span {
      display: block;
      margin-top: 2px;
      font-size: 18px;
      font-weight: 900;
    }
    .rec-top-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-top: 12px;
    }
    .rec-top-btn {
      min-height: 46px;
      border: 0;
      border-radius: 10px;
      background: rgba(255,255,255,0.16);
      color: #fff !important;
      font-size: 13px;
      font-weight: 800;
      text-transform: uppercase;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      text-decoration: none !important;
    }
    .rec-card {
      background: rgba(255,255,255,0.92);
      border: 1px solid rgba(108, 78, 31, 0.08);
      border-radius: 10px;
      box-shadow: 0 16px 28px rgba(86, 61, 24, 0.12);
      padding: 14px;
      margin-bottom: 10px;
    }
    .rec-section-title {
      margin: 0 0 10px;
      font-size: 13px;
      font-weight: 900;
      letter-spacing: 0.08em;
      color: #7e3a20;
      text-transform: uppercase;
    }
    .rec-empty {
      margin: 0;
      padding: 12px;
      border-radius: 10px;
      background: #fff7dc;
      color: #8a6a3a;
      font-size: 13px;
      font-weight: 800;
      text-align: center;
    }
    .rec-item-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .rec-item-card {
      border-radius: 12px;
      background: #fff;
      border: 1px solid rgba(108, 78, 31, 0.08);
      box-shadow: 0 8px 18px rgba(86, 61, 24, 0.08);
      padding: 12px;
    }
    .rec-item-top {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .rec-item-no {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: #f9e7cd;
      color: #7e3a20;
      font-size: 13px;
      font-weight: 900;
      display: flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
    }
    .rec-item-copy {
      flex: 1 1 auto;
      min-width: 0;
    }
    .rec-item-copy strong,
    .rec-item-copy span {
      display: block;
    }
    .rec-item-copy strong {
      font-size: 14px;
      font-weight: 900;
      color: #382819;
      line-height: 1.25;
    }
    .rec-item-copy span {
      margin-top: 4px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.08em;
      color: #8a6a3a;
      text-transform: uppercase;
    }
    .rec-compact-table {
      width: 100%;
      margin-top: 12px;
      border-collapse: separate;
      border-spacing: 0;
      border: 1px solid rgba(126, 58, 32, 0.08);
      border-radius: 10px;
      overflow: hidden;
    }
    .rec-compact-table td {
      width: 25%;
      padding: 9px 8px;
      background: #fbf7eb;
      border-right: 1px solid rgba(126, 58, 32, 0.08);
      border-bottom: 1px solid rgba(126, 58, 32, 0.08);
      text-align: center;
    }
    .rec-compact-table tr:last-child td {
      border-bottom: 0;
    }
    .rec-compact-table td:last-child {
      border-right: 0;
    }
    .rec-compact-table label {
      display: block;
      margin: 0;
      font-size: 10px;
      font-weight: 900;
      letter-spacing: 0.08em;
      color: #8a6a3a;
      text-transform: uppercase;
    }
    .rec-compact-table b {
      display: block;
      margin-top: 5px;
      font-size: 14px;
      color: #382819;
      line-height: 1.2;
    }
    .rec-compact-table .accent {
      background: #fff0e7;
    }
    .rec-item-actions {
      margin-top: 12px;
      display: flex;
      gap: 8px;
    }
    .rec-item-btn {
      min-height: 42px;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
      text-decoration: none !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 1 1 auto;
      white-space: nowrap;
    }
    .rec-item-btn.receive {
      background: linear-gradient(135deg, #0f4c81 0%, #2196F3 100%);
      color: #fff !important;
      border: 0;
    }
    .rec-item-btn.stock {
      background: linear-gradient(135deg, #2f5d17 0%, #58a03f 100%);
      color: #fff !important;
      border: 0;
    }
    .rec-summary-strip {
      padding: 12px;
      border-radius: 10px;
      background: linear-gradient(135deg, #8a1232 0%, #c3385c 100%);
      color: #fff;
      text-align: center;
      margin-bottom: 10px;
    }
    .rec-summary-strip b {
      display: block;
      font-size: 14px;
      line-height: 1.4;
    }
    .rec-footer-action {
      width: 100%;
      min-height: 48px;
      border-radius: 10px;
      background: linear-gradient(135deg, #2f5d17 0%, #58a03f 100%);
      color: #fff !important;
      font-size: 13px;
      font-weight: 900;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none !important;
      border: 0;
    }
    .mobile-modal-dialog {
      width: 92vw;
      max-width: 340px;
      margin: 20px auto;
    }
    .mobile-modal-dialog.wide {
      max-width: 860px;
    }
    @media (max-width: 991px) {
      body {
        background: radial-gradient(circle at top, rgba(246,208,137,0.22), transparent 32%), linear-gradient(180deg, #f6f0d7 0%, #efe3bf 36%, #d3b67d 100%);
      }
      header {
        display: none;
      }
      .desktop-shell {
        display: none;
      }
      .mobile-shell {
        display: block;
      }
      #content.content {
        padding: 0 !important;
        margin-top: 0 !important;
      }
      #modal .modal-dialog {
        width: 92vw;
        max-width: 340px;
        margin: 16px auto;
      }
      #modal .modal-dialog.wide {
        max-width: 94vw;
      }
      .rec-page-shell {
        padding: 0;
      }
    }
  </style>
</head>

<body>
<header style="margin-bottom:85px;">
  <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
    <ul class="nav navbar-nav navbar-left">
      <li class="nav-item">
        <a href="../dash/dash.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">HOME</a>
      </li>
    </ul>
    <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
      <li class="nav-item">
        <a href="../log_out.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
      </li>
    </ul>
  </nav>
</header>

  <div id="page-container" class="page-without-sidebar page-header-fixed">
  <div id="content" class="content">
    <div class="desktop-shell">
      <div class="rec-page-shell">
        <div class="rec-desktop-hero">
          <div>
            <h2 class="rec-desktop-title"><?php echo $counter ? get_counter($counter) : 'Counter'; ?> Receive Desk</h2>
            <div class="rec-desktop-sub">Requisition #<?php echo $req_id; ?> stock receiving and add-to-stock actions</div>
          </div>
          <div class="rec-desktop-metrics">
            <div class="rec-metric">
              <strong>Req No</strong>
              <span><?php echo $req_id; ?></span>
            </div>
            <div class="rec-metric">
              <strong>Counter</strong>
              <span><?php echo $counter ? get_counter($counter) : '-'; ?></span>
            </div>
            <div class="rec-metric">
              <strong>Items</strong>
              <span><?php echo (int)column_count('kp_counter_req_items','id',"req_id='$req_id' AND s2s=2"); ?></span>
            </div>
          </div>
        </div>
        <div class="panel rec-panel">
          <div class="rec-panel-head">
            <h3>Receive Requisition Items</h3>
            <span class="rec-panel-pill"><?php echo $req_id; ?></span>
          </div>
          <div class="panel-body rec-panel-body">
          <div class="rec-table-wrap">
            <table class="table table-striped rec-table" style="padding: 5px 5px;">
              <thead>
                <tr>
                  <th>#</th>
                  <th>ITEM NAME</th>
                  <th>ORDERED QTY</th>
                  <th>ISSUED QTY</th>
                  <th>RECEIVED QTY</th>
                  <th>CURRENT STOCK</th>
                  <th>NEW STOCK</th>
                  <th>ACTION</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sales = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' AND s2s=2 ");
                if (mysqli_num_rows($sales) < 1) {
                  ?>
                  <tr>
                    <td colspan="8" class="text-center">There are no items on this order</td>
                  </tr>
                  <?php
                } else {
                  $no = 0;
                  while ($sa = mysqli_fetch_assoc($sales)) {
                    $item_id = $sa['item_id'];
                    $rec_status = $sa['rec_status'];
                    $counter_row = $sa['counter'];
                    $column = "cntr".$counter_row."_qty";
                    $current_stock = get_column('kp_items',$column,"item_id='$item_id'");
                    $new_stock = $current_stock + $sa['issue_qty'];
                    $no++;
                    ?>
                    <tr>
                      <td><?php echo $no; ?></td>
                      <td><?php echo get_item($item_id); ?></td>
                      <td><?php echo $sa['req_qty']; ?></td>
                      <td><?php echo $sa['issue_qty']; ?></td>
                      <td><?php echo $sa['rec_qty']; ?></td>
                      <td><b style="color:brown"><?php echo $current_stock; ?></b></td>
                      <td><b style="color:brown"><?php echo $new_stock; ?></b></td>
                      <td>
                        <?php if ($rec_status == 1) { ?>
                          <a class="rec-desktop-action stock" href="to_store.php?req_id=<?php echo $req_id; ?>&item_id=<?php echo $item_id; ?>&qty=<?php echo $sa['issue_qty']; ?>">Add To Stock</a>
                        <?php } else { ?>
                          <a data-toggle="modal" data-target="#modal" data-id="<?php echo $item_id; ?>" data-id2="<?php echo $req_id; ?>" class="rec-desktop-action receive rec-open-item">Receive</a>
                        <?php } ?>
                      </td>
                    </tr>
                    <?php
                  }
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="rec-desktop-foot">
          <a data-toggle="modal" data-target="#modal" class="rec-desktop-action stock rec-open-all" data-id2="<?php echo $req_id; ?>">Receive All & Add To Stock</a>
        </div>
        </div>
      </div>
    </div>

    <div class="mobile-shell">
      <div class="rec-mobile-shell">
        <div class="rec-topbar">
          <div class="rec-brand">
            <div class="rec-brand-main">
              <span class="rec-counter-line"><?php echo $counter ? get_counter($counter) : 'Counter'; ?></span>
              <span class="rec-sub-line">Receive Requisition</span>
            </div>
            <div class="rec-badge">
              <strong>Req No</strong>
              <span><?php echo $req_id; ?></span>
            </div>
          </div>
          <div class="rec-top-actions">
            <a href="rec_pending.php" class="rec-top-btn">Back</a>
            <a href="../dash/dash.php" class="rec-top-btn">Home</a>
          </div>
        </div>

        <div class="rec-card">
          <div class="rec-summary-strip">
            <b><?php echo $counter ? get_counter($counter) : 'Receiving Counter'; ?></b>
            <b>Requisition No: <?php echo $req_id; ?></b>
          </div>
          <p class="rec-section-title">Items To Receive</p>
          <div class="rec-item-list">
            <?php
            $sales_mobile = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' AND s2s=2 ");
            if (mysqli_num_rows($sales_mobile) < 1) {
              ?>
              <p class="rec-empty">There are no items on this order.</p>
              <?php
            } else {
              $no = 0;
              while ($sa = mysqli_fetch_assoc($sales_mobile)) {
                $item_id = $sa['item_id'];
                $rec_status = $sa['rec_status'];
                $counter_row = $sa['counter'];
                $column = "cntr".$counter_row."_qty";
                $current_stock = get_column('kp_items',$column,"item_id='$item_id'");
                $new_stock = $current_stock + $sa['issue_qty'];
                $no++;
                ?>
                <div class="rec-item-card">
                  <div class="rec-item-top">
                    <div class="rec-item-no"><?php echo $no; ?></div>
                    <div class="rec-item-copy">
                      <strong><?php echo get_item($item_id); ?></strong>
                      <span><?php echo $rec_status == 1 ? 'Ready To Add To Stock' : 'Pending Receive'; ?></span>
                    </div>
                  </div>
                  <table class="rec-compact-table">
                    <tr>
                      <td>
                        <label>Ordered</label>
                        <b><?php echo $sa['req_qty']; ?></b>
                      </td>
                      <td>
                        <label>Issued</label>
                        <b><?php echo $sa['issue_qty']; ?></b>
                      </td>
                      <td>
                        <label>Received</label>
                        <b><?php echo $sa['rec_qty']; ?></b>
                      </td>
                      <td class="accent">
                        <label>Status</label>
                        <b><?php echo $rec_status == 1 ? 'Ready' : 'Pending'; ?></b>
                      </td>
                    </tr>
                    <tr>
                      <td class="accent">
                        <label>Current</label>
                        <b><?php echo $current_stock; ?></b>
                      </td>
                      <td class="accent">
                        <label>New Stock</label>
                        <b><?php echo $new_stock; ?></b>
                      </td>
                      <td colspan="2">
                        <label>Action</label>
                        <b><?php echo $rec_status == 1 ? 'Add To Stock' : 'Receive First'; ?></b>
                      </td>
                    </tr>
                  </table>
                  <div class="rec-item-actions">
                    <?php if ($rec_status == 1) { ?>
                      <a class="rec-item-btn stock" href="to_store.php?req_id=<?php echo $req_id; ?>&item_id=<?php echo $item_id; ?>&qty=<?php echo $sa['issue_qty']; ?>">Add To Stock</a>
                    <?php } else { ?>
                      <button type="button" data-toggle="modal" data-target="#modal" data-id="<?php echo $item_id; ?>" data-id2="<?php echo $req_id; ?>" class="rec-item-btn receive rec-open-item">Receive</button>
                    <?php } ?>
                  </div>
                </div>
                <?php
              }
            }
            ?>
          </div>
        </div>

        <div class="rec-card">
          <button type="button" data-toggle="modal" data-target="#modal" class="rec-footer-action rec-open-all" data-id2="<?php echo $req_id; ?>">Receive All & Add To Stock</button>
        </div>
      </div>
    </div>
  </div>

  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog mobile-modal-dialog">
      <div class="modal-content" id="load"></div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $(document).on('click','.rec-open-item', function(){
      $('#modal .modal-dialog').removeClass('wide');
      var uid = $(this).data('id');
      var currentReqId = $(this).data('id2');
      $.ajax({
        url: 'rec_modal.php',
        typr: 'POST',
        data: {id:uid, req:currentReqId},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-body text-center"><b>Something went wrong. Please try again.</b></div>');
      });
    });

    $(document).on('click','.rec-open-all', function(){
      $('#modal .modal-dialog').addClass('wide');
      var currentReqId = $(this).data('id2');
      $.ajax({
        url: 'recallmodal.php',
        typr: 'POST',
        data: {req:currentReqId},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-body text-center"><b>Something went wrong. Please try again.</b></div>');
      });
    });
  });
</script>
</body>
</html>
