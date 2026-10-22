<?php
/* Universal attribute/accompaniment selector.
 * Optional caller variables: $attribute_modal_api, $attribute_modal_action,
 * $attribute_modal_item_id, $attribute_modal_qty, $attribute_modal_add_callback.
 */
if (!isset($connection)) {
    require_once dirname(__DIR__, 2) . '/inc/connection.php';
}
if (!function_exists('get_column')) {
    require_once dirname(__DIR__, 2) . '/inc/gen_funcs.php';
}

$attribute_modal_api = (string)($attribute_modal_api ?? 'api/bill_api.php');
$attribute_modal_action = (string)($attribute_modal_action ?? 'add_item');
$attribute_modal_item_id = (int)($attribute_modal_item_id ?? ($_REQUEST['id'] ?? 0));
$attribute_modal_qty = (float)($attribute_modal_qty ?? ($_REQUEST['qty'] ?? 1));
$attribute_modal_required = max(1, (int)ceil($attribute_modal_qty));
$attribute_modal_name = $attribute_modal_item_id > 0 ? (string)get_column('kp_items', 'name', "item_id='$attribute_modal_item_id'") : '';
$attribute_modal_rows = [];
$attribute_modal_query = mysqli_query($connection, "SELECT m.attr_id, a.name, a.price_change FROM kp_item_attribute_mapping m JOIN kp_items_attributes a ON m.attr_id=a.id WHERE m.item_id='$attribute_modal_item_id' ORDER BY a.name ASC");
while ($attribute_modal_query && ($attribute_modal_row = mysqli_fetch_assoc($attribute_modal_query))) {
    $attribute_modal_rows[] = [
        'id' => (int)$attribute_modal_row['attr_id'],
        'name' => (string)$attribute_modal_row['name'],
        'price_change' => (float)($attribute_modal_row['price_change'] ?? 0),
    ];
}
$attribute_modal_existing = [];
$attribute_modal_user_id = (int)($_SESSION['user_id'] ?? 0);
$attribute_modal_status = $attribute_modal_user_id > 0 ? mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$attribute_modal_user_id' AND status=2 ORDER BY sale_id DESC LIMIT 1") : false;
$attribute_modal_status_row = $attribute_modal_status ? mysqli_fetch_assoc($attribute_modal_status) : null;
$attribute_modal_sale_id = (int)($attribute_modal_status_row['sale_id'] ?? 0);
if ($attribute_modal_sale_id > 0) {
    $attribute_modal_live = mysqli_query($connection, "SELECT attr_id, accompaniment_json, qty FROM kp_sale_items_live WHERE sale_id='$attribute_modal_sale_id' AND item_id='$attribute_modal_item_id' AND session_flag=2");
    while ($attribute_modal_live && ($attribute_modal_live_row = mysqli_fetch_assoc($attribute_modal_live))) {
        $attribute_modal_values = json_decode((string)($attribute_modal_live_row['accompaniment_json'] ?? ''), true);
        if (!is_array($attribute_modal_values)) $attribute_modal_values = [];
        if (!$attribute_modal_values && (int)$attribute_modal_live_row['attr_id'] > 0) $attribute_modal_values = [['id' => (int)$attribute_modal_live_row['attr_id']]];
        foreach ($attribute_modal_values as $attribute_modal_value) {
            $attribute_modal_existing[(int)($attribute_modal_value['id'] ?? $attribute_modal_value['attr_id'] ?? 0)] = (int)($attribute_modal_value['qty'] ?? $attribute_modal_live_row['qty'] ?? 0);
        }
    }
}
?>
<style>
  .common-attribute-shell{width:min(1120px,96vw);max-width:none;border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 70px rgba(0,48,31,.35);background:#fff;color:#071653}
  .common-attribute-shell .modal-header{position:relative;padding:22px 30px 20px;background:linear-gradient(135deg,#006b3c,#008f56 58%,#006b48);color:#fff;border:0}
  .common-attribute-shell .modal-title{font-size:28px;font-weight:900;letter-spacing:.2px;text-transform:uppercase}
  .common-attribute-shell .modal-header:after{content:'';position:absolute;right:0;top:0;width:43%;height:100%;background:linear-gradient(145deg,transparent 35%,rgba(0,70,50,.3));pointer-events:none}
  .common-attribute-shell .close{position:relative;z-index:2;color:#fff;opacity:1;font-size:38px;font-weight:300;text-shadow:none}
  .common-attribute-shell .modal-body{padding:0!important;background:#fff;display:grid;grid-template-columns:42% 58%;min-height:520px}
  .common-attribute-meta,.common-attribute-guide{display:none}
  .common-attribute-left{padding:28px 25px;border-right:1px solid #dce8ee;background:#fff}
  .common-attribute-left h3,.common-attribute-picked h3{margin:0 0 16px;color:#075d38;font-size:24px;font-weight:900;text-transform:uppercase}
  .common-attribute-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:11px}.common-attribute-btn{min-height:75px;border:0;border-radius:9px;font-size:16px;color:#071653;background:#edf2f7;font-weight:800;padding:10px;text-align:left;transition:.15s ease;box-shadow:none}.common-attribute-btn:nth-child(3n+1){background:#e5f6ed}.common-attribute-btn:nth-child(3n+2){background:#fff0df}.common-attribute-btn:nth-child(3n){background:#f5eaff}.common-attribute-btn:hover,.common-attribute-btn.is-selected{color:#fff;background:#007c48;border-color:#007c48;transform:translateY(-1px)}
  .common-attribute-picked{margin:0;padding:28px 25px;background:#fff}.common-attribute-picked h3{padding:13px 16px;margin:0;background:#edf9f4;border-radius:8px;font-size:23px}.common-attribute-picked ul{list-style:none;padding:0;margin:15px 0 0;border:1px solid #dce5ec;border-radius:9px;overflow:hidden}.common-attribute-picked li{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px 22px;border-bottom:1px solid #e1e7ec;background:#fff;font-size:16px;font-weight:800}.common-attribute-picked li:last-child{border-bottom:0}.common-attribute-picked li>span:first-child{flex:1}.common-attribute-qty{display:inline-flex;align-items:center;gap:7px}.common-attribute-qty button{width:38px;height:38px;border:0;border-radius:8px;background:#edf2f7;color:#07516b;font-size:23px;font-weight:900;line-height:1}.common-attribute-qty button:last-child{background:#008347;color:#fff}.common-attribute-qty input{width:62px;height:38px;border:1px solid #ccdbe6;border-radius:7px;background:#fff;text-align:center;font-weight:900;color:#071653;font-size:16px}.common-attribute-remove{border:0;border-radius:8px;background:#fde9ed;color:#e11;font-size:0;width:42px;height:38px}.common-attribute-remove:after{content:'🗑';font-size:20px}
  .common-attribute-actions{display:flex;justify-content:flex-end;align-items:center;gap:14px;margin:0;padding:20px 25px;background:#effbf5;border-top:1px solid #dceee5}.common-attribute-summary{margin-right:auto;color:#075d38;font-size:16px;font-weight:700}.common-attribute-summary b{font-size:25px}.common-attribute-submit{border:0;border-radius:8px;background:#008347;color:#fff;padding:16px 30px;font-size:17px;font-weight:900;text-transform:uppercase}.common-attribute-cancel{border:0;border-radius:8px;background:#e7eef4;color:#124b62;padding:16px 30px;font-size:17px;font-weight:900;text-transform:uppercase}.common-attribute-submit[disabled]{opacity:.5;cursor:not-allowed}
  /* Compact, centered presentation. */
  .common-attribute-shell{width:min(940px,92vw);margin:5vh auto 0;border-radius:14px;}
  .common-attribute-shell .modal-header{padding:15px 22px 13px}
  .common-attribute-shell .modal-title{font-size:22px}
  .common-attribute-shell .modal-body{min-height:390px}
  .common-attribute-left{padding:19px 18px}
  .common-attribute-left h3,.common-attribute-picked h3{font-size:19px;margin-bottom:11px}
  .common-attribute-grid{gap:8px}
  .common-attribute-btn{min-height:57px;font-size:13px;padding:7px}
  .common-attribute-picked{padding:19px 18px}
  .common-attribute-picked h3{padding:10px 13px;font-size:19px}
  .common-attribute-picked ul{margin-top:11px}
  .common-attribute-picked li{padding:11px 13px;gap:8px;font-size:14px}
  .common-attribute-qty{gap:5px}
  .common-attribute-qty button{width:30px;height:30px;font-size:19px}
  .common-attribute-qty input{width:42px;height:30px;font-size:14px}
  .common-attribute-remove{width:32px;height:30px}
  .common-attribute-actions{gap:10px;padding:13px 18px}
  .common-attribute-summary{font-size:14px}
  .common-attribute-summary b{font-size:21px}
  .common-attribute-submit,.common-attribute-cancel{padding:11px 22px;font-size:14px}
  @media(max-width:760px){
    .common-attribute-shell{width:98vw;margin:2vh auto 0;border-radius:12px}
    .common-attribute-shell .modal-header{padding:13px 15px 11px}
    .common-attribute-shell .modal-title{font-size:18px}
    .common-attribute-shell .modal-header>div{font-size:12px!important}
    .common-attribute-shell .modal-body{grid-template-columns:50% 50%;min-height:420px}
    .common-attribute-left{padding:16px 10px;border-right:1px solid #dce8ee;border-bottom:0}
    .common-attribute-picked{padding:16px 10px}
    .common-attribute-left h3,.common-attribute-picked h3{font-size:16px;margin-bottom:9px}
    .common-attribute-grid{grid-template-columns:repeat(2,1fr);gap:6px}
    .common-attribute-btn{min-height:62px;font-size:11px;padding:6px}
    .common-attribute-picked h3{font-size:16px;padding:9px 8px}
    .common-attribute-picked li{padding:9px 6px;gap:3px;font-size:11px}
    .common-attribute-qty{gap:2px}.common-attribute-qty button{width:25px;height:28px}.common-attribute-qty input{width:31px;height:28px;font-size:12px}
    .common-attribute-remove{width:27px;height:28px}.common-attribute-remove:after{font-size:13px}
    .common-attribute-actions{padding:10px;gap:6px;flex-wrap:wrap}.common-attribute-summary{width:100%;margin:0;font-size:12px}.common-attribute-cancel,.common-attribute-submit{flex:1;padding:10px 5px;font-size:11px}
  }
  @media(max-width:480px){
    .common-attribute-shell{width:100vw;max-width:100vw;margin:0;border-radius:0}
    .common-attribute-shell .modal-body{grid-template-columns:50% 50%;min-height:440px}
    .common-attribute-left{border-right:1px solid #dce8ee;border-bottom:0;padding:13px 6px}
    .common-attribute-picked{padding:13px 6px}
    .common-attribute-grid{grid-template-columns:repeat(2,1fr);gap:4px}
    .common-attribute-btn{min-height:58px;font-size:10px;padding:4px}
    .common-attribute-picked li{font-size:10px;padding:8px 3px}
    .common-attribute-qty button{width:22px;height:25px}.common-attribute-qty input{width:25px;height:25px}
    .common-attribute-remove{width:24px;height:25px}
  }
</style>
<div class="modal-content common-attribute-shell" id="common-attribute-root" data-api="<?php echo htmlspecialchars($attribute_modal_api, ENT_QUOTES); ?>" data-action="<?php echo htmlspecialchars($attribute_modal_action, ENT_QUOTES); ?>" data-item-id="<?php echo $attribute_modal_item_id; ?>" data-qty="<?php echo htmlspecialchars((string)$attribute_modal_qty, ENT_QUOTES); ?>" data-existing="<?php echo htmlspecialchars(json_encode($attribute_modal_existing), ENT_QUOTES); ?>">
  <div class="modal-header"><h4 class="modal-title">SELECT SODA BOTTLE ITEMS</h4><div style="font-size:16px;margin-top:4px;">Choose a flavour to view available items and add to your order.</div><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
  <div class="modal-body">
    <div class="common-attribute-left"><h3>Soda Flavours</h3><div class="common-attribute-grid">
      <?php foreach ($attribute_modal_rows as $attribute_modal_row) { ?><button type="button" class="common-attribute-btn" data-attr-id="<?php echo $attribute_modal_row['id']; ?>" data-attr-name="<?php echo htmlspecialchars($attribute_modal_row['name'], ENT_QUOTES); ?>" data-price-change="<?php echo $attribute_modal_row['price_change']; ?>"><?php echo htmlspecialchars($attribute_modal_row['name'], ENT_QUOTES); ?> (<?php echo $attribute_modal_row['price_change'] != 0 ? (($attribute_modal_row['price_change'] > 0 ? '+' : '') . $attribute_modal_row['price_change']) : 'Free'; ?>)</button><?php } ?>
    </div></div><div class="common-attribute-picked"><h3>Selected Items (<span id="common-attribute-count">0</span>)</h3><ul id="common-attribute-picked-list"></ul></div>
  </div>
  <div class="modal-footer common-attribute-actions"><div class="common-attribute-summary">Total Quantity: <b id="common-attribute-required">0</b></div><button type="button" class="common-attribute-cancel" data-dismiss="modal">Cancel</button><button type="button" class="common-attribute-submit" id="common-attribute-submit" disabled>Add to Order</button></div>
</div>
<script>
(function(){
  var root=document.getElementById('common-attribute-root');if(!root||typeof $==='undefined')return;
  var itemId=parseInt(root.getAttribute('data-item-id')||'0',10)||0,selected=[],existing=JSON.parse(root.getAttribute('data-existing')||'{}');
  function esc(v){return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
  function totalQty(){return selected.reduce(function(sum,item){return sum+Number(item.qty||0);},0);}
  function render(){var html='';for(var i=0;i<selected.length;i++)html+='<li><span>'+esc(selected[i].name)+'</span><div class="common-attribute-qty"><button type="button" data-qty-action="minus" data-qty-index="'+i+'">−</button><input type="text" value="'+selected[i].qty+'" data-qty-index="'+i+'"><button type="button" data-qty-action="plus" data-qty-index="'+i+'">+</button></div><button type="button" class="common-attribute-remove" data-remove-index="'+i+'" title="Remove">Remove</button></li>';$('#common-attribute-picked-list').html(html);$('#common-attribute-required').text(totalQty());$('#common-attribute-count').text(selected.length);$('#common-attribute-submit').prop('disabled',totalQty()<1);$('.common-attribute-btn').removeClass('is-selected').each(function(){var id=parseInt($(this).data('attrId'),10);if(selected.some(function(item){return item.id===id;}))$(this).addClass('is-selected');});}
  function addAttribute(button){var id=parseInt($(button).data('attrId'),10)||0,found=selected.find(function(item){return item.id===id;});if(found)found.qty++;else selected.push({id:id,name:$(button).data('attrName')||'',price_change:parseFloat($(button).data('priceChange'))||0,qty:1});render();}
  function submit(){var qty=totalQty(),modifier=0;if(qty<1)return;selected.forEach(function(item){modifier+=Number(item.price_change||0)*Number(item.qty||0);});modifier=modifier/qty;var payload={action:root.getAttribute('data-action')||'add_item',id:itemId,qty:qty,attr_id:selected.length===1?selected[0].id:'',price_modifier:modifier,accompaniment_json:JSON.stringify(selected)};$('#common-attribute-submit').prop('disabled',true);$.post(root.getAttribute('data-api'),payload).done(function(data){if(data&&data.ok){if(typeof kwNotify==='function')kwNotify('Order Updated',data.message);if(data.data&&data.data.cart&&typeof renderCurrentCart==='function')renderCurrentCart(data.data.cart);if(typeof refreshCurrentItemsList==='function')refreshCurrentItemsList();if(typeof load_item_list==='function')load_item_list();$('#modal').modal('hide');$('#load').html('');return;}alert(data&&data.message?data.message:'Failed to add item');$('#common-attribute-submit').prop('disabled',false);}).fail(function(xhr){alert('Error adding item: '+(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Connection error'));$('#common-attribute-submit').prop('disabled',false);});}
  $('#common-attribute-select-all').on('click',function(){$('.common-attribute-btn').each(function(){addAttribute(this);});});
  $(document).off('click.commonAttributeSelect','.common-attribute-btn').on('click.commonAttributeSelect','.common-attribute-btn',function(){addAttribute(this);});
  $(document).off('click.commonAttributeRemove','.common-attribute-remove').on('click.commonAttributeRemove','.common-attribute-remove',function(){var index=parseInt($(this).data('removeIndex'),10);if(index>=0){selected.splice(index,1);render();}});
  $(document).off('click.commonAttributeQty','[data-qty-action]').on('click.commonAttributeQty','[data-qty-action]',function(){var index=parseInt($(this).data('qtyIndex'),10),item=selected[index];if(!item)return;item.qty=Math.max(0,Number(item.qty||0)+($(this).data('qtyAction')==='plus'?1:-1));if(item.qty===0)selected.splice(index,1);render();});
  $(document).off('change.commonAttributeQty','.common-attribute-qty input').on('change.commonAttributeQty','.common-attribute-qty input',function(){var index=parseInt($(this).data('qtyIndex'),10),item=selected[index],value=Math.max(0,parseInt($(this).val(),10)||0);if(!item)return;if(value===0)selected.splice(index,1);else item.qty=value;render();});
  Object.keys(existing).forEach(function(id){var button=$('.common-attribute-btn[data-attr-id="'+id+'"]')[0];if(button){for(var i=0;i<Number(existing[id]||0);i++)addAttribute(button);}});$('#common-attribute-submit').off('click.commonAttributeSubmit').on('click.commonAttributeSubmit',submit);render();
})();
</script>
