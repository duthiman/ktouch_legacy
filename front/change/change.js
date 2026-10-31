var changeState= {
  mode:'item',sales:[],items:[],selected:[],counter:null,sourceMode:null,billId:null,pendingMode:null,search:''
}
;
function changeEscape(v) {
  return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function money(v) {
  return Number(v||0).toLocaleString(undefined, {
    minimumFractionDigits:0,maximumFractionDigits:2
  }
  );
}
function renderBills() {
  var term=String(changeState.search||'').replace(/\D/g,''),rows=(changeState.sales||[]).filter(function(row) {
    return !term||String(row.sale_id).indexOf(term)!==-1;
  }
  ),html='<div class="change-bills-table"><table><thead><tr><th>BILL</th> <th>COUNTER</th><th>AMOUNT</th><th>ACTION</th></tr></thead><tbody>';
  if(!rows.length)html+='<tr><td colspan="5">No sales found for this bill number.</td></tr>';
  rows.forEach(function(row) {
    html+='<tr><td><b>#ORD-'+Number(row.sale_id)+'</b></td><td><span class="change-counter">'+changeEscape(row.counter_name)+'</span></td><td>KSh '+money(row.total)+'</td><td><button class="change-view-btn" type="button" data-sale-id="'+Number(row.sale_id)+'">VIEW</button></td></tr>';
  }
  );
  $('#ordscreen').html(html+'</tbody></table></div>');
}
function renderItems() {
  var groups= {
  }
  ;
  (changeState.items||[]).forEach(function(row) {
    var key=row.counter_id;if(!groups[key])groups[key]= {
      name:row.counter_name,rows:[]
    }
    ;groups[key].rows.push(row);
  }
  );
  var keys=Object.keys(groups);
  if(changeState.counter===null&&keys.length)changeState.counter=keys[0];
  var html='<div class="change-counter-tabs">';
  keys.forEach(function(key) {
    html+='<button type="button" class="change-counter-tab '+(String(changeState.counter)===String(key)?'active':'')+'" data-counter="'+key+'">'+changeEscape(groups[key].name)+'</button>';
  }
  );
  html+='</div><div class="change-bills-table change-items-table"><table><thead><tr><th>ITEM</th><th>QTY</th><th>ACTION</th></tr></thead><tbody>';
  var group=groups[changeState.counter];
  if(!group)html+='<tr><td colspan="3">No items found for this shift.</td></tr>';
  else group.rows.forEach(function(row) {
    html+='<tr><td><b>'+changeEscape(row.item_name)+'</b></td><td><strong>'+money(row.item_count)+'</strong></td><td><button type="button" class="change-item-view-btn" data-item-id="'+Number(row.item_id)+'" data-item-name="'+changeEscape(row.item_name)+'">VIEW</button></td></tr>';
  }
  );
  $('#ordscreen').html(html+'</tbody></table></div>');
}
function renderSelected() {
  var html='';
  var changeTotal=0;
  changeState.selected.forEach(function(row) { changeTotal+=Number(row.change_value||0); });
  if(!changeState.selected.length)html='<div class="change-empty"><div class="change-empty-icon">♧</div><p>Open a bill, then select<br>the sold items you want to change.</p></div>';
  else {
    html='<div class="change-selected-table-wrap"><table class="change-selected-table"><thead><tr><th>ITEM</th><th>QUANTITY</th><th>COUNTER</th><th>ACTION</th></tr></thead><tbody>';
    changeState.selected.forEach(function(row,index) {
      html+='<tr><td><b>'+changeEscape(row.item_name)+'</b><small>#ORD-'+Number(row.sale_id)+'</small></td><td>'+money(row.change_qty)+'</td><td><span class="change-counter">'+changeEscape(row.counter_name||'Counter')+'</span></td><td><button type="button" class="change-remove-selected" data-selected-index="'+index+'">REMOVE</button></td></tr>';
    }
    );
    html+='</tbody></table></div>';
  }
  $('#screen').html(html);
  $('#change_selected_count').text(changeState.selected.length);
  $('.change-hint').html('● &nbsp; '+changeState.selected.length+' ITEMS &nbsp; • &nbsp; Change value KSh '+money(changeTotal));
  $('.change-continue').prop('disabled',changeState.selected.length===0);
}
function showChangeConfirmation() {
  if(!changeState.selected.length)return;
  var changeTotal=0;
  var html='<div class="change-confirm-list"><div class="change-confirm-table-wrap"><table class="change-confirm-table"><thead><tr><th>BILL</th><th>ITEM</th><th>COUNTER</th><th>QUANTITY</th><th>VALUE</th></tr></thead><tbody>';
  changeState.selected.forEach(function(row) {
    changeTotal+=Number(row.change_value||0);
    html+='<tr><td>#ORD-'+Number(row.sale_id)+'</td><td><b>'+changeEscape(row.item_name)+'</b></td><td>'+changeEscape(row.counter_name||'Counter')+'</td><td><strong>'+money(row.change_qty)+'</strong></td><td><strong>KSh '+money(row.change_value)+'</strong></td></tr>';
  }
  );
  html+='</tbody><tfoot><tr><th colspan="4">TOTAL CHANGE VALUE</th><th>KSh '+money(changeTotal)+'</th></tr></tfoot></table></div></div><div class="change-confirm-total"><span>'+changeState.selected.length+' selected '+(changeState.selected.length===1?'item':'items')+'</span><b>Change value KSh '+money(changeTotal)+'</b></div>';
  $('#change_confirm_items').html(html);
  $('#change_confirm_modal').modal('show');
}
function continueWithChangeItems() {
  if(!changeState.selected.length)return;
  var button=$('#change_confirm_next');
  button.prop('disabled',true).text('STARTING CHANGE...');
  $.post('API/change_api.php', {
    action:'prepare_change',source_mode:changeState.sourceMode||changeState.mode,items:JSON.stringify(changeState.selected)
  }
  ,null,'json').done(function(response) {
    if(!response.ok) {
      showChangeNotice('Could not start change',response.message||'Please try again.');return;
    }
    window.location.href=(response.data&&response.data.redirect)||'../kwedia/index.php';
  }
  ).fail(function(xhr) {
    var response=xhr.responseJSON|| {
    }
    ;showChangeNotice('Could not start change',response.message||'Please try again.');
  }
  ).always(function() {
    button.prop('disabled',false).html('CONTINUE TO ITEMS &nbsp; →');
  }
  );
}
function renderBillItems(rows,saleId,title) {
  var html='<div class="change-modal-table-wrap"><table class="change-modal-table"><thead><tr><th>ITEM</th><th>BILL</th><th>ORDERED</th><th>QUANTITY</th><th>ACTION</th></tr></thead><tbody>';
  if(!rows.length)html+='<tr><td colspan="5">No items found.</td></tr>';
  rows.forEach(function(row) {
    var unitPrice=Number(row.price||0)||(Number(row.total||0)/(Number(row.qty)||1));
    var billLabel=changeState.mode==='item'?Number(row.sale_id||saleId):'#ORD-'+Number(row.sale_id||saleId);
    html+='<tr><td><b>'+changeEscape(row.item_name)+'</b></td><td>'+billLabel+'</td><td>'+changeEscape(row.qty)+'</td><td><div class="change-touch-qty"><button type="button" class="change-qty-minus">−</button><input type="number" min="1" max="'+Number(row.qty)+'" step="1" value="'+Number(row.qty)+'" class="change-modal-qty"><button type="button" class="change-qty-plus">+</button></div></td><td><button type="button" class="change-add-item" data-mode="'+changeState.mode+'" data-sale-id="'+Number(row.sale_id||saleId)+'" data-item-id="'+Number(row.item_id)+'" data-item-name="'+changeEscape(row.item_name)+'" data-counter-id="'+Number(row.counter_id||row.stock_counter)+'" data-counter-name="'+changeEscape(row.counter_name)+'" data-item-price="'+unitPrice+'" data-max-qty="'+Number(row.qty)+'">ADD</button></td></tr>';
  }
  );
  html+='</tbody></table></div>';
  $('#change_modal_bill').text(title||('#ORD-'+saleId));
  $('#change_modal_items').html(html);
  $('#change_modal_items .change-add-item').each(function(){
    var button=$(this),index=selectedChangeItemIndex(button);
    button.toggleClass('change-item-added',index>-1).text(index>-1?'REMOVE':'ADD').prop('disabled',false);
  });
}

function selectedChangeItemIndex(button) {
  var saleId=Number(button.data('saleId')),itemId=Number(button.data('itemId'));
  for(var i=0;i<changeState.selected.length;i++) {
    if(Number(changeState.selected[i].sale_id)===saleId&&Number(changeState.selected[i].item_id)===itemId)return i;
  }
  return -1;
}
function showChangeNotice(title,message) {
  $('#change_notice_title').text(title);
  $('#change_notice_message').text(message);
  $('#change_notice_modal').modal('show');
}
window.alert=function(message) {
  showChangeNotice('Selection notice',message);
}
;
function openBillItems(saleId) {
  if(changeState.sourceMode==='bill'&&changeState.billId&&changeState.billId!==saleId) {
    showChangeNotice('One bill at a time','You can only add items from one bill. Remove the selected items before opening another bill.');
    return;
  }
  $('#change_modal_items').html('<div class="change-empty">Loading bill items...</div>');
  $('#change_items_modal').modal('show');
  $.getJSON('API/change_api.php', {
    sale_id:saleId,mode:'bill'
  }
  ).done(function(response) {
    if(!response.ok) {
      $('#change_modal_items').html('<div class="change-empty">'+changeEscape(response.message)+'</div>');return;
    }
    renderBillItems((response.data|| {
    }
    ).order_items||[],saleId,'#ORD-'+saleId);
  }
  ).fail(function() {
    $('#change_modal_items').html('<div class="change-empty">Could not load bill items.</div>');
  }
  );
}
function openItemBills(itemId,itemName,counterId) {
  if(changeState.sourceMode==='bill'&&changeState.selected.length) {
    showChangeNotice('Selection already started','Remove the selected bill items before selecting by item.');
    return;
  }
  $('#change_modal_items').html('<div class="change-empty">Loading item bills...</div>');
  $('#change_items_modal').modal('show');
  $.getJSON('API/change_api.php', {
    item_id:itemId,counter_id:counterId,mode:'item'
  }
  ).done(function(response) {
    if(!response.ok) {
      $('#change_modal_items').html('<div class="change-empty">'+changeEscape(response.message)+'</div>');return;
    }
    renderBillItems((response.data|| {
    }
    ).item_bills||[],0,itemName+' · SELECTED COUNTER');
  }
  ).fail(function() {
    $('#change_modal_items').html('<div class="change-empty">Could not load item bills.</div>');
  }
  );
}
function loadChangePage() {
  $('#ordscreen').html('<div class="change-order-meta">Loading current shift...</div>');
  $.getJSON('API/change_api.php', {
    sale_id:sale_id,mode:changeState.mode
  }
  ).done(function(response) {
    if(!response.ok) {
      $('#ordscreen').html('<div class="change-order-meta">'+changeEscape(response.message)+'</div>');return;
    }
    var data=response.data|| {
    }
    ;changeState.sales=data.sales||[];changeState.items=data.items||[];$('#change_bill_count').text(changeState.sales.length);$('#change_item_count').text(changeState.items.reduce(function(n,row) {
      return n+Number(row.item_count||0);
    }
    ,0));changeState.mode==='item'?renderItems():renderBills();renderSelected();
  }
  ).fail(function() {
    $('#ordscreen').html('<div class="change-order-meta">Could not load current shift sales.</div>');
  }
  );
}
function switchMode(next) {
  changeState.selected=[];
  changeState.sourceMode=null;
  changeState.billId=null;
  changeState.mode=next;
  $('.change-tab').removeClass('active').filter('[data-mode="'+next+'"]').addClass('active');
  $('#change_switch_modal').modal('hide');
  renderSelected();
  loadChangePage();
}
$(function() {
  loadChangePage();$(document).on('click','.change-tab',function() {
    var next=$(this).data('mode')==='item'?'item':'bill';if(changeState.selected.length&&next!==changeState.mode) {
      changeState.pendingMode=next;$('#change_switch_modal').modal('show');return;
    }
    switchMode(next);
  }
  );$(document).on('click','#change_switch_confirm',function() {
    switchMode(changeState.pendingMode||'bill');
  }
  );$(document).on('click','.change-counter-tab',function() {
    changeState.counter=$(this).data('counter');renderItems();
  }
  );$(document).on('click','.change-view-btn',function() {
    openBillItems(Number($(this).data('saleId')));
  }
  );$(document).on('click','.change-item-view-btn',function() {
    openItemBills(Number($(this).data('itemId')),$(this).data('itemName'),Number(changeState.counter));
  }
  );$(document).on('click','.change-qty-minus,.change-qty-plus',function() {
    var input=$(this).siblings('.change-modal-qty'),value=parseFloat(input.val()||0),min=parseFloat(input.attr('min')||1),max=parseFloat(input.attr('max')||999999);value=$(this).hasClass('change-qty-plus')?Math.min(max,value+1):Math.max(min,value-1);input.val(value);
  }
  );$(document).on('click','.change-add-item',function() {
    var button=$(this),selectedIndex=selectedChangeItemIndex(button);
    if(selectedIndex>-1) {
      changeState.selected.splice(selectedIndex,1);
      if(!changeState.selected.length) { changeState.sourceMode=null;changeState.billId=null; }
      renderSelected();
      button.removeClass('change-item-added').text('ADD').prop('disabled',false);
      return;
    }
    var row=button.closest('tr'),qty=parseFloat(row.find('.change-modal-qty').val()||0),max=parseFloat(button.data('maxQty')||0),mode=button.data('mode')||changeState.mode,billId=Number(button.data('saleId')),counterId=Number(button.data('counterId')||0);if(qty<=0||qty>max) {
      alert('Enter a quantity between 1 and '+max+'.');return;
    }
    if(changeState.sourceMode==='bill'&&changeState.billId!==billId) {
      alert('You can only add items from one bill.');return;
    }
    if(changeState.sourceMode==='item'&&changeState.counter!==null&&counterId!==Number(changeState.counter)) {
      alert('This item is not from the selected counter.');return;
    }
    if(!changeState.sourceMode) {
      changeState.sourceMode=mode;changeState.billId=mode==='bill'?billId:null;
    }
    var unitPrice=Number(button.data('itemPrice'))||0;
    changeState.selected.push( {
      sale_id:billId,item_id:Number(button.data('itemId')),item_name:button.data('itemName'),counter_name:button.data('counterName'),change_qty:qty,change_value:unitPrice*qty
    }
    );renderSelected();button.addClass('change-item-added').text('REMOVE').prop('disabled',false);
  }
  );$(document).on('click','.change-remove-selected',function() {
    changeState.selected.splice(Number($(this).data('selectedIndex')),1);if(!changeState.selected.length) {
      changeState.sourceMode=null;changeState.billId=null;
    }
    renderSelected();
  }
  );$('.change-clear').on('click',function() {
    changeState.selected=[];changeState.sourceMode=null;changeState.billId=null;renderSelected();
  }
  );$('.change-continue').on('click',showChangeConfirmation);$('#change_confirm_next').on('click',continueWithChangeItems);
}
);
$(function() {
  function updateSearch(value) {
    changeState.search=String(value||'').replace(/\D/g,'');$('#change_search').val(changeState.search);$('#change_numpad_display').text(changeState.search||'Enter bill number');if(changeState.mode==='bill')renderBills();
  }
  $('#change_search').on('input',function() {
    updateSearch(this.value);
  }
  );
  $('#change_search').on('keydown',function(e) {
    if(!/^[0-9]$/.test(e.key)&&['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'].indexOf(e.key)===-1)e.preventDefault();
  }
  );
  $('#change_search_open').on('click',function() {
    if(window.innerWidth>767) {
      updateSearch($('#change_search').val());$('#change_numpad_modal').modal('show');
    }
    else {
      $('#change_search').trigger('focus');
    }
  }
  );
  $(document).on('click','.change-numpad-grid button',function() {
    var key=$(this).data('num'),value=changeState.search||'';if(key==='clear')value='';else if(key==='back')value=value.slice(0,-1);else value+=String(key);updateSearch(value);
  }
  );
}
);
