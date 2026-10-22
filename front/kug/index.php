<?php
include("../clude/head.php");
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id'])) {
  header("Location:../log_out.php");
  exit;
}

$po_id = isset($_SESSION['buy_po']) ? $_SESSION['buy_po'] : 0;
if (empty($po_id)) {
  header("Location:../stock/store_dash.php");
  exit;
}

$counter = get_column('kp_users', 'counter', "user_id='$user_id'");
$dpt = get_column('kp_counters', 'dpt', "cntr_id='$counter'");
$sup_id = get_column('kp_po_status', 'sup_id', "po_id='$po_id'");
$buy_tax = isset($_SESSION['buy_tax']) ? (int)$_SESSION['buy_tax'] : 0;
$purchaseSettings = kp_purchase_settings($connection);
if ((int)$purchaseSettings['front_purchase_enabled'] !== 1) {
  header("Location:../stock/store_dash.php?msg=front_purchase_disabled");
  exit;
}

if ($dpt == 1) {
  $filter = "";
} elseif ($dpt == 2) {
  $filter = "WHERE dpt='$dpt'";
} else {
  $filter = "";
}
?>
<style type="text/css">
body {
  background: url("../assets/img/bg/55.jpg") no-repeat center center fixed;
  background-size: cover;
}
.desktop-shell {
  height: 100vh;
  min-height: 0;
  overflow: hidden;
}
@media screen and (min-width: 992px) {
  html, body {
    height: 100vh;
    overflow: hidden;
  }
}
.desktop-shell > .vertical-box-column {
  top: 70px;
  bottom: 12px;
}
.desktop-shell .vertical-box-column {
  width: 30% !important;
  height: auto !important;
  min-height: 560px;
  position: absolute;
  padding: 0 !important;
  display: flex;
  flex-direction: column;
  gap: 8px;
  background: #fff;
  border: 1px solid #ead9c3;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 26px rgba(74,37,11,.14);
}
.desktop-shell .vertical-box-column > .wrapper:first-child {
  flex: 0 0 auto;
  padding: 10px !important;
  background: linear-gradient(135deg,#7c0a20,#b10421 58%,#d97827) !important;
  text-align: left !important;
}
.desktop-shell .vertical-box-column .row {
  margin: 0 0 8px !important;
}
.desktop-shell .vertical-box-column .col-4,
.desktop-shell .vertical-box-column .col-8 {
  min-width: 0;
}
.desktop-shell #supplier-btn {
  min-height: 64px !important;
  border: 0;
  border-radius: 8px;
  font-size: 14px !important;
  line-height: 1.15;
  box-shadow: 0 5px 12px rgba(0,0,0,.14);
}
.desktop-shell #supplier-status {
  min-height: 64px !important;
  padding: 9px 10px !important;
  border-radius: 8px !important;
  margin: 0 !important;
  background: rgba(255,255,255,.94) !important;
  color: #7c0a20 !important;
  text-align: left;
}
.desktop-shell #supplier-status #supplier-name {
  font-size: 17px !important;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.desktop-shell .vertical-box-column h3 {
  margin: 10px 0 3px !important;
  font-size: 17px !important;
  line-height: 1.25;
}
.desktop-shell .vertical-box-column p {
  font-size: 11px;
  line-height: 1.3;
}
.desktop-shell #screen {
  flex: 1 1 auto;
  min-height: 0;
  width: auto !important;
  height: auto !important;
  overflow: auto !important;
  padding: 0 !important;
  border: 1px solid #eadfe0;
  background: #fff !important;
}
.desktop-shell #screen table {
  margin: 0 !important;
  font-size: 11px;
}
.desktop-shell #screen th {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #fff3e4;
  color: #7c0a20;
  font-size: 10px;
  text-align: center;
  white-space: nowrap;
}
.desktop-shell #screen th h4 { margin: 0; font-size: 10px; font-weight: 900; }
.desktop-shell #screen td { vertical-align: middle; padding: 6px 5px; }
.desktop-shell .vertical-box-column > .wrapper:last-child {
  flex: 0 0 auto;
  display: flex;
  gap: 8px;
  padding: 8px 10px 10px;
  background: #fffaf3;
}
.desktop-shell .vertical-box-column > .wrapper:last-child br { display: none; }
.desktop-shell #btn_sub,
.desktop-shell #cancel {
  flex: 1 1 0;
  min-height: 44px;
  padding: 8px 10px !important;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 900;
}
.desktop-shell .vertical-box {
  display: flex;
  position: relative;
  width: 100%;
  height: calc(100vh - 70px);
  overflow: hidden;
}
.desktop-shell .vertical-box > .vertical-box-column:nth-child(1) {
  width: 8% !important;
  flex: 0 0 8%;
  height: 100% !important;
  position: static !important;
  padding: 4px !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  overflow-y: auto;
}
.desktop-shell .vertical-box > .vertical-box-column:nth-child(2) {
  width: 64% !important;
  flex: 0 0 64%;
  height: 100% !important;
  position: static !important;
  padding: 8px !important;
  background: rgba(255,255,255,.78) !important;
  border: 1px solid #ead9c3 !important;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(74,37,11,.08) !important;
  overflow-y: auto;
}
.desktop-shell .vertical-box > .vertical-box-column:nth-child(3) {
  width: 28% !important;
  flex: none;
  position: absolute !important;
  right: 0;
  top: 0;
  bottom: 0;
}
.desktop-shell {
  display: block;
}
.mobile-shell {
  display: none;
}
.touch-card {
  border-radius: 14px;
  padding: 10px 8px;
  margin-bottom: 8px;
}
.touch-tax {
  display: block;
  width: 100%;
  border: 3px solid #fff;
  border-radius: 14px;
  background: #fff7d6;
  color: #7d4b00;
  font-size: 20px;
  font-weight: bold;
  padding: 14px 10px;
}
.touch-tax input {
  transform: scale(1.8);
  margin-right: 10px;
  vertical-align: middle;
}
.btn-disabled-soft {
  opacity: 0.55;
  cursor: not-allowed;
  pointer-events: none;
}
.kw-mobile-shell {
  max-width: 520px;
  margin: 0 auto;
  padding: 0 5px 30px;
}
.kw-topbar {
  position: sticky;
  top: 10px;
  z-index: 20;
  margin-bottom: 12px;
  padding: 10px 14px 14px;
  border-radius: 10px;
  background: linear-gradient(135deg, #7c0a20 0%, #b10421 50%, #f0a53a 100%);
  box-shadow: 0 18px 30px rgba(74, 37, 11, 0.18);
  color: #fff8ea;
}
.kw-brand {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: center;
}
.kw-brand-main {
  min-width: 0;
}
.kw-account-line,
.kw-counter-line {
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
.kw-counter-line {
  margin-top: 4px;
  opacity: 0.92;
}
.kw-bill-badge {
  min-width: 90px;
  padding: 7px 9px;
  border-radius: 10px;
  text-align: center;
  background: rgba(255,255,255,0.15);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
}
.kw-bill-badge strong {
  display: block;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}
.kw-bill-badge span {
  display: block;
  margin-top: 2px;
  font-size: 18px;
  font-weight: 900;
}
.kw-top-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 12px;
}
.kw-top-btn {
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
}
.kw-card {
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(108, 78, 31, 0.08);
  border-radius: 10px;
  box-shadow: 0 16px 28px rgba(86, 61, 24, 0.12);
  padding: 14px;
  margin-bottom: 10px;
}
.kw-section-title {
  margin: 0 0 10px;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: 0.08em;
  color: #7e3a20;
  text-transform: uppercase;
}
.kw-cat-scroll,
#sub_cat_mobile {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
  white-space: nowrap;
}
.kw-cat-scroll::-webkit-scrollbar,
#sub_cat_mobile::-webkit-scrollbar {
  height: 4px;
}
.kw-mobile-cat,
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
.btn_nav:nth-child(4n+1),
.kw-cat-scroll .kw-mobile-cat:nth-child(4n+1),
#sub_cat_mobile button:nth-child(4n+1) {
  background: #000000 !important;
  border-color: #B10421 !important;
}
.btn_nav:nth-child(4n+2),
.kw-cat-scroll .kw-mobile-cat:nth-child(4n+2),
#sub_cat_mobile button:nth-child(4n+2) {
  background: #0f4c81 !important;
  border-color: #68b7ff !important;
}
.btn_nav:nth-child(4n+3),
.kw-cat-scroll .kw-mobile-cat:nth-child(4n+3),
#sub_cat_mobile button:nth-child(4n+3) {
  background: #2f5d17 !important;
  border-color: #9ed36a !important;
}
.btn_nav:nth-child(4n+4),
.kw-cat-scroll .kw-mobile-cat:nth-child(4n+4),
#sub_cat_mobile button:nth-child(4n+4) {
  background: #6d3d0f !important;
  border-color: #f0b35c !important;
}
.kw-menu-panel,
.kw-bill-panel {
  min-height: 140px;
}
.kug-sub-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 96px;
  min-height: 44px;
  margin: 4px;
  padding: 8px 12px;
  border: 2px solid #b10421;
  border-radius: 10px;
  background: #000 !important;
  color: #fff !important;
  font-size: 12px;
  font-weight: 800;
  line-height: 1.15;
  text-align: center;
  white-space: normal;
  touch-action: manipulation;
}
.kug-sub-btn:nth-child(4n+2) { background:#0f4c81 !important; border-color:#68b7ff; }
.kug-sub-btn:nth-child(4n+3) { background:#2f5d17 !important; border-color:#9ed36a; }
.kug-sub-btn:nth-child(4n+4) { background:#6d3d0f !important; border-color:#f0b35c; }
#menu_mobile {
  min-height: 180px;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6px;
}
#menu_mobile .btn {
  width: 100% !important;
  min-width: 0 !important;
  height: 78px !important;
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
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
#menu .kug-item-btn,
#menu_mobile .kug-item-btn {
  width: 80px;
  min-width: 80px;
  height: 80px;
  margin: 5px;
  padding: 4px;
  border: 2px solid #8a1232;
  border-radius: 10px;
  background: #8a1232;
  color: #fff !important;
  font-family: 'Comic Sans MS', 'Segoe UI', sans-serif;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.15;
  white-space: normal;
  word-wrap: break-word;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  vertical-align: top;
  box-shadow: 0 6px 12px rgba(103, 72, 20, .12);
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
#menu .kug-item-btn:nth-child(4n+2),
#menu_mobile .kug-item-btn:nth-child(4n+2) { background:#0f4c81; border-color:#68b7ff; }
#menu .kug-item-btn:nth-child(4n+3),
#menu_mobile .kug-item-btn:nth-child(4n+3) { background:#2f5d17; border-color:#9ed36a; }
#menu .kug-item-btn:nth-child(4n+4),
#menu_mobile .kug-item-btn:nth-child(4n+4) { background:#6d3d0f; border-color:#f0b35c; }
#menu .kug-item-btn:active,
#menu_mobile .kug-item-btn:active { transform: translateY(1px) scale(.98); }
#menu .kug-item-btn.stock-red,
#menu_mobile .kug-item-btn.stock-red { background:#f8c8ce; border-color:#e58b96; color:#7a1825 !important; }
#menu .kug-item-btn.stock-orange,
#menu_mobile .kug-item-btn.stock-orange { background:#ffe0a8; border-color:#f2ad3d; color:#704300 !important; }
#menu .kug-item-btn.stock-green,
#menu_mobile .kug-item-btn.stock-green { background:#c9efd0; border-color:#67ba76; color:#155b26 !important; }
#screen_mobile {
  max-height: 42vh;
  overflow-y: auto;
  background: #fff;
  border-radius: 10px;
  border: 1px solid rgba(108, 78, 31, 0.08);
  padding: 8px;
}
.kw-info-strip {
  padding: 12px;
  border-radius: 10px;
  background: linear-gradient(135deg, #8a1232 0%, #c3385c 100%);
  color: #fff;
  text-align: center;
  margin-bottom: 10px;
}
.kw-info-strip b,
.kw-info-strip span {
  display: block;
  font-size: 14px;
  line-height: 1.4;
}
.kw-tax-mobile {
  margin-top: 10px;
}
.kw-order-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 10px;
}
.kw-order-btn {
  min-height: 58px;
  border: 0;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 900;
  text-transform: uppercase;
}
.kw-order-send {
  background: linear-gradient(135deg, #4f7e1e 0%, #7fbb33 100%);
  color: #fff;
}
.kw-order-cancel {
  background: linear-gradient(135deg, #8b1f1f 0%, #d24848 100%);
  color: #fff;
}
.kw-cart-fab {
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
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  gap: 2px;
}
.kw-cart-badge {
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
.kw-cart-total {
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
.kw-cart-drawer {
  position: fixed;
  left: 8px;
  right: 8px;
  bottom: 8px;
  z-index: 1040;
  display: none;
}
.kw-cart-drawer.open {
  display: block;
}
.kw-cart-close {
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
#modal.kw-jq-visible {
  display: block !important;
  opacity: 1 !important;
}
#modal.kw-jq-visible .modal-dialog {
  margin: 30px auto;
}
@media (max-width: 991px) {
  body {
    min-height: 100vh;
    background:
      url("../assets/img/bg/55.jpg") no-repeat center center fixed,
      radial-gradient(circle at top, rgba(246, 208, 137, 0.18), transparent 32%),
      linear-gradient(180deg, rgba(246, 240, 215, 0.94) 0%, rgba(239, 227, 191, 0.94) 36%, rgba(211, 182, 125, 0.94) 100%);
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
  .kw-mobile-shell {
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  .kw-menu-panel {
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
  #modal .modal-dialog {
    width: calc(100vw - 24px);
    max-width: 500px;
    margin: 18px auto;
  }
}
@media (min-width: 768px) and (max-width: 991px) {
  .kw-mobile-shell {
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
  #menu_mobile .btn {
    height: 86px !important;
    font-size: 12px !important;
  }
}
</style>
<script>
function kugSetHtmlTargets(selector, html) {
  var targets = document.querySelectorAll(selector);
  for (var i = 0; i < targets.length; i++) {
    targets[i].innerHTML = html;
  }
}
</script>
<body id="whole">
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div id="header" class="header navbar-default" style="background-color: #6F9F0766 !important;">
      <div class="navbar_header" id="sub_cat"></div>
    </div>
    <div class="desktop-shell">
      <div id="content" class="content">
        <div class="vertical-box">
          <div class="vertical-box-column" style="width: 7%; background-color: transparent;">
            <div id="cat_desktop"><span class="text-muted">Loading categories...</span></div>
          </div>
          <div class="vertical-box-column" style="width: 65%;">
            <div id="menu" style="margin-left: 10px;"></div>
          </div>
          <div class="vertical-box-column" style="width: 28% !important; height: 90%; position: absolute; padding: 2px;">
            <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'select_supplier') { ?>
            <div class="alert alert-warning" style="font-size:18px; font-weight:bold;">Select a supplier before raising the purchase.</div>
            <?php } ?>
            <div class="wrapper" style="background-color: #B10421; text-align: center; padding: 8px 6px;">
              <div class="row" style="margin: 0 0 8px 0; display: flex; align-items: stretch;">
                <div class="col-4" style="padding-left: 0; padding-right: 4px;">
                  <button id="supplier-btn" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal" style="font-weight: bold; width: 100%; min-height: 78px; font-size: 20px; white-space: normal;">SELECT SUPPLIER</button>
                </div>
                <div class="col-8" style="padding-left: 4px; padding-right: 0;">
                  <div id="supplier-status" class="touch-card" style="background-color: <?php echo $sup_id > 0 ? '#fff6c7' : '#f6d3d9'; ?>; color: <?php echo $sup_id > 0 ? '#6a4d00' : '#7c1f2d'; ?>; margin-bottom: 0; min-height: 78px;">
                    <div style="font-size: 15px; font-weight: bold;">SUPPLIER SELECTED</div>
                    <div id="supplier-name" style="font-size: 22px; font-weight: bold; line-height: 1.2;"><?php echo $sup_id > 0 ? strtoupper(get_sup($sup_id)) : 'NONE'; ?></div>
                  </div>
                </div>
              </div>
              <div id="tax-wrap" class="touch-card" style="background-color: #b10421; display: <?php echo $sup_id > 0 ? 'block' : 'none'; ?>;">
                <label class="touch-tax">
                  <input type="checkbox" id="buy-tax" <?php echo $buy_tax === 1 ? 'checked' : ''; ?>>
                  ADD TAX
                </label>
              </div>
              <h3 style="color: white; font-size: 24px;"><?php echo get_counter($counter); ?><br> BUY STOCK NO. <?php echo $po_id; ?></h3>
              <p style="color: #fff; margin: 4px 0 0;">Select a supplier first, then raise the purchase here for backend approval and receiving.</p>
            </div>
            <div id="screen" style="overflow-y: scroll; height: 70%; width: auto; background-color: #fff; padding: 5px;"></div>
            <div class="wrapper">
              <button id="btn_sub" class="btn btn_ord <?php echo $sup_id > 0 ? '' : 'btn-disabled-soft'; ?>" data-toggle="modal" data-target="#modal" style="padding-top: 10px;">RAISE PURCHASE</button>
              <button data-toggle="modal" data-target="#modal" type="button" class="btn_ord" id="cancel">CANCEL</button><br>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mobile-shell">
      <div class="kw-mobile-shell">
        <div class="kw-topbar">
          <div class="kw-brand">
            <div class="kw-brand-main">
              <span class="kw-account-line">Buyer: <?php echo get_user($user_id); ?></span>
              <span class="kw-counter-line">Counter: <?php echo get_counter($counter); ?></span>
            </div>
            <div class="kw-bill-badge">
              <strong>Buy No</strong>
              <span><?php echo $po_id; ?></span>
            </div>
          </div>
          <div class="kw-top-actions">
            <button onclick="location.reload();" class="kw-top-btn" type="button">Refresh</button>
            <button id="supplier-btn-mobile" data-toggle="modal" data-target="#modal" class="kw-top-btn" type="button">Supplier</button>
          </div>
          <div class="kw-tax-mobile" id="tax-wrap-mobile" style="display: <?php echo $sup_id > 0 ? 'block' : 'none'; ?>;">
            <label class="touch-tax" style="font-size:16px; padding:12px 10px;">
              <input type="checkbox" id="buy-tax-mobile" <?php echo $buy_tax === 1 ? 'checked' : ''; ?>>
              ADD TAX
            </label>
          </div>
        </div>

        <div class="kw-card">
          <p class="kw-section-title">Categories</p>
          <div class="kw-cat-scroll">
            <div id="cat_mobile"><span class="text-muted">Loading categories...</span></div>
          </div>
          <div id="sub_cat_mobile" style="margin-top:6px;"></div>
        </div>

        <div class="kw-card kw-menu-panel">
          <p class="kw-section-title">Items</p>
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
            <p class="kw-section-title" style="margin:0;">Current Purchase</p>
            <button type="button" class="kw-cart-close" id="kw-cart-close">&times;</button>
          </div>
          <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'select_supplier') { ?>
          <div class="alert alert-warning" style="font-size:14px; font-weight:bold;">Select a supplier before raising the purchase.</div>
          <?php } ?>
          <div class="kw-info-strip">
            <b id="supplier-name-mobile"><?php echo $sup_id > 0 ? strtoupper(get_sup($sup_id)) : 'NO SUPPLIER SELECTED'; ?></b>
            <span><?php echo get_counter($counter); ?> BUY STOCK NO. <?php echo $po_id; ?></span>
          </div>
          <div id="screen_mobile"></div>
          <div class="kw-order-actions">
            <button id="btn_sub_mobile" class="kw-order-btn kw-order-send <?php echo $sup_id > 0 ? '' : 'btn-disabled-soft'; ?>" data-toggle="modal" data-target="#modal" type="button">Raise Purchase</button>
            <button data-toggle="modal" data-target="#modal" type="button" class="kw-order-btn kw-order-cancel" id="cancel-mobile">Cancel</button>
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
  <link rel="stylesheet" href="../assets/css/virtual_keyboard.css">
  <script src="../assets/js/virtual_keyboard.js"></script>
  <script src="buy_scripts.js?v=<?php echo filemtime(__DIR__ . '/buy_scripts.js'); ?>"></script>
</body>
</html>
