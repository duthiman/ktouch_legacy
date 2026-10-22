<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
if (!$user_id) {
  header("Location:../log_out.php");
  exit;
}

$counter = user_counter($user_id);
$pending_requisitions = mysqli_query($connection,"SELECT * FROM kp_counter_req WHERE rec_status=2 AND counter='$counter' AND issue_status=1 ORDER BY req_id ASC") or die("Could not fetch the requisitions");
$pending_transfers = mysqli_query($connection,"SELECT * FROM kp_counter_trans WHERE status=2 AND cntr_to='$counter' ORDER BY trans_id ASC") or die("Could not fetch the requisitions");
$req_count = mysqli_num_rows($pending_requisitions);
$trans_count = mysqli_num_rows($pending_transfers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Receive Pending</title>
  <link href="../../assets/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/css/default/desktop.css" rel="stylesheet" media="screen and (min-width: 768px)" />
  <link href="../../assets/css/default/mobile.css" rel="stylesheet" media="screen and (max-width: 767px)" />
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
  <style>
    body {
      background:
        radial-gradient(circle at top left, rgba(231,163,58,0.18), transparent 26%),
        radial-gradient(circle at bottom right, rgba(126,32,20,0.15), transparent 24%),
        linear-gradient(180deg, #f8f2df 0%, #ecd7aa 100%);
    }
    .desktop-shell {
      display: block;
    }
    .mobile-shell {
      display: none;
    }
    .rec-page-shell {
      max-width: 1180px;
      margin: 0 auto;
      padding: 18px;
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
    .rec-desktop-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      align-items: start;
    }
    .rec-panel-shell {
      width: 100%;
      background: rgba(255,255,255,0.95);
      border-radius: 18px;
      box-shadow: 0 16px 34px rgba(77, 55, 21, 0.12);
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
      min-width: 42px;
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
    .rec-table .btn {
      border-radius: 10px;
      font-weight: 800;
      padding: 8px 14px;
    }
    .rec-desktop-empty {
      margin: 0;
      padding: 22px 16px;
      border-radius: 14px;
      background: #fff6dc;
      color: #8a6a3a;
      font-size: 15px;
      font-weight: 800;
      text-align: center;
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
      min-width: 82px;
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
      gap: 8px;
    }
    .rec-item {
      display: flex;
      gap: 10px;
      align-items: center;
      padding: 12px;
      border-radius: 12px;
      background: #fff;
      border: 1px solid rgba(108, 78, 31, 0.08);
      box-shadow: 0 8px 18px rgba(86, 61, 24, 0.08);
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
      font-size: 13px;
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
    .rec-item-meta {
      display: flex;
      flex-direction: column;
      gap: 6px;
      align-items: flex-end;
      flex: 0 0 auto;
    }
    .rec-item-btn {
      min-height: 40px;
      padding: 0 14px;
      border-radius: 10px;
      background: linear-gradient(135deg, #2f5d17 0%, #58a03f 100%);
      color: #fff !important;
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
      text-decoration: none !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
    }
    @media (max-width: 991px) {
      body {
        background: radial-gradient(circle at top, rgba(246,208,137,0.22), transparent 32%), linear-gradient(180deg, #f6f0d7 0%, #efe3bf 36%, #d3b67d 100%);
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
    }
  </style>
</head>
<body class="fixed-sn deep-orange-skin">
  <div class="desktop-shell">
    <header>
      <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
        <ul class="navbar-nav navbar-left">
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
    <div id="content" class="content" style="margin-left:0;">
      <div class="rec-page-shell">
        <div class="rec-desktop-hero">
          <div>
            <h2 class="rec-desktop-title"><?php echo get_counter($counter); ?> Receive Desk</h2>
            <div class="rec-desktop-sub">Issued stock and incoming transfers waiting for action</div>
          </div>
          <div class="rec-desktop-metrics">
            <div class="rec-metric">
              <strong>Counter</strong>
              <span><?php echo get_counter($counter); ?></span>
            </div>
            <div class="rec-metric">
              <strong>Requisitions</strong>
              <span><?php echo $req_count; ?></span>
            </div>
            <div class="rec-metric">
              <strong>Transfers</strong>
              <span><?php echo $trans_count; ?></span>
            </div>
          </div>
        </div>

        <div class="rec-desktop-grid">
          <div class="rec-panel-shell">
            <div class="rec-panel-head">
              <h3>Issued Requisitions</h3>
              <span class="rec-panel-pill"><?php echo $req_count; ?></span>
            </div>
            <div class="rec-panel-body">
              <?php if ($req_count < 1) { ?>
                <p class="rec-desktop-empty">There are no issued items from the store at the moment.</p>
              <?php } else { ?>
              <div class="rec-table-wrap">
                <table class="table table-striped rec-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Shift</th>
                      <th>Request No.</th>
                      <th>Request User</th>
                      <th>Issue User</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody class="text-center">
                    <?php
                    mysqli_data_seek($pending_requisitions, 0);
                    $no = 0;
                    while ($row = mysqli_fetch_assoc($pending_requisitions)) {
                      $req_id = $row['req_id'];
                      $req_shift = $row['req_shift'];
                      $req_user = $row['req_user'];
                      $issue_user = isset($row['issue_user']) ? $row['issue_user'] : 0;
                      $no++;
                    ?>
                    <tr>
                      <td><?php echo $no; ?></td>
                      <td><?php echo $req_shift; ?></td>
                      <td><?php echo $req_id; ?></td>
                      <td><?php echo get_column('kp_users','username',"user_id='$req_user'"); ?></td>
                      <td><?php echo $issue_user ? get_column('kp_users','username',"user_id='$issue_user'") : 'Store'; ?></td>
                      <td><a class="btn btn-outline-success" href="req_rec.php?req=<?php echo $req_id; ?>">Receive</a></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
              <?php } ?>
            </div>
          </div>

          <div class="rec-panel-shell">
            <div class="rec-panel-head">
              <h3>Pending Transfers</h3>
              <span class="rec-panel-pill"><?php echo $trans_count; ?></span>
            </div>
            <div class="rec-panel-body">
              <?php if ($trans_count < 1) { ?>
                <p class="rec-desktop-empty">There are no transfers at the moment.</p>
              <?php } else { ?>
              <div class="rec-table-wrap">
                <table class="table table-striped rec-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Transfer No.</th>
                      <th>Shift</th>
                      <th>From</th>
                      <th>By</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody class="text-center">
                    <?php
                    mysqli_data_seek($pending_transfers, 0);
                    $no = 0;
                    while ($row = mysqli_fetch_assoc($pending_transfers)) {
                      $trans_id = $row['trans_id'];
                      $trans_shift = $row['shift'];
                      $cntr_from = isset($row['cntr_from']) ? $row['cntr_from'] : 0;
                      $user_from = isset($row['user_from']) ? $row['user_from'] : 0;
                      $no++;
                    ?>
                    <tr>
                      <td><?php echo $no; ?></td>
                      <td><?php echo $trans_id; ?></td>
                      <td><?php echo $trans_shift; ?></td>
                      <td><?php echo $cntr_from ? get_counter($cntr_from) : '-'; ?></td>
                      <td><?php echo $user_from ? get_user($user_from) : '-'; ?></td>
                      <td><a class="btn btn-outline-primary" href="../trans/trans_rec.php?trans=<?php echo $trans_id; ?>">Accept Transfer</a></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mobile-shell">
    <div class="rec-mobile-shell">
      <div class="rec-topbar">
        <div class="rec-brand">
          <div class="rec-brand-main">
            <span class="rec-counter-line"><?php echo get_counter($counter); ?></span>
            <span class="rec-sub-line">Pending Receipts</span>
          </div>
          <div class="rec-badge">
            <strong>Counter</strong>
            <span><?php echo get_counter($counter); ?></span>
          </div>
        </div>
        <div class="rec-top-actions">
          <a href="../dash/dash.php" class="rec-top-btn">Home</a>
          <a href="../log_out.php" class="rec-top-btn">Log Out</a>
        </div>
      </div>

      <div class="rec-card">
        <p class="rec-section-title">Issued Requisitions</p>
        <?php
        mysqli_data_seek($pending_requisitions, 0);
        if ($req_count < 1) {
          ?>
          <p class="rec-empty">There are no issued items from the store at the moment.</p>
          <?php
        } else {
          $no = 0;
          ?>
          <div class="rec-item-list">
            <?php
            while ($row = mysqli_fetch_assoc($pending_requisitions)) {
              $req_id = $row['req_id'];
              $req_shift = $row['req_shift'];
              $req_user = $row['req_user'];
              $issue_user = isset($row['issue_user']) ? $row['issue_user'] : 0;
              $no++;
              ?>
              <div class="rec-item">
                <div class="rec-item-no"><?php echo $no; ?></div>
                <div class="rec-item-copy">
                  <strong>Request #<?php echo $req_id; ?></strong>
                  <span>Shift <?php echo $req_shift; ?> | Req <?php echo get_column('kp_users','username',"user_id='$req_user'"); ?> | Issue <?php echo $issue_user ? get_column('kp_users','username',"user_id='$issue_user'") : 'Store'; ?></span>
                </div>
                <div class="rec-item-meta">
                  <a class="rec-item-btn" href="req_rec.php?req=<?php echo $req_id; ?>">Receive</a>
                </div>
              </div>
              <?php
            }
            ?>
          </div>
          <?php
        }
        ?>
      </div>

      <div class="rec-card">
        <p class="rec-section-title">Pending Transfers</p>
        <?php
        mysqli_data_seek($pending_transfers, 0);
        if ($trans_count < 1) {
          ?>
          <p class="rec-empty">There are no transfers at the moment.</p>
          <?php
        } else {
          $no = 0;
          ?>
          <div class="rec-item-list">
            <?php
            while ($row = mysqli_fetch_assoc($pending_transfers)) {
              $trans_id = $row['trans_id'];
              $trans_shift = $row['shift'];
              $cntr_from = isset($row['cntr_from']) ? $row['cntr_from'] : 0;
              $user_from = isset($row['user_from']) ? $row['user_from'] : 0;
              $no++;
              ?>
              <div class="rec-item">
                <div class="rec-item-no"><?php echo $no; ?></div>
                <div class="rec-item-copy">
                  <strong>Transfer #<?php echo $trans_id; ?></strong>
                  <span>Shift <?php echo $trans_shift; ?> | From <?php echo $cntr_from ? get_counter($cntr_from) : 'Counter'; ?> | By <?php echo $user_from ? get_user($user_from) : 'User'; ?></span>
                </div>
                <div class="rec-item-meta">
                  <a class="rec-item-btn" href="../trans/trans_rec.php?trans=<?php echo $trans_id; ?>">Accept</a>
                </div>
              </div>
              <?php
            }
            ?>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  </div>

  <div id="edit_item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div id="results"></div>
        <div class="modal-body">
          <div id="modal-loader" style="display: none; text-align: center;"></div>
          <div id="dynamic-content"></div>
        </div>
      </div>
    </div>
  </div>

  <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
</body>
</html>
