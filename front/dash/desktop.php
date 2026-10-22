<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>K-TOUCH Waiter Dashboard</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/desktop.css?v=20261011-profile2">
</head>
<body data-layout="desktop">
  <header class="topbar"> 
    <nav class="top-actions">
      <button class="refresh">⟳ <span>Refresh</span></button>
      <button class="open-counter">▣ <span>Open Counter</span></button>
      <button class="open-shift">☀ <span>Open Shift</span></button>
      <button class="logout">⇥ <span>Log Out</span></button>
      <button class="home">⌂ <span>Home</span></button>
    </nav>
  </header>

  <main class="dashboard">
    <section class="column">
      <section class="profile">
        <div class="identity">
          <h2></h2>
          <div class="id-row">
            <b></b>
            <span></span>
          </div>
          <p></p>
          <em></em>
        </div>
        <div class="profile-info">
          <div class="shift-row">
            <div><i>▣</i><span><small>Counter</small><b></b></span></div>
            <div><i>☀</i><span><small>Shift</small><b></b></span></div>
            <div><i>▦</i><span><b></b></span></div>
          </div>
          <div class="sales-row">
            <div class="sales-summary-trigger" role="button" tabindex="0"><small>TODAY'S SALES</small><b></b></div>
            <div><small>ORDERS SERVED</small><b></b></div>
            <div><b></b><small>TOP WAITER TODAY</small></div>
          </div>
        </div>
        <div class="rank profile-rank">
          <span class="rank-crown">♛</span>
          <small>TODAY'S RANK</small>
          <strong></strong>
          <em>IN TODAY'S SALES</em>
        </div>
        <div class="profile-actions">
          <button class="open-counter">▣ <span>OPEN COUNTER</span><b>›</b></button>
          <button class="refresh">⟳ <span>REFRESH</span><b>›</b></button>
          <button class="logout">⇥ <span>LOG OUT</span><b>›</b></button>
        </div>
      </section>

      <section class="card common"> 
        <div class="action-grid">
          <button class="action featured place"><i>🛒</i><span class="copy"><b>PLACE ORDER</b></span></button>
          <button class="action featured continue"><i>≫</i><span class="copy"><b>CONTINUE BILL</b></span></button>
          <button class="action view-orders"><i>▤</i><span class="copy"><b>VIEW ORDERS</b></span></button>
          <button class="action kpesa"><i>▯</i><span class="copy"><b>K-PESA</b></span></button>
          <button class="action wide lock"><i>♜</i><span class="copy"><b>CHANGE PASS</b></span></button>
        </div>
      </section>

      <section class="card role" id="role-actions-card">
        <div class="action-grid">
          <button class="action butchery"><i>★</i><span class="copy"><b>BUTCHERY</b></span><em>›</em></button>
          <button class="action barman"><i>🍸</i><span class="copy"><b>BARMAN</b></span><em>›</em></button>
          <button class="action clear"><i>▰</i><span class="copy"><b>CLEAR BILL</b></span></button>
          <button class="action returns"><i>↩</i><span class="copy"><b>RETURNS</b></span></button>
          <button class="action stock"><i>◆</i><span class="copy"><b>STOCK</b></span><em>›</em></button>
          <button class="action wide danger close-counter"><i>⏻</i><span class="copy"><b>CLOSE COUNTER</b></span></button>
        </div>
      </section>
    </section>

    <aside class="column">
      <section class="card approval">
        <header><h2>▲ &nbsp; Needs Approval</h2></header>
        <div class="approval-list">
          <button class="review-returns"><i>↩</i><span>Returns not approved</span><strong></strong><b></b><em>REVIEW</em></button>
          <button class="review-mpesa"><i>▯</i><span>M-Pesa claimed<br>not approved</span><strong></strong><b></b><em>REVIEW</em></button>
          <button class="review-voids"><i>⊘</i><span>Voided bills<br>not approved</span><strong></strong><b></b><em>REVIEW</em></button>
        </div>
      </section>

      <section class="wallet">
        <h2>My Token Wallet</h2>
        <div class="wallet-content">
          <span class="coin">◉</span>
          <div class="wallet-stat"><small>Balance</small><b></b></div>
          <div class="wallet-stat"><small>This Month</small><b></b></div>
          <div class="wallet-stat"><small>Today</small><b></b></div>
        </div>
        <button class="claim">CLAIM TOKENS &nbsp; →</button>
      </section>

      <section class="card orders">
        <header><h2>Recent Orders</h2><button class="view-all">View All</button></header>
        <table>
          <thead><tr><th>Bill No.</th><th>Time</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
          <tbody></tbody>
        </table>
      </section>
    </aside>
</main>
<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" id="load"></div>
</div>
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
<script src="assets/js/dashboard.js?v=20261013-permissions2"></script>
</body>
</html>
