<?php require_once '../clude/head.php'; ?>
<link rel="stylesheet" href="kug.css">
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu kug-page">
  <?php require_once '../clude/header.php'; ?>
  <?php require_once '../clude/top_menu.php'; ?>
  <div class="content">
  
  <div class="title"><span>🛒</span>PURCHASE MANAGEMENT DASHBOARD</div>
  <div class="shell">
    <aside class="sidebar">
      <div class="home"><button>⌂<span>HOME</span></button><button onclick="location.reload()">⟳<span>REFRESH</span></button></div>
      <section class="box"><h2>♟ REMINDERS</h2><div class="list"><button><i>◉</i>Pending Approvals<b class="violet">1</b></button><button><i>▣</i>Pending Receiving<b class="orange">6</b></button><button><i>◉</i>In Ordering<b class="blue">4</b></button><button><i>▧</i>In Packaging<b class="violet">3</b></button><button><i>▰</i>In Shipping<b class="teal">5</b></button><button><i>●</i>Delayed Shipments<b class="red">1</b></button><button><i>▣</i>Suppliers<b class="green">5</b></button></div></section>
      <section class="box"><h2>⚙ OPERATIONS</h2><div class="section-list"><button type="button" id="generate-lpo" class="kug-modal-trigger"><span>⊕</span>Generate LPO</button><button><span>●</span>Approve LPO <b class="badge">1</b></button><button><span>⌂</span>Local Purchases</button><button><span>♨</span>International Purchases</button><button><span>▰</span>Shipping</button><button><span>●</span>Receive Goods (GRN) <b class="badge" style="background:#f27800">6</b></button><button><span>☷</span>Active Orders</button><button><span>▤</span>GRN History</button></div></section>
      <section class="box"><h2>▰ SUPPLIER MANAGEMENT</h2><div class="section-list"><button><span>▥</span>Manage Suppliers</button><button><span>⊕</span>Add Supplier</button></div></section>
      <section class="box"><h2>◔ REPORTS</h2><div class="section-list"><button><span>▧</span>PO List</button><button><span>▧</span>International Purchase Report</button><button><span>▧</span>Shipping Report</button><button><span>▧</span>Supplier List</button><button><span>△</span>GRN Variances</button><button><span>▧</span>Supplier Statement</button></div></section>
    </aside>
    <main class="main">
      <div class="kpis"><article class="kpi"><small>TODAY'S PURCHASE ORDERS</small><b>0</b><span>orders created today</span><i>🛒</i></article><article class="kpi"><small>PENDING APPROVAL</small><b>1</b><span>awaiting manager</span><i>◷</i></article><article class="kpi"><small>ORDERING</small><b>4</b><span>purchase orders</span><i>▰</i></article><article class="kpi"><small>PACKAGING</small><b>3</b><span>orders being packed</span><i>▣</i></article><article class="kpi"><small>IN SHIPPING</small><b>5</b><span>orders in transit</span><i>▰</i></article><article class="kpi"><small>PENDING RECEIVING</small><b>6</b><span>awaiting receipt</span><i>▣</i></article></div>
      <div class="values"><article class="value"><small>SPEND THIS MONTH</small><b>KSh 12.0K</b><span>Total approved PO value in current month</span><i>▥</i></article><article class="value"><small>INTERNATIONAL ORDERS VALUE</small><b>KSh 2.84M</b><span>Total value of international orders</span><i>◎</i></article><article class="value"><small>SHIPPING &amp; CUSTOMS</small><b>KSh 486K</b><span>Shipping and customs costs</span><i>▰</i></article><article class="value"><small>ALL-TIME SPEND</small><b>KSh 1.03M</b><span>Cumulative approved PO value</span><i>⌁</i></article></div>
      <div class="middle">
        <section class="panel trend"><h2 class="panel-title">⌁ 6-MONTH PURCHASE TREND <span class="filters"><label>◉ Local</label><label>○ International</label><label>○ All</label></span></h2><div class="trend-wrap"><div class="chart-legend"><span><i></i>PO Count</span><span><i></i>Amount (KSh)</span></div><svg viewBox="0 0 800 235" role="img" aria-label="Six month purchase trend"><g><line class="grid" x1="45" x2="755" y1="190" y2="190"/><line class="grid" x1="45" x2="755" y1="150" y2="150"/><line class="grid" x1="45" x2="755" y1="110" y2="110"/><line class="grid" x1="45" x2="755" y1="70" y2="70"/><line class="grid" x1="45" x2="755" y1="30" y2="30"/></g><g><rect class="bar" x="70" y="58" width="58" height="132"/><rect class="bar" x="197" y="104" width="58" height="86"/><rect class="bar" x="324" y="156" width="58" height="34"/><rect class="bar" x="451" y="174" width="58" height="16"/><rect class="bar" x="578" y="177" width="58" height="13"/><rect class="bar" x="705" y="156" width="40" height="34"/></g><polyline class="line" points="99,28 226,104 353,151 480,172 607,176 725,158"/><g><circle cx="99" cy="28" r="3"/><circle cx="226" cy="104" r="3"/><circle cx="353" cy="151" r="3"/><circle cx="480" cy="172" r="3"/><circle cx="607" cy="176" r="3"/><circle cx="725" cy="158" r="3"/></g><g text-anchor="middle"><text x="99" y="212">Mar 20</text><text x="226" y="212">Apr 20</text><text x="353" y="212">May 20</text><text x="480" y="212">Jun 20</text><text x="607" y="212">Jul 20</text><text x="725" y="212">Aug 20</text></g></svg></div></section>
        <section class="panel pipeline"><h2 class="panel-title">▰ ORDER PIPELINE</h2><div class="pipeline-body"><div class="stage ordering"><div class="name"><span class="ico">🛒</span><div><small>ORDERING</small><b>4 Orders</b></div></div><div class="track"><i style="width:22%"></i></div><em>22%</em><strong>KSh 820K</strong></div><div class="stage packaging"><div class="name"><span class="ico">📦</span><div><small>PACKAGING</small><b>3 Orders</b></div></div><div class="track"><i style="width:17%"></i></div><em>17%</em><strong>KSh 640K</strong></div><div class="stage shipping"><div class="name"><span class="ico">🚚</span><div><small>SHIPPING</small><b>5 Orders</b></div></div><div class="track"><i style="width:28%"></i></div><em>28%</em><strong>KSh 1.38M</strong></div><div class="stage receiving"><div class="name"><span class="ico">✓</span><div><small>RECEIVING</small><b>6 Orders</b></div></div><div class="track"><i style="width:33%"></i></div><em>33%</em><strong>KSh 972K</strong></div></div><div class="pipeline-total"><span>Active Pipeline: 18 Orders</span><span>Total Value: &nbsp; KSh 3.81M</span></div></section>
      </div>
      <div class="lower">
        <section class="panel tracking"><h2 class="panel-title">◎ LAST FIVE PURCHASES</h2><table><thead><tr><th>PO #</th><th>SUPPLIER</th><th>DATE</th><th>AMOUNT</th><th>STATUS</th></tr></thead><tbody id="latest-purchases-body"><tr><td colspan="5" class="text-center">Loading purchases...</td></tr></tbody></table></section>
        <section class="panel quick"><h2 class="panel-title">ϟ QUICK ACTIONS</h2><div class="quick-grid"><button><b>⊕</b>New Local PO</button><button><b>◎</b>New International PO</button><button><b>🚚</b>Update Shipping</button><button><b>▣</b>Receive Goods</button><button><b>☷</b>Active Orders</button><button><b>♟</b>Suppliers</button></div></section>
      </div>
    </main>
  </div>
  <button class="help">?<span>HELP</span></button>
</div>
</div>
<?php require_once '../clude/foot.php'; ?>
<div id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display:none;">
  <div class="modal-dialog" id="load"></div>
</div>
<div id="modal2" class="modal fade bs-example-modal-lg" tabindex="1" role="dialog" aria-labelledby="myModalLabel" style="display:none;">
  <div class="modal-dialog" id="load2"></div>
</div>
<script src="kug.js"></script>
