function setTotHtml(ids, html) {
  for (var i = 0; i < ids.length; i++) {
    var el = document.getElementById(ids[i]);
    if (el) {
      el.innerHTML = html;
      el.style.visibility = "visible";
    }
  }
}

var totsApiUrl = 'API/tots_api.php';

function totsApiRequest(options) {
  return $.ajax({
    url: totsApiUrl,
    type: options.type || 'GET',
    dataType: 'json',
    data: options.data || {}
  });
}

function showTotInlineModal(message) {
  $('#load').html(
    '<div class="modal-content">' +
      '<div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">' + message + '</div>' +
    '</div>'
  );
  $('#modal').modal('show');
}

$(document).ready(function(){
  $.ajaxSetup({ cache: false });
  load_tot_bootstrap();

  function bindTotTap(selector, handler) {
    var tapLimit = 12;

    $(document).on('pointerdown', selector, function(event){
      this.__totTapStartX = event.clientX;
      this.__totTapStartY = event.clientY;
      this.__totTapMoved = false;
    });

    $(document).on('pointermove', selector, function(event){
      if (typeof this.__totTapStartX === 'undefined') {
        return;
      }
      if (Math.abs(event.clientX - this.__totTapStartX) > tapLimit || Math.abs(event.clientY - this.__totTapStartY) > tapLimit) {
        this.__totTapMoved = true;
      }
    });

    $(document).on('pointerup', selector, function(event){
      if (this.__totTapMoved) {
        return;
      }
      this.__totPointerHandled = true;
      event.preventDefault();
      handler.call(this, event);
    });

    $(document).on('click', selector, function(event){
      if (this.__totPointerHandled) {
        this.__totPointerHandled = false;
        event.preventDefault();
        return;
      }
      event.preventDefault();
      handler.call(this, event);
    });
  }

  bindTotTap('#getItem', function(){
    var uid = $(this).data('id');
    tot_action(uid);
  });

  bindTotTap('.tot-mobile-cat-btn', function(){
    var id = $(this).data('cat-id');
    req_navbar(id);
    load_cat_food(id);
  });

  bindTotTap('.tot-desktop-cat-btn', function(){
    var id = $(this).data('cat-id');
    req_navbar(id);
    load_cat_food(id);
  });

  bindTotTap('.tot-mobile-sub-btn', function(){
    var id = $(this).data('sub-id');
    load_sub_food(id);
  });

  bindTotTap('.tot-live-remove', function(){
    var id = $(this).data('tot-delete-id');
    tot_del_item(id);
  });

  $(document).on('click','#tot-cart-toggle', function(){
    totsApiRequest({
      type: 'POST',
      data: { action: 'get_summary_modal' }
    })
    .done(function(res){
      $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
      $('#modal').modal('show');
    })
    .fail(function() {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Something went wrong, Please try again...</div></div>');
      $('#modal').modal('show');
    });
  });
  $(document).on('click','#tot-cart-close', function(){
    $('#tot-cart-drawer').removeClass('open');
  });

  $(document).on('click', '#tot-submit-trigger, .tot-submit-trigger', function(event){
    event.preventDefault();
    totsApiRequest({
      type: 'POST',
      data: { action: 'get_print_modal' }
    })
    .done(function(res){
      $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
      $('#modal').modal('show');
    })
    .fail(function() {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Could not load printer counters. Please try again.</div></div>');
      $('#modal').modal('show');
    });
  });

  $(document).on('click', '.tot-cancel-trigger', function(event){
    event.preventDefault();
    totsApiRequest({
      type: 'POST',
      data: { action: 'get_cancel_modal' }
    })
      .done(function(res){
        $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
        $('#modal').modal('show');
      })
      .fail(function() {
        showTotInlineModal('Could not load the cancel confirmation. Please try again.');
      });
  });

  $(document).on('click', '#tot-cancel-confirm', function(event){
    event.preventDefault();
    $('#tot-cancel-feedback').html('<div class="alert alert-warning" style="margin-bottom:0;font-weight:700;">Cancelling generated units...</div>');
    totsApiRequest({
      type: 'POST',
      data: { action: 'cancel' }
    })
    .done(function(res){
      if (res && res.ok) {
        $('#tot-cancel-feedback').html('<div class="alert alert-success" style="margin-bottom:0;font-weight:700;">Generated units cancelled.</div>');
        window.location = (res.data && res.data.redirect) ? res.data.redirect : 'index.php';
        return;
      }
      $('#tot-cancel-feedback').html('<div class="alert alert-danger" style="margin-bottom:0;font-weight:700;">' + ((res && res.message) ? res.message : 'Could not cancel generated units.') + '</div>');
    })
    .fail(function(){
      $('#tot-cancel-feedback').html('<div class="alert alert-danger" style="margin-bottom:0;font-weight:700;">Could not cancel generated units.</div>');
    });
  });

  $(document).on('click', '[data-tot-print-counter]', function(){
    var counterId = parseInt($(this).data('totPrintCounter'), 10) || 0;
    if (!counterId) {
      return;
    }
    $('#tot-print-feedback').html('<div class="alert alert-info" style="margin-bottom:0;font-weight:700;">Sending TOT to printer...</div>');
    totsApiRequest({
      type: 'POST',
      data: { action: 'submit_print', cntr_id: counterId }
    })
    .done(function(res){
      if (res && res.ok) {
        if (res.data && res.data.redirect) {
          window.location = res.data.redirect;
          return;
        }
        $('#tot-print-feedback').html('<div class="alert alert-success" style="margin-bottom:0;font-weight:700;">TOT queued successfully.</div>');
        $('#modal').modal('hide');
        window.location = '../dash/dash.php';
        return;
      }
      $('#tot-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:0;font-weight:700;">' + ((res && res.message) ? res.message : 'Could not submit TOT print.') + '</div>');
    })
    .fail(function(){
      $('#tot-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:0;font-weight:700;">Could not submit TOT print.</div>');
    });
  });

});

function applyLiveListHtml(html) {
  $('#screen').html(html);
  $('#screen_mobile').html(html);
  if ($('#tot-summary-list').length) {
    $('#tot-summary-list').html(html);
  }
  syncTotDrawer();
}

function load_tot_bootstrap() {
  totsApiRequest({
    data: { action: 'bootstrap' }
  }).done(function(res) {
    var data = res && res.data ? res.data : {};
    if (res && !res.ok) {
      showTotInlineModal(res.message || 'Could not load generated units.');
      return;
    }
    $('#tot-category-desktop').html(data.categories_desktop_html || '');
    $('#tot-category-mobile').html(data.categories_mobile_html || '');
    setTotHtml(["menu", "menu_mobile"], data.items_html || '');
    applyLiveListHtml(data.live_list_html || '');
  });
}

function req_navbar(id){
  totsApiRequest({
    data: { action: 'get_subcategories', id: id }
  }).done(function(res) {
    setTotHtml(["sub_cat", "sub_cat_mobile"], (res && res.data && res.data.html) ? res.data.html : '');
  });
}
function load_sub_food(id){
  totsApiRequest({
    data: { action: 'get_subcategory_items', id: id }
  }).done(function(res) {
    setTotHtml(["menu", "menu_mobile"], (res && res.data && res.data.html) ? res.data.html : '');
  });
}
function load_cat_food(id){
  totsApiRequest({
    data: { action: 'get_category_items', id: id }
  }).done(function(res) {
    setTotHtml(["menu", "menu_mobile"], (res && res.data && res.data.html) ? res.data.html : '');
  });
}
function tot_action(id){  
  totsApiRequest({
    type: 'POST',
    data: { action: 'add_item', item_id: id }
  }).done(function(res) {
    var code = res && res.data ? res.data.code : '';
    if (code === 'NO_COUNTER_SELECTED') {
      $('#load').html(
        '<div class="modal-content" style="border-radius:16px; overflow:hidden;">' +
          '<div class="modal-header" style="background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">' +
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<h4 class="modal-title" style="font-weight:900;">No Counter Selected</h4>' +
          '</div>' +
          '<div class="modal-body" style="background:#fffdf8; padding:14px;">' +
            '<div class="alert alert-warning" style="margin-bottom:0; font-weight:700;">Please select or open a counter first before generating TOTs.</div>' +
          '</div>' +
          '<div class="modal-footer" style="background:#fffdf8;">' +
            '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
            '<a href="../stock/store_dash.php" class="btn btn-danger">Go to Dashboard</a>' +
          '</div>' +
        '</div>'
      );
      $('#modal').modal('show');
      return;
    }
    if (code === 'TOT_SESSION_MISSING') {
      window.location.href = 'tot_init.php';
      return;
    }
    if (!res || !res.ok) {
      if (res && res.message) {
        showTotInlineModal(res.message);
      }
      return;
    }
    applyLiveListHtml((res.data && res.data.live_list_html) ? res.data.live_list_html : '');
  });
}
function load_item_list(){
  totsApiRequest({
    data: { action: 'get_live_list' }
  }).done(function(res) {
    applyLiveListHtml((res && res.data && res.data.html) ? res.data.html : '');
  });
}
function syncTotDrawer() {
  var rows = $('#screen_mobile .tot-live-item').length;
  $('#tot-cart-count').text(rows);
  $('#tot-cart-total').text(rows ? rows + ' items' : '0');
}
function tot_del_item(id) {
  totsApiRequest({
    data: { action: 'delete_item', id: id }
  }).done(function(res) {
    if (!res || !res.ok) {
      return;
    }
    applyLiveListHtml((res.data && res.data.live_list_html) ? res.data.live_list_html : '');
  });
}
