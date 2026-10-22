<?php 
include("../clude/head.php");  
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] < 1) {
  header("Location:../log_out.php");
  exit;
}
$user_id = (int)$_SESSION['user_id'];
$req_id = (int)($_SESSION['req'] ?? 0);
$counter = function_exists('get_column')
  ? (int)get_column('kp_users', 'counter', "user_id='$user_id'")
  : 0;
?>
<link rel="stylesheet" href="gwit.css?v=<?php echo filemtime(__DIR__ . '/gwit.css'); ?>">
<body id="whole">
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div class="desktop-shell">
      <div id="header" class="header navbar-default" style="background-color: #6F9F0766 !important;">
        <div class="navbar-header" id="sub_cat"></div>
      </div>
      <div id="content" class="content">
        <div class="vertical-box">
          <div class="vertical-box-column" style="width: 7%; background-color: transparent;" id="gwit_categories_desktop">
          </div>

          <div class="vertical-box-column" style="width: 65%;">
            <div id="menu" style="margin-left: 10px;"></div>
          </div>
          <div class="vertical-box-column" style="width: 28% !important; height: 90%; position: absolute; padding: 2px;">
            <div class="wrapper" style="background-color: #B10421; text-align: center; padding: 2px;">
              <h3 style="color: white; font-size: 28px;"><span id="gwit_counter_name_desktop">Counter</span><br> REQUISITION NO. <?php echo $req_id; ?> ITEMS</h3>
            </div>
            <div id="screen" style="overflow-y:scroll; height: 70%; width: auto; background-color: #fff; padding: 5px;"></div>
            <div class="wrapper">
              <button id="btn_sub" class="btn btn_ord" data-toggle="modal" data-target="#modal" style="padding-top: 10px;">SUBMIT REQUISTION</button>
              <button data-toggle="modal" data-target="#modal" type="button" class="btn_ord" id="cancel">CANCEL</button><br>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mobile-shell">
      <div class="req-mobile-shell">
        <div class="req-topbar">
          <div class="req-brand">
            <div class="req-brand-main">
              <span class="req-counter-line" id="gwit_counter_name_mobile">Counter</span>
              <span class="req-number-line">Requisition Draft</span>
            </div>
            <div class="req-badge">
              <strong>Req No</strong>
              <span><?php echo $req_id; ?></span>
            </div>
          </div>
          <div class="req-top-actions">
            <a href="../stock/store_dash.php" class="req-top-btn">Back</a>
            <a href="../dash/dash.php" class="req-top-btn">Home</a>
            <button type="button" class="req-top-btn req-refresh-btn" onclick="window.location.reload();" aria-label="Refresh" title="Refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>
          </div>
        </div>

        <div class="req-card">
          <p class="req-section-title">Categories</p>
          <div class="req-cat-scroll" id="gwit_categories_mobile"></div>
          <div id="sub_cat_mobile" style="margin-top:6px;"></div>
        </div>

        <div class="req-card req-menu-panel">
          <p class="req-section-title">Items</p>
          <div id="menu_mobile"></div>
        </div>
      </div>

      <button type="button" class="req-cart-fab" id="req-cart-toggle">
        <span class="glyphicon glyphicon-shopping-cart"></span>
        <span class="req-cart-badge" id="req-cart-count">0</span>
        <span class="req-cart-total" id="req-cart-total">0</span>
      </button>

      <div class="req-cart-drawer" id="req-cart-drawer">
        <div class="req-card req-list-panel">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <p class="req-section-title" style="margin:0;">Current Requisition</p>
            <button type="button" class="req-cart-close" id="req-cart-close">&times;</button>
          </div>
          <div class="req-account-strip">
            <b><?php echo get_counter($counter); ?></b>
            <b>Requisition No: <?php echo $req_id; ?></b>
          </div>
          <div id="screen_mobile"></div>
          <div class="req-actions">
            <button id="btn_sub_mobile" class="req-action-btn req-submit-btn" data-toggle="modal" data-target="#modal">Submit</button>
            <button id="cancel_mobile" class="req-action-btn req-cancel-btn" data-toggle="modal" data-target="#modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <div id="modal" class="modal" tabindex="-1">
      <div class="modal-dialog" id="load"></div>
    </div>
    <div id="modal2" class="modal" tabindex="-1">
      <div class="modal-dialog" id="load2"></div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script src="req_scripts.js?v=<?php echo filemtime(__DIR__ . '/req_scripts.js'); ?>"></script>
</body>
</html>
