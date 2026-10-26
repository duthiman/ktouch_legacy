(function () {
  'use strict';

  var apiUrl = 'api/dash_api.php';
  var dashboardData = null;

  function one(selector, root) { return (root || document).querySelector(selector); }
  function all(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }
  function text(selector, value, root) {
    var node = one(selector, root);
    if (node && value !== undefined && value !== null) node.textContent = value;
    return node;
  }
  function money(value) {
    var number = Number(value || 0);
    return 'KES ' + number.toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  }
  function dateLabel(value) {
    if (!value) return '—';
    var date = new Date(String(value).replace(' ', 'T'));
    return isNaN(date.getTime()) ? String(value) : date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }
  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[c];
    });
  }
  function userName(user) { return user && (user.name || user.username || user.user_name) || 'User'; }

  function applyCashierVisibility(data) {
    var user = data.user || {};
    var shift = data.shift || {};
    var counters = data.counters || {};
    var isCashier = Number(user.job_id) === 1;
    var canManageCounters = [1, 3].indexOf(Number(user.drole)) !== -1 && Number(user.job_id) === 2;
    var shiftIsOpen = String(shift.name || '').trim() !== '';
    // Use the server decision when available so mobile and desktop follow the
    // same active-counter rules, including deleted/inactive counters.
    var canOpenCounter = typeof shift.can_open_counter === 'boolean'
      ? shift.can_open_counter
      : (canManageCounters && shiftIsOpen && Number(counters.total || 0) > Number(counters.open || 0));
    var canOpenShift = typeof shift.can_open_shift === 'boolean'
      ? shift.can_open_shift
      : (canManageCounters && !shiftIsOpen);

    console.log('[Dashboard permissions]', {
      user_id: user.id || user.user_id || null,
      user_name: user.name || user.username || user.user_name || '',
      job: user.job_id,
      drole: user.drole,
      shift: shift.name || '',
      counters_total: Number(counters.total || 0),
      counters_open: Number(counters.open || 0),
      show_open_counter: canOpenCounter,
      show_open_shift: canOpenShift
    });
    var counterSelectors = '.open, .open-counter';
    var shiftSelectors = '.open-shift, .open-shift-btn, .dash-open-shift, .dash-mobile-open-shift';
    var cashierOnlySelectors = '#role-actions-card, .returns, .review-returns, .stock, .close-counter';

    all(counterSelectors).forEach(function (node) {
      node.hidden = !canOpenCounter;
    });
    all(shiftSelectors).forEach(function (node) {
      node.hidden = !canOpenShift;
    });
    all(cashierOnlySelectors).forEach(function (node) {
      node.hidden = !isCashier;
    });
  }

  function renderProfile(data) {
    var user = data.user || {}, shift = data.shift || {}, sales = data.sales || {};
    var name = userName(user), counter = user.counter_name || '—', role = user.job_name || user.role_name || 'Staff';
    var position = role + ' · ' + counter;
    var shiftName = shift.name || '—';
    var shiftDate = shift.date ? dateLabel(shift.date) : '—';
    var rankingPosition = data.ranking_position || data.position || sales.position || '—';
    text('.profile .identity h2', name.toUpperCase());
    text('.profile .id-row b', user.id ? 'ID #' + user.id : '');
    text('.profile .id-row span', role);
    text('.profile .identity p', 'Position: ' + position);
    text('.profile .identity em', shift.has_date ? 'On Shift' : 'Off Shift');
    text('.profile-rank strong', rankingPosition === '—' ? '—' : '#' + rankingPosition);
    text('.profile-info .shift-row div:nth-child(1) b', counter);
    text('.profile-info .shift-row div:nth-child(2) b', shiftName);
    text('.profile-info .shift-row div:nth-child(3) b', shiftDate);
    text('.sales-row > div:nth-child(1) b', money(sales.total_sales));
    text('.sales-row > div:nth-child(2) b', String(sales.total_bills || 0));
    text('.sales-row > div:nth-child(3) b', '—');

    text('.person h1', name.toUpperCase());
    text('.person .id b', user.id ? 'ID #' + user.id : '');
    text('.person .id .role', role);
    text('.rank strong', rankingPosition === '—' ? '—' : '#' + rankingPosition);
    text('.metric.sales b', money(sales.total_sales));
    text('.metric.orders b', String(sales.total_bills || 0));
    text('.metric.with-icon b', shiftDate);
  }

  function openSalesSummary() {
    var sales = (dashboardData || {}).sales || {};
    var shift = (dashboardData || {}).shift || {};
    var sold = sales.sold_items || [], returned = sales.returned_items || [], counters = sales.counter_summary || [];
    var soldRows = sold.map(function (item) { return '<tr><td>' + esc(item.name) + '</td><td>' + item.qty + '</td><td>' + esc(item.counter || '—') + '</td><td>' + money(item.total) + '</td></tr>'; }).join('') || '<tr><td colspan="4">No items sold this shift.</td></tr>';
    var returnedRows = returned.map(function (item) { return '<tr><td>' + esc(item.name) + '</td><td>' + item.qty + '</td><td>' + esc(item.counter || '—') + '</td><td>' + money(item.amount) + '</td><td><span class="return-pill ' + String(item.status).toLowerCase() + '">' + esc(item.status) + '</span><small class="return-confirm">' + esc(item.confirmed_by) + '</small></td></tr>'; }).join('') || '<tr><td colspan="5">No items returned this shift.</td></tr>';
    var counterRows = counters.map(function (item) { return '<tr><td>' + esc(item.counter || '—') + '</td><td>' + item.sold_qty + '</td><td>' + money(item.sold_total) + '</td><td>' + item.return_qty + '</td><td>' + money(item.return_total) + '</td></tr>'; }).join('') || '<tr><td colspan="5">No counter movement found this shift.</td></tr>';
    var css = '<style>.sales-summary-modal{font-family:Inter,Segoe UI,Arial,sans-serif;background:#f5faf7}.sales-summary-head{display:flex;justify-content:space-between;align-items:center;padding:18px 20px;color:#fff;background:linear-gradient(135deg,#087e4b,#045d36)}.sales-summary-head h3{margin:3px 0;font-size:20px}.sales-summary-head small{color:#c9f0d8}.sales-summary-avatar{display:grid;place-items:center;width:44px;height:44px;border:2px solid #c9f0d8;border-radius:50%;font-size:20px;font-weight:800}.sales-summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:12px}.sales-summary-stat{padding:11px;border:1px solid #d7e8de;border-radius:8px;background:#fff}.sales-summary-stat small{display:block;color:#6b7e74;font-size:9px;font-weight:900}.sales-summary-stat strong{display:block;margin-top:5px;color:#087e4b;font-size:17px}.sales-summary-tabs{display:flex;gap:7px;padding:0 12px;background:#fff}.sales-summary-tab{flex:1;padding:10px;border:1px solid #d7e8de;border-bottom:3px solid transparent;border-radius:7px 7px 0 0;background:#f7fbf8;color:#426456;font-weight:800}.sales-summary-tab.active{border-color:#087e4b;border-bottom-color:#087e4b;background:#eaf7ef;color:#087e4b}.sales-summary-panel{display:none;max-height:230px;overflow:auto;padding:0 12px 12px;background:#fff}.sales-summary-panel.active{display:block}.sales-summary-panel table{width:100%;border-collapse:collapse;font-size:12px}.sales-summary-panel th,.sales-summary-panel td{padding:8px 5px;border-bottom:1px solid #e6eee9;text-align:left}.sales-summary-panel th{font-size:9px;color:#71847a;text-transform:uppercase}.sales-summary-panel th:nth-child(n+2),.sales-summary-panel td:nth-child(n+2){text-align:right}.return-pill{display:inline-block;padding:3px 6px;border-radius:10px;font-size:9px;font-weight:900}.return-pill.approved{background:#e4f7eb;color:#087e4b}.return-pill.rejected{background:#ffebeb;color:#b42318}.return-pill.pending{background:#fff3d8;color:#956300}.return-confirm{display:block;color:#899991;font-size:9px}</style>';
    var html = css + '<div class="sales-summary-modal"><div class="sales-summary-head"><div><small>CURRENT SHIFT SUMMARY</small><h3>' + esc((dashboardData.user || {}).name || 'My summary') + '</h3><small>' + esc(shift.name || 'No active shift') + '</small></div><div class="sales-summary-avatar">' + esc(String((dashboardData.user || {}).name || '?').charAt(0).toUpperCase()) + '</div></div><div class="sales-summary-grid"><div class="sales-summary-stat"><small>SHIFT SALES</small><strong>' + money(sales.total_sales) + '</strong></div><div class="sales-summary-stat"><small>MONTHLY SALES</small><strong>' + money(sales.month_sales) + '</strong></div><div class="sales-summary-stat"><small>TODAY’S SALES</small><strong>' + money(sales.today_sales) + '</strong></div><div class="sales-summary-stat"><small>SHIFT RETURNS</small><strong>' + money(sales.shift_returns) + '</strong></div><div class="sales-summary-stat"><small>TOTAL RETURNS</small><strong>' + money(sales.total_returns) + '</strong></div><div class="sales-summary-stat"><small>CHANGES</small><strong>' + Number(sales.changes_count || 0) + '</strong></div></div><div class="sales-summary-tabs"><button type="button" class="sales-summary-tab active" data-summary-tab="sold">Items sold <b>' + sold.length + '</b></button><button type="button" class="sales-summary-tab" data-summary-tab="returned">Items returned <b>' + returned.length + '</b></button><button type="button" class="sales-summary-tab" data-summary-tab="counters">By counter <b>' + counters.length + '</b></button></div><section class="sales-summary-panel active" data-summary-panel="sold"><table><thead><tr><th>Item</th><th>Qty</th><th>Counter</th><th>Total</th></tr></thead><tbody>' + soldRows + '</tbody></table></section><section class="sales-summary-panel" data-summary-panel="returned"><table><thead><tr><th>Item</th><th>Qty</th><th>Counter</th><th>Total</th><th>Decision</th></tr></thead><tbody>' + returnedRows + '</tbody></table></section><section class="sales-summary-panel" data-summary-panel="counters"><table><thead><tr><th>Counter</th><th>Sold qty</th><th>Sold total</th><th>Return qty</th><th>Return total</th></tr></thead><tbody>' + counterRows + '</tbody></table></section></div>';
    loadModalHtml('Sales summary', html);
  }

  function loadModalHtml(title, html) {
    var existing = document.getElementById('modal');
    if (!existing || !one('#load', existing)) return;
    one('#load', existing).innerHTML = '<div class="modal-content dashboard-modal-content"><div class="modal-header"><h4 class="modal-title">' + esc(title) + '</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">' + html + '</div></div>';
    if (window.jQuery && window.jQuery.fn.modal) window.jQuery(existing).modal('show');
    else existing.style.display = 'block';
  }

  function renderApprovals(data) {
    var attention = data.attention || {}, returns = attention.returns || {}, mpesa = attention.mpesa || {}, voids = attention.voids || {};
    var values = [
      ['↩', 'Returns not approved', returns.count || 0, returns.amount || 0, 'review-returns'],
      ['▣', 'M-Pesa claimed not approved', mpesa.count || 0, mpesa.amount || 0, 'review-mpesa'],
      ['⊘', 'Voided bills not approved', voids.count || 0, voids.amount || 0, 'review-voids']
    ];
    var approvalCard = one('.approval');
    var hasApproval = values.some(function (value) { return Number(value[2]) > 0; });
    if (approvalCard) approvalCard.hidden = !hasApproval;
    var list = one('.approval-list');
    if (list) list.innerHTML = values.map(function (v) { return '<button class="approval-row ' + v[4] + '"><i>' + v[0] + '</i><span>' + esc(v[1]) + '</span><b>' + v[2] + '</b><strong>' + money(v[3]) + '</strong><em>REVIEW</em></button>'; }).join('');
    var mobile = one('.approval');
    if (mobile) {
      all('.approval-row', mobile).forEach(function (row, index) {
        var v = values[index];
        if (v) row.innerHTML = '<i>' + v[0] + '</i><span>' + esc(v[1]) + '</span><strong>' + v[2] + '</strong><b>' + money(v[3]) + '</b><button>REVIEW</button>';
      });
    }
  }

  function renderWallet(data) {
    var tokens = data.tokens || {}, nodes = all('.wallet-stat');
    var values = [money(tokens.total), money(tokens.month), money(tokens.today)];
    nodes.forEach(function (node, index) { text('b', values[index], node); });
    text('.wallet-balance', money(tokens.total));
  }

  function renderOrders(data) {
    var orders = data.recent_orders || [], bodies = all('.orders tbody');
    if (!bodies.length) return;
    var rows = orders.map(function (order) {
      return '<tr><td>▥ &nbsp; Bill #' + esc(order.sale_id || order.id) + '</td><td>' + esc(order.time || order.created_at || order.datime || '') + '</td><td>' + money(order.amount) + '</td><td><button data-sale-id="' + esc(order.sale_id || order.id) + '">VIEW</button></td></tr>';
    }).join('');
    bodies.forEach(function (body) { body.innerHTML = rows; });
  }

  function render(data) {
    dashboardData = data;
    applyCashierVisibility(data);
    renderProfile(data);
    renderApprovals(data);
    renderWallet(data);
    renderOrders(data);
    document.documentElement.classList.add('dashboard-loaded');
  }

  function executeScripts(container) {
    all('script', container).forEach(function (script) {
      if (script.src) return;
      try { new Function(script.textContent)(); } catch (error) { console.error(error); }
    });
  }

  function loadModal(url) {
    fetch(url, { headers: { Accept: 'text/html' } })
      .then(function (response) { if (!response.ok) throw new Error('Could not load modal'); return response.text(); })
      .then(function (html) {
        var existing = document.getElementById('modal');
        if (existing && one('#load', existing)) {
          var load = one('#load', existing);
          load.innerHTML = html;
          executeScripts(load);
          if (window.jQuery && window.jQuery.fn.modal) window.jQuery(existing).modal('show');
          else existing.style.display = 'block';
          return;
        }
        var wrapper = document.createElement('div');
        wrapper.className = 'modal fade dashboard-modal';
        wrapper.innerHTML = '<div class="modal-dialog modal-lg"><div class="modal-content dashboard-modal-content"></div></div>';
        var content = one('.dashboard-modal-content', wrapper);
        content.innerHTML = html;
        document.body.appendChild(wrapper);
        executeScripts(content);
        if (window.jQuery && window.jQuery.fn.modal) window.jQuery(wrapper).modal('show');
        else wrapper.style.display = 'block';
        wrapper.addEventListener('hidden.bs.modal', function () { wrapper.remove(); });
      })
      .catch(function (error) { console.error(error); alert('Unable to load this window.'); });
  }

  function refresh() {
    return fetch(apiUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
      .then(function (response) {
        return response.json().then(function (payload) {
          if (!response.ok || !payload.success) {
            if (payload.data && payload.data.auto_logout) {
              window.location.replace(payload.data.redirect || '../index.php');
              return null;
            }
            throw new Error(payload.message || 'Dashboard API failed');
          }
          render(payload.data || {});
          return payload;
        });
      })
      .catch(function (error) { console.error(error); });
  }

  function go(path) {
    window.location.href = path;
  }

  document.addEventListener('click', function (event) {
    var target = event.target.closest('button, a, .sales-summary-trigger, .review-returns, .review-mpesa, .review-voids');
    if (!target) return;
    if (target.closest('.sales-summary-trigger')) { event.preventDefault(); openSalesSummary(); return; }
    if (target.closest('.logout')) { event.preventDefault(); go('../log_out.php'); return; }
    if (target.closest('.home')) { event.preventDefault(); go('index.php'); return; }
    if (target.closest('.place')) { event.preventDefault(); loadModal('../counter/counter_select.php'); return; }
    if (target.closest('.lock, .password')) { event.preventDefault(); loadModal('../staff/cpass.php'); return; }
    if (target.closest('.open-shift')) { event.preventDefault(); go('../shift/index.php?action=shift.open'); return; }
    if (target.closest('.open')) { event.preventDefault(); loadModal('../counter/open.php'); return; }
    if (target.closest('.open-counter')) { event.preventDefault(); loadModal('../counter/open.php'); return; }
    if (target.closest('.continue')) { event.preventDefault(); go('../open/index.php'); return; }
    if (target.closest('.orders tbody button')) { event.preventDefault(); var saleId = Number(target.getAttribute('data-sale-id') || 0); if (saleId > 0) loadModal('../view/API/view_api.php?action=bill_items_modal&sale_id=' + encodeURIComponent(saleId)); return; }
    if (target.closest('.return-sale')) { event.preventDefault(); var returnSaleId = Number(target.getAttribute('data-id') || 0); if (returnSaleId > 0) go('../ret/ret_page.php?sale_id=' + encodeURIComponent(returnSaleId)); return; }
    if (target.closest('.change-item-btn')) { event.preventDefault(); var changeSaleId = Number(target.getAttribute('data-sale-id') || 0); if (changeSaleId > 0) go('../change/index.php?sale_id=' + encodeURIComponent(changeSaleId)); return; }
    if (target.closest('.view-orders, .all, .view-all')) { event.preventDefault(); go('../view/my_sales.php'); return; }
    if (target.closest('.kpesa, .claim')) { event.preventDefault(); go('../kpesa/mpesas.php'); return; }
    if (target.closest('.clear')) { event.preventDefault(); go('../cash/'); return; }
    if (target.closest('.returns, .review-returns')) { event.preventDefault(); go('../ret/appr/returns.php'); return; }
    if (target.closest('.stock')) { event.preventDefault(); go('../stock/store_dash.php'); return; }
    if (target.closest('.butchery')) { event.preventDefault(); go('../butch/index.php'); return; }
    if (target.closest('.barman')) { event.preventDefault(); go('../barp/index.php'); return; }
    if (target.closest('.close-counter, .danger')) { event.preventDefault(); loadModal('../counter/close_modal.php'); return; }
    if (target.closest('.review-mpesa')) { event.preventDefault(); go('../kpesa/mpesas.php'); return; }
    if (target.closest('.review-voids')) { event.preventDefault(); go('../view/my_sales.php'); return; }
    if (target.matches('.refresh, #refreshBtn, .top-actions button:first-child')) { event.preventDefault(); window.location.reload(); return; }
    if (target.closest('.role-tabs')) {
      event.preventDefault();
      all('.role-tabs button').forEach(function (button) { button.classList.toggle('active', button === target); });
    }
  });

  document.addEventListener('click', function (event) {
    var tab = event.target.closest('.sales-summary-tab');
    if (!tab) return;
    var name = tab.getAttribute('data-summary-tab');
    all('.sales-summary-tab').forEach(function (item) { item.classList.toggle('active', item === tab); });
    all('.sales-summary-panel').forEach(function (panel) { panel.classList.toggle('active', panel.getAttribute('data-summary-panel') === name); });
  });

  refresh();
}());
