<?php
require('../../clude/head.php');
$reqId = (int)($_GET['req_id'] ?? 0);
$counter = (int)($_GET['cntr'] ?? 0);
?>
<link rel="stylesheet" href="../thit.css">
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require('../../clude/header.php'); ?>
  <div id="content" class="content">
    <div id="thit-req-view" data-req-id="<?php echo $reqId; ?>" data-counter="<?php echo $counter; ?>">
      <div class="text-center" style="padding:70px"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading requisition...</p></div>
    </div>
  </div>
</div>

<div id="thit-req-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title" id="thit-req-modal-title">Confirm action</h4></div>
    <div class="modal-body"><div id="thit-req-modal-message"></div><div id="thit-req-modal-input"></div></div>
    <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal">Cancel</button><button class="btn btn-success" id="thit-req-modal-confirm">Confirm</button></div>
  </div></div>
</div>

<?php require('../../clude/foot.php'); ?>
<script>
(function ($) {
  'use strict';
  var reqId = Number($('#thit-req-view').data('req-id') || 0), pending = null;
  function esc(value) { return $('<div>').text(value == null ? '' : value).html(); }
  function showModal(title, message, input, action) { $('#thit-req-modal-title').text(title); $('#thit-req-modal-message').html('<p>' + message + '</p>'); $('#thit-req-modal-input').html(input || ''); pending = action; $('#thit-req-modal').modal('show'); }
  function loadDetails() {
    $.getJSON('api.php', { action: 'details', req_id: reqId }).done(function (res) {
      if (!res.ok) { $('#thit-req-view').html('<div class="alert alert-danger">' + esc(res.message) + '</div>'); return; }
      var request = res.data.request || {}, items = res.data.items || [], rows = '', stockErrors = 0;
      items.forEach(function (item, index) { var qty = Number(item.store_qty || 0), requested = Number(item.req_qty || 0), enough = qty >= requested; if (!enough) stockErrors++; rows += '<tr><td>' + (index + 1) + '</td><td>' + esc(item.name || ('Item #' + item.item_id)) + '</td><td>' + requested + '</td><td>' + qty + '</td><td>' + (enough ? '<span class="label label-success">STOCK OK</span>' : '<span class="label label-danger">LESS STOCK</span>') + '</td><td><button class="btn btn-success btn-xs thit-req-issue-one" data-item="' + item.item_id + '" data-name="' + esc(item.name) + '" data-qty="' + requested + '"><i class="fa fa-truck"></i> Issue One</button> <button class="btn btn-primary btn-xs thit-req-adjust-stock" data-item="' + item.item_id + '" data-name="' + esc(item.name) + '" data-value="' + qty + '">Adjust Stock</button> <button class="btn btn-info btn-xs thit-req-adjust-reorder" data-item="' + item.item_id + '" data-name="' + esc(item.name) + '"><i class="fa fa-sliders"></i> Adjust Reorder</button></td></tr>'; });
      if (!rows) rows = '<tr><td colspan="6" class="text-center">No pending items remain.</td></tr>';
      var allButton = items.length ? '<button class="btn ' + (stockErrors ? 'btn-warning' : 'btn-success') + ' btn-lg btn-block thit-req-issue-all"><i class="fa fa-check-double"></i> ' + (stockErrors ? 'Issue Available Items' : 'Issue All Items') + '</button>' : '';
      $('#thit-req-view').html('<div class="row"><div class="col-md-8 col-12 order-2 order-md-1"><div class="panel panel-purple"><div class="panel-heading"><h4 style="color:#fff;font-weight:bold">ITEMS TO ISSUE: ' + esc(request.counter_name || request.counter || '') + ' COUNTER | REQUISITION #' + esc(request.req_id) + '</h4></div><div class="panel-body"><div class="row"><div class="col-md-4"><strong>Requested by:</strong> ' + esc(request.requester_name || request.req_user) + '</div><div class="col-md-4"><strong>Shift:</strong> ' + esc(request.req_shift) + '</div><div class="col-md-4"><strong>Items pending:</strong> ' + items.length + '</div></div><hr><div class="table-responsive" style="overflow-x:auto;-webkit-overflow-scrolling:touch"><table class="table table-striped table-bordered table-hover" style="min-width:900px"><thead><tr><th>#</th><th>Item</th><th>Req Qty</th><th>Current Stock</th><th>Status</th><th>Actions</th></tr></thead><tbody>' + rows + '</tbody></table></div></div></div></div><div class="col-md-4 col-12 order-1 order-md-2"><div class="panel panel-inverse"><div class="panel-body">' + allButton + '<a class="btn btn-default btn-lg btn-block" style="margin-top:10px" href="../index.php"><i class="fa fa-arrow-left"></i> Back to THIT</a></div></div><div class="alert alert-info"><h5><i class="fa fa-info-circle"></i> WHAT TO DO</h5><ul><li>Adjust stock when the available quantity is not enough.</li><li>Issue individual items or issue all available items.</li><li>Use the action buttons in the table for quantity changes.</li></ul></div><div class="alert alert-danger"><h5><i class="fa fa-warning"></i> IMPORTANT</h5><p>Confirm each action carefully. Stock changes are saved immediately.</p></div></div></div>');
    }).fail(function () { $('#thit-req-view').html('<div class="alert alert-danger">Could not load requisition details.</div>'); });
  }
  function postInventory(data, onSuccess) { $.post('../../inventory/API/inventory_api.php', data, function (res) { if (!res || !res.ok) { alert(res && res.message ? res.message : 'Action failed.'); return; } onSuccess(res); }, 'json').fail(function () { alert('Could not complete the action.'); }); }
  $(function () {
    loadDetails();
    $(document).on('click', '.thit-req-issue-one', function () { var button = $(this); showModal('Issue Item', 'Confirm issuing <strong>' + esc(button.data('name')) + '</strong>?', '<label>Issue quantity</label><input id="thit-req-qty" type="number" min="0" step="any" class="form-control" value="' + button.data('qty') + '">', function () { postInventory({ action: 'issue_request_item', req_id: reqId, item: button.data('item'), qty: $('#thit-req-qty').val(), portions: $('#thit-req-qty').val() }, loadDetails); }); });
    $(document).on('click', '.thit-req-adjust-stock', function () { var button = $(this); showModal('Adjust Stock', 'Set the store quantity for <strong>' + esc(button.data('name')) + '</strong>.', '<input id="thit-req-qty" type="number" min="0" step="any" class="form-control" value="' + button.data('value') + '"><label>Reason</label><textarea id="thit-req-reason" class="form-control"></textarea>', function () { postInventory({ action: 'adjust_store_qty', item_id: button.data('item'), qty: $('#thit-req-qty').val(), init: button.data('value'), reason: $('#thit-req-reason').val() }, loadDetails); }); });
    $(document).on('click', '.thit-req-adjust-reorder', function () { var button = $(this); showModal('Adjust Reorder Level', 'Set the reorder level for <strong>' + esc(button.data('name')) + '</strong>.', '<input id="thit-req-qty" type="number" min="0" step="any" class="form-control" value="0"><label>Reason</label><textarea id="thit-req-reason" class="form-control"></textarea>', function () { postInventory({ action: 'adjust_reorder_qty', item_id: button.data('item'), qty: $('#thit-req-qty').val(), init: 0, reason: $('#thit-req-reason').val() }, loadDetails); }); });
    $(document).on('click', '.thit-req-issue-all', function () { showModal('Issue All Items', '<strong>Are you sure you want to issue all available items?</strong><br>This will update stock and complete the request.', '', function () { postInventory({ action: 'issue_all_request', req: reqId }, function () { window.location.href = '../index.php'; }); }); });
    $(document).on('click', '#thit-req-modal-confirm', function () { if (!pending) return; var action = pending; pending = null; $('#thit-req-modal').modal('hide'); action(); });
  });
}(jQuery));
</script>
