$(document).ready(function(){
  $.ajaxSetup({ cache: false });
  window.KWEDIA_API_URL = '../API/kwedia/bill_api.php';
  window.KWEDIA_COMMON_CATALOG_URL = '../API/common/api.php';
  window.__kwCatalogLoaded = false;
  window.__kwCatalog = { categories: [], subcategories: [], items: [] };
  window.__kwCurrentCategoryId = 0;
  window.__kwCurrentSubcategoryId = 0;
  window.__kwCurrentSearch = '';
  var kwEventNs = '.kwTap';
  function showKwModalHtml(data) {
    $('#load').html('');
    $('#load').html(data);
    $('#modal').modal('show');
    setTimeout(function(){
      var modalRoot = $('#modal');
      if (!modalRoot.length) {
        return;
      }
      var $focusField = modalRoot.find('input:focus, textarea:focus, select:focus');
      if ($focusField.length) {
        $focusField.blur();
      }
      if (document.activeElement && modalRoot.has(document.activeElement).length) {
        try {
          document.activeElement.blur();
        } catch (e) {}
      }
    }, 30);
  }
  function showKwModalError(message) {
    showKwModalHtml('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">' + message + '</div></div>');
  }
  function loadKwModal(url, data, method, loadingHtml) {
    if (loadingHtml) {
      showKwModalHtml(loadingHtml);
    }
    $.ajax({
      url: url,
      type: method || 'POST',
      data: data || {},
      dataType: 'html'
    })
    .done(function(data){
      showKwModalHtml(data);
    })
    .fail(function() {
      showKwModalError('Something went wrong, Please try again...');
    });
  }
  window.loadKwModal = loadKwModal;
  function bindSingleTap(selector, handler) {
    var tapLimit = 12;
    var clickSuppressMs = 700;
    var supportsPointer = !!window.PointerEvent;

    $(document).off('pointerdown' + kwEventNs, selector);
    $(document).off('pointermove' + kwEventNs, selector);
    $(document).off('pointerup' + kwEventNs, selector);
    $(document).off('click' + kwEventNs, selector);

    if (supportsPointer) {
      $(document).on('pointerdown' + kwEventNs, selector, function(event){
        this.__kwTapStartX = event.clientX;
        this.__kwTapStartY = event.clientY;
        this.__kwTapMoved = false;
      });

      $(document).on('pointermove' + kwEventNs, selector, function(event){
        if (typeof this.__kwTapStartX === 'undefined') {
          return;
        }
        if (Math.abs(event.clientX - this.__kwTapStartX) > tapLimit || Math.abs(event.clientY - this.__kwTapStartY) > tapLimit) {
          this.__kwTapMoved = true;
        }
      });

      $(document).on('pointerup' + kwEventNs, selector, function(event){
        if (this.__kwTapMoved) {
          return;
        }
        if (this.__kwTapLock && (Date.now() - this.__kwTapLock) < clickSuppressMs) {
          event.preventDefault();
          return;
        }
        this.__kwTapLock = Date.now();
        event.preventDefault();
        event.stopPropagation();
        handler.call(this, event);
      });
      return;
    }

    $(document).on('click' + kwEventNs, selector, function(event){
      if (this.__kwTapLock && (Date.now() - this.__kwTapLock) < clickSuppressMs) {
        event.preventDefault();
        return;
      }
      this.__kwTapLock = Date.now();
      event.preventDefault();
      event.stopPropagation();
      handler.call(this, event);
    });
  }

  initDeciMode();
  fetchOrderDetails();
  fetchCategories();
  load_item_list();
  bindSingleTap('.additem', function(){
    var id = $(this).data('id');
    var attri = parseInt($(this).data('attri'), 10) || 0;
    if (!id) {
      return false;
    }
    if (attri === 1) {
      openAttrItem(id);
      return false;
    }
    if (isDeciModalMode()) {
      openDeciItem(id);
      return;
    }
    recp(id);
  });
  bindSingleTap('.mobile-cat-btn', function(){
    var id = $(this).data('catId') || $(this).data('cat-id');
    load_sub(id);
    load_food(id);
  });
  bindSingleTap('.mobile-sub-btn', function(){
    var id = $(this).data('subId') || $(this).data('sub-id');
    load_sub_food(id);
  });
  $(document).on('click','#cancel', function(e){   
    e.preventDefault();
    loadKwModal('cancel_modal.php', {}, 'POST');
  });
  $(document).on('click','.cancel-order-btn', function(e){
    e.preventDefault();
    loadKwModal('cancel_modal.php', {}, 'POST');
  });
  $(document).on('click','#kw-cart-toggle', function(){
    $('#kw-cart-drawer').toggleClass('open');
  });
  $(document).on('click','#kw-cart-close', function(){
    $('#kw-cart-drawer').removeClass('open');
  });
  $(document).on('change','.deci-mode-switch', function(){
    setDeciMode($(this).is(':checked'));
  });
  $(document).on('click', '#kw-confirm-cancel-sale', function(e){
    e.preventDefault();
    cancelCurrentSale();
  });
});
function initDeciMode() {
  var saved = localStorage.getItem('kwedia_deci_modal_mode');
  var enabled = saved === null ? true : saved === '1';
  setDeciMode(enabled);
}
function setDeciMode(enabled) {
  localStorage.setItem('kwedia_deci_modal_mode', enabled ? '1' : '0');
  $('.deci-mode-switch').prop('checked', enabled);
  $('#deci_mode_text_desktop, #deci_mode_text_mobile').text(enabled ? 'Modal Mode' : 'Touch Mode');
  applyDeciModeToButtons();
}
function kwNotify(title, message) {
  if ($.gritter && typeof $.gritter.add === 'function') {
    $.gritter.add({ title: title, text: message, time: 2000 });
    return;
  }
  if (window.console && typeof window.console.log === 'function') {
    window.console.log((title || 'Notice') + ': ' + (message || ''));
  }
}
function getActiveDeciSwitch() {
  if ($('.mobile-shell').is(':visible')) {
    return $('#deci_mode_mobile');
  }
  return $('#deci_mode_desktop');
}
function isDeciModalMode() {
  var activeSwitch = getActiveDeciSwitch();
  if (activeSwitch.length) {
    return activeSwitch.is(':checked');
  }
  return localStorage.getItem('kwedia_deci_modal_mode') !== '0';
}
function applyDeciModeToButtons() {
  var modalMode = isDeciModalMode();
  $('.additem').toggleClass('deci-touch-mode', !modalMode);
}
window.openAttrItem = function(id) {
  loadKwModal('attr_modal.php', {id:id,qty:1}, 'POST');
};
window.openDeciItem = function(id) {
  if (!isDeciModalMode()) {
    recp(id);
    return false;
  }
  loadKwModal(
    'deci_modal.php',
    {id:id},
    'POST',
    '<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Loading...</div></div>'
  );
  return false;
};
window.complete = function() {   
  amount=$('#tt').text();
  if (amount>0) {
    loadKwModal('submit_confirm.php', {}, 'POST');
  }
};
window.openBillCounterModal = function(openState, openMode, billMode) {
  var bill_cntr = $('#bill_cntr_select').val() || 0;
  loadKwModal(
    'bill_counter_modal.php',
    {
      open_state: openState || '',
      open_mode: openMode || '',
      bill_mode: billMode || '',
      bill_cntr: bill_cntr,
      final_verdict: 'open'
    },
    'POST',
    '<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Loading counters...</div></div>'
  );
};
window.openCloseCurrentCounterModal = function() {
  var bill_cntr = $('#bill_cntr_select').val() || 0;
  loadKwModal(
    'bill_counter_modal.php',
    {close_current_bill: 1, bill_cntr: bill_cntr, final_verdict: 'close'},
    'POST',
    '<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Loading counters...</div></div>'
  );
};
window.openCloseParentCounterModal = function(parentSaleId, printFinal, openMode, billMode) {
  var bill_cntr = $('#bill_cntr_select').val() || 0;
  loadKwModal(
    'bill_counter_modal.php',
    {
      close_parent_sale_id: parentSaleId || 0,
      print_final: printFinal ? 1 : 0,
      open_mode: openMode || '',
      bill_mode: billMode || '',
      bill_cntr: bill_cntr,
      final_verdict: 'close'
    },
    'POST',
    '<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Loading counters...</div></div>'
  );
};
window.openOpenBillModeModal = function() {
  loadKwModal(
    'open_bill_mode_modal.php',
    {},
    'POST',
    '<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Loading open bill options...</div></div>'
  );
};
function submitPlaceSale(params) {
  params = params || {};
  $.post(window.KWEDIA_API_URL, $.extend({ action: params.action || 'open_order' }, params))
  .done(function(res) {
    var payload = res && res.data ? res.data : {};
    if (res && res.ok && payload.redirect) {
      window.location = payload.redirect;
      return;
    }
    alert((res && res.message) ? res.message : 'Unable to send order right now.');
  })
  .fail(function(jqXHR) {
    var message = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Unable to send order right now.';
    alert(message);
  });
}
window.confirmSubmitBill = function(openState, counterId, openMode, billMode, identifierData) {
  var bill_cntr = counterId || $('#bill_cntr_select').val() || 0;
  submitPlaceSale({
    action: 'open_order',
    bill_cntr: bill_cntr,
    open_state: openState || '',
    open_mode: openMode || '',
    bill_mode: billMode || '',
    identifier_type: identifierData && identifierData.type ? identifierData.type : '',
    identifier_value: identifierData && identifierData.value ? identifierData.value : '',
    identifier_label: identifierData && identifierData.label ? identifierData.label : '',
    identifier_hex: identifierData && identifierData.hex ? identifierData.hex : '',
    save_preset: identifierData && identifierData.save_preset ? 1 : 0
  });
};
window.confirmDirectSubmitBill = function(openMode, billMode, openState, identifierData) {
  submitPlaceSale({
    action: 'open_order',
    open_state: openState || '',
    open_mode: openMode || '',
    bill_mode: billMode || '',
    identifier_type: identifierData && identifierData.type ? identifierData.type : '',
    identifier_value: identifierData && identifierData.value ? identifierData.value : '',
    identifier_label: identifierData && identifierData.label ? identifierData.label : '',
    identifier_hex: identifierData && identifierData.hex ? identifierData.hex : '',
    save_preset: identifierData && identifierData.save_preset ? 1 : 0
  });
};
window.confirmCloseOpenOrder = function(parentSaleId, printFinal, billCounterId) {
  parentSaleId = parseInt(parentSaleId, 10) || 0;
  if (parentSaleId < 1) {
    alert('Invalid bill selected.');
    return;
  }
  billCounterId = parseInt(billCounterId, 10) || parseInt($('#bill_cntr_select').val(), 10) || 0;
  $.ajax({
    url: window.KWEDIA_API_URL,
    type: 'POST',
    data: {
      action: 'close_order',
      close_parent_sale_id: parentSaleId,
      print_final: printFinal ? 1 : 0,
      bill_cntr: billCounterId,
      open_mode: 'open',
      bill_mode: printFinal ? 'last' : 'each'
    },
    dataType: 'json'
  }).done(function(res) {
    var payload = res && res.data ? res.data : {};
    if (!res || !res.ok) {
      alert(res && res.message ? res.message : 'Unable to close this bill right now.');
      return;
    }
    alert(res.message || ('Bill #' + parentSaleId + ' was closed successfully.'));
    window.location = payload.redirect || '../log_out.php';
  }).fail(function() {
    alert('Unable to close this bill right now.');
  });
};
window.confirmCloseCurrentOrder = function(billCounterId) {
  billCounterId = parseInt(billCounterId, 10) || parseInt($('#bill_cntr_select').val(), 10) || 0;
  var activeSaleId = parseInt($('#kw-sale-id').text(), 10) || parseInt($('#kw-sale-id-mobile').text(), 10) || parseInt($('#kw-cart-sale-id').text(), 10) || 0;
  $.ajax({
    url: window.KWEDIA_API_URL,
    type: 'POST',
    data: {
      action: 'close_order',
      sale_id: activeSaleId,
      bill_cntr: billCounterId,
      close_current_bill: 1,
      final_verdict: 'close'
    },
    dataType: 'json'
  }).done(function(res) {
    var payload = res && res.data ? res.data : {};
    if (!res || !res.ok) {
      alert(res && res.message ? res.message : 'Unable to close this bill right now.');
      return;
    }
    alert(res.message || 'Bill closed successfully.');
    window.location = payload.redirect || '../log_out.php';
  }).fail(function() {
    alert('Unable to close this bill right now.');
  });
};
$(document).on('click', '.kw-counter-btn[data-bill-counter]', function(){
  var counterId = $(this).data('billCounter');
  $('#bill_cntr_select').val(counterId);
  $('.kw-counter-btn[data-bill-counter]').removeClass('active');
  $(this).addClass('active');
});
$(document).on('click', '.kw-bill-counter-btn[data-bill-counter]', function(){
  var counterId = $(this).data('billCounter');
  $('#bill_cntr_select').val(counterId);
  $('.kw-bill-counter-btn[data-bill-counter]').removeClass('active');
  $(this).addClass('active');
});
function recp(id) {
  if (window.__kwAddLock && window.__kwAddLock[id]) {
    return;
  }
  window.__kwAddLock = window.__kwAddLock || {};
  window.__kwAddLock[id] = true; // Lock before AJAX call
  var qty = 1;
  $.post(window.KWEDIA_API_URL, {
    action: 'add_item',
    id: id,
    qty: qty
  }).done(function(data) {
    if (data.ok) {
      kwNotify('Order Updated', data.message);
      if (data.data && data.data.cart) {
        renderCurrentCart(data.data.cart);
      }
      refreshCurrentItemsList();
    } else {
      alert(data.message || 'Failed to add item');
    }
    load_item_list();
  }).fail(function(jqXHR) {
    alert('Error adding item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
  }).always(function() {
      setTimeout(function(){
        if (window.__kwAddLock) {
          delete window.__kwAddLock[id];
        }
      }, 450);
    });
}
function orderWithAttribute(item_id, attr_id, price_modifier,qty) {
    //var qty = 1;
    $.post(window.KWEDIA_API_URL, {
      action: 'add_item',
      id: item_id,
      qty: qty,
      attr_id: attr_id,
      price_modifier: price_modifier
    }).done(function(data) {
      if (data.ok) {
        kwNotify('Order Updated', data.message);
        if (data.data && data.data.cart) {
          renderCurrentCart(data.data.cart);
        }
        refreshCurrentItemsList();
        document.getElementById('close').click();
      } else {
        alert(data.message || 'Failed to add item');
      }
      load_item_list();
    }).fail(function(jqXHR) {
        alert('Error adding item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
        });
}
function orderattr(){
  var qty = (typeof getFinalQty === 'function') ? getFinalQty() : $("#deci_qty").val();
  var id = $("#item_id").val();
  $.ajax({
      url: 'attr_modal.php',
      typr: 'POST', 
      data: {qty:qty,id:id},
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(''); 
      $('#load').html(data);
      setTimeout(function(){
        var modalRoot = $('#modal');
        if (modalRoot.length) {
          modalRoot.modal('show');
        }
      }, 0);
    })
    .fail(function() {
      $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
    });
}
function order(){
  var qty = (typeof getFinalQty === 'function') ? getFinalQty() : $("#deci_qty").val();
  var id = $("#item_id").val();
  $.post(window.KWEDIA_API_URL, {
    action: 'add_item',
    id: id,
    qty: qty
  }).done(function(data) {
    if (data.ok) {
      kwNotify('Order Updated', data.message);
      if (data.data && data.data.cart) {
        renderCurrentCart(data.data.cart);
      }
      refreshCurrentItemsList();
      document.getElementById('close').click();
    } else {
      alert(data.message || 'Failed to add item');
    }
    load_item_list();
  }).fail(function(jqXHR) {
    alert('Error adding item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
  });
}

// function del_item(item_id,hhr){ 
//     $.get("del_item.php", { item_id: item_id,hhr: hhr}, 
//    function(data) {  
//     load_item_list();
//    });
// }
function del_item(item_id, hhr, attr_id, accompaniment_json) {
  var accompanimentPayload = accompaniment_json ? decodeURIComponent(accompaniment_json) : '';
  $.post(window.KWEDIA_API_URL, {
    action: 'remove_item',
    item_id: item_id,
    hhr: hhr,
    attr_id: attr_id,
    accompaniment_json: accompanimentPayload
  }).done(function(data) {
    if (data.ok) {
      kwNotify('Item Removed', data.message);
      refreshCurrentItemsList();
    } else {
      alert(data.message || 'Failed to remove item');
    }
    load_item_list();
  }).fail(function(jqXHR) {
    alert('Error removing item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
  });
}
function cancelCurrentSale() {
  $.post(window.KWEDIA_API_URL, {
    action: 'cancel_order'
  }).done(function(data) {
    if (data.ok) {
      kwNotify('Sale Cancelled', data.message);
      $('#modal').modal('hide');
      $('#load').html('');
      $('#screen, #screen_mobile').html('');
      $('#kw-cart-count').text('0');
      $('#kw-cart-total').text('0');
      window.location = '../dash/dash.php?redir=order_cancelled';
      return;
    }
    alert(data.message || 'Failed to cancel sale');
  }).fail(function(jqXHR) {
    alert('Error cancelling sale: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
  });
}
function load_item_list(){
   $.getJSON(window.KWEDIA_API_URL, { action: 'get_current_cart' })
   .done(function(res) {
     if (!res || !res.ok) {
       return;
     }
     renderCurrentCart(res.data || {});
   }); 
}

function load_sub(id){
   window.__kwCurrentCategoryId = parseInt(id, 10) || 0;
   if (window.__kwCatalogLoaded) {
     renderSubcategories(window.__kwCatalog.subcategories.filter(function(row) {
       return parseInt(row.cat_id, 10) === window.__kwCurrentCategoryId;
     }));
     return;
   }
   $.getJSON(window.KWEDIA_API_URL, { action: 'get_subcategories', category_id: window.__kwCurrentCategoryId })
   .done(function(res) {
     if (!res || !res.ok) {
       return;
     }
     renderSubcategories((res.data && res.data.subcategories) || []);
   }); 
}
function load_food(id){
  window.__kwCurrentCategoryId = parseInt(id, 10) || 0;
  window.__kwCurrentSubcategoryId = 0;
  if (window.__kwCatalogLoaded) {
    renderItems(kwCatalogFilteredItems(window.__kwCurrentCategoryId, 0));
    return;
  }
  $.getJSON(window.KWEDIA_API_URL, {
    action: 'get_items',
    category_id: window.__kwCurrentCategoryId,
    search: window.__kwCurrentSearch || ''
  }).done(function(res) {
    if (!res || !res.ok) {
      return;
    }
    renderItems((res.data && res.data.items) || []);
  });
}
function load_sub_food(id){
  window.__kwCurrentSubcategoryId = parseInt(id, 10) || 0;
  if (window.__kwCatalogLoaded) {
    renderItems(kwCatalogFilteredItems(window.__kwCurrentCategoryId, window.__kwCurrentSubcategoryId));
    return;
  }
  $.getJSON(window.KWEDIA_API_URL, {
    action: 'get_items',
    category_id: window.__kwCurrentCategoryId || 0,
    subcategory_id: window.__kwCurrentSubcategoryId,
    search: window.__kwCurrentSearch || ''
  }).done(function(res) {
    if (!res || !res.ok) {
      return;
    }
    renderItems((res.data && res.data.items) || []);
  });
}
function refreshCurrentItemsList() {
  if (window.__kwCurrentSubcategoryId) {
    load_sub_food(window.__kwCurrentSubcategoryId);
    return;
  }
  if (window.__kwCurrentCategoryId) {
    load_food(window.__kwCurrentCategoryId);
  }
}
function syncMobileCart() {
  var rows = $('#screen_mobile tbody tr').filter(function(){
    return $(this).find('a').length > 0;
  }).length;
  var total = $('#screen_mobile #tt').first().text();
  $('#kw-cart-count').text(rows);
  $('#kw-cart-total').text(total ? total : '0');
}
function fetchOrderDetails() {
  $.getJSON(window.KWEDIA_API_URL, { action: 'get_order_details' }).done(function(res) {
    if (!res || !res.ok || !res.data) {
      return;
    }
    var data = res.data;
    $('#kw-account-name, #kw-account-name-mobile, #kw-cart-account-name, #kw-account-name-hero').text(data.user_name || '');
    $('#kw-counter-name, #kw-counter-name-mobile, #kw-cart-counter-name, #kw-counter-name-hero').text(data.counter_name || '');
    $('#kw-sale-id, #kw-sale-id-mobile, #kw-cart-sale-id, #kw-sale-id-hero').text(data.sale_id || 0);
  });
}
function fetchCategories() {
  $.getJSON(window.KWEDIA_COMMON_CATALOG_URL, { action: 'fetch_catalog', menu: 1 }).done(function(res) {
    if (!res || !res.ok || !res.data) {
      return;
    }
    var categories = res.data.categories || [];
    window.__kwCatalog.categories = categories;
    window.__kwCatalog.subcategories = res.data.subcategories || [];
    window.__kwCatalog.items = res.data.items || [];
    window.__kwCatalogLoaded = true;
    renderCategories(categories);
    if (categories.length) {
      load_sub(categories[0].cat_id);
      load_food(categories[0].cat_id);
    } else {
      $('#menu, #menu_mobile, #sub_cat, #sub_cat_mobile').html('');
    }
  });
}
function kwCatalogFilteredItems(categoryId, subcategoryId) {
  var search = String(window.__kwCurrentSearch || '').toLowerCase().trim();
  return (window.__kwCatalog.items || []).filter(function(item) {
    var matchesCategory = !categoryId || parseInt(item.cat_id, 10) === parseInt(categoryId, 10);
    var matchesSubcategory = !subcategoryId || parseInt(item.sub_id, 10) === parseInt(subcategoryId, 10);
    var matchesSearch = !search || String(item.name || '').toLowerCase().indexOf(search) !== -1;
    return matchesCategory && matchesSubcategory && matchesSearch;
  });
}
function renderCategories(categories) {
  var desktopHtml = '';
  var mobileHtml = '';
  for (var i = 0; i < categories.length; i++) {
    desktopHtml += '<button type="button" onclick="kwLoadCategory(\'' + categories[i].cat_id + '\');" class="btn_nav">' + escapeHtml(categories[i].name) + '</button>';
    mobileHtml += '<button onclick="kwLoadCategory(\'' + categories[i].cat_id + '\');" class="kw-mobile-cat mobile-cat-btn" data-cat-id="' + categories[i].cat_id + '" type="button">' + escapeHtml(categories[i].name) + '</button>';
  }
  $('#kw-categories-desktop').html(desktopHtml);
  $('#kw-categories-mobile').html(mobileHtml);
}
function renderSubcategories(subcategories) {
  var html = '';
  for (var i = 0; i < subcategories.length; i++) {
    html += '<button type="button" onclick="kwLoadSubFood(\'' + subcategories[i].sub_id + '\')" data-sub-id="' + subcategories[i].sub_id + '" class="mobile-sub-btn kw-mobile-cat">' + escapeHtml(subcategories[i].name) + '</button>';
  }
  $('#sub_cat').html(html);
  $('#sub_cat_mobile').html(html);
}
function renderItems(items) {
  var html = '';
  var sortedItems = (items || []).slice().sort(function(a, b) {
    return String(a.name || '').localeCompare(String(b.name || ''), undefined, {
      numeric: true,
      sensitivity: 'base'
    });
  });
  for (var i = 0; i < sortedItems.length; i++) {
    var item = sortedItems[i];
    var disabled = Number(item.qty) <= 0 ? 'disabled' : '';
    html += '<button ' + disabled + ' data-id="' + item.item_id + '" data-attri="' + item.has_attributes + '" onclick="kwOrderItem(\'' + item.item_id + '\',\'' + item.has_attributes + '\')" type="button" class="btn btn-inverse m-r-5 m-b-5 additem" style="font-family: \'comic sans ms\'; font-weight: bold; color: #FFFFFF !important; font-size: 12px; word-wrap:break-word; width: 80px; white-space:normal; height:80px; background: ' + item.stock_color + '; border: 2px solid ' + item.stock_color + '; padding-left: 0px; padding-right: 0px; margin: 5px 5px 5px 5px; border-radius: 10px;">' + escapeHtml(item.name) + '</button>';
  }
  $('#menu').html(html);
  $('#menu_mobile').html(html);
  applyDeciModeToButtons();
}
function renderCurrentCart(cart) {
  var rows = cart.rows || cart.items || [];
  var html = '<table class="table table-bordered"><thead class="thead-info"><tr><th>ITEM</th><th>QTY</th><th>PRICE</th><th>ACTION</th></tr></thead><tbody>';
  var hasHappy = false;
  var i;

  for (i = 0; i < rows.length; i++) {
    if (rows[i].bucket === 'happy_hour') {
      hasHappy = true;
      continue;
    }

    var itemName = rows[i].item_name || rows[i].name || '';
    var removeAccompaniment = encodeURIComponent(rows[i].accompaniment_json || '');

    html += '<tr style="color: #373a3c;">'
      + '<td>' + escapeHtml(itemName) + '</td>'
      + '<td>' + rows[i].qty + '</td>'
      + '<td>' + rows[i].total + '</td>'
      + '<td><a onclick="del_item(\'' + rows[i].item_id + '\',\'1\',\'' + (rows[i].attr_id || '') + '\',\'' + removeAccompaniment + '\')" class="btn btn-outline-danger btn-sm text-red">Remove</a></td>'
      + '</tr>';
  }

  if (hasHappy) {
    html += '<tr><td colspan="4" class="text-center"><h5><b>HAPPY HOUR ORDER</b></h5></td></tr>';

    for (i = 0; i < rows.length; i++) {
      if (rows[i].bucket !== 'happy_hour') continue;

      var happyName = rows[i].item_name || rows[i].name || '';
      var happyAccompaniment = encodeURIComponent(rows[i].accompaniment_json || '');

      html += '<tr style="color: #373a3c;">'
        + '<td>' + escapeHtml(happyName) + '</td>'
        + '<td>' + rows[i].qty + '</td>'
        + '<td>' + rows[i].total + '</td>'
        + '<td><a onclick="del_item(\'' + rows[i].item_id + '\',\'2\',\'' + (rows[i].attr_id || '') + '\',\'' + happyAccompaniment + '\')" class="btn btn-danger btn-xs">DEL</a></td>'
        + '</tr>';
    }
  }

  html += '<tfoot class="table-inverse"><tr style="color: #373a3c;"><th colspan="2" class="text-center">TOTAL</th><th colspan="2" id="tt">' + (cart.total || 0) + '</th></tr></tfoot>';
  html += '</tbody></table>';

  $('#screen').html(html);
  $('#screen_mobile').html(html);
  syncMobileCart();
}
function escapeHtml(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
function closeord(){
  submitPlaceSale({ action: 'close_order' });
}
function retord(){
  window.location = '../ret/ret_place.php';
}
