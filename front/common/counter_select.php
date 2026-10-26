<?php
/* Universal counter-selection modal.
 * Callers may set these variables before including this file:
 * $counter_select_api, $counter_select_action, $counter_select_status,
 * $counter_select_sale_id, $counter_select_title, $counter_select_note.
 */
if (!isset($connection)) {
    require_once dirname(__DIR__, 2) . '/inc/connection.php';
}

$counter_select_api = (string)($counter_select_api ?? '../kwedia/api/bill_api.php');
$counter_select_action = (string)($counter_select_action ?? 'initialise');
$counter_select_status = (int)($counter_select_status ?? 1);
$counter_select_sale_id = (int)($counter_select_sale_id ?? ($_REQUEST['sale_id'] ?? 0));
$counter_select_title = (string)($counter_select_title ?? ($counter_select_sale_id ? 'Select Counter For Bill No. ' . $counter_select_sale_id : 'Select Counter'));
$counter_select_note = (string)($counter_select_note ?? ($counter_select_sale_id ? 'Choose where this bill should continue.' : 'Choose a counter to continue.'));

$counter_select_rows = [];
$counter_select_query = mysqli_query($connection, "SELECT cntr_id, name FROM kp_counters WHERE del_status=2 AND take_status=1 ORDER BY cntr_id ASC");
while ($counter_select_query && ($counter_select_row = mysqli_fetch_assoc($counter_select_query))) {
    $counter_select_rows[] = $counter_select_row;
}
$counter_select_single = count($counter_select_rows) === 1 ? $counter_select_rows[0] : null;
?>
<style>
  .local-draft-overlay{position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,.58);display:flex;align-items:center;justify-content:center;padding:18px}.local-draft-modal{width:min(420px,100%);padding:28px;border-radius:20px;background:#fff;text-align:center;box-shadow:0 24px 70px rgba(15,23,42,.3);font-family:Arial,sans-serif}.local-draft-icon{width:48px;height:48px;line-height:48px;margin:0 auto 12px;border-radius:50%;background:#fff1cf;color:#a76b00;font-size:28px;font-weight:900}.local-draft-modal h3{margin:0 0 8px;color:#17324d;font-size:21px}.local-draft-sale{font-size:12px;font-weight:800;color:#7b8a99}.local-draft-modal p{color:#64748b}.local-draft-modal ul{text-align:left;padding-left:24px;max-height:160px;overflow:auto}.local-draft-actions{display:flex;justify-content:center;gap:10px;margin-top:20px}.local-draft-actions button{border:0;border-radius:10px;padding:11px 16px;font-weight:800;cursor:pointer}.local-draft-cancel{background:#f5dddd;color:#8c2d2d}.local-draft-continue{background:#1e63a5;color:#fff}
  .counter-select-shell .modal-content{width:100%;max-width:460px;margin:0 auto;border:0;border-radius:18px;overflow:hidden;box-shadow:0 20px 40px rgba(33,19,6,.24)}
  .counter-select-shell .modal-header{border:0;padding:14px 16px;background:linear-gradient(135deg,#7e2014,#b94828 52%,#e7a33a);color:#fff8ea}
  .counter-select-shell .modal-title{margin:0;font-size:20px;font-weight:900;text-transform:uppercase;line-height:1.2}
  .counter-select-shell .close{color:#fff;opacity:1}.counter-select-shell .modal-body{padding:14px;background:linear-gradient(180deg,#fff9ea,#f7edd2)}
  .counter-select-note{margin:0 0 10px;padding:10px 12px;border-radius:12px;background:#fff;color:#7e3a20;font-size:12px;font-weight:800;text-transform:uppercase;box-shadow:0 8px 16px rgba(86,61,24,.08)}
  .counter-select-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.counter-select-btn{min-height:96px;border:3px solid #61b846;border-radius:16px;background:linear-gradient(135deg,#02550e,#2e8a2d);color:#fff!important;text-decoration:none!important;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px 10px;box-shadow:0 14px 24px rgba(25,88,18,.18)}
  .counter-select-btn strong,.counter-select-btn span{display:block}.counter-select-btn strong{font-size:24px;font-weight:900;line-height:1}.counter-select-btn span{margin-top:8px;font-size:12px;font-weight:900;letter-spacing:.05em;text-transform:uppercase;line-height:1.2}.counter-select-empty{padding:16px;border-radius:12px;background:#fff7dc;color:#8a6a3a;font-size:13px;font-weight:800;text-align:center}.counter-select-shell .modal-footer{border:0;background:#fffdf8;padding:12px 14px 14px}.counter-select-close{min-height:44px;border:0;border-radius:12px;background:#ead6d6;color:#7e2014;font-size:13px;font-weight:900;text-transform:uppercase;padding:0 18px}
  @media(max-width:420px){.counter-select-btn{min-height:84px}}
</style>
<div class="counter-select-shell">
  <div class="modal-content" data-init-api="<?php echo htmlspecialchars($counter_select_api, ENT_QUOTES); ?>" data-init-action="<?php echo htmlspecialchars($counter_select_action, ENT_QUOTES); ?>" data-init-status="<?php echo (int)$counter_select_status; ?>" data-sale-id="<?php echo (int)$counter_select_sale_id; ?>">
    <div class="modal-header">
      <?php if (!$counter_select_sale_id) { ?><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button><?php } ?>
      <h3 class="modal-title"><?php echo htmlspecialchars($counter_select_title, ENT_QUOTES); ?></h3>
    </div>
    <div class="modal-body">
      <p class="counter-select-note"><?php echo htmlspecialchars($counter_select_note, ENT_QUOTES); ?></p>
      <?php if (!$counter_select_rows) { ?>
        <div class="counter-select-empty">No active counters are available right now.</div>
      <?php } else { ?>
        <div class="counter-select-grid">
          <?php foreach ($counter_select_rows as $counter_select_row) { ?>
            <a href="#" class="counter-select-btn" data-counter="<?php echo (int)$counter_select_row['cntr_id']; ?>"><strong><?php echo (int)$counter_select_row['cntr_id']; ?></strong><span><?php echo htmlspecialchars((string)$counter_select_row['name'], ENT_QUOTES); ?></span></a>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
    <div class="modal-footer"><button type="button" class="counter-select-close" data-dismiss="modal">Close</button></div>
  </div>
</div>
<script>
(function(){
  var root=document.querySelector('.counter-select-shell .modal-content');
  if(!root||typeof $==='undefined')return;
  var shell=root.closest('.counter-select-shell'), buttons=shell.querySelectorAll('.counter-select-btn');
  function unlock(){for(var i=0;i<buttons.length;i++){buttons[i].classList.remove('is-locked');buttons[i].style.pointerEvents='';}}
  function esc(v){return $('<div>').text(v==null?'':String(v)).html();}
  function showDraft(xhr,requestedCounter){
    var res=(xhr&&xhr.responseJSON)||xhr||{},data=res.data||res,items=Array.isArray(data.items)?data.items:[];
    var counterName=data.counter_name||('Counter '+(data.counter||''));
    var sourceLabel=data.items_source==='open'?'Open-order items':'Live draft items';
    var list=items.length?'<ul style="text-align:left;padding-left:22px">'+items.map(function(item){item=item||{};var name=item.item_name||item.name||'Item',qty=item.qty||item.quantity||0;return '<li><strong>'+esc(name)+'</strong> &times; '+esc(qty)+'</li>';}).join('')+'</ul>':'<p>No draft items found.</p>';
    unlock();
    var overlay=$('<div class="local-draft-overlay"><div class="local-draft-modal"><div class="local-draft-icon">!</div><h3>Draft sale already open</h3><div class="local-draft-sale">SALE #'+esc(data.sale_id||'')+' · '+esc(counterName)+'</div><p>'+esc(sourceLabel)+' are attached to this bill. Choose whether to continue or cancel it.</p>'+list+'<div class="local-draft-actions"><button type="button" class="local-draft-cancel">Cancel sale</button><button type="button" class="local-draft-continue">Continue sale</button></div></div></div>');
    $('body').append(overlay);
    overlay.find('.local-draft-continue').on('click',function(){overlay.remove();if(data.sale_id&&data.counter)submit(data.counter,2,data.sale_id);});
    overlay.find('.local-draft-cancel').on('click',function(){var button=$(this).prop('disabled',true).text('Cancelling...');$.post(root.getAttribute('data-init-api'),{action:'cancel_order'}).done(function(cancel){if(cancel&&cancel.ok){overlay.remove();submit(requestedCounter,1,0);}else{button.prop('disabled',false).text('Cancel sale');}});});
  }
  function submit(counter,status,saleId){for(var i=0;i<buttons.length;i++){buttons[i].classList.add('is-locked');buttons[i].style.pointerEvents='none';}$.ajax({url:root.getAttribute('data-init-api'),type:'POST',dataType:'json',data:{action:root.getAttribute('data-init-action'),counter:counter,st:status,sale_id:saleId||0}}).done(function(res){var data=res&&res.data?res.data:{};var redirect=data.redirect||res.redirect||'';if(res&&(res.ok||res.success)&&redirect){window.location.href=redirect;return;}if(data.ongoing_sale){showDraft(res,counter);return;}unlock();}).fail(function(xhr){if(xhr&&xhr.status===409){showDraft(xhr,counter);return;}unlock();});}
  function select(){var button=this,counter=parseInt(button.getAttribute('data-counter')||'0',10)||0,status=parseInt(root.getAttribute('data-init-status')||'0',10)||0;if(!counter||!status)return;submit(counter,status,root.getAttribute('data-sale-id')||0);}
  for(var i=0;i<buttons.length;i++)buttons[i].addEventListener('click',function(e){e.preventDefault();select.call(this);});
  <?php if ($counter_select_single) { ?>
  if(buttons.length===1)buttons[0].click();
  <?php } ?>
})();
</script>
