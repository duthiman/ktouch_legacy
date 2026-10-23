<?php 
include("../clude/head.php"); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_id']==0) {
  header("Location:../log_out.php");
}
?>
<link rel="stylesheet" href="kwedia_desktop.css">
<link rel="stylesheet" href="kwedia_mobile.css">
<script src="kwedia.js"></script>
<body  id="whole"> 
  <!-- begin #page-container -->
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div class="desktop-shell">
    <!-- begin #header -->
    <div id="header" class="header navbar-default" style="background-color: #6F9F0766 !important; ">
        <div class="navbar-header" id="sub_cat" aria-label="Subcategories"></div>
    </div>
    <!-- END HEADER-->
    <!-- begin #content -->
    <div id="content" class="content">
      <div class="vertical-box">
        <div class="vertical-box-column" style="width: 8%; background-color: transparent;">
            <div style="margin: 5px;">
                <button onclick="location.reload();" class="btn btn-primary btn-lg">🔄 Refresh</button>
            </div>
            <div class="mode-switch-card"> 
                <div class="switch-copy">
                  <strong id="deci_mode_text_desktop">Modal Mode</strong>
                  
                </div>
              <div class="switch-holder">
                <div class="switch-toggle">
                  <input type="checkbox" id="deci_mode_desktop" class="deci-mode-switch" checked>
                  <label for="deci_mode_desktop"></label>
                </div>
              </div>
            </div>

          <div id="kw-categories-desktop"></div>
        </div>

          <div class="vertical-box-column" style="width: 64%;" >
            <div id="menu" style="margin-left: 10px;"> </div>
          </div>
          <div class="vertical-box-column "  style=" width: 28% !important; height: 90%; position: absolute; padding-right: 2px;">
            <div class="wrapper" style="background-color: #B10421; text-align: center; padding: 2px;">
              <b style="color: white; font-size: 20px;">ACCOUNT : <span id="kw-account-name"></span></b> <br>
              <b style="color: white; font-size: 20px;"><span id="kw-counter-name"></span> COUNTER BILL NO: <span id="kw-sale-id">0</span></b>
              <div class="kw-order-mode-badge" id="kw-order-mode-desktop">FRESH ORDER</div>
            </div>
            <div id="screen" style="overflow-y:scroll; height: 80%; width: auto; background-color: #fff; padding:5px;"></div>
              <div class="wrapper kw-desktop-order-actions" style="background-color: transparent; padding: 10px 0px 2px 0px;">
                <button id="kw-desktop-close-btn" onclick="openCloseCurrentCounterModal()" class="btn btn_ord" data-sales-action="sale.close_order">CLOSE SALE</button>
                <button id="kw-desktop-open-btn" onclick="openOpenBillModeModal()" class="btn btn_ord" data-sales-action="sale.open_order">OPEN SALE</button>
                <button id="kw-desktop-continue-btn" onclick="complete();" class="btn btn_ord" style="display:none;">CONTINUE</button>
                <button id="kw-desktop-close-open-btn" onclick="complete();" class="btn btn_ord" style="display:none;">CLOSE OPEN</button>
                <button type="button" class="btn btn_ord" id="cancel" data-sales-action="sales.cancel" style="display:none;">CANCEL SALE</button>
              </div>
            </div>
          </div>
        </div>
    <!-- end #content --> 
    </div>

    <div class="mobile-shell">
      <div class="kw-mobile-shell">
        <div class="kw-topbar">
          <div class="kw-brand">
            <div class="kw-brand-main">
              <span class="kw-account-line">Account: <span id="kw-account-name-mobile"></span></span>
              <span class="kw-counter-line">Counter: <span id="kw-counter-name-mobile"></span></span>
            </div>
            <div class="kw-bill-badge">
              <strong>Bill No</strong>
                <span id="kw-sale-id-mobile">0</span>
            </div>
            <div class="kw-order-mode-badge" id="kw-order-mode-mobile">FRESH ORDER</div>
          </div>
          <div class="kw-top-actions">
            <button onclick="location.reload();" class="kw-top-btn" type="button">Refresh</button>
              <button type="button" class="kw-top-btn cancel-order-btn" data-sales-action="sales.cancel" style="display:none;">CANCEL SALE</button>
            <div class="mode-switch-card mobile-mode-switch mobile-inline-switch">
              <div class="switch-holder">
                <div class="switch-copy">
                  <strong id="deci_mode_text_mobile">Modal</strong>
                  <span>Switch between popup and touch ordering.</span>
                </div>
                <div class="switch-toggle">
                  <input type="checkbox" id="deci_mode_mobile" class="deci-mode-switch" checked>
                  <label for="deci_mode_mobile"></label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="kw-card">
          <p class="kw-section-title">Categories</p>
          <div class="kw-cat-scroll" id="kw-categories-mobile"></div>
          <div id="sub_cat_mobile" style="margin-top:6px;"></div>
        </div>

        <div class="kw-card kw-menu-panel">
          <div class="kw-mobile-menu-search">
            <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
            <input type="search" id="kw-mobile-item-search" placeholder="Search items..." autocomplete="off">
            <button type="button" id="kw-mobile-item-search-clear" aria-label="Clear search">&times;</button>
          </div>
          <div id="menu_mobile"></div>
        </div>
      </div>

      <button type="button" class="kw-cart-fab" id="kw-cart-toggle">
        <span class="glyphicon glyphicon-shopping-cart"></span>
        <span class="kw-cart-badge" id="kw-cart-count">0</span>
        <span class="kw-cart-total" id="kw-cart-total">0</span>
      </button>

      <div class="kw-cart-drawer" id="kw-cart-drawer">
        <div class="kw-card kw-bill-panel">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <p class="kw-section-title" style="margin:0;">Current Order</p>
            <button type="button" class="kw-cart-close" id="kw-cart-close" aria-label="Add more items" title="Close panel and add more items">Add More Items</button>
          </div>
          <div class="kw-account-strip">
            <b>Account : <span id="kw-cart-account-name"></span></b>
            <b><span id="kw-cart-counter-name"></span> Counter Bill No: <span id="kw-cart-sale-id">0</span></b>
            <span class="kw-order-mode-badge" id="kw-order-mode-cart">FRESH ORDER</span>
          </div>
          <div id="screen_mobile"></div>
          <div class="kw-order-actions" style="grid-template-columns: 1fr 1fr;">
            <button id="kw-mobile-close-btn" onclick="openCloseCurrentCounterModal();" class="kw-order-btn kw-order-send" data-sales-action="sale.close_order" type="button">Close Order</button>
            <button id="kw-mobile-open-btn" onclick="openOpenBillModeModal();" class="kw-order-btn kw-order-send" data-sales-action="sale.open_order" type="button">Open Order</button>
            <button id="kw-mobile-continue-btn" onclick="complete();" class="kw-order-btn kw-order-send" style="display:none;" type="button">Continue</button>
            <button id="kw-mobile-close-open-btn" onclick="complete();" class="kw-order-btn kw-order-send" style="display:none;" type="button">Close Open</button>
          </div>
        </div>
      </div>
    </div>

      <div id="modal" class="modal" tabindex="-1">
        <div class="modal-dialog" id="load"> </div>
      </div>  
    </div>
  <!-- ================== BEGIN BASE JS ================== -->
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script> 
  <!--<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>-->
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
  <script src="js.js?v=<?php echo filemtime(__DIR__ . '/js.js'); ?>"></script>
  <!-- ================== END BASE JS ================== --> 
</body>
</html>
