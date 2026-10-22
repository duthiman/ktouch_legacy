<?php 
include("../clude/head.php"); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_id']==0) {
  header("Location:../log_out.php");
}
$folder="front/kugura/"; 
thesion_page($folder);
$user_id = $_SESSION['user_id'];
$requested_sale_id = (int)($_GET['sale_id'] ?? 0);
$requested_sale_counter = (int)($_GET['sale_counter'] ?? 0);
if ($requested_sale_id > 0) {
  $active_child = mysqli_query(
    $connection,
    "SELECT sale_id
     FROM kp_sale_status
     WHERE user_id='$user_id' AND sale_id='$requested_sale_id'
     LIMIT 1"
  );
  $active_open = mysqli_query(
    $connection,
    "SELECT sale_id, counter_id
     FROM kp_sales_open
     WHERE user_id='$user_id' AND (sale_id='$requested_sale_id' OR child_sale_id='$requested_sale_id') AND status=1
     ORDER BY open_id DESC
     LIMIT 1"
  );
  $active_child_row = $active_child ? mysqli_fetch_assoc($active_child) : null;
  $active_open_row = $active_open ? mysqli_fetch_assoc($active_open) : null;
  if ($active_child_row || $active_open_row) {
    $resume_counter = $requested_sale_counter;
    if ($resume_counter < 1) {
      $resume_counter = (int)($active_open_row['counter_id'] ?? 0);
    }
    if ($resume_counter < 1) {
      $resume_counter = (int)get_column('kp_thesion_front','cntr_id',"user_id='$user_id'");
    }
    if ($resume_counter > 0 && function_exists('sales_thesion')) {
      sales_thesion("front/kwedia/", $requested_sale_id, $resume_counter, 2);
    } elseif ($resume_counter > 0) {
      mysqli_query($connection, "UPDATE kp_thesion_front SET sale_id='$requested_sale_id', cntr_id='$resume_counter', status=2 WHERE user_id='$user_id'");
    }
  }
}
$sale_id = get_column('kp_thesion_front','sale_id',"user_id='$user_id'");
$status = get_column('kp_thesion_front','status',"user_id='$user_id'");
$counter = get_column('kp_thesion_front','cntr_id',"user_id='$user_id'");
$sale_id = (int)$sale_id;
$status = (int)$status;
$counter = (int)$counter;
if ($sale_id <= 0 || $status <= 0) {
  $redir_url = "../dash/dash.php?redir=order_lost";
  if (!headers_sent()) {
    header("Location: " . $redir_url);
    exit;
  }
  echo '<script>window.location=' . json_encode($redir_url) . ';</script>';
  echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redir_url, ENT_QUOTES) . '"></noscript>';
  exit;
}
$dpt = get_column('kp_counters','dpt',"cntr_id='$counter'");  
if ($dpt==1) {
  $filter="";
}elseif ($dpt==2) {
  $filter="WHERE dpt='$dpt'";
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
      <div class="navbar-header" id="sub_cat" ></div>
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
              <b style="color: white; font-size: 20px;">ACCOUNT : <span id="kw-account-name"><?php echo get_user($user_id); ?></span></b> <br>
              <b style="color: white; font-size: 20px;"><span id="kw-counter-name"><?php echo get_counter($counter); ?></span> COUNTER BILL NO: <span id="kw-sale-id"><?PHP echo $sale_id; ?></span></b>  
            </div>
            <div id="screen" style="overflow-y:scroll; height: 80%; width: auto; background-color: #fff; padding:5px;"></div>
              <div class="wrapper kw-desktop-order-actions" style="background-color: transparent; padding: 10px 0px 2px 0px;">
                <button onclick="openCloseCurrentCounterModal()" class="btn btn_ord">CLOSE ORDER</button>
                <button onclick="openOpenBillModeModal()" class="btn btn_ord">OPEN ORDER</button>
                <?php
                if ($status==2) {}elseif ($status==1) {
                  ?>
                  <button  data-toggle="modal" data-target="#modal" type="button" class="btn btn_ord" id="cancel">CANCEL</button>
                  <?php
                }
                ?>
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
              <span class="kw-account-line">Account: <span id="kw-account-name-mobile"><?php echo get_user($user_id); ?></span></span>
              <span class="kw-counter-line">Counter: <span id="kw-counter-name-mobile"><?php echo get_counter($counter); ?></span></span>
            </div>
            <div class="kw-bill-badge">
              <strong>Bill No</strong>
              <span id="kw-sale-id-mobile"><?php echo $sale_id; ?></span>
            </div>
          </div>
          <div class="kw-top-actions">
            <button onclick="location.reload();" class="kw-top-btn" type="button">Refresh</button>
            <?php if ($status==1) { ?>
              <button data-toggle="modal" data-target="#modal" type="button" class="kw-top-btn cancel-order-btn">Cancel</button>
            <?php } else { ?>
              <button type="button" class="kw-top-btn" onclick="history.back();">Cancel</button>
            <?php } ?>
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
          <p class="kw-section-title">Menu</p>
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
            <button type="button" class="kw-cart-close" id="kw-cart-close">&times;</button>
          </div>
          <div class="kw-account-strip">
            <b>Account : <span id="kw-cart-account-name"><?php echo get_user($user_id); ?></span></b>
            <b><span id="kw-cart-counter-name"><?php echo get_counter($counter); ?></span> Counter Bill No: <span id="kw-cart-sale-id"><?PHP echo $sale_id; ?></span></b>
          </div>
          <div id="screen_mobile"></div>
          <div class="kw-order-actions" style="grid-template-columns: 1fr 1fr 1fr;">
            <button onclick="complete();" class="kw-order-btn kw-order-send" type="button">Close Bill</button>
            <?php if ($status==1) { ?>
              <button data-toggle="modal" data-target="#modal" type="button" class="kw-order-btn kw-order-cancel cancel-order-btn">Cancel</button>
            <?php } ?>
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
