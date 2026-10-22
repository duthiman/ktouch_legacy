var returnsState = {
  rows: [],
  requiresCashierApproval: false
};

function returnsEscapeHtml(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function renderReturnsTable() {
  if (!returnsState.rows.length) {
    return '<div class="empty-state">There are no returns waiting right now.</div>';
  }

  var html = '<div class="returns-table-wrap"><table class="table table-striped table-bordered returns-table"><thead><tr><th>Staff</th><th>Sale ID</th><th>Item</th><th>Return Qty</th><th>Counter</th><th>Stage</th><th>Action</th></tr></thead><tbody>';
  for (var i = 0; i < returnsState.rows.length; i++) {
    var row = returnsState.rows[i];
    html += '<tr>';
    html += '<td>' + returnsEscapeHtml(row.staff) + '</td>';
    html += '<td>' + returnsEscapeHtml(row.sale_id) + '</td>';
    html += '<td>' + returnsEscapeHtml(row.item) + '</td>';
    html += '<td>' + returnsEscapeHtml(row.qty) + '</td>';
    html += '<td>' + returnsEscapeHtml(row.counter) + '</td>';
    html += '<td>' + (row.stage === 'cashier' ? 'Cashier' : 'Counter') + '</td>';
    html += '<td class="returns-action-cell">';
    if (row.stage === 'cashier') {
      html += '<button type="button" class="returns-btn confirm-btn" onclick="openConfirmModal(' + Number(row.id) + ')">Cashier Approve</button> ';
      html += '<button type="button" class="returns-btn reject-btn" onclick="openRejectModal(' + Number(row.id) + ')">Reject</button>';
    } else if (row.can_counter_action) {
      html += '<button type="button" class="returns-btn confirm-btn" onclick="openConfirmModal(' + Number(row.id) + ')">Confirm</button> ';
      html += '<button type="button" class="returns-btn reject-btn" onclick="openRejectModal(' + Number(row.id) + ')">Reject</button>';
    } else {
      html += '<span class="returns-btn confirm-btn disabled-btn">Other Counter</span>';
    }
    html += '</td></tr>';
  }
  html += '</tbody></table></div>';
  return html;
}

function applyReturnsState(data) {
  returnsState.rows = data.rows || [];
  returnsState.requiresCashierApproval = !!data.requires_cashier_approval;
  $('#returns_intro').text(
    returnsState.requiresCashierApproval
      ? 'Cashier approval comes first, then the same counter approves stock return.'
      : 'Confirm or reject completed return requests for your counter.'
  );
  var html = renderReturnsTable();
  $('#returns_desktop_body').html(html);
  $('#returns_mobile_body').html(html);
}

function loadReturnsPage() {
  $('#returns_desktop_body').html('<div class="empty-state">Loading returns...</div>');
  $('#returns_mobile_body').html('<div class="empty-state">Loading returns...</div>');
  $.getJSON('../API/ret_api.php', { action: 'approval_list' })
    .done(function(response) {
      if (!response.ok) {
        $('#returns_desktop_body, #returns_mobile_body').html('<div class="empty-state">' + returnsEscapeHtml(response.message || 'Could not load returns.') + '</div>');
        return;
      }
      applyReturnsState(response.data || {});
    })
    .fail(function(xhr) {
      var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not load returns.';
      $('#returns_desktop_body, #returns_mobile_body').html('<div class="empty-state">' + returnsEscapeHtml(message) + '</div>');
    });
}

function renderApprovalModal(modal) {
  var alertClass = modal.mode === 'reject' ? 'alert-danger' : 'alert-warning';
  var actionLabel = modal.mode === 'reject' ? 'Confirm Reject' : 'Confirm';
  var html = '<div class="modal-content" style="border-radius:14px; overflow:hidden;">';
  html += '<div class="modal-header" style="background: linear-gradient(135deg, ' + (modal.mode === 'reject' ? '#7a1717 0%, #b22d2d 52%, #db6b4a 100%' : '#7e2014 0%, #b94828 52%, #e7a33a 100%') + '); color:#fff8ea;">';
  html += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
  html += '<h4 class="modal-title" style="font-weight:900;">' + returnsEscapeHtml(modal.title) + '</h4></div>';
  html += '<div class="modal-body" style="background:#fffdf8;"><div style="overflow-x:auto;"><table class="table table-striped table-bordered" style="margin-bottom:10px;"><thead><tr><th>Bill No.</th><th>Item</th><th>Qty</th><th>Counter</th></tr></thead><tbody><tr>';
  html += '<td>' + returnsEscapeHtml(modal.sale_id) + '</td>';
  html += '<td>' + returnsEscapeHtml(modal.item) + '</td>';
  html += '<td>' + returnsEscapeHtml(modal.qty) + '</td>';
  html += '<td>' + returnsEscapeHtml(modal.counter) + '</td>';
  html += '</tr></tbody></table></div>';
  html += '<div class="alert ' + alertClass + '" style="margin-bottom:0; font-weight:700;">' + returnsEscapeHtml(modal.message) + '</div></div>';
  html += '<div class="modal-footer">';
  html += '<button type="button" class="btn btn-danger" data-dismiss="modal" style="font-weight:bold;">Close</button>';
  html += '<button type="button" class="btn btn-success" style="font-weight:bold;" onclick="submitReturnAction(' + Number(modal.id) + ', \'' + returnsEscapeHtml(modal.stage) + '\', \'' + returnsEscapeHtml(modal.mode) + '\')">' + actionLabel + '</button>';
  html += '</div></div>';
  $('#load').html(html);
  $('#modal').modal('show');
}

function openConfirmModal(id) {
  $.getJSON('../API/ret_api.php', { action: 'approval_modal', id: id, mode: 'confirm' })
    .done(function(response) {
      if (!response.ok) {
        $('#load').html('<div class="modal-content"><div class="modal-body">' + returnsEscapeHtml(response.message || 'Something went wrong, please try again.') + '</div></div>');
        $('#modal').modal('show');
        return;
      }
      renderApprovalModal((response.data || {}).modal || {});
    })
    .fail(function(xhr) {
      var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong, please try again.';
      $('#load').html('<div class="modal-content"><div class="modal-body">' + returnsEscapeHtml(message) + '</div></div>');
      $('#modal').modal('show');
    });
}

function openRejectModal(id) {
  $.getJSON('../API/ret_api.php', { action: 'approval_modal', id: id, mode: 'reject' })
    .done(function(response) {
      if (!response.ok) {
        $('#load').html('<div class="modal-content"><div class="modal-body">' + returnsEscapeHtml(response.message || 'Something went wrong, please try again.') + '</div></div>');
        $('#modal').modal('show');
        return;
      }
      renderApprovalModal((response.data || {}).modal || {});
    })
    .fail(function(xhr) {
      var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong, please try again.';
      $('#load').html('<div class="modal-content"><div class="modal-body">' + returnsEscapeHtml(message) + '</div></div>');
      $('#modal').modal('show');
    });
}

function submitReturnAction(id, stage, mode) {
  var $button = $('#modal .modal-footer .btn-success');
  if (!$button.length || $button.prop('disabled')) return;
  $button.prop('disabled', true).text('Processing...');
  var action = mode === 'reject'
    ? 'reject_return'
    : (stage === 'cashier' ? 'cashier_approve' : 'confirm_return');

  $.post('../API/ret_api.php', { action: action, id: id }, null, 'json')
    .done(function(response) {
      if (!response.ok) {
        $button.prop('disabled', false).text(mode === 'reject' ? 'Reject' : 'Confirm');
        alert(response.message || 'Action failed.');
        return;
      }
      $('#modal').modal('hide');
      if (action === 'confirm_return') {
        var payload = (response.data || {}).approvals || {};
        applyReturnsState(payload);
        var queuedPrints = (response.data || {}).queued_prints || [];
        if (queuedPrints.length) {
          alert(queuedPrints.length === 2
            ? 'Return receipt and updated bill queued for printing.'
            : 'Return receipt queued for printing.');
        }
        return;
      }
      applyReturnsState(response.data || {});
    })
    .fail(function(xhr) {
      $button.prop('disabled', false).text(mode === 'reject' ? 'Reject' : 'Confirm');
      alert((xhr.responseJSON && xhr.responseJSON.message) || 'Action failed.');
    });
}

$(document).ready(function() {
  loadReturnsPage();
});
