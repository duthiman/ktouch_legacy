<?php 
include("../clude/head.php");  
$user_id=$_SESSION['user_id'];
$req_id = $_SESSION["req"];
if (!isset($_SESSION['user_id'])) {
  header("Location:../log_out.php");
}
$counter = get_column('kp_users','counter',"user_id='$user_id'");
$dpt = get_column('kp_counters','dpt',"cntr_id='$counter'");
if ($dpt==1) {
  $filter="";
}elseif ($dpt==2) {
  $filter="WHERE dpt='$dpt'";
}
?>
<style type="text/css">
body {
  background: url("../assets/img/bg/5.jpg") no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
.desktop-shell {
  display: block;
}
.mobile-shell {
  display: none;
}
.req-mobile-shell {
  max-width: 520px;
  margin: 0 auto;
  padding: 0 5px 30px;
}
.req-topbar {
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
.req-brand {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: center;
}
.req-brand-main {
  min-width: 0;
}
.req-counter-line,
.req-number-line {
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
.req-number-line {
  margin-top: 4px;
  opacity: 0.92;
}
.req-badge {
  min-width: 82px;
  padding: 7px 9px;
  border-radius: 10px;
  text-align: center;
  background: rgba(255,255,255,0.15);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
}
.req-badge strong {
  display: block;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}
.req-badge span {
  display: block;
  margin-top: 2px;
  font-size: 18px;
  font-weight: 900;
}
.req-top-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 12px;
}
.req-top-btn {
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
.req-card {
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(108, 78, 31, 0.08);
  border-radius: 10px;
  box-shadow: 0 16px 28px rgba(86, 61, 24, 0.12);
  padding: 14px;
  margin-bottom: 10px;
}
.req-section-title {
  margin: 0 0 10px;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: 0.08em;
  color: #7e3a20;
  text-transform: uppercase;
}
.req-cat-scroll,
#sub_cat_mobile {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
  white-space: nowrap;
}
.req-cat-scroll::-webkit-scrollbar,
#sub_cat_mobile::-webkit-scrollbar {
  height: 4px;
}
.req-mobile-cat,
#sub_cat_mobile button {
  border: 3px solid #B10421;
  border-radius: 10px;
  min-height: 46px;
  min-width: 88px;
  padding: 8px 10px;
  background: #000000;
  color: #FFFFFF !important;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  white-space: normal;
  box-shadow: 0 8px 16px rgba(103, 72, 20, 0.12);
  line-height: 1.2;
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
.req-cat-scroll .req-mobile-cat:nth-child(4n+1),
#sub_cat_mobile button:nth-child(4n+1) {
  background: #000000 !important;
  border-color: #B10421 !important;
}
.req-cat-scroll .req-mobile-cat:nth-child(4n+2),
#sub_cat_mobile button:nth-child(4n+2) {
  background: #0f4c81 !important;
  border-color: #68b7ff !important;
}
.req-cat-scroll .req-mobile-cat:nth-child(4n+3),
#sub_cat_mobile button:nth-child(4n+3) {
  background: #2f5d17 !important;
  border-color: #9ed36a !important;
}
.req-cat-scroll .req-mobile-cat:nth-child(4n+4),
#sub_cat_mobile button:nth-child(4n+4) {
  background: #6d3d0f !important;
  border-color: #f0b35c !important;
}
.req-menu-panel,
.req-list-panel {
  min-height: 140px;
}
.req-list-panel {
  min-height: 0;
}
#menu_mobile {
  min-height: 180px;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6px;
}
#menu_mobile #getItem,
#menu_mobile .btn {
  width: 100% !important;
  min-width: 0 !important;
  height: 72px !important;
  margin: 0 !important;
  padding-left: 4px !important;
  padding-right: 4px !important;
  border-radius: 10px !important;
  font-size: 11px !important;
  line-height: 1.15 !important;
  vertical-align: top;
  display: flex !important;
  align-items: center;
  justify-content: center;
  text-align: center;
  box-sizing: border-box;
}
#screen_mobile {
  max-height: 42vh;
  overflow-y: auto;
  background: #fff;
  border-radius: 10px;
  border: 1px solid rgba(108, 78, 31, 0.08);
  padding: 8px;
}
.req-account-strip {
  padding: 12px;
  border-radius: 10px;
  background: linear-gradient(135deg, #8a1232 0%, #c3385c 100%);
  color: #fff;
  text-align: center;
  margin-bottom: 10px;
}
.req-account-strip b {
  display: block;
  font-size: 14px;
  line-height: 1.4;
}
.req-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 10px;
}
.req-action-btn {
  min-height: 48px;
  border: 0;
  border-radius: 10px;
  color: #fff !important;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none !important;
}
.req-submit-btn {
  background: linear-gradient(135deg, #2f5d17 0%, #58a03f 100%);
}
.req-cancel-btn {
  background: linear-gradient(135deg, #7a1717 0%, #b22d2d 52%, #db6b4a 100%);
}
.req-cart-fab {
  position: fixed;
  right: 16px;
  bottom: 18px;
  width: 78px;
  height: 78px;
  border: 0;
  border-radius: 50%;
  background: linear-gradient(135deg, #8a1232 0%, #c3385c 100%);
  color: #fff;
  box-shadow: 0 14px 26px rgba(71, 18, 18, 0.28);
  z-index: 1050;
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  gap: 2px;
}
.req-cart-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 24px;
  height: 24px;
  padding: 0 6px;
  border-radius: 12px;
  background: #ffd45d;
  color: #733f00;
  font-size: 12px;
  font-weight: 900;
  display: flex;
  align-items: center;
  justify-content: center;
}
.req-cart-total {
  display: block;
  max-width: 62px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #fff5d9;
  font-size: 10px;
  font-weight: 900;
  line-height: 1.1;
}
.req-cart-drawer {
  position: fixed;
  left: 8px;
  right: 8px;
  bottom: 8px;
  z-index: 1040;
  display: none;
}
.req-cart-drawer.open {
  display: block;
}
.req-cart-close {
  width: 38px;
  height: 38px;
  border: 0;
  border-radius: 50%;
  background: #f0d8dd;
  color: #8a1232;
  font-size: 22px;
  font-weight: 900;
  line-height: 1;
}
#modal {
  overflow-y: auto !important;
  padding-right: 0 !important;
}
#load.modal-dialog {
  width: calc(100vw - 32px);
  max-width: 560px;
  margin: 28px auto;
  left: 0 !important;
  right: 0 !important;
  transform: none !important;
  -webkit-transform: none !important;
}
#load .modal-content {
  max-height: calc(100vh - 56px);
  overflow: hidden;
}
@media (max-width: 991px) {
  body {
    min-height: 100vh;
    background:
      url("../assets/img/bg/5.jpg") no-repeat center center fixed,
      radial-gradient(circle at top, rgba(246,208,137,0.22), transparent 32%),
      linear-gradient(180deg, rgba(246,240,215,0.94) 0%, rgba(239,227,191,0.94) 36%, rgba(211,182,125,0.94) 100%);
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
    color: #2b2418;
    padding-bottom: env(safe-area-inset-bottom, 24px);
    overflow: hidden;
  }
  html,
  body,
  #page-container {
    height: 100vh;
  }
  #page-container.page-header-fixed {
    padding-top: 0 !important;
  }
  #page-container > #header {
    display: none !important;
  }
  #content.content {
    margin-top: 0 !important;
    padding-top: 0 !important;
  }
  .desktop-shell {
    display: none;
  }
  .mobile-shell {
    display: block;
    height: 100vh;
  }
  .req-mobile-shell {
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  .req-menu-panel {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
  }
  #menu_mobile {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    align-content: start;
    padding-right: 2px;
  }
  #sub_cat_mobile .navbar-header {
    float: none !important;
    margin: 0 !important;
    display: flex;
    gap: 8px;
    flex-wrap: nowrap;
  }
  #sub_cat_mobile button {
    flex: 0 0 auto;
  }
  #load.modal-dialog {
    width: calc(100vw - 20px);
    max-width: none;
    margin: 10px auto;
    left: 0 !important;
    right: 0 !important;
  }
  #load .modal-content {
    max-height: calc(100vh - 20px);
    border-radius: 14px;
  }
  .req-cart-fab {
    display: flex;
  }
}
@media (min-width: 768px) and (max-width: 991px) {
  .req-mobile-shell {
    max-width: none;
    width: calc(100vw - 24px);
    height: 100vh;
    padding-left: 12px;
    padding-right: 12px;
  }
  #menu_mobile {
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px;
  }
  #menu_mobile #getItem,
  #menu_mobile .btn {
    height: 86px !important;
    font-size: 12px !important;
  }
}
</style>
<body id="whole">
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div class="desktop-shell">
      <div id="header" class="header navbar-default" style="background-color: #6F9F0766 !important;">
        <div class="navbar-header" id="sub_cat"></div>
      </div>
      <div id="content" class="content">
        <div class="vertical-box">
          <div class="vertical-box-column" style="width: 7%; background-color: transparent;">
            <?php
            $get_category = "SELECT * FROM kp_cat $filter";
            $get_category_r = mysqli_query($connection,$get_category) or die("Could not fetch the categories");
            if (mysqli_num_rows($get_category_r) < 1) {
              echo "There is no registsred category at the moment";
            }else{
              while ($r = mysqli_fetch_assoc($get_category_r)) {
            ?>
            <button type="button" onclick="req_navbar('<?php echo $r['cat_id']; ?>');load_cat_food('<?php echo $r['cat_id']; ?>')" class="btn_nav"><?php echo $r['name']; ?></button>
            <?php
              }
            }
            ?>
          </div>

          <div class="vertical-box-column" style="width: 65%;">
            <div class="gwit-desktop-item-tools"><input type="search" id="gwit-item-search-desktop" class="form-control" placeholder="Search items in this list..."><button type="button" class="btn btn-default gwit-refresh"><i class="fa fa-refresh"></i> Refresh</button></div>
            <div id="menu" style="margin-left: 10px;"></div>
          </div>
          <div class="vertical-box-column" style="width: 28% !important; height: 90%; position: absolute; padding: 2px;">
            <div class="wrapper" style="background-color: #B10421; text-align: center; padding: 2px;">
              <h3 style="color: white; font-size: 28px;"><?php echo get_counter($counter); ?><br> REQUISITION NO. <?php echo $req_id; ?> ITEMS</h3>
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
              <span class="req-counter-line"><?php echo get_counter($counter); ?></span>
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
            <button type="button" class="req-top-btn gwit-refresh"><i class="fa fa-refresh"></i> Refresh</button>
          </div>
        </div>

        <div class="req-card">
          <p class="req-section-title">Categories</p>
          <div class="req-cat-scroll">
            <?php
            $get_category = "SELECT * FROM kp_cat $filter";
            $get_category_r = mysqli_query($connection,$get_category) or die("Could not fetch the categories");
            if (mysqli_num_rows($get_category_r) > 0) {
              while ($r = mysqli_fetch_assoc($get_category_r)) {
            ?>
            <button type="button" onclick="req_navbar('<?php echo $r['cat_id']; ?>');load_cat_food('<?php echo $r['cat_id']; ?>')" class="req-mobile-cat req-mobile-cat-btn" data-cat-id="<?php echo $r['cat_id']; ?>"><?php echo $r['name']; ?></button>
            <?php
              }
            }
            ?>
          </div>
          <div id="sub_cat_mobile" style="margin-top:6px;"></div>
        </div>

        <div class="req-card req-menu-panel">
          <div class="gwit-mobile-item-tools"><p class="req-section-title">Items</p><input type="search" id="gwit-item-search-mobile" class="form-control" placeholder="Search items..."></div>
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
  <script src="gwit_item_tools.js?v=<?php echo filemtime(__DIR__ . '/gwit_item_tools.js'); ?>"></script>
</body>
</html>
