<style>
  .open-modal { border: 0; border-radius: 24px; overflow: hidden; background: #fff9f1; box-shadow: 0 28px 52px rgba(73,67,25,.24); }
  .open-modal-head { padding: 16px 18px; background: linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%); color: #fff9f1; }
  .open-modal-head h3 { margin: 0; font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
  .open-modal-head p { margin: 6px 0 0; font-size: 12px; font-weight: 700; opacity: .94; }
  .open-modal-body { padding: 14px; background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%); }
  .open-modal-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
  .open-modal-stat { padding: 12px; border-radius: 16px; background: #fff; border: 1px solid rgba(97,184,70,.12); }
  .open-modal-stat strong { display: block; font-size: 10px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; color: #7a5424; }
  .open-modal-stat span { display: block; margin-top: 7px; font-size: 17px; font-weight: 900; color: #2f7a31; }
  .open-items-box { max-height: 46vh; overflow: auto; border-radius: 18px; border: 1px solid rgba(97,184,70,.12); background: #fff; }
  .open-items-box table { margin-bottom: 0; }
  .open-items-box thead th { position: sticky; top: 0; z-index: 1; background: #f8f2e7; color: #2f7a31; }
  .open-items-total { margin-top: 12px; padding: 12px 14px; border-radius: 16px; background: #e9f6df; color: #205e24; font-size: 14px; font-weight: 900; display: flex; justify-content: space-between; gap: 12px; }
  .open-modal-actions { display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
  .open-modal-btn { min-height: 48px; padding: 0 18px; border-radius: 16px; border: 1px solid rgba(97,184,70,.18); background: #fff; color: #7a5424; font-size: 13px; font-weight: 900; text-transform: uppercase; }
  .open-modal-btn.primary { border: 0; background: linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%); color: #fff; }
</style>
<div class="modal-content open-modal" id="open-bill-modal-root" data-sale-id="<?php echo (int)($_GET['sale_id'] ?? $_POST['sale_id'] ?? 0); ?>">
  <div class="open-modal-head">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; font-size:26px;">&times;</button>
    <h3 id="open-bill-modal-title">Confirm Bill</h3>
    <p>Review the items in this order before moving to the identifier step.</p>
  </div>
  <div class="open-modal-body" id="open-bill-modal-body">Loading...</div>
</div>
<script>
(function() {
  var root = document.getElementById('open-bill-modal-root');
  if (!root) return;
  var saleId = parseInt(root.getAttribute('data-sale-id') || '0', 10) || 0;
  var body = document.getElementById('open-bill-modal-body');
  var title = document.getElementById('open-bill-modal-title');
  function esc(v){return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
  function money(v){return Number(v||0).toFixed(2);}
  $.getJSON('api/open_api.php', { action:'bill_modal', sale_id:saleId }).done(function(res){
    var data = res && res.data ? res.data : {};
    if (!res || !res.ok) {
      body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">' + esc(res && res.message ? res.message : 'Unable to load bill.') + '</div>';
      return;
    }
    title.textContent = 'Confirm Bill #' + (data.sale_id || saleId);
    var rows = '';
    var items = data.items || [];
    for (var i = 0; i < items.length; i++) {
      rows += '<tr><td>' + esc(items[i].name) + '</td><td class="text-center">' + esc(items[i].qty) + '</td><td class="text-right">' + money(items[i].price) + '</td><td class="text-right">' + money(items[i].total) + '</td></tr>';
    }
    if (!rows) rows = '<tr><td colspan="4" class="text-center" style="padding:18px;">No items found for this bill.</td></tr>';
    body.innerHTML =
      '<div class="open-modal-summary">'
      + '<div class="open-modal-stat"><strong>Served By</strong><span>' + esc(String(data.owner_name || '').toUpperCase()) + '</span></div>'
      + '<div class="open-modal-stat"><strong>Counter</strong><span>' + esc(data.counter_name || '') + '</span></div>'
      + '<div class="open-modal-stat"><strong>Items</strong><span>' + items.length + '</span></div>'
      + '</div>'
      + '<div class="open-items-box"><table class="table table-striped"><thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Price</th><th class="text-right">Amount</th></tr></thead><tbody>' + rows + '</tbody></table></div>'
      + '<div class="open-items-total"><span>Total</span><span>KSh ' + money(data.grand_total) + '</span></div>'
      + '<div class="open-modal-actions"><button type="button" class="open-modal-btn" data-dismiss="modal">Back</button><button type="button" class="open-modal-btn primary" id="open-bill-modal-continue"' + (items.length < 1 ? ' disabled' : '') + '>Continue</button></div>';
    var btn = document.getElementById('open-bill-modal-continue');
    if (btn) btn.addEventListener('click', function(){ if (window.openLoadIdentifierModal) window.openLoadIdentifierModal(data.sale_id || saleId); });
  }).fail(function(){
    body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">Unable to load bill.</div>';
  });
})();
</script>
