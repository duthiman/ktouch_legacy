function kwSetHtmlTargets(selector, html) {
  var targets = document.querySelectorAll(selector);
  for (var i = 0; i < targets.length; i++) {
    targets[i].innerHTML = html;
  }
}

function kwApiUrl() {
  return 'api/bill_api.php';
}

function kwEscapeHtml(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function kwFetchJson(url, params, done) {
  var query = new URLSearchParams(params || {}).toString();
  fetch(url + (query ? ('?' + query) : ''), {
    credentials: 'same-origin'
  })
    .then(function(response) { return response.json(); })
    .then(done);
}

function kwRenderSubcategoryButtons(subcategories) {
  var html = '';
  for (var i = 0; i < subcategories.length; i++) {
    html += '<button type="button" onclick="kwLoadSubFood(\'' + subcategories[i].sub_id + '\')" data-sub-id="' + subcategories[i].sub_id + '" class="mobile-sub-btn kw-mobile-cat">' + kwEscapeHtml(subcategories[i].name) + '</button>';
  }
  return html;
}

function kwRenderFoodButtons(items) {
  var html = '';
  for (var i = 0; i < items.length; i++) {
    var item = items[i];
    var disabled = Number(item.qty) <= 0 ? 'disabled' : '';
    html += '<button ' + disabled + ' data-id="' + item.item_id + '" data-attri="' + item.has_attributes + '" onclick="kwOrderItem(\'' + item.item_id + '\',\'' + item.has_attributes + '\')" type="button" class="btn btn-inverse m-r-5 m-b-5 additem" style="font-family: \'comic sans ms\'; font-weight: bold; color: #FFFFFF !important; font-size: 12px; word-wrap:break-word; width: 80px; white-space:normal; height:80px; background: ' + item.stock_color + '; border: 2px solid ' + item.stock_color + '; padding-left: 0px; padding-right: 0px; margin: 5px 5px 5px 5px; border-radius: 10px;">' + kwEscapeHtml(item.name) + '</button>';
  }
  return html;
}

function kwFetchInto(url, params, done) {
  if (window.jQuery && typeof jQuery.get === 'function') {
    jQuery.get(url, params, function(data) {
      done(data);
    });
    return;
  }
  var query = new URLSearchParams(params || {}).toString();
  fetch(url + (query ? ('?' + query) : ''), { credentials: 'same-origin' })
    .then(function(response) { return response.text(); })
    .then(done);
}

function kwLoadModalWithJquery(url, params, method, loadingHtml) {
  if (!(window.jQuery && typeof jQuery.ajax === 'function')) {
    return false;
  }
  function showModalShell() {
    var $modal = jQuery('#modal');
    if (!$modal.length) {
      return;
    }
    $modal.addClass('kw-jq-visible show').css('display', 'block').attr('aria-modal', 'true').removeAttr('aria-hidden');
    jQuery('body').addClass('modal-open');
    if (!jQuery('.modal-backdrop').length) {
      jQuery('<div class="modal-backdrop fade show"></div>').appendTo('body');
    } else {
      jQuery('.modal-backdrop').addClass('show').show();
    }
  }
  if (loadingHtml) {
    jQuery('#load').html(loadingHtml);
    showModalShell();
  }
  jQuery.ajax({
    url: url,
    type: method || 'POST',
    data: params || {},
    dataType: 'html'
  })
  .done(function(data) {
    jQuery('#load').html(data);
    showModalShell();
  })
  .fail(function() {
    jQuery('#load').html('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Something went wrong, Please try again...</div></div>');
    showModalShell();
  });
  return true;
}

function kwLoadSubFallback(id) {
  kwFetchJson(kwApiUrl(), { action: 'get_subcategories', category_id: id }, function(res) {
    var data = kwRenderSubcategoryButtons((res && res.data && res.data.subcategories) || []);
    kwSetHtmlTargets('#sub_cat, #sub_cat_mobile', data);
    var buttons = document.querySelectorAll('#sub_cat_mobile button');
    for (var i = 0; i < buttons.length; i++) {
      buttons[i].classList.add('kw-mobile-cat', 'mobile-sub-btn');
      buttons[i].setAttribute('type', 'button');
    }
  });
}

function kwLoadFoodFallback(id) {
  kwFetchJson(kwApiUrl(), { action: 'get_items', category_id: id }, function(res) {
    var data = kwRenderFoodButtons((res && res.data && res.data.items) || []);
    kwSetHtmlTargets('#menu, #menu_mobile', data);
    if (typeof applyDeciModeToButtons === 'function') {
      applyDeciModeToButtons();
    }
  });
}

function kwLoadCategory(id) {
  if (typeof load_sub === 'function') {
    load_sub(id);
  } else {
    kwLoadSubFallback(id);
  }
  if (typeof load_food === 'function') {
    load_food(id);
  } else {
    kwLoadFoodFallback(id);
  }
}

function kwLoadSubFoodFallback(id) {
  kwFetchJson(kwApiUrl(), { action: 'get_items', subcategory_id: id }, function(res) {
    var data = kwRenderFoodButtons((res && res.data && res.data.items) || []);
    kwSetHtmlTargets('#menu, #menu_mobile', data);
    if (typeof applyDeciModeToButtons === 'function') {
      applyDeciModeToButtons();
    }
  });
}

function kwLoadSubFood(id) {
  if (typeof load_sub_food === 'function') {
    load_sub_food(id);
  } else {
    kwLoadSubFoodFallback(id);
  }
}

function kwRefreshSaleItems() {
  if (typeof load_item_list === 'function') {
    load_item_list();
    return;
  }
  kwFetchInto('sale_items.php', {}, function(data) {
    kwSetHtmlTargets('#screen, #screen_mobile', data);
  });
}

function kwAddSaleItem(id) {
  kwFetchInto('sale_action.php', { id: id, qty: 1 }, function() {
    kwRefreshSaleItems();
  });
}

function kwCurrentSaleTotal() {
  var totalNode = document.getElementById('tt');
  if (!totalNode) {
    return 0;
  }
  var value = parseFloat((totalNode.textContent || totalNode.innerText || '0').replace(/,/g, ''));
  return isNaN(value) ? 0 : value;
}

function kwOrderItem(id, hasAttr) {
  if (typeof openAttrItem === 'function' && typeof openDeciItem === 'function' && typeof recp === 'function' && typeof isDeciModalMode === 'function') {
    if (parseInt(hasAttr, 10) === 1) {
      openAttrItem(id);
      return;
    }
    if (isDeciModalMode()) {
      openDeciItem(id);
      return;
    }
    recp(id);
    return;
  }
  if (parseInt(hasAttr, 10) === 1) {
    if (typeof loadKwModal === 'function') {
      loadKwModal('attr_modal.php', {id:id,qty:1}, 'POST');
    }
    return;
  }
  kwAddSaleItem(id);
}

window.complete = function() {
  if (kwCurrentSaleTotal() <= 0) {
    return false;
  }
  if (!kwLoadModalWithJquery('submit_confirm.php', {}, 'POST')) {
    kwFetchInto('submit_confirm.php', {}, function(data) {
      kwSetHtmlTargets('#load', data);
    });
  }
  return false;
};

window.confirmSubmitBill = function(openState, counterId, openMode, billMode, identifierData) {
  var billCounterInput = document.getElementById('bill_cntr_select');
  var billCounter = counterId || (billCounterInput ? (billCounterInput.value || 0) : 0);
  var params = new URLSearchParams();
  params.append('action', 'open_order');
  params.append('bill_cntr', billCounter || 0);
  params.append('open_state', openState || '');
  params.append('open_mode', openMode || '');
  params.append('bill_mode', billMode || '');
  params.append('identifier_type', identifierData && identifierData.type ? identifierData.type : '');
  params.append('identifier_value', identifierData && identifierData.value ? identifierData.value : '');
  params.append('identifier_label', identifierData && identifierData.label ? identifierData.label : '');
  params.append('identifier_hex', identifierData && identifierData.hex ? identifierData.hex : '');
  params.append('save_preset', identifierData && identifierData.save_preset ? '1' : '0');

  fetch(kwApiUrl(), {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    },
    body: params.toString()
  })
    .then(function(response) { return response.json(); })
    .then(function(res) {
      var payload = res && res.data ? res.data : {};
      if (res && res.ok && payload.redirect) {
        window.location = payload.redirect;
        return;
      }
      if (payload && payload.ongoing_sale) {
        var rows = (payload.items || []).map(function(item) {
          return '<li><strong>' + String(item.item_name || item.name || 'Item').replace(/[&<>"']/g, '') + '</strong> &times; ' + (item.qty || item.quantity || 0) + '</li>';
        }).join('');
        var html = '<p>Items already added to sale <strong>#' + (payload.sale_id || '') + '</strong>:</p><ul style="text-align:left;padding-left:22px">' + rows + '</ul>';
        if (window.Swal && typeof Swal.fire === 'function') {
          Swal.fire({ icon: 'warning', title: 'Draft sale #' + (payload.sale_id || ''), html: html, showDenyButton: true, confirmButtonText: 'Continue sale', denyButtonText: 'Cancel sale', allowOutsideClick: false }).then(function(result) {
            if (result.isConfirmed) {
              // The draft is already saved. Reopen it without placing the items again.
              window.location.href = 'index.php';
            } else if (result.isDenied) {
              jQuery.post(kwApiUrl(), { action: 'sales.cancel' }).done(function(cancel) {
                if (cancel && cancel.ok) window.confirmSubmitBill(openState, counterId, openMode, billMode, identifierData);
              });
            }
          });
        }
        return;
      }
      alert((res && res.message) ? res.message : 'Unable to send order right now.');
    })
    .catch(function() {
      alert('Unable to send order right now.');
    });
};

window.del_item = function(item_id, hhr, attr_id) {
  if (window.jQuery && typeof jQuery.get === 'function') {
    jQuery.get('del_item.php', { item_id: item_id, hhr: hhr, attr_id: attr_id }, function() {
      kwRefreshSaleItems();
    });
    return false;
  }
  kwFetchInto('del_item.php', { item_id: item_id, hhr: hhr, attr_id: attr_id }, function() {
    kwRefreshSaleItems();
  });
  return false;
};

if (window.jQuery) {
  jQuery(function($) {
    function kwHideModalShell() {
      $('#modal').removeClass('kw-jq-visible show').css('display', 'none').attr('aria-hidden', 'true').removeAttr('aria-modal');
      $('body').removeClass('modal-open');
      $('.modal-backdrop').remove();
    }
    $(document).on('click', '#modal [data-dismiss="modal"], #modal .close, #modal #close', function(event) {
      event.preventDefault();
      kwHideModalShell();
    });
    $(document).on('click', '#modal', function(event) {
      if (event.target === this) {
        kwHideModalShell();
      }
    });
  });
}
