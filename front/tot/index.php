<?php
include("../clude/head.php");
$tot_id = isset($_SESSION['tot']) ? $_SESSION['tot'] : '';
?>
<link href="tots.css?v=<?php echo filemtime(__DIR__ . '/tots.css'); ?>" rel="stylesheet" />
<body id="whole">
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div class="desktop-shell">
      <div id="header" class="header navbar-default tot-header">
        <div class="navbar-header" id="sub_cat"></div>
      </div>
      <div id="content" class="content">
        <div class="vertical-box tot-shell">
          <div class="vertical-box-column tot-category-column">
            <div id="tot-category-desktop"></div>
          </div>

          <div class="vertical-box-column tot-menu-column">
            <div id="menu" class="tot-menu"></div>
          </div>
          <div class="vertical-box-column tot-summary-column">
            <div class="wrapper tot-summary-header">
              <b class="tot-summary-title">GENERATED UNITS</b>
            </div>
            <div id="screen" class="tot-screen"></div>
            <div class="wrapper tot-desktop-actions">
              <button type="button" class="btn btn_ord tot-submit-trigger tot-desktop-action">SUBMIT UNITS</button>
              <button type="button" class="btn btn_ord tot-desktop-action tot-cancel-trigger" id="cancel">CANCEL</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mobile-shell">
      <div class="tot-mobile-shell">
        <div class="tot-topbar">
          <div class="tot-brand">
            <div class="tot-brand-main">
              <span class="tot-counter-line"><?php echo get_counter(user_counter($_SESSION['user_id'])); ?></span>
              <span class="tot-number-line">Unit Generator</span>
            </div>
            <div class="tot-badge">
              <strong>Unit No</strong>
              <span><?php echo $tot_id; ?></span>
            </div>
          </div>
          <div class="tot-top-actions">
            <a href="index.php" class="tot-top-btn">Refresh</a>
            <button type="button" class="tot-top-btn tot-cancel-trigger" id="cancel_top_mobile">Cancel</button>
          </div>
        </div>

        <div class="tot-card">
          <p class="tot-section-title">Categories</p>
          <div class="tot-cat-scroll" id="tot-category-mobile"></div>
          <div id="sub_cat_mobile" class="tot-sub-cat-mobile"></div>
        </div>

        <div class="tot-card tot-menu-panel">
          <p class="tot-section-title">Items</p>
          <div id="menu_mobile"></div>
        </div>
      </div>

      <button type="button" class="tot-cart-fab" id="tot-cart-toggle">
        <span class="glyphicon glyphicon-list-alt"></span>
        <span class="tot-cart-badge" id="tot-cart-count">0</span>
        <span class="tot-cart-total" id="tot-cart-total">0</span>
      </button>

      <div class="tot-cart-drawer" id="tot-cart-drawer">
        <div class="tot-card tot-list-panel">
          <div class="tot-drawer-head">
            <p class="tot-section-title tot-drawer-title">Generated Units</p>
            <button type="button" class="tot-cart-close" id="tot-cart-close">&times;</button>
          </div>
          <div class="tot-receipt-strip">
            <b><?php echo get_counter(user_counter($_SESSION['user_id'])); ?></b>
            <b>Unit No: <?php echo $tot_id; ?></b>
          </div>
          <div id="screen_mobile"></div>
          <div class="tot-actions">
            <button type="button" class="tot-action-btn tot-submit-btn tot-submit-trigger">Submit</button>
            <button type="button" class="tot-action-btn tot-cancel-btn tot-cancel-trigger" id="cancel_mobile">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <div id="modal" class="modal" tabindex="-1">
      <div class="modal-dialog" id="load"></div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script src="tot_scripts.js?v=<?php echo filemtime(__DIR__ . '/tot_scripts.js'); ?>"></script>
</body>
</html>
