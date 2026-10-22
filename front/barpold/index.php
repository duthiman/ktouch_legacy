<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_name = htmlspecialchars((string)($_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Barman'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Bar Desk</title>
  <style>
    :root {
      color-scheme: light;
      --ink: #24170f;
      --muted: #7c6658;
      --cream: #fffaf1;
      --card: #ffffff;
      --line: #eadbca;
      --accent: #9b3f20;
      --accent-dark: #6f2917;
      --success: #2d7c45;
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; min-height: 100%; }
    body {
      background: var(--cream);
      color: var(--ink);
      font-family: Arial, Helvetica, sans-serif;
    }
    button, a { -webkit-tap-highlight-color: transparent; touch-action: manipulation; }
    .bar-shell { min-height: 100vh; }
    .bar-mobile-view { display: none; }
    .bar-header {
      position: sticky;
      top: 0;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: 14px clamp(14px, 3vw, 34px);
      background: linear-gradient(135deg, var(--accent-dark), var(--accent));
      color: #fff8ed;
      box-shadow: 0 4px 16px rgba(71, 33, 18, .15);
    }
    .brand h1 { margin: 0; font-size: clamp(20px, 3vw, 30px); letter-spacing: .04em; }
    .brand p { margin: 4px 0 0; color: #ffe9d2; font-size: 12px; font-weight: 700; }
    .header-actions { display: flex; align-items: center; gap: 8px; }
    .header-actions a {
      padding: 9px 12px;
      border: 1px solid rgba(255,255,255,.45);
      border-radius: 9px;
      color: #fff;
      text-decoration: none;
      font-size: 12px;
      font-weight: 800;
    }
    .bar-main { width: min(1400px, 100%); margin: 0 auto; padding: 18px clamp(12px, 3vw, 34px) 34px; }
    .bar-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .summary-card, .bar-card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 7px 20px rgba(74, 43, 22, .06); }
    .summary-card { padding: 15px; }
    .summary-card small { display: block; color: var(--muted); font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .summary-card strong { display: block; margin-top: 7px; color: var(--accent-dark); font-size: 25px; }
    .bar-card { overflow: hidden; }
    .bar-horizontal-scroll {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      overscroll-behavior-inline: contain;
      padding: 14px 16px 16px;
      scrollbar-width: thin;
      -webkit-overflow-scrolling: touch;
      touch-action: pan-x;
    }
    .bar-horizontal-scroll::-webkit-scrollbar { height: 6px; }
    .bar-horizontal-scroll::-webkit-scrollbar-thumb { background: #d8bba0; border-radius: 99px; }
    .waiter-card {
      flex: 0 0 150px;
      min-height: 104px;
      padding: 13px;
      border: 1px solid var(--line);
      border-radius: 13px;
      background: linear-gradient(160deg, #fff, #fff8ee);
      box-shadow: 0 5px 14px rgba(74,43,22,.08);
      cursor: pointer;
      user-select: none;
    }
    .waiter-card:active { transform: scale(.98); }
    .waiter-card strong { display: block; overflow: hidden; color: var(--accent-dark); font-size: 15px; text-overflow: ellipsis; white-space: nowrap; }
    .waiter-card small { display: block; margin-top: 9px; color: var(--muted); font-size: 11px; font-weight: 700; }
    .waiter-card b { display: block; margin-top: 5px; color: var(--success); font-size: 13px; }
    .waiter-card em { display: block; margin-top: 8px; color: var(--accent); font-size: 10px; font-style: normal; font-weight: 900; text-transform: uppercase; }
    .waiter-empty { width: 100%; padding: 20px 4px; color: var(--muted); text-align: center; }
    .waiter-modal { position: fixed; inset: 0; z-index: 50; display: none; align-items: center; justify-content: center; padding: 18px; background: rgba(36,23,15,.58); }
    .waiter-modal.open { display: flex; }
    .waiter-modal-card { width: min(520px, 100%); overflow: hidden; border: 1px solid var(--line); border-radius: 18px; background: #fffaf1; box-shadow: 0 18px 50px rgba(36,23,15,.28); }
    .waiter-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 17px 18px; background: linear-gradient(135deg, var(--accent-dark), var(--accent)); color: #fff; }
    .waiter-modal-head h2 { margin: 0; font-size: 20px; }
    .waiter-modal-head small { display: block; margin-top: 4px; color: #ffe9d2; font-weight: 700; }
    .waiter-modal-close { border: 0; background: transparent; color: #fff; font-size: 28px; line-height: 1; cursor: pointer; }
    .waiter-summary-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; padding: 16px; }
    .waiter-summary-stat { padding: 13px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
    .waiter-summary-stat small { display: block; color: var(--muted); font-size: 10px; font-weight: 900; text-transform: uppercase; }
    .waiter-summary-stat strong { display: block; margin-top: 7px; color: var(--accent-dark); font-size: 20px; }
    .waiter-modal-actions { display: flex; gap: 10px; padding: 0 16px 16px; }
    .waiter-modal-actions button, .waiter-modal-actions a { flex: 1; min-height: 46px; border: 0; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 900; cursor: pointer; }
    .waiter-print-btn { display: grid; place-items: center; background: var(--accent); color: #fff; }
    .waiter-close-btn { background: #f3e5d3; color: var(--accent-dark); }
    .waiter-inline-summary { display: none; margin: 0 16px 16px; padding: 15px; border: 1px solid var(--line); border-radius: 14px; background: #fffaf1; }
    .waiter-inline-summary.open { display: block; }
    .waiter-inline-summary-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .waiter-inline-summary-head h3 { margin: 0; color: var(--accent-dark); font-size: 17px; }
    .waiter-inline-summary-head small { color: var(--muted); font-weight: 800; }
    .waiter-summary-grid { padding: 0; }
    .waiter-summary-stat { padding: 11px; }
    .waiter-print-inline { min-height: 42px; width: 100%; margin-top: 12px; border: 0; border-radius: 10px; background: var(--accent); color: #fff; font-weight: 900; cursor: pointer; }
    .printer-list { display: grid; gap: 9px; padding: 16px; }
    .printer-option { min-height: 48px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--accent-dark); font-weight: 900; cursor: pointer; }
    .printer-option.selected { border-color: var(--accent); background: #f7dfc8; }
    .card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 15px 16px; border-bottom: 1px solid var(--line); }
    .card-head h2 { margin: 0; font-size: 16px; }
    .card-head span { color: var(--muted); font-size: 12px; font-weight: 700; }
    .bar-tabs { display: flex; gap: 8px; overflow-x: auto; padding: 12px 16px 0; }
    .bar-tab { border: 0; border-radius: 9px 9px 0 0; padding: 11px 14px; background: #f3e5d3; color: var(--accent-dark); font-weight: 800; white-space: nowrap; }
    .bar-tab.active { background: var(--accent); color: #fff; }
    .bar-empty { padding: 48px 18px; text-align: center; color: var(--muted); }
    .bar-empty strong { display: block; margin-bottom: 7px; color: var(--ink); font-size: 16px; }
    .mobile-header { padding: 16px; background: linear-gradient(145deg, var(--accent-dark), var(--accent)); color: #fff8ed; }
    .mobile-header-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .mobile-header h1 { margin: 0; font-size: 22px; letter-spacing: .05em; }
    .mobile-header p { margin: 5px 0 0; color: #ffe9d2; font-size: 12px; font-weight: 700; }
    .mobile-logout { padding: 10px 12px; border: 1px solid rgba(255,255,255,.45); border-radius: 9px; color: #fff; text-decoration: none; font-size: 12px; font-weight: 800; }
    .mobile-main { padding: 12px; }
    .mobile-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-bottom: 12px; }
    .mobile-summary-card { min-width: 0; padding: 11px 8px; border: 1px solid var(--line); border-radius: 12px; background: #fff; text-align: center; box-shadow: 0 5px 14px rgba(74,43,22,.06); }
    .mobile-summary-card small { display: block; overflow: hidden; color: var(--muted); font-size: 9px; font-weight: 800; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
    .mobile-summary-card strong { display: block; margin-top: 6px; color: var(--accent-dark); font-size: 17px; }
    .mobile-orders-card { overflow: hidden; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 7px 20px rgba(74,43,22,.06); }
    .mobile-waiters-card { margin-bottom: 12px; overflow: hidden; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 7px 20px rgba(74,43,22,.06); }
    .mobile-waiters-head { display: flex; align-items: center; justify-content: space-between; padding: 14px; border-bottom: 1px solid var(--line); }
    .mobile-waiters-head h2 { margin: 0; font-size: 17px; }
    .mobile-waiters-head span { color: var(--muted); font-size: 11px; font-weight: 800; }
    .mobile-orders-head { display: flex; align-items: center; justify-content: space-between; padding: 14px; border-bottom: 1px solid var(--line); }
    .mobile-orders-head h2 { margin: 0; font-size: 17px; }
    .mobile-orders-head span { color: var(--muted); font-size: 11px; font-weight: 800; }
    .mobile-tabs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; padding: 10px; background: #fffaf1; }
    .mobile-tab { min-height: 44px; border: 0; border-radius: 9px; background: #f3e5d3; color: var(--accent-dark); font-size: 11px; font-weight: 900; }
    .mobile-tab.active { background: var(--accent); color: #fff; }
    .mobile-empty { padding: 48px 18px; color: var(--muted); text-align: center; }
    .mobile-empty strong { display: block; margin-bottom: 7px; color: var(--ink); font-size: 16px; }
    @media (max-width: 620px) {
      .bar-desktop-view { display: none; }
      .bar-mobile-view { display: block; }
    }
  </style>
</head>
<body>
  <main class="bar-shell" id="bar-app">
    <header class="bar-header bar-desktop-view">
      <div class="brand">
        <h1>BAR DESK</h1>
        <p>Welcome, <?php echo $user_name; ?></p>
      </div>
      <nav class="header-actions" aria-label="Bar navigation">
        <a href="../dash/index.php">Home</a>
        <a href="../log_out.php">Log Out</a>
      </nav>
    </header>

    <section class="bar-main bar-desktop-view">
      <div class="bar-summary" aria-label="Bar summary">
        <div class="summary-card"><small>New Orders</small><strong id="bar-new-count">—</strong></div>
        <div class="summary-card"><small>Ready Orders</small><strong id="bar-ready-count">—</strong></div>
        <div class="summary-card"><small>Current Shift</small><strong id="bar-shift">—</strong></div>
      </div>

      <section class="bar-card" aria-labelledby="bar-waiters-title">
        <div class="card-head">
          <h2 id="bar-waiters-title">Waiters</h2>
          <span id="bar-waiters-shift">Loading…</span>
        </div>
        <div class="bar-horizontal-scroll" id="bar-waiters-desktop" aria-label="Waiter list"></div>
      </section>

      <section class="bar-card" aria-labelledby="bar-orders-title">
        <div class="card-head">
          <h2 id="bar-orders-title">Bar Orders</h2>
          <span id="bar-status">Ready</span>
        </div>
        <div class="bar-tabs" role="tablist" aria-label="Bar order status">
          <button type="button" class="bar-tab active" data-bar-tab="new">New</button>
          <button type="button" class="bar-tab" data-bar-tab="ready">Ready</button>
          <button type="button" class="bar-tab" data-bar-tab="history">History</button>
        </div>
        <div class="bar-empty" id="bar-orders-shell">
          <strong>Bar orders will appear here</strong>
          <span>The live order feed will be connected next.</span>
        </div>
        <div class="waiter-inline-summary" id="waiter-summary-desktop"></div>
      </section>
    </section>

    <section class="bar-mobile-view" aria-label="Mobile bar desk">
      <header class="mobile-header">
        <div class="mobile-header-row">
          <div>
            <h1>BAR DESK</h1>
            <p><?php echo $user_name; ?> · Live orders</p>
          </div>
          <div class="header-actions">
            <a class="mobile-logout" href="../dash/index.php">Home</a>
            <a class="mobile-logout" href="../log_out.php">Log Out</a>
          </div>
        </div>
      </header>
      <div class="mobile-main">
        <div class="mobile-summary">
          <div class="mobile-summary-card"><small>New</small><strong>—</strong></div>
          <div class="mobile-summary-card"><small>Ready</small><strong>—</strong></div>
          <div class="mobile-summary-card"><small>Shift</small><strong>—</strong></div>
        </div>
        <section class="mobile-waiters-card" aria-labelledby="mobile-waiters-title">
          <div class="mobile-waiters-head"><h2 id="mobile-waiters-title">Waiters</h2><span id="mobile-waiters-shift">Loading…</span></div>
          <div class="bar-horizontal-scroll" id="bar-waiters-mobile" aria-label="Waiter list"></div>
          <div class="waiter-inline-summary" id="waiter-summary-mobile"></div>
        </section>
        <section class="mobile-orders-card">
          <div class="mobile-orders-head"><h2>Bar Orders</h2><span>Ready</span></div>
          <div class="mobile-tabs">
            <button type="button" class="mobile-tab active">New</button>
            <button type="button" class="mobile-tab">Ready</button>
            <button type="button" class="mobile-tab">History</button>
          </div>
          <div class="mobile-empty"><strong>Bar orders will appear here</strong><span>The live order feed will be connected next.</span></div>
        </section>
      </div>
    </section>
  </main>
  <div class="waiter-modal" id="waiter-modal" role="dialog" aria-modal="true" aria-labelledby="waiter-modal-name">
    <div class="waiter-modal-card">
      <div class="waiter-modal-head">
        <div><h2 id="waiter-modal-name">Select printer</h2><small>Choose where to print the waiter summary</small></div>
        <button type="button" class="waiter-modal-close" id="waiter-modal-close" aria-label="Close">&times;</button>
      </div>
      <div class="printer-list" id="printer-list"><div class="waiter-empty">Loading printers…</div></div>
      <div class="waiter-modal-actions">
        <button type="button" class="waiter-close-btn" id="waiter-close-btn">Close</button>
        <button type="button" class="waiter-print-btn" id="waiter-print-btn">Print summary</button>
      </div>
    </div>
  </div>
  <script>
    (function () {
      function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
          return ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'})[char];
        });
      }

      function renderWaiters(waiters) {
        if (!waiters.length) return '<div class="waiter-empty">No waiters found</div>';
        return waiters.map(function (waiter) {
          return '<button type="button" class="waiter-card" data-waiter-id="' + Number(waiter.user_id) + '">' +
            '<strong>' + escapeHtml(waiter.name) + '</strong>' +
            '<small>' + Number(waiter.order_count) + ' orders</small>' +
            '<b>KES ' + Number(waiter.sales_total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</b>' +
          '</button>';
        }).join('');
      }

      function printWaiterSummary(userId) {
        var returnTo = encodeURIComponent('../barp/index.php');
        return '../chaba/receipt_counter.php?type=staff_sum&id=' + encodeURIComponent(userId) +
          '&user_id=' + encodeURIComponent(userId) + '&return_to=' + returnTo;
      }

      var selectedWaiterId = 0;
      var selectedPrinterId = 0;

      function closeWaiterSummary() {
        var modal = document.getElementById('waiter-modal');
        if (modal) modal.classList.remove('open');
      }

      function money(value) { return Number(value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

      function renderInlineSummary(data, userId) {
        var name = data.waiter ? data.waiter.name : 'Waiter summary';
        var html = '<div class="waiter-inline-summary-head"><div><h3>' + escapeHtml(name) + '</h3><small>' + escapeHtml(data.shift || 'No active shift') + '</small></div><small>Position #' + Number(data.position || 0) + '</small></div>' +
          '<div class="waiter-summary-grid">' +
          '<div class="waiter-summary-stat"><small>Sale totals</small><strong>KES ' + money(data.sales_total) + '</strong></div>' +
          '<div class="waiter-summary-stat"><small>Sales count</small><strong>' + Number(data.sales_count || 0) + '</strong></div>' +
          '<div class="waiter-summary-stat"><small>Items sold</small><strong>' + Number(data.items_count || 0) + '</strong></div>' +
          '<div class="waiter-summary-stat"><small>Returns count</small><strong>' + Number(data.returns_count || 0) + '</strong></div>' +
          '<div class="waiter-summary-stat"><small>Returns total</small><strong>KES ' + money(data.returns_total) + '</strong></div>' +
          '</div><button type="button" class="waiter-print-inline" data-print-summary="' + Number(userId) + '">Print summary</button>';
        ['waiter-summary-desktop', 'waiter-summary-mobile'].forEach(function (id) {
          var box = document.getElementById(id);
          if (box) { box.innerHTML = html; box.classList.add('open'); }
        });
      }

      function loadWaiterSummary(userId) {
        var modal = document.getElementById('waiter-modal');
        var desktop = document.getElementById('waiter-summary-desktop');
        var mobile = document.getElementById('waiter-summary-mobile');
        if (!desktop || !mobile) return;
        desktop.innerHTML = mobile.innerHTML = '<div class="waiter-empty">Loading summary…</div>';
        desktop.classList.add('open'); mobile.classList.add('open');
        selectedWaiterId = Number(userId);
        fetch('api.php?action=waiter_summary&user_id=' + encodeURIComponent(userId), { credentials: 'same-origin', cache: 'no-store' })
          .then(function (response) { return response.json(); })
          .then(function (result) {
            if (!result || !result.success) throw new Error('Summary unavailable');
            var data = result.data || {};
            renderInlineSummary(data, userId);
          })
          .catch(function () { desktop.innerHTML = mobile.innerHTML = '<div class="waiter-empty">Unable to load waiter summary</div>'; });
      }

      function openPrinterModal(userId) {
        selectedWaiterId = Number(userId);
        selectedPrinterId = 0;
        var modal = document.getElementById('waiter-modal');
        var list = document.getElementById('printer-list');
        modal.classList.add('open');
        list.innerHTML = '<div class="waiter-empty">Loading printers…</div>';
        fetch('api.php?action=print_counters', {credentials: 'same-origin', cache: 'no-store'})
          .then(function (response) { return response.json(); })
          .then(function (result) {
            var counters = result && result.data && Array.isArray(result.data.counters) ? result.data.counters : [];
            list.innerHTML = counters.length ? counters.map(function (counter) {
              return '<button type="button" class="printer-option" data-printer-id="' + Number(counter.id) + '">' + escapeHtml(counter.name) + '</button>';
            }).join('') : '<div class="waiter-empty">No printers available</div>';
          })
          .catch(function () { list.innerHTML = '<div class="waiter-empty">Unable to load printers</div>'; });
      }

      fetch('api.php', { credentials: 'same-origin', cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(function (result) {
          var data = result && result.data ? result.data : {};
          var html = renderWaiters(Array.isArray(data.waiters) ? data.waiters : []);
          var desktop = document.getElementById('bar-waiters-desktop');
          var mobile = document.getElementById('bar-waiters-mobile');
          if (desktop) desktop.innerHTML = html;
          if (mobile) mobile.innerHTML = html;
          var shift = escapeHtml(data.shift || 'No active shift');
          var desktopShift = document.getElementById('bar-waiters-shift');
          var mobileShift = document.getElementById('mobile-waiters-shift');
          if (desktopShift) desktopShift.innerHTML = shift;
          if (mobileShift) mobileShift.innerHTML = shift;
        })
        .catch(function () {
          var message = '<div class="waiter-empty">Unable to load waiters</div>';
          var desktop = document.getElementById('bar-waiters-desktop');
          var mobile = document.getElementById('bar-waiters-mobile');
          if (desktop) desktop.innerHTML = message;
          if (mobile) mobile.innerHTML = message;
        });

      document.addEventListener('click', function (event) {
        var card = event.target.closest ? event.target.closest('.waiter-card') : null;
        if (card) loadWaiterSummary(card.getAttribute('data-waiter-id'));
        var printButton = event.target.closest ? event.target.closest('[data-print-summary]') : null;
        if (printButton) { event.stopPropagation(); openPrinterModal(printButton.getAttribute('data-print-summary')); }
        var printer = event.target.closest ? event.target.closest('.printer-option') : null;
        if (printer) {
          selectedPrinterId = Number(printer.getAttribute('data-printer-id'));
          document.querySelectorAll('.printer-option').forEach(function (item) { item.classList.toggle('selected', item === printer); });
        }
      });
      document.getElementById('waiter-print-btn').addEventListener('click', function () {
        if (!selectedWaiterId || !selectedPrinterId) return;
        var button = this; button.disabled = true; button.textContent = 'Queuing…';
        var body = new URLSearchParams({action: 'queue_staff_summary', user_id: String(selectedWaiterId), counter_id: String(selectedPrinterId)});
        fetch('api.php', {method: 'POST', credentials: 'same-origin', body: body})
          .then(function (response) { return response.json(); })
          .then(function (result) { if (!result.success) throw new Error('Queue failed'); closeWaiterSummary(); })
          .catch(function () { button.disabled = false; button.textContent = 'Print summary'; alert('Could not queue the summary.'); });
      });
      document.getElementById('waiter-modal-close').addEventListener('click', closeWaiterSummary);
      document.getElementById('waiter-close-btn').addEventListener('click', closeWaiterSummary);
      document.getElementById('waiter-modal').addEventListener('click', function (event) {
        if (event.target === this) closeWaiterSummary();
      });
    }());
  </script>
</body>
</html>
