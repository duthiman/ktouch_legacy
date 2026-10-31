$(document).ready(function(){
  $.ajaxSetup({ cache: false });
  window.KWEDIA_API_URL = 'api/bill_api.php';
  window.KWEDIA_COMMON_CATALOG_URL = 'api/bill_api.php';
  window.__kwCatalogLoaded = false;
  window.__kwCatalog = { categories: [], subcategories: [], items: [] };
  window.__kwCurrentCategoryId = 0;
  window.__kwCurrentSubcategoryId = 0;
  window.__kwCurrentSearch = '';
  window.kwHandleAutoLogout = function(response) {
    var data = response && response.data ? response.data : {};
    if (!data.auto_logout) return false;
    alert((response && response.message) || 'There is no active shift. Please login again.');
    window.location.replace(data.redirect || '../index.php');
    return true;
  };
  var kwCartMoved = false;
  var kwCartDrag = null;
  function initMovableCart() {
    var cart = document.getElementById('kw-cart-toggle');
    if (!cart) return;
    var saved = null;
    try { saved = JSON.parse(localStorage.getItem('kwedia_cart_position') || 'null'); } catch (e) {}
    if (saved && Number.isFinite(Number(saved.left)) && Number.isFinite(Number(saved.top))) {
      cart.style.left = Math.max(4, Math.min(Number(saved.left), window.innerWidth - cart.offsetWidth - 4)) + 'px';
      cart.style.top = Math.max(4, Math.min(Number(saved.top), window.innerHeight - cart.offsetHeight - 4)) + 'px';
      cart.style.right = 'auto';
      cart.style.bottom = 'auto';
    }
    cart.addEventListener('pointerdown', function (event) {
      if (event.button !== undefined && event.button !== 0) return;
      event.preventDefault();
      kwCartMoved = false;
      kwCartDrag = { id: event.pointerId, x: event.clientX, y: event.clientY, left: cart.getBoundingClientRect().left, top: cart.getBoundingClientRect().top };
      cart.classList.add('kw-cart-dragging');
      if (cart.setPointerCapture) cart.setPointerCapture(event.pointerId);
    });
    cart.addEventListener('pointermove', function (event) {
      if (!kwCartDrag || kwCartDrag.id !== event.pointerId) return;
      event.preventDefault();
      var dx = event.clientX - kwCartDrag.x;
      var dy = event.clientY - kwCartDrag.y;
      if (Math.abs(dx) > 4 || Math.abs(dy) > 4) kwCartMoved = true;
      if (!kwCartMoved) return;
      cart.style.left = Math.max(4, Math.min(kwCartDrag.left + dx, window.innerWidth - cart.offsetWidth - 4)) + 'px';
      cart.style.top = Math.max(4, Math.min(kwCartDrag.top + dy, window.innerHeight - cart.offsetHeight - 4)) + 'px';
      cart.style.right = 'auto';
      cart.style.bottom = 'auto';
    });
    cart.addEventListener('pointerup', function (event) {
      if (!kwCartDrag || kwCartDrag.id !== event.pointerId) return;
      event.preventDefault();
      cart.classList.remove('kw-cart-dragging');
      if (kwCartMoved) {
        try { localStorage.setItem('kwedia_cart_position', JSON.stringify({ left: cart.offsetLeft, top: cart.offsetTop })); } catch (e) {}
      }
      kwCartDrag = null;
    });
    cart.addEventListener('pointercancel', function () {
      cart.classList.remove('kw-cart-dragging');
      kwCartDrag = null;
      kwCartMoved = true;
      setTimeout(function () { kwCartMoved = false; }, 0);
    });
    cart.addEventListener('click', function (event) {
      if (kwCartMoved) {
        event.preventDefault();
        event.stopImmediatePropagation();
        kwCartMoved = false;
      }
    }, true);
  }
  initMovableCart();
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
  fetchPageBootstrap();
  bindSingleTap('.additem', function(){
    var availableQty = Number($(this).attr('data-stock-qty'));
    if (this.disabled || !isFinite(availableQty) || availableQty <= 0) {
      return false;
    }
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
  window.kwOpenCancelOrderModal = function() {
    if (typeof loadKwModal === 'function') {
      loadKwModal('cancel_modal.php', {}, 'POST');
    }
  };
  $(document).on('click','#cancel, .cancel-order-btn', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();
    window.kwOpenCancelOrderModal();
    return false;
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
    if (window.kwHandleAutoLogout(res)) return;
    var payload = res && res.data ? res.data : {};
    if (res && res.ok && payload.redirect) {
      window.location = payload.redirect;
      return;
    }
    alert((res && res.message) ? res.message : 'Unable to send order right now.');
  })
  .fail(function(jqXHR) {
    if (window.kwHandleAutoLogout(jqXHR.responseJSON)) return;
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
    if (window.refreshKwCatalog) window.refreshKwCatalog();
  }).fail(function(jqXHR) {
    if (window.kwHandleAutoLogout(jqXHR.responseJSON)) return;
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
      if (window.refreshKwCatalog) window.refreshKwCatalog();
    }).fail(function(jqXHR) {
        if (window.kwHandleAutoLogout(jqXHR.responseJSON)) return;
        alert('Error adding item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
        });
}
function orderattr(button){
  button = button || (document.activeElement && document.activeElement.classList.contains('deci-order') ? document.activeElement : null);
  if (button && button.disabled) return;
  if (window.setDeciOrderLoading) window.setDeciOrderLoading(button, true);
  var qty = (typeof getFinalQty === 'function') ? getFinalQty() : $("#deci_qty").val();
  var id = $("#item_id").val();
  $.ajax({
      url: 'attr_modal.php',
      typr: 'POST', 
      data: {qty:qty,id:id},
      dataType: 'html'
    })
    .done(function(data){
      if (window.setDeciOrderLoading) window.setDeciOrderLoading(button, false);
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
      if (window.setDeciOrderLoading) window.setDeciOrderLoading(button, false);
      $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
    });
}
function order(button){
  button = button || (document.activeElement && document.activeElement.classList.contains('deci-order') ? document.activeElement : null);
  if (button && button.disabled) return;
  if (window.setDeciOrderLoading) window.setDeciOrderLoading(button, true);
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
    if (window.refreshKwCatalog) window.refreshKwCatalog();
  }).fail(function(jqXHR) {
    if (window.kwHandleAutoLogout(jqXHR.responseJSON)) return;
    alert('Error adding item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
  }).always(function() {
    if (window.setDeciOrderLoading) window.setDeciOrderLoading(button, false);
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
    if (window.refreshKwCatalog) window.refreshKwCatalog();
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
   if (window.__kwCartRequest) {
     return window.__kwCartRequest;
   }
   window.__kwCartRequest = $.getJSON(window.KWEDIA_API_URL, { action: 'get_current_cart' })
   .done(function(res) {
     if (!res || !res.ok) {
       return;
     }
     renderCurrentCart(res.data || {});
   })
   .always(function(){
     window.__kwCartRequest = null;
   });
   return window.__kwCartRequest;
}

function load_sub(id){
   window.__kwCurrentCategoryId = parseInt(id, 10) || 0;
   if (window.__kwCatalogLoaded) {
     renderSubcategories(window.__kwCatalog.subcategories.filter(function(row) {
       return parseInt(row.cat_id || row.category_id || row.category || 0, 10) === window.__kwCurrentCategoryId;
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
    if (data.cart) {
      renderCurrentCart(data.cart);
    }
  });
}
function fetchPageBootstrap() {
  var params = { action: 'page_bootstrap' };
  var query = new URLSearchParams(window.location.search || '');
  if (query.get('sale_id')) params.sale_id = query.get('sale_id');
  if (query.get('sale_counter')) params.sale_counter = query.get('sale_counter');

  $.getJSON(window.KWEDIA_API_URL, params)
    .done(function(res) {
      var data = res && res.data ? res.data : {};
      if (!res || !res.ok) {
        window.location.replace(data.redirect || '../dash/index.php?redir=order_lost');
        return;
      }
      $('#kw-account-name, #kw-account-name-mobile, #kw-cart-account-name, #kw-account-name-hero').text(data.user_name || '');
      $('#kw-counter-name, #kw-counter-name-mobile, #kw-cart-counter-name, #kw-counter-name-hero').text(data.counter_name || '');
      $('#kw-sale-id, #kw-sale-id-mobile, #kw-cart-sale-id, #kw-sale-id-hero').text(data.sale_id || 0);
      window.__kwCounterId = Number(data.counter_id || 0);
      $('.cancel-order-btn, #cancel').toggle(Number(data.status || 0) === 1);
      if (data.cart) renderCurrentCart(data.cart);
      fetchCategories(window.__kwCounterId, false);
    })
    .fail(function(xhr) {
      var data = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : {};
      window.location.replace(data.redirect || '../dash/index.php?redir=order_lost');
    });
}
function fetchCategories(counterId, preserveSelection) {
  var oldCategoryId = window.__kwCurrentCategoryId;
  var oldSubcategoryId = window.__kwCurrentSubcategoryId;
  $.getJSON(window.KWEDIA_COMMON_CATALOG_URL, {
    action: 'fetch_catalog',
    menu: 1,
    counter_id: Number(counterId || window.__kwCounterId || 0)
  }).done(function(res) {
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
      var categoryId = preserveSelection && categories.some(function(row) {
        return parseInt(row.cat_id, 10) === parseInt(oldCategoryId, 10);
      }) ? oldCategoryId : categories[0].cat_id;
      load_sub(categoryId);
      if (preserveSelection && oldSubcategoryId) {
        window.__kwCurrentCategoryId = parseInt(categoryId, 10) || 0;
        window.__kwCurrentSubcategoryId = oldSubcategoryId;
        renderItems(kwCatalogFilteredItems(window.__kwCurrentCategoryId, oldSubcategoryId));
      } else {
        load_food(categoryId);
      }
    } else {
      $('#menu, #menu_mobile, #sub_cat, #sub_cat_mobile').html('');
    }
  });
}
window.refreshKwCatalog = function() {
  fetchCategories(window.__kwCounterId || 0, true);
};
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
    var subId = subcategories[i].sub_id || subcategories[i].subcategory_id || subcategories[i].id || 0;
    html += '<button type="button" onclick="kwLoadSubFood(\'' + subId + '\')" data-sub-id="' + subId + '" class="mobile-sub-btn kw-mobile-cat kw-subcategory-btn">' + escapeHtml(subcategories[i].name || subcategories[i].subcategory_name || '') + '</button>';
  }
  if (!html) {
    html = '<span class="kw-subcategories-empty">No subcategories</span>';
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
    var qty = Number(item.qty);
    var reorder = Number(item.reorder);
    var stockColor = qty <= 0 ? 'red' : (qty <= reorder ? 'orange' : 'green');
    var disabled = qty <= 0 ? 'disabled aria-disabled="true"' : '';
    var click = qty > 0 ? ' onclick="kwOrderItem(\'' + item.item_id + '\',\'' + item.has_attributes + '\')"' : '';
    html += '<button ' + disabled + ' data-stock-qty="' + qty + '" data-id="' + item.item_id + '" data-attri="' + item.has_attributes + '"' + click + ' type="button" class="btn btn-inverse m-r-5 m-b-5 additem stock-' + stockColor + '" title="' + (qty <= 0 ? 'Out of stock' : ('Available: ' + qty)) + '" style="font-family: \'comic sans ms\'; font-weight: bold; color: #FFFFFF !important; font-size: 12px; word-wrap:break-word; width: 80px; white-space:normal; height:80px; background: ' + stockColor + '; border: 2px solid ' + stockColor + '; padding-left: 0px; padding-right: 0px; margin: 5px 5px 5px 5px; border-radius: 10px;">' + escapeHtml(item.name) + '</button>';
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
