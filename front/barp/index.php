<!doctype html>
<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bar Counter Dashboard</title>
    <link rel="stylesheet" href="assets/barpCss.css?v=<?php echo filemtime(__DIR__ . '/assets/barpCss.css'); ?>">

</head>
<body>
  <main class="page">
    <header class="header">
      <h1>Bar Counter Dashboard</h1>
      <div class="actions">
        <button class="home" onclick="location.href='../dash/index.php'">&#8962; &nbsp; Home</button>
        <button class="refresh" onclick="location.reload()">&#8635; &nbsp; Refresh</button>
        <button class="logout" onclick="location.href='../log_out.php'">&#10162; &nbsp; Logout</button>
      </div>
    </header>
    <section class="card waiters">
      <h2>Waiters Posted to<br>This Counter</h2>
      <div class="waiter-list" id="waiter-list">
        <div class="empty">Loading waiters...</div>
      </div>
    </section>
    <section class="overview">
      <div class="hero">
        <small>COUNTER SALES - CURRENT SHIFT</small>
        <div class="privacy-total"><strong id="sales" class="privacy-value privacy-hero">&mdash;</strong><button type="button" class="privacy-eye" data-privacy-group="hero" aria-label="Hold to view sales total">&#128065;</button></div>
        <button class="balance" id="counter-sales-balance">! &nbsp; SALES &amp; ITEMS NOT BALANCED</button>
        <div class="change">
          <b>&#8593; 12.4%</b>
          <small>vs previous shift</small>
        </div>
        <div class="chart">
          <svg viewBox="0 0 640 150" preserveAspectRatio="none">
            <g class="gridline">
              <line x1="38" y1="18" x2="625" y2="18"/>
              <line x1="38" y1="58" x2="625" y2="58"/>
              <line x1="38" y1="98" x2="625" y2="98"/>
              <line x1="38" y1="138" x2="625" y2="138"/>
            </g>
            <path class="chart-fill" d="M42 112 L157 102 L272 118 L388 82 L503 105 L620 64 L620 138 L42 138 Z"/>
            <path class="line chart-line" d="M42 112 L157 102 L272 118 L388 82 L503 105 L620 64"/>
            <g class="point chart-point"><circle cx="42" cy="112" r="3.5"></circle><circle cx="157" cy="102" r="3.5"></circle><circle cx="272" cy="118" r="3.5"></circle><circle cx="388" cy="82" r="3.5"></circle><circle cx="503" cy="105" r="3.5"></circle><circle cx="620" cy="64" r="3.5"></circle></g>
            <g class="labels">
              <!-- The six hour labels are filled from the live API data. -->
            </g>
          </svg>
        </div>
      </div>
      <div class="card breakdown">
        <h2>Sales Breakdown</h2>
        <div class="donut">
          <span id="donut-total" class="privacy-value privacy-hero">&mdash;</span>
        </div>
        <div class="legend" id="sales-legend"></div>
          </div>
        </section>
        <section class="counter-kpis">
            <div class="card counter-kpi-card"><span>&#8635;</span><small>Total Returns</small><strong id="kpi-returns">0</strong></div>
            <div class="card counter-kpi-card"><span>&times;</span><small>Total Voids</small><strong id="kpi-voids">0</strong></div>
            <div class="card counter-kpi-card"><span>&#9635;</span><small>Total Orders</small><strong id="kpi-orders">0</strong></div>
            <div class="card counter-kpi-card"><span>&#9635;</span><small>Items Out</small><strong id="kpi-items">0</strong></div>
        </section>
          <section class="card receipts">
            <h2>Last 10 Receipts</h2>
            <div class="receipt-scroll">
              <table>
                <thead>
                  <tr>
                    <th>Bill</th>
                    <th>User</th>
                    <th>Time</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="receipt-list">
                  <tr><td colspan="5" class="empty">Loading receipts...</td></tr>
                </tbody>
              </table>
            </div>
          </section>
          <section class="card totals">
            <h2>Counter Totals <button type="button" class="privacy-eye" data-privacy-group="totals" aria-label="Hold to view counter totals">&#128065;</button></h2>
            <div class="total-row">
              <i>&#9635;</i>
              <span>Total Orders</span>
                    <b id="metric-orders" class="privacy-value privacy-totals">0</b>
            </div>
            <div class="total-row">
              <i>&#9633;</i>
              <span>Items Sold</span>
                    <b id="metric-items" class="privacy-value privacy-totals">0</b>
            </div>
            <div class="total-row">
              <i>&#8635;</i>
              <span>Returns</span>
                    <b id="metric-returns" class="privacy-value privacy-totals">0</b>
            </div>
            <div class="split">
              <div>
                <i class="green">&#10003;</i>
                <span>Approved<br>
                <b id="metric-approved" class="privacy-value privacy-totals">0</b>
                </span>
              </div>
              <div>
                <i class="bad">&times;</i>
                <span>Rejected<br>
                <b id="metric-rejected" class="privacy-value privacy-totals">0</b>
                </span>
              </div>
            </div>
            <div class="split">
              <div>
                <i>&#8596;</i>
                <span>Transfers In<br>
                    <b id="metric-in" class="privacy-value privacy-totals">0</b>
                </span>
              </div>
              <div>
                <i>&#8596;</i>
                <span>Transfers Out<br>
                    <b id="metric-out" class="privacy-value privacy-totals">0</b>
                </span>
              </div>
            </div>
            <div class="total-row">
              <i class="bad">&#9675;</i>
              <span>Counter Status</span>
              <b class="bad">&mdash;</b>
            </div>
          </section>
        </section>
        <section class="card alerts">
          <div class="panel">
            <h2>Alerts</h2>
            <div class="scroll">
              <div id="alerts-empty" class="empty">No alerts</div>
              <div class="alert" data-alert="reorder" tabindex="0"><span>&#9888; Below Reorder</span><b id="alert-reorder">0</b></div>
              <div class="alert" data-alert="out" tabindex="0"><span>&#8855; Stock Out</span><b id="alert-out">0</b></div>
              <div class="alert" data-alert="returns" tabindex="0" role="button"><span>&#8635; Returns</span><b id="alert-returns">0</b></div>
              <div class="alert" data-alert="receiving" tabindex="0"><span>&#9635; Requests Pending Receiving</span><b id="alert-requests">0</b></div>
              <div class="alert"><span>&#8594; Transfers Sent</span><b id="alert-transfers">0</b></div>
              <div class="alert"><span>&#8596; Transfer Requests</span><b id="alert-transfer-requests">0</b></div>
              <div class="alert"><span>! Voids</span><b id="alert-voids">0</b></div>
            </div>
          </div>
        </section>
        <section class="card performance">
          <div class="panel">
            <h2>Waiter Performance</h2>
            <div class="scroll" id="performance-list">
              <div class="empty">Loading performance...</div>
            </div>
          </div>
        </section>
      </main>
      <div class="modal" id="user-modal"><div class="modal-card"><div class="summary" id="summary"></div></div></div>
      <div class="modal" id="modal"><div class="modal-card"><div class="modal-head"><h2>Select printer</h2><button id="modal-close">&times;</button></div><div class="printers" id="printers"></div><div class="modal-actions"><button id="modal-cancel">Cancel</button><button id="modal-print">Print totals</button></div></div></div>
      <div class="modal" id="pending-change-modal" aria-hidden="true"><div class="modal-card"><div class="modal-head pending-change-head"><div><span class="pending-change-kicker">PENDING CHANGE</span><h2>Change in progress</h2></div><button id="pending-change-close" aria-label="Close">&times;</button></div><div class="pending-change-body" id="pending-change-list"></div></div></div>
      <div class="modal" id="alert-modal" aria-hidden="true"><div class="modal-card"><div class="modal-head"><h2 id="alert-modal-title">Stock alerts</h2><button type="button" class="close-modal-button" id="alert-modal-close" aria-label="Close modal">Close modal</button></div><div class="summary" id="alert-modal-body"></div></div></div>
      <style>
        .add-stock-alert-btn{border:0;border-radius:5px;padding:7px 11px;background:#2775ef!important;color:#fff;font-weight:800;cursor:pointer;}
        .add-stock-alert-btn:hover{background:#155dcc!important;}
      </style>
      <script src="assets/barpJs.js?v=<?php echo filemtime(__DIR__ . '/assets/barpJs.js'); ?>"></script>
    </body>
  </html>

