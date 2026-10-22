var gwitState = {
  context: {},
  categories: [],
  subcategories: [],
  items: [],
  reqItems: []
};
var gwitCatalogLoaded = false;

function gwitEscapeHtml(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function setReqHtml(ids, html) {
  for (var i = 0; i < ids.length; i++) {
    var el = document.getElementById(ids[i]);
    if (el) {
      el.innerHTML = html;
      el.style.visibility = 'visible';
    }
  }
}

function renderCategories() {
  var desktopHtml = '';
  var mobileHtml = '';
  for (var i = 0; i < gwitState.categories.length; i++) {
    var row = gwitState.categories[i];
    desktopHtml += '<button type="button" class="btn_nav gwit-cat-btn" data-cat-id="' + Number(row.cat_id) + '">' + gwitEscapeHtml(row.name) + '</button>';
    mobileHtml += '<button type="button" class="req-mobile-cat req-mobile-cat-btn gwit-cat-btn" data-cat-id="' + Number(row.cat_id) + '">' + gwitEscapeHtml(row.name) + '</button>';
  }
  setReqHtml(['gwit_categories_desktop'], desktopHtml || '<div class="gwit-empty">No categories found.</div>');
  setReqHtml(['gwit_categories_mobile'], mobileHtml || '<div class="gwit-empty">No categories found.</div>');
}

function renderSubcategories(categoryId) {
  var html = '';
  var selectedCategoryId = Number(categoryId || 0);
  var rows = gwitState.subcategories.filter(function(row) {
    return !selectedCategoryId || Number(row.cat_id || 0) === selectedCategoryId;
  });
  for (var i = 0; i < rows.length; i++) {
    var row = rows[i];
    html += '<button type="button" class="req-mobile-sub-btn gwit-sub-btn" data-sub-id="' + Number(row.sub_id) + '">' + gwitEscapeHtml(row.name) + '</button>';
  }
  setReqHtml(['sub_cat', 'sub_cat_mobile'], html);
}

function renderItems(items) {
  var rows = Array.isArray(items) ? items : gwitState.items;
  if (!rows.length) {
    setReqHtml(['menu', 'menu_mobile'], '<div class="gwit-empty">No stock items found.</div>');
    return;
  }
  var html = '<div class="req-items-grid">';
  for (var i = 0; i < rows.length; i++) {
    var row = rows[i];
    html += '<button type="button" class="req-item-btn gwit-item-btn" data-id="' + Number(row.item_id) + '">' + gwitEscapeHtml(row.name) + '</button>';
  }
  html += '</div>';
  setReqHtml(['menu', 'menu_mobile'], html);
}

function renderReqItems() {
  if (!gwitState.reqItems.length) {
    setReqHtml(['screen', 'screen_mobile'], '<div class="req-live-empty">No requisition items added yet.</div>');
    updateReqCartState();
    return;
  }
  var html = '<div class="req-list-shell">';
  for (var i = 0; i < gwitState.reqItems.length; i++) {
    var row = gwitState.reqItems[i];
    html += '<div class="req-live-item">';
    html += '<div class="req-live-no">' + (i + 1) + '</div>';
    html += '<div class="req-live-copy"><strong>' + gwitEscapeHtml(row.item_name) + '</strong><span>Requested Qty</span></div>';
    html += '<div class="req-live-meta"><strong>' + gwitEscapeHtml(row.req_qty) + '</strong><button type="button" class="req-live-remove" onclick="del_item(' + Number(row.item_id) + ')">Remove</button></div>';
    html += '</div>';
  }
  html += '</div>';
  setReqHtml(['screen', 'screen_mobile'], html);
  updateReqCartState();
}

function applyBootstrap(data) {
  gwitState.context = data.context || {};
  gwitState.reqItems = data.req_items || [];
  $('#gwit_counter_name_desktop, #gwit_counter_name_mobile').text((gwitState.context || {}).counter_name || 'Counter');
  renderReqItems();
}

function loadBootstrap() {
  setReqHtml(['menu', 'menu_mobile'], '<div class="gwit-loading">Loading catalog...</div>');
  setReqHtml(['screen', 'screen_mobile'], '<div class="gwit-loading">Loading requisition...</div>');
  $.getJSON('API/gwit_api.php', { action: 'bootstrap' })
    .done(function(response) {
      if (!response.ok) {
        setReqHtml(['menu', 'menu_mobile', 'screen', 'screen_mobile'], '<div class="gwit-empty">' + gwitEscapeHtml(response.message || 'Could not load requisition.') + '</div>');
        return;
      }
      applyBootstrap(response.data || {});
    })
    .fail(function(xhr) {
      var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not load requisition.';
      setReqHtml(['menu', 'menu_mobile', 'screen', 'screen_mobile'], '<div class="gwit-empty">' + gwitEscapeHtml(message) + '</div>');
    });
}

function req_navbar(id) {
  var categoryId = Number(id || 0);
  if (gwitCatalogLoaded) {
    renderSubcategories(categoryId);
    renderItems(gwitState.items.filter(function(item) {
      return Number(item.cat_id || 0) === categoryId;
    }));
    return;
  }
  load_cat_food(categoryId);
}

function load_cat_food(id) {
  if (gwitCatalogLoaded) {
    req_navbar(id);
    return;
  }
  $.getJSON('API/gwit_api.php', { action: 'category_items', cat_id: id })
    .done(function(response) {
      if (!response.ok) {
        alert(response.message || 'Could not load category items.');
        return;
      }
      gwitState.subcategories = (response.data || {}).subcategories || [];
      gwitState.items = (response.data || {}).items || [];
      renderSubcategories();
      renderItems();
    });
}

function load_sub_food(id) {
  if (gwitCatalogLoaded) {
    var subId = Number(id || 0);
    renderItems(gwitState.items.filter(function(item) {
      return Number(item.sub_id || 0) === subId;
    }));
    return;
  }
  $.getJSON('API/gwit_api.php', { action: 'subcategory_items', sub_id: id })
    .done(function(response) {
      if (!response.ok) {
        alert(response.message || 'Could not load subcategory items.');
        return;
      }
      gwitState.items = (response.data || {}).items || [];
      renderItems();
    });
}

function req_action() {
  var item_id = $('#item_id').val();
  var qty = $('#req_qty').val();
  $.post('API/gwit_api.php', { action: 'add_item', item_id: item_id, qty: qty }, null, 'json')
    .done(function(response) {
      if (!response.ok) {
        $('#results').html('<div class="alert alert-danger"><h5><b>' + gwitEscapeHtml(response.message || 'Failed to add item.') + '</b></h5></div>');
        return;
      }
      gwitState.reqItems = (response.data || {}).req_items || [];
      renderReqItems();
      $('#modal').modal('hide');
    })
    .fail(function(xhr) {
      var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add item.';
      $('#results').html('<div class="alert alert-danger"><h5><b>' + gwitEscapeHtml(message) + '</b></h5></div>');
    });
}

function load_item_list() {
  $.getJSON('API/gwit_api.php', { action: 'req_items' })
    .done(function(response) {
      if (response.ok) {
        gwitState.reqItems = (response.data || {}).req_items || [];
        renderReqItems();
      }
    });
}

function del_item(id) {
  $.post('API/gwit_api.php', { action: 'delete_item', item_id: id }, null, 'json')
    .done(function(response) {
      if (response.ok) {
        gwitState.reqItems = (response.data || {}).req_items || [];
        renderReqItems();
      }
    });
}

function updateReqCartState() {
  var rows = gwitState.reqItems.length;
  $('#req-cart-count').text(rows);
  $('#req-cart-total').text(rows ? rows + ' items' : '0');
}

function loadGwitCatalog() {
  $.getJSON('../API/common/api.php', { action: 'fetch_catalog', stock: 1 })
    .done(function(response) {
      if (!response || !response.ok || !response.data) {
        return;
      }
      var data = response.data;
      gwitState.categories = data.categories || [];
      gwitState.subcategories = data.subcategories || [];
      gwitState.items = data.items || [];
      gwitCatalogLoaded = true;
      renderCategories();

      var firstCategory = gwitState.categories.length ? Number(gwitState.categories[0].cat_id) : 0;
      renderSubcategories(firstCategory);
      renderItems(gwitState.items.filter(function(item) {
        return !firstCategory || Number(item.cat_id || 0) === firstCategory;
      }));
    })
    .fail(function() {
      setReqHtml(['menu', 'menu_mobile'], '<div class="gwit-empty">Could not load the item catalog.</div>');
    });
}

function renderItemModal(item) {
  var html = '<div class="req-deci-shell"><input type="hidden" id="item_id" value="' + Number(item.item_id) + '"><div class="modal-content"><div class="req-deci-body">';
  html += '<div class="req-deci-top"><div class="req-deci-top-row"><div class="req-deci-top-main"><h4>Choose Quantity</h4><p>' + gwitEscapeHtml(item.name) + '</p></div><div class="req-deci-badge"><small>Entry</small>Qty</div></div></div>';
  if (Number(item.port_status || 0) === 1) {
    html += '<div class="req-deci-hint">This item has portions. Request using the portion size ' + gwitEscapeHtml(item.portions) + '.</div>';
  }
  html += '<div id="results"></div><input type="text" id="req_qty" class="req-deci-display" autofocus />';
  html += '<div class="req-deci-pad">';
  var buttons = ['7','8','9','4','5','6','1','2','3'];
  for (var i = 0; i < buttons.length; i++) {
    html += '<button type="button" class="req-deci-key req-num" data-value="' + buttons[i] + '">' + buttons[i] + '</button>';
  }
  html += '<button type="button" class="req-deci-key req-deci-key-clear" id="req_backspace">&larr;</button><button type="button" class="req-deci-key req-num" data-value="0">0</button><button type="button" class="req-deci-key req-num" data-value=".">.</button></div>';
  html += '<div class="req-deci-actions"><button type="button" data-dismiss="modal" class="req-deci-action-btn req-deci-close">Close</button><button type="button" id="pay" class="req-deci-action-btn req-deci-submit">Submit</button></div>';
  html += '</div></div></div>';
  $('#load').html(html);
  $('#modal').modal('show');
  $('.req-num').off('click').on('click', function() { var qty = $('#req_qty'); qty.val(qty.val() + $(this).data('value')); });
  $('#req_backspace').off('click').on('click', function() { var qty = $('#req_qty'); qty.val(qty.val().slice(0, -1)); });
  $('#pay').off('click').on('click', function() { req_action(); });
}

function renderCancelModal() {
  var html = '<div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">WARNING</h4></div><div class="modal-body"><div class="container-fluid"><div class="row"><div class="alert alert-danger"><p>WARNING!! This action is irreversible and will delete currently placed orders.</p></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-outline-success" id="gwit-cancel-confirm">CONTINUE</button></div></div>';
  $('#load').html(html);
  $('#gwit-cancel-confirm').off('click').on('click', function() {
    $.post('API/gwit_api.php', { action: 'cancel_request' }, null, 'json').done(function(response) {
      if (response.ok && response.data && response.data.redirect) {
        window.location = response.data.redirect;
      }
    });
  });
}

function renderSubmitModal(modal) {
  var html = '<div class="req-confirm-shell"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" style="font-weight: bold;">' + gwitEscapeHtml(modal.counter_name) + ' STOCK REQUEST CONFIRMATION</h4></div><div class="modal-body req-confirm-body"><div id="req-print-feedback"></div><div class="req-confirm-warning">Make sure you confirm the requested items below. The request cannot go through without items.</div><div class="req-confirm-summary"><div><strong>Counter</strong><span>' + gwitEscapeHtml(modal.counter_name) + '</span></div><div style="text-align:right;"><strong>Items Raised</strong><span>' + Number(modal.item_count || 0) + '</span></div></div><div class="req-confirm-list">';
  if (!modal.req_items || !modal.req_items.length) {
    html += '<div class="req-confirm-empty">No items have been added to this requisition yet.</div>';
  } else {
    for (var i = 0; i < modal.req_items.length; i++) {
      var row = modal.req_items[i];
      html += '<div class="req-confirm-item"><div class="req-confirm-item-top"><div class="req-confirm-item-no">' + (i + 1) + '</div><div class="req-confirm-item-name">' + gwitEscapeHtml(row.item_name) + '</div><div class="req-confirm-item-qty"><strong>Qty</strong><span>' + gwitEscapeHtml(row.req_qty) + '</span></div></div></div>';
    }
  }
  html += '</div>';
  if (Number(modal.item_count || 0) > 0 && Number(modal.print_mode || 0) !== 1) {
    html += '<div class="req-confirm-pick-copy">Choose where to collect the printed requisition.</div><div class="req-print-grid">';
    for (var j = 0; j < (modal.print_counters || []).length; j++) {
      var pc = modal.print_counters[j];
      html += '<button type="button" class="req-print-btn' + (Number(pc.cntr_id) === Number(modal.counter) ? ' active' : '') + '" data-req-print-counter="' + Number(pc.cntr_id) + '"><strong>' + Number(pc.cntr_id) + '</strong><span>' + gwitEscapeHtml(pc.name) + (pc.dpt ? ' - DPT ' + Number(pc.dpt) : '') + '</span></button>';
    }
    html += '</div>';
  }
  html += '</div>';
  if (Number(modal.item_count || 0) > 0) {
    html += '<div class="modal-footer"><button type="button" class="req-confirm-btn req-confirm-close" data-dismiss="modal">Close</button><button type="button" class="req-confirm-btn req-confirm-submit" id="gwitia-submit-request" data-print-mode="' + Number(modal.print_mode || 0) + '">Request</button></div>';
  } else {
    html += '<div class="modal-footer" style="grid-template-columns:1fr;"><button type="button" class="req-confirm-btn req-confirm-close" data-dismiss="modal">Close</button></div>';
  }
  html += '</div></div>';
  $('#load').html(html);
  var selectedReqCounter = Number(modal.print_mode || 0) === 1 ? 0 : Number(modal.counter || 0);
  $(document).off('click.gwitiaCounter').on('click.gwitiaCounter', '[data-req-print-counter]', function() {
    selectedReqCounter = parseInt($(this).data('reqPrintCounter'), 10) || 0;
    $('[data-req-print-counter]').removeClass('active');
    $(this).addClass('active');
    $('#req-print-feedback').html('');
  });
  $(document).off('click.gwitiaSubmit').on('click.gwitiaSubmit', '#gwitia-submit-request', function() {
    var printMode = parseInt($(this).data('printMode'), 10) || 0;
    if (printMode !== 1 && !selectedReqCounter) {
      $('#req-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">Please select a printer counter.</div>');
      return;
    }
    $('#gwitia-submit-request').prop('disabled', true);
    $('#req-print-feedback').html('<div class="alert alert-info" style="margin-bottom:12px;font-weight:700;">Sending requisition...</div>');
    $.post('API/gwit_api.php', { action: 'submit_request', cntr_id: selectedReqCounter }, null, 'json').done(function(response) {
      if (response.ok && response.data && response.data.redirect) {
        window.location = response.data.redirect;
        return;
      }
      $('#req-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">' + gwitEscapeHtml(response.message || 'Could not submit requisition.') + '</div>');
      $('#gwitia-submit-request').prop('disabled', false);
    }).fail(function(xhr) {
      var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not submit requisition.';
      $('#req-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">' + gwitEscapeHtml(message) + '</div>');
      $('#gwitia-submit-request').prop('disabled', false);
    });
  });
}

$(document).ready(function() {
  $.ajaxSetup({ cache: false });
  loadGwitCatalog();
  loadBootstrap();
  $(document).on('click', '#req-cart-toggle', function() { $('#req-cart-drawer').toggleClass('open'); });
  $(document).on('click', '#req-cart-close', function() { $('#req-cart-drawer').removeClass('open'); });
  $(document).on('click', '.gwit-cat-btn', function(e) { e.preventDefault(); load_cat_food($(this).data('catId') || $(this).data('cat-id')); });
  $(document).on('click', '.gwit-sub-btn', function(e) { e.preventDefault(); load_sub_food($(this).data('subId') || $(this).data('sub-id')); });
  $(document).on('click', '.gwit-item-btn', function(e) {
    e.preventDefault();
    $('#load').html('<div class="modal-content"><div class="modal-body gwit-loading" style="padding:24px;">Loading item...</div></div>');
    $('#modal').modal('show');
    var itemId = $(this).data('id');
    $.getJSON('API/gwit_api.php', { action: 'item_modal', item_id: itemId })
      .done(function(response) {
        if (!response || !response.ok || !response.data || !response.data.item) {
          $('#load').html('<div class="modal-content"><div class="modal-body alert alert-danger" style="margin:0;">' + gwitEscapeHtml((response && response.message) || 'Could not load item.') + '</div></div>');
          return;
        }
        var item = response.data.item;
        $.ajax({
          url: 'req_modal.php',
          type: 'POST',
          data: {
            item_id: item.item_id,
            item_name: item.name,
            port_status: item.port_status,
            portions: item.portions
          },
          dataType: 'html'
        }).done(function(html) {
          $('#load').html(html);
          $('#modal').modal('show');
        }).fail(function() {
          $('#load').html('<div class="modal-content"><div class="modal-body alert alert-danger" style="margin:0;">Could not load item form.</div></div>');
        });
      }).fail(function(xhr) {
        var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not load item.';
        $('#load').html('<div class="modal-content"><div class="modal-body alert alert-danger" style="margin:0;">' + gwitEscapeHtml(message) + '</div></div>');
    });
  });
  $(document).on('click', '#cancel, #cancel_mobile', function(e) {
    e.preventDefault();
    $.getJSON('API/gwit_api.php', { action: 'cancel_modal' }).done(function(response) {
      if (response.ok) {
        renderCancelModal();
      }
    });
  });
  $(document).on('click', '#btn_sub, #btn_sub_mobile', function(e) {
    e.preventDefault();
    $('#load').html('<div class="modal-content"><div class="modal-body gwit-loading" style="padding:24px;">Loading confirmation...</div></div>');
    $('#modal').modal('show');
    $.ajax({
      url: 'submit_modal.php',
      type: 'GET',
      dataType: 'html'
    }).done(function(html) {
      $('#load').html(html);
      $('#modal').modal('show');
    }).fail(function() {
      $('#load').html('<div class="modal-content"><div class="modal-body alert alert-danger" style="margin:0;">Could not load confirmation.</div></div>');
    });
  });
});
