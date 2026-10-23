<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>K-TOUCH Waiter Dashboard</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/mobile.css?v=20261011-profile16">
</head>
<body data-layout="mobile">
  <main class="phone"> 
    
    <section class="profile">
      <div class="profile-top-new">
        <div class="person">
          <h1></h1>
          <div class="id"><b></b><span class="role"></span></div>
           
        </div>
        <div class="rank">
          <span class="rank-crown">♛</span>
          <small>TODAY'S RANK</small> 
          <strong></strong> 
          <em>IN TODAY'S SALES</em>
        </div>
      </div>
      <div class="metric-row-new"> 
        <div class="metric sales sales-summary-trigger" role="button" tabindex="0"><span class="metric-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M5 20v-5m7 5V10m7 10V4" stroke-width="4"/></svg></span><div><small>TODAY'S SALES</small><b></b></div></div>
        <div class="metric orders">
          <span class="metric-icon"><svg class="icon" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M2 3h3l3 13h12l2-9H6"/></svg></span><div><small>ORDERS SERVED</small><b></b></div></div>
        <div class="metric with-icon"><span class="metric-icon"><svg class="icon" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6m10-6v6M3 10h18"/></svg></span><div><small>DATE</small><b></b></div></div>
      </div>
      <div class="profile-btns">
        <button class="open-shift">↗ &nbsp; OPEN SHIFT</button>
        <button class="open open-counter">▣          &nbsp; OPEN COUNTER</button>
        <button class="refresh">⟳       &nbsp; REFRESH</button>
        <button class="logout">⇥        &nbsp; LOG OUT</button>
      </div>
    </section>

    <section class="card" >
      <div class="actions">
        <button class="action primary place"><i>🛒</i><span><b>PLACE ORDER</b></span></button>
        <button class="action primary continue"><i>≫</i><span><b>CONTINUE BILL</b></span></button>
        <button class="action view-orders"><i>▤</i><span><b>VIEW ORDERS</b></span></button>
        <button class="action kpesa"><i>▯</i><span><b>K-PESA</b></span></button>
        <button class="action wide password"><i>♜</i><span><b>CHANGE PASS</b></span></button>
      </div>
    </section>

    <section class="card" id="role-actions-card"> 
      <div class="actions role-actions">
        <button class="action butchery"><i>★</i><span><b>BUTCHERY</b></span></button>
        <button class="action barman"><i>🍸</i><span><b>BARMAN</b></span></button>
        <button class="action clear"><i>▰</i><span><b>CLEAR BILL</b></span></button>
        <button class="action returns"><i>↩</i><span><b>RETURNS</b></span></button>
        <button class="action stock"><i>◆</i><span><b>STOCK</b></span></button>
        <button class="action wide danger close-counter"><i>⏻</i><span><b>CLOSE COUNTER</b></span></button>
      </div>
    </section>

    <section class="card approval">
      <header><h2>▲ &nbsp; Needs Approval</h2></header>
      <div class="approval-row review-returns"><i>↩</i><span>Returns not approved</span><strong></strong><b></b><button>REVIEW</button></div>
      <div class="approval-row review-mpesa"><i>▯</i><span>M-Pesa claimed<br>not approved</span><strong></strong><b></b><button>REVIEW</button></div>
      <div class="approval-row review-voids"><i>⊘</i><span>Voided bills<br>not approved</span><strong></strong><b></b><button>REVIEW</button></div>
    </section>

    <section class="card wallet">
      <h2>My Token Wallet</h2>
      <div class="wallet-grid">
        <i>◉</i>
        <div class="wallet-stat"><small>Balance</small><b></b></div>
        <div class="wallet-stat"><small>This Month</small><b></b></div>
        <div class="wallet-stat"><small>Today</small><b></b></div>
      </div>
      <button class="claim">CLAIM TOKENS &nbsp; →</button>
    </section>

    <section class="card orders">
      <header><h2>Recent Orders</h2><button class="all">View All</button></header>
      <table>
        <thead><tr><th>Bill No.</th><th>Time</th><th>Amount</th><th>Action</th></tr></thead>
        <tbody></tbody>
      </table>
    </section>
</main>
<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" id="load"></div>
</div>
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="assets/js/dashboard.js?v=20261013-permissions2"></script>
</body>
</html>
