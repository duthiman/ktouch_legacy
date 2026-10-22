<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Transfer</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <style type="text/css">
    body { background: url("../assets/img/bg/5.jpg") no-repeat center center fixed; background-size: cover; }
    .desktop-shell { display: block; }
    .mobile-shell { display: none; }
    .req-mobile-shell { max-width: 520px; margin: 0 auto; padding: 0 5px 30px; }
    .req-topbar { position: sticky; top: 10px; z-index: 20; margin-bottom: 12px; padding: 10px 14px 14px; border-radius: 10px; background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); box-shadow: 0 18px 30px rgba(74, 37, 11, 0.18); color: #fff8ea; }
    .req-brand { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; }
    .req-counter-line, .req-number-line { display: block; margin: 0; color: #fff8ea; font-size: 12px; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .req-number-line { margin-top: 4px; opacity: 0.92; }
    .req-badge { min-width: 82px; padding: 7px 9px; border-radius: 10px; text-align: center; background: rgba(255,255,255,0.15); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18); }
    .req-badge strong { display: block; font-size: 9px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
    .req-badge span { display: block; margin-top: 2px; font-size: 18px; font-weight: 900; }
    .req-top-actions, .req-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .req-top-actions { margin-top: 12px; }
    .req-top-btn, .req-action-btn { min-height: 46px; border: 0; border-radius: 10px; color: #fff !important; font-size: 13px; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; justify-content: center; text-align: center; text-decoration: none !important; }
    .req-top-btn { background: rgba(255,255,255,0.16); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18); }
    .req-card { background: rgba(255,255,255,0.92); border: 1px solid rgba(108, 78, 31, 0.08); border-radius: 10px; box-shadow: 0 16px 28px rgba(86, 61, 24, 0.12); padding: 14px; margin-bottom: 10px; }
    .req-section-title { margin: 0 0 10px; font-size: 13px; font-weight: 900; letter-spacing: 0.08em; color: #7e3a20; text-transform: uppercase; }
    .req-cat-scroll, #sub_cat_mobile { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; white-space: nowrap; }
    .req-mobile-cat, #sub_cat_mobile button { border: 3px solid #B10421; border-radius: 10px; min-height: 46px; min-width: 88px; padding: 8px 10px; background: #000; color: #fff !important; font-size: 12px; font-weight: 800; text-transform: uppercase; white-space: normal; }
    #menu_mobile { min-height: 180px; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; }
    #menu_mobile #getItem, #menu_mobile .btn { width: 100% !important; min-width: 0 !important; height: 72px !important; margin: 0 !important; padding: 4px !important; border-radius: 10px !important; font-size: 11px !important; line-height: 1.15 !important; display: flex !important; align-items: center; justify-content: center; text-align: center; }
    #screen_mobile { max-height: 42vh; overflow-y: auto; background: #fff; border-radius: 10px; border: 1px solid rgba(108, 78, 31, 0.08); padding: 8px; }
    .req-account-strip { padding: 12px; border-radius: 10px; background: linear-gradient(135deg, #8a1232 0%, #c3385c 100%); color: #fff; text-align: center; margin-bottom: 10px; }
    .req-submit-btn { background: linear-gradient(135deg, #2f5d17 0%, #58a03f 100%); }
    .req-cancel-btn { background: linear-gradient(135deg, #7a1717 0%, #b22d2d 52%, #db6b4a 100%); }
    .req-cart-fab { position: fixed; right: 14px; bottom: 18px; z-index: 1035; display: none; align-items: center; gap: 8px; min-height: 56px; padding: 0 14px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #8a1232 0%, #c3385c 100%); color: #fff; }
    .req-cart-drawer { position: fixed; left: 8px; right: 8px; bottom: 8px; z-index: 1040; display: none; }
    .req-cart-drawer.open { display: block; }
    .req-cart-close { width: 38px; height: 38px; border: 0; border-radius: 50%; background: #f0d8dd; color: #8a1232; font-size: 22px; font-weight: 900; line-height: 1; }
    #modal { overflow-y: auto !important; padding-right: 0 !important; }
    #load.modal-dialog { width: calc(100vw - 32px); max-width: 560px; margin: 28px auto; }
    #load .modal-content { max-height: calc(100vh - 56px); overflow: hidden; }
    @media (max-width: 991px) { .desktop-shell { display: none; } .mobile-shell { display: block; height: 100vh; } .req-mobile-shell { height: 100vh; display: flex; flex-direction: column; overflow: hidden; } .req-cart-fab { display: flex; } }
  </style>
</head>
<body id="whole">
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div class="desktop-shell">
      <div id="header" class="header navbar-default" style="background-color: #6F9F0766 !important;">
        <div class="navbar-header" id="sub_cat"></div>
      </div>
      <div id="content" class="content">
        <div class="vertical-box">
          <div class="vertical-box-column" style="width: 7%; background-color: transparent;" id="trans-desktop-categories"></div>
          <div class="vertical-box-column" style="width: 65%;"><div id="menu" style="margin-left: 10px;"></div></div>
          <div class="vertical-box-column" style="width: 28% !important; height: 90%; position: absolute; padding-right: 2px;">
            <div class="wrapper" style="background-color: #B10421; text-align: center;"><b style="color: white; font-size: 20px;">TRANSFER ITEMS FOR NO <span id="trans-draft-number">0</span></b></div>
            <div id="screen" style="overflow-y:scroll; height: 80%; width: auto; background-color: #fff; padding:5px;"></div>
            <div class="wrapper">
              <button id="btn_sub" type="button" data-toggle="modal" data-target="#modal" class="btn btn_ord" style="padding-top: 10px;">SUBMIT TRANSFER</button>
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
              <span class="req-counter-line" id="trans-counter-name-mobile"></span>
              <span class="req-number-line">Transfer Draft</span>
            </div>
            <div class="req-badge"><strong>Trans No</strong><span id="trans-draft-number-mobile">0</span></div>
          </div>
          <div class="req-top-actions">
            <a href="../stock/store_dash.php" class="req-top-btn">Back</a>
            <a href="../dash/dash.php" class="req-top-btn">Home</a>
          </div>
        </div>
        <div class="req-card">
          <p class="req-section-title">Categories</p>
          <div class="req-cat-scroll" id="trans-mobile-categories"></div>
          <div id="sub_cat_mobile" style="margin-top:6px;"></div>
        </div>
        <div class="req-card req-menu-panel">
          <p class="req-section-title">Items</p>
          <div id="menu_mobile"></div>
        </div>
      </div>
      <button type="button" class="req-cart-fab" id="req-cart-toggle"><span class="glyphicon glyphicon-shopping-cart"></span><span class="req-cart-badge" id="req-cart-count">0</span><span class="req-cart-total" id="req-cart-total">0</span></button>
      <div class="req-cart-drawer" id="req-cart-drawer">
        <div class="req-card req-list-panel">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;"><p class="req-section-title" style="margin:0;">Current Transfer</p><button type="button" class="req-cart-close" id="req-cart-close">&times;</button></div>
          <div class="req-account-strip"><b id="trans-counter-name-strip"></b><b>Transfer No: <span id="trans-draft-number-strip">0</span></b></div>
          <div id="screen_mobile"></div>
          <div class="req-actions"><button id="btn_sub_mobile" type="button" class="req-action-btn req-submit-btn" data-toggle="modal" data-target="#modal">Submit</button><button id="cancel_mobile" type="button" class="req-action-btn req-cancel-btn" data-toggle="modal" data-target="#modal">Cancel</button></div>
        </div>
      </div>
    </div>
    <div id="modal" class="modal" tabindex="-1"><div class="modal-dialog" id="load"></div></div>
  </div>
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script src="trans_scripts.js"></script>
</body>
</html>
