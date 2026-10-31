<?php
include("../clude/head.php");
require_once __DIR__ . '/API/change_schema.php';
change_schema_boot($connection);
$sale_id = (int)($_GET['sale_id'] ?? 0);
?>
<link href="change.css?v=<?php echo filemtime(__DIR__ . '/change.css'); ?>" rel="stylesheet">
<style>
.change-confirm-dialog{max-width:560px;margin:8vh auto}.change-confirm-content{border:0;border-radius:14px;overflow:hidden;box-shadow:0 18px 55px rgba(20,33,61,.28)}.change-confirm-head{display:flex;align-items:center;gap:14px;padding:22px 24px;background:linear-gradient(115deg,var(--green),#008b58);color:#fff}.change-confirm-head h4{margin:0;font-size:20px;font-weight:900}.change-confirm-head p{margin:5px 0 0;font-size:12px;opacity:.86}.change-confirm-head .close{margin-left:auto;color:#fff;opacity:.9;font-size:28px;text-shadow:none}.change-confirm-icon{width:48px;height:48px;border:2px solid rgba(255,255,255,.8);border-radius:50%;display:grid;place-items:center;font-size:27px}.change-confirm-body{padding:20px 24px 8px}.change-confirm-label{color:var(--muted);font-size:11px;font-weight:900;letter-spacing:.5px;margin-bottom:9px}.change-confirm-list{border:1px solid var(--line);border-radius:8px;overflow:hidden}.change-confirm-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 14px;border-bottom:1px solid var(--line)}.change-confirm-row:last-child{border-bottom:0}.change-confirm-row b{display:block;color:var(--navy);font-size:14px}.change-confirm-row small{display:block;margin-top:4px;color:var(--muted);font-size:11px}.change-confirm-row strong{color:var(--green);font-size:12px;white-space:nowrap}.change-confirm-total{display:flex;justify-content:space-between;padding:14px 2px 6px;color:var(--muted);font-size:12px}.change-confirm-total b{color:var(--orange)}.change-confirm-foot{display:flex;gap:10px;padding:14px 24px 22px}.change-confirm-foot button{height:44px;border-radius:6px;font-size:12px;font-weight:900}.change-confirm-back{width:35%;border:1px solid #aab1bf;background:#fff;color:var(--navy)}.change-confirm-next{flex:1;border:0;background:linear-gradient(100deg,var(--orange),var(--orange-light));color:#fff}
@media(max-width:767px){.change-confirm-dialog{margin:12vh 10px}.change-confirm-head{padding:18px}.change-confirm-body{padding:16px 18px 6px}.change-confirm-foot{padding:12px 18px 18px}}
</style>

<body class="change-page">
  <header class="change-topbar"> 
    <nav class="change-top-actions" aria-label="Page actions">
      <a href="../dash/dash.php" class="change-touch-btn change-home"><span class="change-btn-icon">⌂</span><span>HOME</span></a>
      <a href="../log_out.php" class="change-touch-btn change-logout"><span class="change-btn-icon">⇥</span><span>LOG OUT</span></a>
    </nav>
  </header>

  <main class="change-shell">
    <section class="change-hero">
      <div class="change-hero-title">
        <div class="change-hero-icon">⇄</div>
        <div><h1>CHANGE SOLD ITEMS</h1><p>Select sold items and choose their replacement</p></div>
      </div>
      <div class="change-summary">
        <div class="change-summary-user"><span class="change-chart">▥</span><span>TODAY'S SALES</span></div>
        <div class="change-stat"><strong id="change_bill_count">1</strong><small>BILL</small></div>
        <div class="change-stat"><strong id="change_item_count">—</strong><small>ITEM</small></div>
      </div>
    </section>

    <section class="change-modebar">
      <div class="change-tabs" role="tablist">
        <button type="button" class="change-tab active" data-mode="item">◇ &nbsp; BY ITEM<span>Find anything sold</span></button>
        <button type="button" class="change-tab" data-mode="bill">▤ &nbsp; BY BILL<span>Find a receipt</span></button>
      </div>
      <div class="change-search"><input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="Bill number" id="change_search"><button type="button" id="change_search_open" aria-label="Search">⌕</button></div>
    </section>

    <section class="change-grid">
      <div class="change-panel bills-panel">
            <div class="change-panel-title">▤ &nbsp; TODAY'S BILLS</div>
        <p class="change-panel-note">Tap View to open the bill and select items you want to change.</p>
        <div id="ordscreen"></div>
      </div>

      <div class="change-panel selected-panel">
        <div class="change-panel-title change-selected-head"><span>♧ &nbsp; SELECTED ITEMS (<b id="change_selected_count">0</b>)</span></div>
        <div id="screen" class="change-screen"><div class="change-empty"><div class="change-empty-icon">♧</div><p>Open a bill, then select<br>the sold items you want to change.</p></div></div>
            <div class="change-replacement"><b>REPLACEMENT ITEM</b><div>Search or select replacement item <span>⌕</span></div></div>
        <div class="change-bottom"><div class="change-hint">● &nbsp; 0 ITEMS &nbsp; • &nbsp; Difference KSh 0</div><div class="change-actions"><button type="button" class="change-clear">CLEAR</button><button type="button" class="change-continue" disabled>CONTINUE &nbsp; →</button></div></div>
      </div>
    </section>
    <div class="change-cashier-note">⚠ &nbsp; Changes require cashier confirmation</div>
  </main>

  <div id="change_items_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog change-items-dialog" role="document">
      <div class="modal-content change-items-modal-content">
        <div class="change-modal-head"><button type="button" class="close" data-dismiss="modal">&times;</button><h4>ITEMS IN BILL <span id="change_modal_bill">#ORD-0</span></h4></div>
        <div class="change-modal-body"><div id="change_modal_items"></div></div>
        <div class="change-modal-foot"><button type="button" class="change-modal-close" data-dismiss="modal">CLOSE</button></div>
      </div>
    </div>
  </div>
  <div id="change_switch_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog change-switch-dialog" role="document"><div class="modal-content change-switch-content">
      <div class="change-switch-icon">⇄</div><h4>Switch selection mode?</h4>
      <p>Your selected items will be cleared when you switch between bills and items.</p>
      <div class="change-switch-actions"><button type="button" class="change-switch-cancel" data-dismiss="modal">KEEP ITEMS</button><button type="button" class="change-switch-confirm" id="change_switch_confirm">CLEAR &amp; SWITCH</button></div>
    </div></div>
  </div>
  <div id="change_notice_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog change-switch-dialog" role="document"><div class="modal-content change-switch-content">
      <div class="change-switch-icon">!</div><h4 id="change_notice_title">Selection notice</h4><p id="change_notice_message"></p>
      <div class="change-switch-actions"><button type="button" class="change-switch-confirm" data-dismiss="modal">OK, GOT IT</button></div>
    </div></div>
  </div>
  <div id="change_numpad_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog change-numpad-dialog" role="document"><div class="modal-content change-numpad-content">
      <div class="change-modal-head"><button type="button" class="close" data-dismiss="modal">&times;</button><h4>SEARCH BY BILL NUMBER</h4></div>
      <div class="change-numpad-display" id="change_numpad_display"></div>
      <div class="change-numpad-grid"><button data-num="7">7</button><button data-num="8">8</button><button data-num="9">9</button><button data-num="4">4</button><button data-num="5">5</button><button data-num="6">6</button><button data-num="1">1</button><button data-num="2">2</button><button data-num="3">3</button><button class="action" data-num="clear">CLEAR</button><button data-num="0">0</button><button class="action" data-num="back">⌫</button></div>
      <div class="change-numpad-footer"><button type="button" class="change-modal-close" data-dismiss="modal">DONE</button></div>
    </div></div>
  </div>

  <div id="change_confirm_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog change-confirm-dialog" role="document">
      <div class="modal-content change-confirm-content">
        <div class="change-confirm-head"><div class="change-confirm-icon">⇄</div><div><h4>READY TO CHANGE?</h4><p>Review the sold items before choosing their replacements.</p></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="change-confirm-body"><div class="change-confirm-label">ITEMS YOU WANT TO CHANGE</div><div id="change_confirm_items"></div></div>
        <div class="change-confirm-foot"><button type="button" class="change-confirm-back" data-dismiss="modal">GO BACK</button><button type="button" class="change-confirm-next" id="change_confirm_next">CONTINUE TO ITEMS &nbsp; →</button></div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script>var sale_id = <?php echo json_encode($sale_id); ?>;</script>
  <script src="change.js?v=<?php echo filemtime(__DIR__ . '/change.js'); ?>"></script>
</body></html>
