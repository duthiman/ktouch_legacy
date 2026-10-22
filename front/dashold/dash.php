<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="theme-color" content="#8c2f1d">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Landing</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="dash_desktop.css">
  <link rel="stylesheet" href="dash_mobile.css">
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body class="fixed-sn deep-orange-skin">
  <div id="dash-page">
    <div class="desktop-shell">
      <header>
        <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
          <ul class="nav navbar-nav navbar-left">
            <li class="nav-item dash-open-shift" style="display:none;margin-left:10px;margin-top:10px;"><a href="../shift/open_shift.php" class="btn btn-sm btn-success">OPEN SHIFT</a></li>
            <li class="nav-item dash-open-counter" style="display:none;margin-left:10px;margin-top:10px;"><button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal" id="open">OPEN COUNTER</button></li>
          </ul>
          <ul class="nav navbar-nav navbar-right" style="margin-right:10px;margin-top:10px;">
            <li class="nav-item"><a href="../log_out.php" class="btn btn-sm btn-success">Log Out</a></li>
          </ul>
        </nav>
      </header>
      <main class="container-fluid">
        <div class="container" style="margin-top:90px;">
          <div id="dash-desktop-sale-cap"></div>
          <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4 cont">
              <div id="dash-desktop-alerts"></div>
              <div class="dash-desktop-actions" style="display:none;">
                <button type="button" data-toggle="modal" data-target="#modal" id="sell" class="btn btn-sm m-b-10 btn-landing">PLACE ORDER</button>
                <a href="../open/index.php" class="btn btn-sm m-b-10 btn-landing">CONTINUE BILL</a>
                <a href="../view/my_sales.php" class="btn btn-sm m-b-10 btn-landing">VIEW ORDERS</a>
                <button type="button" data-toggle="modal" data-target="#modal" id="cpass" class="btn btn-sm m-b-10 btn-landing">CHANGE PASS</button>
                <a href="../kpesa/mpesas.php" target="_blank" rel="noopener" class="btn btn-sm m-b-10 btn-landing">K-PESA</a>
              </div>
              <div class="dash-desktop-manager-actions" style="display:none;">
                <a href="../ret/appr/returns.php" class="btn btn-sm m-b-10 btn-landing">RETURNS</a>
                <a href="../cash" class="btn btn-sm m-b-10 btn-landing">CLEAR BILL</a>
                <a href="../stock/store_dash.php" class="btn btn-sm m-b-10 btn-landing">STOCK</a>
                <button type="button" data-toggle="modal" data-target="#modal" id="close-counter" class="btn btn-sm m-b-10 btn-close">CLOSE COUNTER</button>
              </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12" style="border-radius:5px;border:4px solid #e2752a;width:26%;margin-left:20px;background-color:white;">
              <div style="padding-top:20px;">
                <div style="width:100%;" class="desktop-token-card">
                  <h4>My Tokens</h4><div id="dash-token-total" class="desktop-token-total">0.00</div>
                  <div class="desktop-token-grid"><div class="desktop-token-pill"><strong>This Month</strong><span id="dash-token-month">0.00</span></div><div class="desktop-token-pill"><strong>Today</strong><span id="dash-token-today">0.00</span></div></div>
                </div>
                <table class="table table-striped table-bordered"><tbody>
                  <tr><td><b>TOTAL BILLS</b></td><td><b id="dash-sales-total-bills">0</b></td></tr>
                  <tr><td><b>TOTAL SALES</b></td><td><b id="dash-sales-total-sales">0.00</b></td></tr>
                  <tr><td><b>RETURNED ITEMS</b></td><td><b id="dash-returns-total">0</b></td></tr>
                  <tr><td><b>VOIDED BILLS</b></td><td><b id="dash-sales-voided">0</b></td></tr>
                  <tr><td><b>TOTAL MPESA</b></td><td><b id="dash-sales-mpesa">0.00</b></td></tr>
                  <tr><td><b>TOTAL ITEMS SOLD</b></td><td><b id="dash-sales-items">0.00</b></td></tr>
                </tbody></table>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <div class="mobile-shell">
      <div class="app-shell">
        <div class="app-topbar"><div class="brand-row"><div><h1>K-Touch</h1><p id="dash-mobile-user-line"></p></div><div class="brand-row-actions"><div class="app-badge"><strong>Open</strong><span id="dash-mobile-open-counters">0/0</span></div></div></div>
          <div class="top-actions"><a href="../shift/open_shift.php" class="dash-mobile-open-shift">Open Shift</a><button type="button" data-toggle="modal" data-target="#modal" id="open-mobile" class="dash-mobile-open-counter">Open Counter</button><a href="../log_out.php">Log Out</a></div>
        </div>
        <div id="dash-mobile-alerts"></div><div id="dash-mobile-sale-cap"></div>
        <div class="menu-card"><p class="section-title">Quick Actions</p><div class="action-grid dash-mobile-actions" style="display:none;">
          <button type="button" data-toggle="modal" data-target="#modal" id="sell-mobile" class="app-btn btn-order"><span>Sales</span><b>Place Order</b></button>
          <a href="../open/index.php" class="app-btn btn-view"><span>Continue</span><b>Continue Bill</b></a>
          <a href="../view/my_sales.php" class="app-btn btn-view"><span>Orders</span><b>View Orders</b></a>
          <button type="button" data-toggle="modal" data-target="#modal" id="cpass-mobile" class="app-btn btn-pass"><span>Security</span><b>Change Pass</b></button>
          <a href="../ret/appr/returns.php" class="app-btn btn-return dash-mobile-manager-action">Returns</a>
          <a href="../cash" class="app-btn btn-bill dash-mobile-manager-action">Clear Bill</a>
          <a href="../stock/store_dash.php" class="app-btn btn-stock dash-mobile-manager-action">Stock</a>
          <button type="button" data-toggle="modal" data-target="#modal" id="close-counter-mobile" class="app-btn btn-closecounter dash-mobile-manager-action">Close Counter</button>
        </div></div>
        <div id="dash-mobile-stats"></div><div class="mobile-footer">Built for touch. Tap a card to continue.</div>
      </div>
    </div>
  </div>

  <div id="modal" class="modal fade">
    <div class="modal-dialog" id="load"></div>
  </div>

  <script src="dash.js"></script>
</body>
</html>
