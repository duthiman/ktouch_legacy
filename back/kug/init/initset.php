<?php
include('funcs.php');
$user_id = $_SESSION['mwene'] ?? 0;
?>
<style>
.kug-modal-header {
  background: linear-gradient(135deg, #008080, #0d9488);
  color: #fff; padding: .85rem 1.1rem;
}
.kug-modal-header .modal-title { font-size: .92rem; font-weight: 700; letter-spacing: .04em; }
.kug-modal-header .btn-close { filter: invert(1) brightness(2); opacity: .8; }
.po-type-btn {
  flex: 1; padding: .45rem .6rem; border: 2px solid #e5e7eb;
  background: #fff; border-radius: 8px; cursor: pointer;
  font-size: .78rem; font-weight: 700; text-align: center;
  transition: all .18s; color: #6c757d;
}
.po-type-btn:hover { border-color: #008080; color: #008080; background: #f0fdf9; }
.po-type-btn.active { border-color: #008080; background: #008080; color: #fff; }
.po-type-btn .po-type-icon { font-size: 1.1rem; display: block; margin-bottom: .2rem; }
.kug-sup-wrap { position: relative; }
.kug-sup-wrap .form-control { padding-left: 2.3rem; }
.kug-sup-wrap .sup-icon { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); color: #6c757d; font-size: .85rem; z-index: 2; }
#init-sup-dropdown { position:absolute;top:100%;left:0;right:0;z-index:99;max-height:200px;overflow-y:auto;
  background:#fff;border:1px solid #e5e7eb;border-radius:6px;box-shadow:0 6px 20px rgba(0,0,0,.12);display:none; }
#init-sup-dropdown .sup-item { padding:6px 12px; cursor:pointer; font-size:.78rem; border-bottom:1px solid #f3f4f6; }
#init-sup-dropdown .sup-item:hover { background:#f0fdf9; color:#008080; }
#init-sup-dropdown .sup-item:last-child { border-bottom:none; }
.held-po-row { font-size:.72rem; cursor:pointer; transition:background .15s; }
.held-po-row:hover { background:#f0fdf9 !important; }
.held-po-badge { font-size:.58rem; padding:2px 6px; border-radius:4px; font-weight:700; }
</style>

<div class="modal-content" style="border:none; box-shadow:0 8px 40px rgba(0,0,0,.18);">

  <div class="kug-modal-header d-flex justify-content-between align-items-center">
    <span class="modal-title"><i class="fa fa-shopping-cart me-2"></i>GENERATE NEW PURCHASE ORDER</span>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>

  <div class="modal-body px-3 py-3">

    <div id="init-result" class="mb-2"></div>

    <!-- PO Type -->
    <div class="mb-3">
      <div class="mb-1" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;">
        <i class="fa fa-tag me-1"></i>PURCHASE ORDER TYPE
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="po-type-btn active" id="potype-local" data-val="2">
          <i class="fa fa-map-marker po-type-icon"></i>LOCAL
        </button>
        <button type="button" class="po-type-btn" id="potype-intl" data-val="1">
          <i class="fa fa-globe po-type-icon"></i>INTERNATIONAL
        </button>
      </div>
      <input type="hidden" id="potype" value="2">
    </div>

    <!-- Supplier Search -->
    <div class="mb-3">
      <div class="mb-1" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;">
        <i class="fa fa-building me-1"></i>SUPPLIER
      </div>
      <div class="d-flex gap-2">
        <div class="kug-sup-wrap flex-fill" style="position:relative;">
          <i class="fa fa-search sup-icon"></i>
          <input type="text" class="form-control form-control-sm"
                 id="bar" name="bar" autocomplete="off"
                 placeholder="Type supplier name or phone number…"
                 onfocus="this.select()">
          <input type="hidden" id="sup_id" value="0">
          <div id="init-sup-dropdown"></div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-success flex-shrink-0" id="newsup"
                title="Add new supplier" style="white-space:nowrap;">
          <i class="fa fa-plus me-1"></i>New
        </button>
      </div>
      <div id="sup-confirm" class="d-none mt-1 px-2 py-1 rounded"
           style="background:#f0fdf9;border:1px solid #bbf7d0;font-size:.76rem;color:#065f46;">
        <i class="fa fa-check-circle me-1"></i><span id="sup-confirm-name"></span>
      </div>
    </div>

    <!-- Held / Ongoing POs -->
    <div id="init-held-orders" class="mb-2">
      <div class="text-muted small text-center"><i class="fa fa-spinner fa-spin me-1"></i>Loading existing orders…</div>
    </div>
 

  </div>

  <div class="modal-footer py-2 px-3 d-flex justify-content-between align-items-center"
       style="border-top:1px solid #f0f0f0;">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
      <i class="fa fa-times me-1"></i>Cancel
    </button>
    <button type="button" id="newpo" class="btn btn-sm btn-success" disabled>
      <i class="fa fa-arrow-right me-1"></i>START PURCHASE
    </button>
  </div>

</div>

<script>
(function () {
'use strict';

// Load held/in-progress purchase orders through the module API.
$.getJSON('api/lpo_api.php', { action: 'init_data' })
  .done(function (response) {
    var orders = response && response.held_orders ? response.held_orders : [];
    if (!orders.length) {
      $('#init-held-orders').html('');
      return;
    }
    var html = '<div class="mb-1" style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;"><i class="fa fa-pause-circle me-1 text-warning"></i>EXISTING ORDERS (HELD / IN-PROGRESS)</div>';
    html += '<div class="border rounded" style="max-height:160px;overflow-y:auto;"><table class="table table-sm table-borderless mb-0"><thead style="background:#f9fafb;position:sticky;top:0;"><tr style="font-size:.62rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;"><th class="py-1 px-2">PO #</th><th class="py-1 px-2">Supplier</th><th class="py-1 px-2">Date</th><th class="py-1 px-2">Status</th><th class="py-1 px-2 text-end">Action</th></tr></thead><tbody>';
    orders.forEach(function (order) {
      var held = parseInt(order.po_status, 10) === 3;
      html += '<tr class="held-po-row"><td class="py-1 px-2 fw-bold text-primary">#' + parseInt(order.po_id, 10) + '</td><td class="py-1 px-2">' + $('<span>').text(order.sup_name || 'Unknown').html() + '</td><td class="py-1 px-2 text-muted">' + $('<span>').text(order.po_day || '–').html() + '</td><td class="py-1 px-2"><span class="held-po-badge ' + (held ? 'bg-warning text-dark' : 'bg-info text-white') + '">' + (held ? 'Held' : 'Active') + '</span></td><td class="py-1 px-2 text-end"><button class="btn btn-xs btn-outline-primary init-continue-po" data-po="' + parseInt(order.po_id, 10) + '" data-sup="' + parseInt(order.sup_id, 10) + '" style="font-size:.62rem;padding:1px 8px;"><i class="fa fa-arrow-right me-1"></i>Continue</button></td></tr>';
    });
    $('#init-held-orders').html(html + '</tbody></table></div>');
  })
  .fail(function () { $('#init-held-orders').html('<div class="alert alert-danger py-1 small mb-0">Could not load existing orders.</div>'); });

// ── PO type toggle ────────────────────────────────────────────────
document.getElementById('potype-local').addEventListener('click', function () {
  document.getElementById('potype').value = '2';
  this.classList.add('active');
  document.getElementById('potype-intl').classList.remove('active');
});
document.getElementById('potype-intl').addEventListener('click', function () {
  document.getElementById('potype').value = '1';
  this.classList.add('active');
  document.getElementById('potype-local').classList.remove('active');
});

// ── Supplier AJAX search (custom dropdown — no jQuery UI needed) ──
var _supTimer = null;
var $bar      = $('#bar');
var $dd       = $('#init-sup-dropdown');

$bar.on('input', function () {
  var term = $.trim($(this).val());
  $('#sup_id').val('0');
  $('#sup-confirm').addClass('d-none');
  _checkReady();

  if (term.length < 1) { $dd.hide().html(''); return; }

  clearTimeout(_supTimer);
  _supTimer = setTimeout(function () {
    $.ajax({
      url: '../sup/api.php',
      type: 'POST',
      dataType: 'json',
      data: { q: term }
    }).done(function (list) {
      if (!list || !list.length) {
        $dd.html('<div class="p-2 text-muted small text-center">No suppliers found</div>').show();
        return;
      }
      var h = '';
      list.forEach(function (s) {
        h += '<div class="sup-item" data-id="' + s.value + '" data-label="' + $('<span/>').text(s.label).html() + '">' + $('<span/>').text(s.label).html() + '</div>';
      });
      $dd.html(h).show();
    }).fail(function () {
      $dd.html('<div class="p-2 text-danger small text-center">Search error</div>').show();
    });
  }, 250);
});

$dd.on('click', '.sup-item', function () {
  var id    = $(this).data('id');
  var label = $(this).data('label');
  $bar.val(label);
  $('#sup_id').val(id);
  $('#sup-confirm-name').text(label);
  $('#sup-confirm').removeClass('d-none');
  $dd.hide().html('');
  _checkReady();
});

$(document).on('click', function (e) {
  if (!$(e.target).closest('.kug-sup-wrap').length) $dd.hide();
});

function _checkReady() {
  var ok = parseInt($('#sup_id').val() || '0') > 0;
  document.getElementById('newpo').disabled = !ok;
}

function redirectToOrderPage(poId) {
  window.location.href = 'ord/ordPage.php?po_id=' + encodeURIComponent(poId);
}

// ── New supplier (inline form toggle) ─────────────────────────────
$('#newsup').on('click', function () {
  $('#load2').html('<div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading supplier form…</div></div>');
  $('#modal2').modal('show');
  $.ajax({url:'../sup/sup_new.php', type:'GET', dataType:'html'})
    .done(function (html) { $('#load2').html(html); })
    .fail(function () { $('#load2').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load supplier form.</div></div></div>'); });
});

$(document).on('submit', '#sup_form', function (e) {
  e.preventDefault();
  var form = this;
  var button = $('#new_sup');
  button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving');
  $.ajax({
    url: '../sup/api.php', type: 'POST', dataType: 'json',
    data: {
      action:'create', name:$('#sup_name').val(), sup_phone:$('#sup_phone').val(),
      sup_email:$('#sup_email').val(), sup_location:$('#sup_location').val(),
      sup_address:$('#sup_address').val(), sup_pin:$('#sup_pin').val(),
      sup_vat:$('#sup_vat').val(), sup_type:$('#sup_type').val()
    }
  }).done(function (response) {
    $('#sup_form #results').html('<div class="alert alert-'+(response.ok?'success':'danger')+'">'+(response.message || 'Supplier request completed.')+'</div>');
    if (response.ok) {
      $('#bar').val($('#sup_name').val()); $('#sup_id').val(response.data.sup_id || 0);
      $('#sup-confirm-name').text($('#sup_name').val()); $('#sup-confirm').removeClass('d-none');
      $('#modal2').modal('hide'); _checkReady();
      supplierNotice(response.message || 'Supplier added successfully.', 'success');
    } else {
      supplierNotice(response.message || 'Could not save supplier.', 'danger');
    }
  }).fail(function (xhr) {
    var response = xhr.responseJSON || {};
    $('#sup_form #results').html('<div class="alert alert-danger">'+(response.message || 'Could not save supplier.')+'</div>');
    supplierNotice(response.message || 'Could not save supplier.', 'danger');
  }).always(function () { button.prop('disabled', false).html('<i class="fa fa-check"></i> Save'); });
});

function supplierNotice(message, type) {
  var old = $('#supplier-api-notice');
  if (old.length) old.remove();
  var notice = $('<div id="supplier-api-notice" class="alert alert-'+(type || 'success')+'" style="position:fixed;right:24px;top:80px;z-index:2000;min-width:280px;box-shadow:0 5px 18px rgba(0,0,0,.2);"><button type="button" class="close" aria-label="Close">&times;</button><i class="fa fa-'+(type === 'danger' ? 'exclamation-circle' : 'check-circle')+'"></i> '+message+'</div>');
  $('body').append(notice);
  notice.find('.close').on('click', function () { notice.remove(); });
  window.setTimeout(function () { notice.fadeOut(300, function () { notice.remove(); }); }, 3500);
}

$('#init-sup-form').on('submit', function (e) {
  e.preventDefault();
  var $btn = $(this).find('button[type=submit]');
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Saving…');

  $.ajax({
    url: 'sup/supnewact.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'html'
  }).done(function () {
    $('#init-result').html('<div class="alert alert-success py-1 small mb-0"><i class="fa fa-check-circle me-1"></i>Supplier saved! Search for them above.</div>');
    $('#init-newsup-area').addClass('d-none');
    $('#init-sup-form')[0].reset();
    $btn.prop('disabled', false).html('<i class="fa fa-check me-1"></i>Save Supplier');
    setTimeout(function () { $('#init-result').html(''); }, 4000);
  }).fail(function () {
    $('#init-result').html('<div class="alert alert-danger py-1 small mb-0">Failed to save supplier.</div>');
    $btn.prop('disabled', false).html('<i class="fa fa-check me-1"></i>Save Supplier');
  });
});

// ── Continue existing PO ──────────────────────────────────────────
$(document).on('click', '.init-continue-po', function () {
  var po_id  = $(this).data('po');
  var sup_id = $(this).data('sup');
  $('#init-result').html('<div class="text-muted small"><i class="fa fa-spinner fa-spin me-1"></i>Loading order…</div>');

  $.ajax({ url: 'api/lpo_api.php', type: 'GET', data: { action: 'continue', po_id: po_id, sup_id: sup_id }, dataType: 'json' })
    .done(function (data) {
      if (data && data.success && data.po_id) {
        redirectToOrderPage(data.po_id);
      } else {
        $('#init-result').html('<div class="alert alert-danger py-1 small mb-0">' + ((data && data.message) || 'Error continuing order.') + '</div>');
      }
    }).fail(function () {
      $('#init-result').html('<div class="alert alert-danger py-1 small mb-0">Server error.</div>');
    });
});

// ── Start Purchase ────────────────────────────────────────────────
$('#newpo').on('click', function () {
  var sup_id  = $('#sup_id').val();
  var potype  = $('#potype').val();

  if (!sup_id || parseInt(sup_id) === 0) {
    $('#init-result').html('<div class="alert alert-danger py-2 small mb-0"><i class="fa fa-exclamation-triangle me-1"></i>Please select a supplier.</div>');
    return;
  }

  $('#init-result').html('<div class="text-muted small"><i class="fa fa-spinner fa-spin me-1"></i>Creating order…</div>');
  $('#newpo').prop('disabled', true);

  $.ajax({
    url: 'api/lpo_api.php',
    type: 'POST',
    data: { action: 'initialize', sup_id: sup_id, type: potype, poby: 5 },
    dataType: 'json'
  })
  .done(function (data) {
    if (data && data.success && data.po_id) {
      redirectToOrderPage(data.po_id);
    } else {
      $('#init-result').html('<div class="alert alert-danger py-2 small mb-0"><i class="fa fa-exclamation-triangle me-1"></i>' + ((data && data.message) || 'An error occurred.') + '</div>');
      $('#newpo').prop('disabled', false);
    }
  })
  .fail(function () {
    $('#init-result').html('<div class="alert alert-danger py-2 small mb-0"><i class="fa fa-exclamation-triangle me-1"></i>Server error. Please try again.</div>');
    $('#newpo').prop('disabled', false);
  });
});

})();
</script>
