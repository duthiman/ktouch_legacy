<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>K-Touch Staff Dashboard</title>
<style>
:root{
  --green:#087a35;
  --green-dark:#045c28;
  --green-soft:#eaf6ee;
  --orange:#c74408;
  --orange-dark:#a93605;
  --orange-soft:#fff0e8;
  --teal:#078b98;
  --navy:#102b3b;
  --red:#ef2028;
  --ink:#14222d;
  --muted:#68747c;
  --line:#dce4e2;
  --white:#fff;
  --radius:5px;
  --shadow:0 8px 24px rgba(0,0,0,.08);
}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;font-family:Arial,Helvetica,sans-serif;color:var(--ink);background:#064d2b}
body{
  position:relative;
  overflow-x:hidden;
}
body:before,body:after{
  content:"";position:fixed;z-index:0;pointer-events:none;
  width:280px;height:280px;border:1px solid rgba(255,128,30,.34);
  transform:rotate(45deg);
}
body:before{left:-180px;top:80px;box-shadow:80px 80px 0 rgba(255,128,30,.08),160px 160px 0 rgba(255,128,30,.05)}
body:after{right:-190px;bottom:40px;box-shadow:-80px -80px 0 rgba(255,128,30,.08),-160px -160px 0 rgba(255,128,30,.05)}
.pattern{
  position:fixed;inset:0;z-index:0;pointer-events:none;
  background:
    radial-gradient(circle at 2% 35%,#25a957 0 7px,transparent 8px),
    radial-gradient(circle at 98% 65%,#f06419 0 5px,transparent 6px);
}
.app{
  position:relative;z-index:1;
  width:min(1650px,94vw);
  margin:28px auto;
  background:#fff;
  border:1px solid rgba(255,255,255,.8);
  border-radius:5px;
  box-shadow:0 18px 50px rgba(0,0,0,.25);
  padding:28px 46px 38px;
}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:24px;margin-bottom:24px}
.logo{height:58px;max-width:190px;object-fit:contain}
.logo-fallback{font-size:34px;font-weight:800;color:var(--green)}
.logo-fallback span{color:#f15a24}
.top-actions{display:flex;gap:12px}
button,.btn{font:inherit;border-radius:5px;cursor:pointer}
.top-btn{
  min-width:130px;height:44px;padding:0 20px;background:#fff;border:1px solid var(--green);
  color:var(--green);font-weight:700
}
.top-btn.home{background:var(--green);color:#fff}
.top-btn.logout{background:var(--red);border-color:var(--red);color:#fff}

.welcome{margin-bottom:24px}
.welcome small{font-size:16px}
.welcome h1{font-size:28px;margin:5px 0 7px}
.staff-badge{font-size:12px;color:var(--green);border:1px solid var(--green);padding:4px 8px;border-radius:5px;vertical-align:middle}
.position{font-size:15px;color:#33424a}

.main-grid{display:grid;grid-template-columns:1.08fr 1fr;gap:20px}
.left,.right{min-width:0}.main-grid>.left{display:contents}.main-grid>.left .stats-row{grid-column:1/-1}.main-grid>.left .panel{grid-column:1}

.stats-row{display:grid;grid-template-columns:1.08fr 1fr;gap:20px;margin-bottom:20px;width:100%}
.stats-card{
  background:linear-gradient(135deg,#087a35,#056127);
  color:#fff;border-radius:5px;padding:20px;
  min-height:164px;
}
.stats-main{display:grid;grid-template-columns:100px 1fr 1fr 1fr;align-items:center}
.rank{width:82px;height:82px;border:2px solid #fff;border-radius:50%;display:grid;place-items:center;text-align:center;font-weight:800}
.rank small{display:block;font-size:9px}
.kpi{padding:0 18px;border-left:1px solid rgba(255,255,255,.45)}
.kpi:first-of-type{border-left:0}
.kpi label{font-size:11px;display:block;margin-bottom:8px}
.kpi strong{font-size:20px}
.shift{margin-top:13px;border-top:1px solid rgba(255,255,255,.35);padding-top:11px;text-align:center;font-size:14px}
.dot{display:inline-block;width:10px;height:10px;background:#75d52e;border-radius:50%;margin-right:7px}

.wallet{
  background:linear-gradient(135deg,var(--orange),var(--orange-dark));
  color:#fff;border-radius:5px;padding:18px;min-height:164px
}
.wallet h2{font-size:18px;margin:0 0 18px}
.wallet-data{display:grid;grid-template-columns:1.15fr 1fr 1fr;align-items:center}
.wallet-kpi{padding:0 18px;border-left:1px solid rgba(255,255,255,.55)}
.wallet-kpi:first-child{border-left:0;padding-left:0}
.wallet-kpi label{font-size:10px;display:block;margin-bottom:7px}
.wallet-kpi strong{font-size:17px}
.claim{margin-top:14px;width:100%;height:40px;border:1px solid #fff;background:rgba(0,0,0,.13);color:#fff;font-weight:800}

.panel{
  background:#fff;border:1px solid var(--line);border-radius:5px;
  box-shadow:0 3px 12px rgba(0,0,0,.04);padding:10px;margin-bottom:12px
}
.actions{display:grid;grid-template-columns:1fr 1fr;gap:9px}
.action{
  min-height:62px;border:1px solid var(--green);background:#fff;color:var(--green);
  display:flex;align-items:center;gap:16px;padding:10px 18px;font-weight:800;text-align:left
}
.action.primary{background:var(--green);color:#fff}
.action.teal{background:var(--teal);color:#fff;border-color:var(--teal)}
.action.dark{grid-column:1/-1;background:var(--navy);color:#fff;border-color:var(--navy)}
.action.orange{border-color:#ff6a1a;color:#ed530d}
.action.red{border-color:#ff3a3a;color:#e91b24}
.action-icon{font-size:25px;width:32px;text-align:center}
.action-label{flex:1}
.arrow{font-size:21px}

.recent{
  background:#fff;border:1px solid var(--line);border-radius:5px;
  min-height:400px;overflow:hidden
}
.recent-head{height:53px;display:flex;align-items:center;justify-content:space-between;padding:0 18px;border-bottom:1px solid var(--line)}
.recent-head h2{font-size:18px;margin:0}
.view-all{border:1px solid var(--green);background:#fff;color:var(--green);padding:8px 17px;font-weight:700}
table{width:100%;border-collapse:collapse;font-size:12px}
th,td{padding:12px 10px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap}
th{font-size:11px}
.bill{color:var(--green);font-weight:800}
.bill:before{content:"";display:inline-block;width:7px;height:7px;background:var(--green);margin-right:8px}
.status{display:inline-block;padding:7px 12px;background:var(--orange-soft);color:#f05b25;border-radius:5px}
.view{border:1px solid var(--green);background:#fff;color:var(--green);padding:7px 15px;font-weight:700}

.bottom-actions{margin-top:12px}
@media (min-width:1000px){
  .left .panel{padding:10px}
  .bottom-actions .actions{grid-template-columns:1fr 1fr}
  .bottom-actions .action:last-child{grid-column:1/-1}
}
@media (max-width:999px){
  body{background:#f4f6f5}
  body:before,body:after,.pattern{display:none}
  .app{
    width:100%;margin:0;border:0;border-radius:0;box-shadow:none;
    padding:14px 10px 24px;background:#f7f8f7
  }
  .topbar{margin-bottom:13px}
  .logo{height:42px;max-width:140px}
  .top-actions{gap:5px}
  .top-btn{min-width:auto;height:36px;padding:0 10px;font-size:10px}
  .top-btn.home{display:none}
  .welcome{background:#fff;border:1px solid var(--line);border-radius:5px;padding:14px;margin-bottom:10px}
  .welcome small{font-size:12px}
  .welcome h1{font-size:18px;margin:4px 0}
  .position{font-size:11px}
  .main-grid{display:flex;flex-direction:column;gap:10px}
  .left,.right{display:contents}
  .stats-row{display:flex;flex-direction:column;gap:10px;margin:0}
  .stats-card{min-height:auto;padding:13px;border-radius:5px;order:1}
  .stats-main{grid-template-columns:58px 1fr 1fr 1fr;gap:0}
  .rank{width:52px;height:52px;font-size:12px}
  .rank small{font-size:7px}
  .kpi{padding:0 8px}
  .kpi label{font-size:7px;margin-bottom:4px}
  .kpi strong{font-size:14px}
  .shift{grid-column:1/-1;margin-top:9px;padding-top:8px;font-size:11px}
  .wallet{min-height:auto;padding:12px;order:2}
  .wallet h2{font-size:15px;margin-bottom:10px}
  .wallet-data{grid-template-columns:1.1fr 1fr 1fr}
  .wallet-kpi{padding:0 8px}
  .wallet-kpi label{font-size:7px;margin-bottom:4px}
  .wallet-kpi strong{font-size:12px}
  .claim{height:34px;margin-top:9px;font-size:10px}
  .panel{padding:7px;margin:0;order:3}
  .actions{gap:7px}
  .action{min-height:48px;padding:7px 10px;gap:8px;font-size:10px}
  .action-icon{font-size:19px;width:24px}
  .action.dark{grid-column:1/-1}
  .bottom-actions{order:4}
  .recent{order:5;min-height:0}
  .recent-head{height:45px;padding:0 12px}
  .recent-head h2{font-size:15px}
  .view-all{font-size:10px;padding:7px 12px}
  table{font-size:8px}
  th,td{padding:8px 5px}
  th{font-size:7px}
  .status{padding:5px 7px;font-size:7px}
  .view{padding:5px 8px;font-size:8px}
}
</style>
</head>
<body>
<div class="pattern"></div>

<main class="app">
  <header class="topbar">
    <!-- Replace ktouch-logo.png with your actual K-Touch logo file -->
    <img class="logo" src="ktouch-logo.png" alt="K-Touch POS System"
         onerror="this.style.display='none';document.querySelector('.logo-fallback').style.display='block'">
    <div class="logo-fallback" style="display:none">K<span>-touch</span></div>

    <nav class="top-actions">
      <button class="top-btn">⟳ Refresh</button>
      <button class="top-btn">▣ Open Counter</button>
      <button class="top-btn logout">↪ Log Out</button>
      <button class="top-btn home">⌂ Home</button>
    </nav>
  </header>

  <section class="welcome">
    <small>Welcome back,</small>
    <h1>KTOUCH_ADMIN <span class="staff-badge">Staff</span></h1>
    <div class="position">Position: Staff&nbsp; • &nbsp;Counter: BAR</div>
  </section>

  <section class="main-grid">
    <div class="left">
      <div class="stats-row">
        <article class="stats-card">
          <div class="stats-main">
            <div class="rank"><div><small>TODAY'S RANK</small>#1</div></div>
            <div class="kpi"><label>TODAY'S SALES</label><strong>KES 101,742</strong></div>
            <div class="kpi"><label>ORDERS SERVED</label><strong>30</strong></div>
            <div class="kpi"><label>DATE</label><strong>24 Aug<br>2026</strong></div>
          </div>
          <div class="shift"><span class="dot"></span>On Shift</div>
        </article>

        <article class="wallet">
          <h2>My Token Wallet</h2>
          <div class="wallet-data">
            <div class="wallet-kpi"><label>Balance</label><strong>KES 745.5</strong></div>
            <div class="wallet-kpi"><label>This Month</label><strong>KES -4</strong></div>
            <div class="wallet-kpi"><label>Today</label><strong>KES 0</strong></div>
          </div>
          <button class="claim">CLAIM TOKENS&nbsp; →</button>
        </article>
      </div>

      <section class="panel">
        <div class="actions">
          <button class="action primary"><span class="action-icon">🛒</span><span class="action-label">PLACE ORDER</span><span class="arrow">›</span></button>
          <button class="action teal"><span class="action-icon">≫</span><span class="action-label">CONTINUE BILL</span><span class="arrow">›</span></button>
          <button class="action"><span class="action-icon">▤</span><span class="action-label">VIEW ORDERS</span></button>
          <button class="action"><span class="action-icon">▯</span><span class="action-label">K-PESA</span></button>
          <button class="action dark"><span class="action-icon">♜</span><span class="action-label">CHANGE PASS</span><span class="arrow">›</span></button>
        </div>
      </section>

      <section class="panel bottom-actions">
        <div class="actions">
          <button class="action"><span class="action-icon">🍸</span><span class="action-label">BARMAN</span><span class="arrow">›</span></button>
          <button class="action" style="border-color:#0aa0b0;color:#087b88"><span class="action-icon">▰</span><span class="action-label">CLEAR BILL</span><span class="arrow">›</span></button>
          <button class="action orange"><span class="action-icon">↶</span><span class="action-label">RETURNS</span><span class="arrow">›</span></button>
          <button class="action"><span class="action-icon">◆</span><span class="action-label">STOCK</span><span class="arrow">›</span></button>
          <button class="action red"><span class="action-icon">⏻</span><span class="action-label">CLOSE COUNTER</span></button>
        </div>
      </section>
    </div>

    <div class="right">
      <section class="recent">
        <div class="recent-head">
          <h2>Recent Orders</h2>
          <button class="view-all">View All</button>
        </div>
        <table>
          <thead><tr><th>Bill No.</th><th>Time</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <tr><td class="bill">Bill #123</td><td>2026-09-02 18:54:08</td><td>KES 1,200</td><td><span class="status">PENDING</span></td><td><button class="view">VIEW</button></td></tr>
            <tr><td class="bill">Bill #120</td><td>2026-09-02 13:08:07</td><td>KES 1,600</td><td><span class="status">PENDING</span></td><td><button class="view">VIEW</button></td></tr>
            <tr><td class="bill">Bill #119</td><td>2026-09-02 10:56:17</td><td>KES 2,000</td><td><span class="status">PENDING</span></td><td><button class="view">VIEW</button></td></tr>
            <tr><td class="bill">Bill #118</td><td>2026-09-02 10:50:35</td><td>KES 400</td><td><span class="status">PENDING</span></td><td><button class="view">VIEW</button></td></tr>
            <tr><td class="bill">Bill #117</td><td>2026-09-01 22:07:35</td><td>KES 1,700</td><td><span class="status">PENDING</span></td><td><button class="view">VIEW</button></td></tr>
          </tbody>
        </table>
      </section>
    </div>
  </section>
</main>
</body>
</html>