<style>
  .oct-modal .modal-content { border-radius: 18px; overflow: hidden; }
  .oct-head { padding: 14px 16px; background: linear-gradient(135deg, #8a1232 0%, #c5432f 100%); color: #fff; }
  .oct-head h3 { margin: 0; font-size: 20px; font-weight: 900; text-transform: uppercase; }
  .oct-head p { margin: 6px 0 0; font-size: 12px; font-weight: 700; opacity: .95; }
  .oct-body { padding: 16px; background: #fffaf3; }
  .oct-summary { margin-bottom: 14px; padding: 12px 14px; border-radius: 12px; background: #fff; border: 1px solid rgba(122,84,36,.12); color: #7a5424; font-weight: 800; }
  .oct-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .oct-btn { display: flex; align-items: flex-start; width: 100%; min-height: 124px; padding: 14px; border-radius: 14px; border: 2px solid #e6d0b3; background: #fff; text-align: left; }
  .oct-btn strong { display: block; font-size: 15px; font-weight: 900; }
  .oct-btn span { display: block; margin-top: 5px; font-size: 12px; color: #7a5424; line-height: 1.45; }
  .oct-btn.continue-btn { border-color: #9acb87; color: #2f7a31; }
  .oct-btn.open-btn { border-color: #d6a6b4; color: #8a1232; }
</style>
<div class="oct-modal" id="open-continue-type-root" data-sale-id="<?php echo (int)($_GET['sale_id'] ?? $_POST['sale_id'] ?? 0); ?>">
  <div class="modal-content">
    <div class="oct-head">
      <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
      <h3 id="open-continue-type-title">Bill Next Step</h3>
      <p>Choose how the next bill should behave after this first printed bill.</p>
    </div>
    <div class="oct-body" id="open-continue-type-body">Loading...</div>
    <div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">Back</button></div>
  </div>
</div>
<script>
(function() {
  var root = document.getElementById('open-continue-type-root');
  if (!root) return;
  var saleId = parseInt(root.getAttribute('data-sale-id') || '0', 10) || 0;
  var body = document.getElementById('open-continue-type-body');
  var title = document.getElementById('open-continue-type-title');
  function esc(v){return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
  function money(v){return Number(v||0).toFixed(2);}
  $.getJSON('api/open_api.php', { action:'continue_type', sale_id:saleId }).done(function(res){
    var data = res && res.data ? res.data : {};
    if (!res || !res.ok) {
      body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">' + esc(res && res.message ? res.message : 'Unable to load bill options.') + '</div>';
      return;
    }
    title.textContent = 'Bill #' + (data.sale_id || saleId) + ' Next Step';
    var options = data.options || [];
    var html = '<div class="oct-summary">First bill total: <strong style="font-size:18px;">' + money(data.amount) + '</strong></div><div class="oct-grid">';
    for (var i = 0; i < options.length; i++) {
      var style = options[i].bill_mode === 'last' ? 'open-btn' : 'continue-btn';
      html += '<button type="button" class="oct-btn ' + style + '" data-bill-mode="' + esc(options[i].bill_mode) + '"><span><strong>' + esc(options[i].title) + '</strong><span>' + esc(options[i].copy) + '</span></span></button>';
    }
    html += '</div>';
    body.innerHTML = html;
    var buttons = body.querySelectorAll('[data-bill-mode]');
    for (var j = 0; j < buttons.length; j++) {
      buttons[j].addEventListener('click', function() {
        var billMode = this.getAttribute('data-bill-mode') || 'each';
        if (window.openLoadIdentifierModal) window.openLoadIdentifierModal(saleId, 0, billMode);
      });
    }
  }).fail(function(){ body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">Unable to load bill options.</div>'; });
})();
</script>
