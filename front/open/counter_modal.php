<style>
  .ocm-modal { border: 0; border-radius: 5px; overflow: hidden; background: #fff9f1; box-shadow: 0 28px 52px rgba(73,67,25,.24); }
  .ocm-head { padding: 16px 18px; background: linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%); color: #fff9f1; }
  .ocm-head h3 { margin: 0; font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
  .ocm-head p { margin: 6px 0 0; font-size: 12px; font-weight: 700; opacity: .94; }
  .ocm-body { padding: 12px; background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%); display:flex; flex-direction:column; gap:10px; }
  .ocm-panel { padding: 10px; border-radius: 5px; border: 1px solid rgba(97,184,70,.16); background: rgba(255,255,255,.92); margin-bottom: 10px; }
  .ocm-switcher { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
  .ocm-pill { border: 1px solid rgba(47,122,49,.18); background: #fff; color: #2f7a31; border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 800; }
  .ocm-pill.active { background: #2f7a31; color: #fff; }
  .ocm-table { width:100%; border-collapse: collapse; font-size: 11px; color:#5a4324; }
  .ocm-table th, .ocm-table td { padding: 6px 7px; border: 1px solid rgba(122,84,36,.14); }
  .ocm-table th { background:#eef8e8; color:#2f7a31; font-size:10px; text-transform: uppercase; }
  .ocm-table td.num { text-align:right; white-space:nowrap; }
  .ocm-actions-wrap { display:grid; gap:10px; }
  .ocm-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
  .ocm-btn { min-height: 72px; border-radius: 5px; border: 2px solid rgba(97,184,70,.18); background: #fff; color: #2f7a31; text-align: center; padding: 8px; box-shadow: 0 14px 24px rgba(79,58,24,.08); }
  .ocm-btn strong { display: block; font-size: 15px; font-weight: 900; line-height: 1.15; }
  .ocm-close-bill-tile { border-color: rgba(138,18,50,.24); background: linear-gradient(180deg, #fff6f8 0%, #ffe9ee 100%); color: #8a1232; }
  .ocm-items-holder { min-height: 120px; }
</style>
<div class="modal-content ocm-modal" id="open-counter-root" data-sale-id="<?php echo (int)($_GET['sale_id'] ?? $_POST['sale_id'] ?? 0); ?>">
  <div class="ocm-head">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; font-size:26px;">&times;</button>
    <h3 id="open-counter-title">Loading Counter...</h3>
    <p id="open-counter-copy">Loading bill counter state...</p>
  </div>
  <div class="ocm-body">
    <div id="open-counter-summary" style="margin-bottom:12px;"></div>
    <div class="ocm-actions-wrap">
      <div class="ocm-grid" id="open-counter-grid"></div>
    </div>
    <div class="ocm-panel ocm-items-holder" id="open-counter-family-holder" style="display:none;">
      <div id="open-counter-family"></div>
    </div>
    <div class="alert alert-info" id="open-counter-feedback" style="display:none; margin-top:12px;"></div>
    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:12px;">
      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
<script>
(function() {
  var root = document.getElementById('open-counter-root');
  if (!root) return;
  var saleId = parseInt(root.getAttribute('data-sale-id') || '0', 10) || 0;
  var title = document.getElementById('open-counter-title');
  var copy = document.getElementById('open-counter-copy');
  var summary = document.getElementById('open-counter-summary');
  var grid = document.getElementById('open-counter-grid');
  var feedback = document.getElementById('open-counter-feedback');
  var familyHolder = document.getElementById('open-counter-family-holder');
  var familyRoot = document.getElementById('open-counter-family');
  var modalData = null;
  var selectedView = null;
  function esc(v){return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
  function money(v){return Number(v||0).toFixed(2);}
  function show(msg, type){feedback.style.display='block'; feedback.className='alert alert-' + (type||'info'); feedback.textContent=msg;}
  function trimQty(v){
    var n = Number(v || 0).toFixed(2);
    return n.replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');
  }
  function renderFamily(data) {
    var views = data.family_views || [];
    if (!views.length) return '';
    var activeId = Number(data.active_child_sale_id || data.target_sale_id || data.sale_id || 0);
    var heading = 'Bill Family';
    if ((data.family_type || '') === 'fresh_open') {
      heading = 'Fresh Order Items';
    } else if ((data.family_type || '').indexOf('parent') !== -1 || (data.family_type || '').indexOf('closed_parent') !== -1) {
      heading = 'Parent And Children';
    }
    var switcher = '<div style="font-size:11px;font-weight:900;color:#2f7a31;text-transform:uppercase;margin-bottom:8px;">' + esc(heading) + '</div><div class="ocm-switcher">';
    for (var i = 0; i < views.length; i++) {
      var view = views[i];
      var isActive = Number(view.sale_id || 0) === activeId || (!activeId && i === 0);
      switcher += '<button type="button" class="ocm-pill' + (isActive ? ' active' : '') + '" data-view-sale-id="' + Number(view.sale_id || 0) + '">' + esc(view.label || ('Bill #' + view.sale_id)) + '</button>';
    }
    switcher += '</div><div id="open-counter-family-items"></div>';
    return switcher;
  }
  function renderFamilyItems(viewSaleId) {
    if (!modalData) return;
    var views = modalData.family_views || [];
    var target = null;
    for (var i = 0; i < views.length; i++) {
      if (Number(views[i].sale_id || 0) === Number(viewSaleId || 0)) { target = views[i]; break; }
    }
    if (!target && views.length) target = views[0];
    var host = document.getElementById('open-counter-family-items');
    if (!host || !target) return;
    selectedView = target;
    var items = target.items || [];
    var html = '<div style="font-size:12px;font-weight:800;margin-bottom:8px;color:#7a5424;">Showing ' + esc(target.label || '') + (target.is_open ? ' <span class="badge badge-warning">OPEN</span>' : '') + '</div>';
    if (modalData && modalData.family_views && modalData.family_views.length > 1) {
      html += '<div style="font-size:11px;color:#7a5424;margin-bottom:8px;">Select a child bill above to view its items. Open child items are shown from the live sale print.</div>';
    }
    if (!items.length) {
      html += '<div class="alert alert-warning" style="margin-bottom:0;">No items on this bill yet.</div>';
    } else {
      html += '<table class="ocm-table"><thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead><tbody>';
      for (var j = 0; j < items.length; j++) {
        var item = items[j];
        html += '<tr><td>' + esc(item.name || '') + '</td><td>' + esc(trimQty(item.qty)) + '</td><td>' + esc(money(item.total)) + '</td></tr>';
      }
      html += '</tbody></table>';
    }
    host.innerHTML = html;
    var pills = root.querySelectorAll('[data-view-sale-id]');
    pills.forEach(function(pill) {
      pill.classList.toggle('active', Number(pill.getAttribute('data-view-sale-id') || '0') === Number(target.sale_id || 0));
    });
  }
  function submit(counterId, billMode, isClose) {
    var continueSaleId = saleId;
    if (selectedView && selectedView.is_open) {
      continueSaleId = Number(selectedView.sale_id || 0) || saleId;
    }

    if (isClose) {
      show('Loading closing counters...', 'info');
      $.ajax({
        url: 'bill_counter_modal.php',
        type: 'POST',
        data: {
          final_verdict: 'close',
          close_parent_sale_id: saleId,
          open_mode: 'open',
          bill_mode: billMode,
          bill_cntr: counterId || 0
        },
        dataType: 'html'
      }).done(function(html) {
        $('#load').html(html);
      }).fail(function() {
        show('Could not load close counter selection right now.', 'danger');
      });
      return;
    }

    show('Initializing bill session...', 'info');
    $.ajax({
      url: 'api/open_api.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'initialise',
        sale_id: continueSaleId,
        counter: counterId,
        st: 2
      }
    }).done(function(res){
      if (!res || !res.ok) {
        show(res && res.message ? res.message : 'Could not complete this action.', 'danger');
        return;
      }
      var redirect = (res.data && res.data.redirect) ? res.data.redirect : '../kwedia/index.php';
      window.location = redirect;
    }).fail(function(){ show('Could not complete this action right now.', 'danger'); });
  }
  $.getJSON('api/open_api.php', { action:'counter', sale_id:saleId }).done(function(res){
    var data = res && res.data ? res.data : {};
    modalData = data;
    if (!res || !res.ok) {
      summary.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">' + esc(res && res.message ? res.message : 'Unable to load counters.') + '</div>';
      familyHolder.style.display = 'none';
      return;
    }
    title.textContent = data.is_open_bill_mode ? 'Open Bill Counter' : 'Continuation Counter';
    copy.textContent = data.is_open_bill_mode ? ('Choose the counter you want to continue this open bill from for bill #' + saleId + '.') : ('Choose the counter you want to add this continuation bill from for parent bill #' + saleId + '.');
    var helperCopy = '';
    if ((data.family_type || '') === 'fresh_open') {
      helperCopy = 'This is a fresh order, so the live sale print items are shown below.';
    } else if ((data.family_type || '').indexOf('parent') !== -1 || (data.family_type || '').indexOf('closed_parent') !== -1) {
      helperCopy = 'Parent and child bills are listed below. If an open child exists, its live items are shown by default.';
    }
    summary.innerHTML = '<div class="alert alert-secondary" style="margin-bottom:0;">Family total: <strong>' + money(data.family_total) + '</strong>' + (data.identifier_label ? ' | Tag: <strong>' + esc(data.identifier_label) + '</strong>' : '') + ' | Type: <strong>' + esc(data.family_type || '') + '</strong>' + (helperCopy ? '<br><span style="font-size:11px;">' + esc(helperCopy) + '</span>' : '') + '</div>';
    var html = '<button type="button" class="ocm-btn ocm-close-bill-tile" data-close="1" data-bill-mode="' + esc(data.bill_mode || 'each') + '"><strong>CLOSE BILL</strong></button>';
    var counters = data.counters || [];
    for (var i = 0; i < counters.length; i++) {
      html += '<button type="button" class="ocm-btn" data-counter-id="' + counters[i].cntr_id + '" data-bill-mode="' + esc(data.bill_mode || 'each') + '"><strong>' + esc(counters[i].name) + '</strong></button>';
    }
    grid.innerHTML = html;
    familyRoot.innerHTML = renderFamily(data);
    familyHolder.style.display = data.family_views && data.family_views.length ? 'block' : 'none';
    renderFamilyItems(data.active_child_sale_id || data.target_sale_id || data.sale_id || 0);
  }).fail(function(){
    summary.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">Unable to load counters.</div>';
    familyHolder.style.display = 'none';
  });
  familyRoot.addEventListener('click', function(e) {
    var pill = e.target.closest('[data-view-sale-id]');
    if (!pill) return;
    renderFamilyItems(Number(pill.getAttribute('data-view-sale-id') || '0'));
  });
  grid.addEventListener('click', function(e){
    var btn = e.target.closest('button');
    if (!btn) return;
    var billMode = btn.getAttribute('data-bill-mode') || 'each';
    if (btn.getAttribute('data-close') === '1') {
      submit(0, billMode, true);
      return;
    }
    submit(parseInt(btn.getAttribute('data-counter-id') || '0', 10) || 0, billMode, false);
  });
})();
</script>
