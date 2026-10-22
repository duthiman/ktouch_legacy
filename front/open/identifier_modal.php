<style>
  .oid-modal { border: 0; border-radius: 5px; overflow: hidden; background: #fff9f1; box-shadow: 0 28px 52px rgba(73,67,25,.24); }
  .oid-head { padding: 16px 18px; background: linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%); color: #fff9f1; }
  .oid-head h3 { margin: 0; font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
  .oid-head p { margin: 6px 0 0; font-size: 12px; font-weight: 700; opacity: .94; }
  .oid-body { padding: 14px; background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%); display:flex; flex-direction:column; gap:10px; max-height:calc(100vh - 120px); }
  .oid-topbar { display:grid; grid-template-columns:1fr auto; gap:8px; align-items:stretch; flex:0 0 auto; }
  .oid-switch { display:inline-grid; grid-template-columns:1fr 1fr; gap:8px; width:100%; padding:8px; border-radius:5px; background:#f4ebdc; }
  .oid-switch-btn { min-height:46px; border-radius:5px; border:0; background:transparent; color:#7a5424; font-size:13px; font-weight:900; text-transform:uppercase; }
  .oid-switch-btn.active { background:#fff; color:#2f7a31; box-shadow:0 12px 18px rgba(79,58,24,.08); }
  .oid-toolbar-btn { min-height:46px; padding:0 12px; border-radius:5px; border:1px solid rgba(97,184,70,.18); background:#fff; color:#2f7a31; font-size:12px; font-weight:900; text-transform:uppercase; white-space:nowrap; }
  .oid-toolbar-btn.active { background:#2f7a31; color:#fff; border-color:#2f7a31; }
  .oid-main-scroll,
  .oid-items-panel { flex:1 1 auto; min-height:0; overflow-y:auto; padding-right:2px; }
  .oid-items-panel[hidden] { display:none !important; }
  .oid-colors { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:10px; max-height:390px; overflow-y:auto; }
  .oid-color-btn { min-height:84px; padding:12px; border-radius:5px; border:2px solid rgba(97,184,70,.16); background:#fff; text-align:left; }
  .oid-color-btn.active { border-color:#f36c26; box-shadow:0 14px 24px rgba(79,58,24,.12); }
  .oid-color-swatch { width:100%; height:26px; border-radius:5px; border:1px solid rgba(0,0,0,.08); }
  .oid-color-btn strong { display:block; margin-top:10px; font-size:13px; font-weight:900; color:#2f7a31; }
  .oid-name-input { width:100%; height:54px; padding:0 14px; border-radius:5px; border:1px solid rgba(122,84,36,.18); background:#fff; color:#3b2a18; font-size:20px; font-weight:900; }
  .oid-presets { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
  .oid-preset-btn { min-height:40px; padding:0 14px; border-radius:5px; border:1px solid rgba(97,184,70,.18); background:#fff; color:#7a5424; font-size:12px; font-weight:900; }
  .oid-preview { margin-top:14px; padding:12px 14px; border-radius:5px; background:#fff; border:1px solid rgba(97,184,70,.12); color:#3b2a18; }
  .oid-preview strong { display:block; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; color:#7a5424; }
  .oid-preview span { display:block; margin-top:6px; font-size:18px; font-weight:900; color:#2f7a31; }
  .oid-items-box { max-height:280px; overflow:auto; border-radius:5px; border:1px solid rgba(97,184,70,.12); background:#fff; }
  .oid-items-total { margin-top:10px; padding:10px 12px; border-radius:5px; background:#e9f6df; color:#205e24; font-size:13px; font-weight:900; display:flex; justify-content:space-between; }
  .oid-actions { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; flex:0 0 auto; padding-top:8px; border-top:1px solid rgba(97,184,70,.12); background:linear-gradient(180deg, rgba(255,253,248,.96) 0%, rgba(255,245,234,.98) 100%); }
  .oid-btn { min-height:48px; width:100%; padding:0 14px; border-radius:5px; border:1px solid rgba(97,184,70,.18); background:#fff; color:#7a5424; font-size:13px; font-weight:900; text-transform:uppercase; }
  .oid-btn.primary { border:0; background:linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%); color:#fff; }
  .oid-btn.warn { background:#8a1232; color:#fff; border-color:#8a1232; }
  @media (max-width: 575px) {
    .oid-topbar { grid-template-columns:1fr; }
    .oid-actions { grid-template-columns:1fr 1fr 1fr; gap:8px; }
    .oid-btn { min-height:44px; padding:0 8px; font-size:11px; }
  }
</style>
<div class="modal-content oid-modal" id="open-identifier-root"
  data-sale-id="<?php echo (int)($_GET['sale_id'] ?? $_POST['sale_id'] ?? 0); ?>"
  data-counter-id="<?php echo (int)($_GET['counter_id'] ?? $_POST['counter_id'] ?? 0); ?>"
  data-bill-mode="<?php echo htmlspecialchars((string)($_GET['bill_mode'] ?? $_POST['bill_mode'] ?? 'each'), ENT_QUOTES); ?>"
  data-fresh-open="<?php echo !empty($_GET['fresh_open']) || !empty($_POST['fresh_open']) ? 1 : 0; ?>">
  <div class="oid-head">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; font-size:26px;">&times;</button>
    <h3 id="open-identifier-title">Identify Bill</h3>
    <p>Choose a color or name for this open parent bill before continuing.</p>
  </div>
  <div class="oid-body" id="open-identifier-body">Loading...</div>
</div>
<script>
(function() {
  var root = document.getElementById('open-identifier-root');
  if (!root) return;
  var saleId = parseInt(root.getAttribute('data-sale-id') || '0', 10) || 0;
  var counterId = parseInt(root.getAttribute('data-counter-id') || '0', 10) || 0;
  var billMode = root.getAttribute('data-bill-mode') || 'each';
  var freshOpen = root.getAttribute('data-fresh-open') === '1';
  var body = document.getElementById('open-identifier-body');
  var title = document.getElementById('open-identifier-title');
  var currentType = 'color';
  var currentValue = '';
  var currentLabel = '';
  var currentHex = '';
  function esc(v){return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
  function money(v){return Number(v||0).toFixed(2);}
  function updatePreview() {
    var preview = document.getElementById('oid-preview-value');
    if (!preview) return;
    if (currentType === 'color' && currentLabel) preview.textContent = currentLabel + (currentHex ? ' (' + currentHex + ')' : '');
    else if (currentType === 'name' && currentValue) preview.textContent = currentValue;
    else preview.textContent = currentType === 'name' ? 'Type a bill name' : 'Choose a color';
  }
  function showFeedback(msg, kind) {
    var feedback = document.getElementById('oid-feedback');
    if (!feedback) return;
    feedback.className = 'alert alert-' + (kind || 'info');
    feedback.style.display = 'block';
    feedback.textContent = msg;
  }
  function render(data) {
    title.textContent = 'Identify Bill #' + (data.sale_id || saleId);
    var colors = data.colors || [];
    var items = data.items || [];
    var colorsHtml = '';
    for (var i = 0; i < colors.length; i++) {
      colorsHtml += '<button type="button" class="oid-color-btn" data-color-key="' + esc(colors[i].key) + '" data-color-label="' + esc(colors[i].label) + '" data-color-hex="' + esc(colors[i].hex) + '"><div class="oid-color-swatch" style="background:' + esc(colors[i].hex) + ';"></div><strong>' + esc(colors[i].label) + '</strong></button>';
    }
    var itemRows = '';
    for (var j = 0; j < items.length; j++) {
      itemRows += '<tr><td>' + esc(items[j].name) + '</td><td class="text-center">' + esc(items[j].qty) + '</td><td class="text-right">' + money(items[j].total) + '</td></tr>';
    }
    if (!itemRows) itemRows = '<tr><td colspan="3" class="text-center" style="padding:18px;">No items found for this bill.</td></tr>';
    body.innerHTML =
      '<div class="oid-topbar">'
      + '<div class="oid-switch"><button type="button" class="oid-switch-btn active" data-tab="color">Color</button><button type="button" class="oid-switch-btn" data-tab="name">Name</button></div>'
      + '<button type="button" class="oid-toolbar-btn" id="oid-items-toggle">View Items</button>'
      + '</div>'
      + '<div class="oid-main-scroll" id="oid-main-scroll">'
      + '<div id="oid-color-section"><div class="oid-colors">' + colorsHtml + '</div></div>'
      + '<div id="oid-name-section" hidden><input type="text" class="oid-name-input" id="oid-name-input" maxlength="40" placeholder="Type guest or table name"><label style="display:flex;align-items:center;gap:8px;margin-top:12px;"><input type="checkbox" id="oid-save-preset" value="1"> Save this name for later</label></div>'
      + '<div class="oid-preview"><strong>Selected Identifier</strong><span id="oid-preview-value">Choose a color</span></div>'
      + '</div>'
      + '<div class="oid-items-panel" id="oid-items-panel" hidden><div class="oid-items-box"><table class="table table-striped"><thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Amount</th></tr></thead><tbody>' + itemRows + '</tbody></table></div><div class="oid-items-total"><span>Total</span><span>KSh ' + money(data.items_total) + '</span></div></div>'
      + '<div class="alert alert-info" id="oid-feedback" style="display:none;"></div>'
      + '<div class="oid-actions"><button type="button" class="oid-btn" id="oid-back-btn">Back</button><button type="button" class="oid-btn primary" id="oid-save-btn">Add Identifier</button><button type="button" class="oid-btn warn" id="oid-close-btn">Close Bill</button></div>';
    updatePreview();
    var itemsVisible = false;
    function toggleItems(forceState) {
      itemsVisible = typeof forceState === 'boolean' ? forceState : !itemsVisible;
      document.getElementById('oid-items-panel').hidden = !itemsVisible;
      document.getElementById('oid-main-scroll').hidden = itemsVisible;
      var toggleBtn = document.getElementById('oid-items-toggle');
      if (toggleBtn) {
        toggleBtn.classList.toggle('active', itemsVisible);
        toggleBtn.textContent = itemsVisible ? 'Back To Tag' : 'View Items';
      }
    }
    body.addEventListener('click', function(e) {
      var tab = e.target.closest('[data-tab]');
      if (tab) {
        var mode = tab.getAttribute('data-tab');
        currentType = mode;
        document.querySelectorAll('[data-tab]').forEach(function(btn){ btn.classList.toggle('active', btn === tab); });
        document.getElementById('oid-color-section').hidden = mode !== 'color';
        document.getElementById('oid-name-section').hidden = mode !== 'name';
        toggleItems(false);
        updatePreview();
        return;
      }
      var itemsButton = e.target.closest('#oid-items-toggle');
      if (itemsButton) {
        toggleItems();
        return;
      }
      var color = e.target.closest('[data-color-key]');
      if (color) {
        document.querySelectorAll('[data-color-key]').forEach(function(btn){ btn.classList.remove('active'); });
        color.classList.add('active');
        currentType = 'color';
        currentValue = color.getAttribute('data-color-key') || '';
        currentLabel = color.getAttribute('data-color-label') || currentValue;
        currentHex = color.getAttribute('data-color-hex') || '';
        updatePreview();
        return;
      }
    });
    toggleItems(false);
    document.getElementById('oid-back-btn').addEventListener('click', function() {
      if (freshOpen) {
        if (window.openBillCounterModal) window.openBillCounterModal('open', 'open', billMode);
      } else if (counterId > 0) {
        if (window.openLoadCounterModal) window.openLoadCounterModal(saleId, 'open');
      } else if (window.openLoadContinueTypeModal) {
        window.openLoadContinueTypeModal(saleId);
      }
    });
    document.getElementById('oid-name-input').addEventListener('input', function() {
      currentType = 'name';
      currentValue = String(this.value || '').trim();
      currentLabel = currentValue;
      currentHex = '';
      updatePreview();
    });
    document.getElementById('oid-save-btn').addEventListener('click', function() {
      var payload = {
        action: 'save_identifier',
        sale_id: saleId,
        type: currentType,
        bill_mode: billMode,
        fresh_open: freshOpen ? 1 : 0,
        value: currentType === 'color' ? currentValue : (document.getElementById('oid-name-input').value || '').trim(),
        label: currentType === 'color' ? currentLabel : '',
        hex: currentType === 'color' ? currentHex : '',
        save_preset: document.getElementById('oid-save-preset') && document.getElementById('oid-save-preset').checked ? 1 : 0
      };
      $.ajax({ url:'api/open_api.php', type:'POST', dataType:'json', data:payload }).done(function(res) {
        var apiData = res && res.data ? res.data : {};
        if (!res || !res.ok) {
          showFeedback(res && res.message ? res.message : 'Unable to save the identifier.', 'danger');
          return;
        }
        if (freshOpen) {
          $.ajax({
            url:'api/open_api.php',
            type:'POST',
            dataType:'json',
            data:{
              action:'open_order',
              open_state:'open',
              open_mode:'open',
              bill_mode:billMode,
              bill_cntr:counterId,
              identifier_type: apiData.identifier ? apiData.identifier.type : payload.type,
              identifier_value: apiData.identifier ? apiData.identifier.value : payload.value,
              identifier_label: apiData.identifier ? apiData.identifier.label : payload.label,
              identifier_hex: apiData.identifier ? apiData.identifier.hex : payload.hex,
              save_preset: payload.save_preset
            }
          }).done(function(openRes){
            if (!openRes || !openRes.ok) {
              showFeedback(openRes && openRes.message ? openRes.message : 'Unable to register the open bill.', 'danger');
              return;
            }
            window.location = (openRes.data && openRes.data.redirect) ? openRes.data.redirect : '../log_out.php';
          }).fail(function(){ showFeedback('Unable to register the open bill right now.', 'danger'); });
          return;
        }
        showFeedback(res.message || 'Identifier saved.', 'success');
        if (apiData.redirect) {
          window.setTimeout(function(){ window.location = apiData.redirect; }, 250);
        }
      }).fail(function(){ showFeedback('Unable to save the identifier right now.', 'danger'); });
    });
    document.getElementById('oid-close-btn').addEventListener('click', function() {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:28px;text-align:center;font-weight:900;">Loading counters...</div></div>');
      $.ajax({
        url:'bill_counter_modal.php',
        type:'POST',
        dataType:'html',
        data:{
          final_verdict:'close',
          close_parent_sale_id:saleId,
          print_final: billMode === 'last' ? 1 : 0,
          open_mode:'open',
          bill_mode:billMode
        }
      }).done(function(html){
        $('#load').html(html);
      }).fail(function(){
        showFeedback('Unable to load the print counter right now.', 'danger');
      });
    });
  }
  $.getJSON('api/open_api.php', {
    action:'load_identifier_modal',
    sale_id:saleId,
    counter_id:counterId,
    bill_mode:billMode,
    fresh_open:freshOpen ? 1 : 0
  }).done(function(res){
    if (!res || !res.ok || !res.data) {
      body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">' + esc(res && res.message ? res.message : 'Unable to load the identifier step.') + '</div>';
      return;
    }
    render(res.data);
  }).fail(function(){
    body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">Unable to load the identifier step.</div>';
  });
})();
</script>

