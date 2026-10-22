var retState = {
  sale: { rows: [], totals: { count: 0, amount: 0 }, is_draft: false },
  pendingReturns: [],
  voidPromptShown: false
};

function retEscapeHtml(value) {
  return String(value == null ? "" : value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function setPanelHtml(ids, html) {
  for (var i = 0; i < ids.length; i++) {
    var el = document.getElementById(ids[i]);
    if (el) {
      el.innerHTML = html;
      el.style.visibility = "visible";
    }
  }
}

function renderPendingReturns() {
  var rows = retState.pendingReturns || [];
  if (!rows.length) {
    return '<div class="ret-empty">No return items selected yet</div>';
  }

  var html = '<div class="return-list-shell">';
  html += '<div class="return-list-wrap"><table class="table table-striped table-bordered"><thead class="thead-info"><tr><th>ID</th><th>Item</th><th>Qty</th><th>Counter</th><th>Action</th></tr></thead><tbody>';
  for (var i = 0; i < rows.length; i++) {
    var row = rows[i];
    html += '<tr style="color:#373a3c;">';
    html += '<td>' + retEscapeHtml(row.ret_id) + '</td>';
    html += '<td>' + retEscapeHtml(row.item_name) + '</td>';
    html += '<td>' + retEscapeHtml(row.qty) + '</td>';
    html += '<td>' + retEscapeHtml(row.counter_name) + '</td>';
    html += '<td><button type="button" class="ret-del-btn" onclick="ret_del_this(' + Number(row.ret_id) + ')">Delete</button></td>';
    html += '</tr>';
  }
  html += '</tbody></table></div>';
  html += '<div class="ret-finish-bar"><button type="button" class="ret-finish-btn" onclick="openFinishReturn()">Finish</button></div>';
  html += '</div>';
  return html;
}

function renderSaleItems() {
  var sale = retState.sale || {};
  var rows = sale.rows || [];
  var html = '<div class="ret-list-shell">';

  if (sale.is_draft) {
    html += '<div class="ret-list-note">Draft bill items were found in the live sale print. They are shown here for visibility, but returns should be raised after the bill is finalized.</div>';
  }

  if (!rows.length) {
    html += '<div class="ret-empty">No bill items were found for this sale.</div></div>';
    return html;
  }

  html += '<div class="ret-list-table-wrap"><table class="table table-striped table-bordered"><thead class="thead-info"><tr><th>Item</th><th>Qty</th><th>Amount</th><th>Counter</th><th>Return</th></tr></thead><tbody>';
  for (var i = 0; i < rows.length; i++) {
    var row = rows[i];
    html += '<tr style="color:#373a3c;">';
    html += '<td>' + retEscapeHtml(row.item_name) + '</td>';
    html += '<td>' + retEscapeHtml(row.qty) + '</td>';
    html += '<td>' + Number(row.total || 0).toFixed(2) + '</td>';
    html += '<td><span class="ret-counter-badge">' + retEscapeHtml(row.counter_name) + '</span></td>';
    html += '<td>';
    if (sale.is_draft) {
      html += '<span class="ret-return-btn ret-return-disabled">Draft Only</span>';
    } else {
      html += '<button type="button" class="ret-return-btn" data-id="' + Number(row.item_id) + '" data-id2="' + Number(sale_id) + '" data-id3="' + Number(row.counter) + '" id="ret">Return</button>';
    }
    html += '</td></tr>';
  }
  html += '<tr class="ret-list-total" style="color:#373a3c;"><td colspan="2" style="font-size:20px;">Total</td><td style="font-size:20px;">' + Number((sale.totals || {}).amount || 0).toFixed(2) + '</td><td colspan="2"></td></tr>';
  html += '</tbody></table></div></div>';
  return html;
}

function renderRetState(data) {
  retState.sale = data.sale || { rows: [], totals: { count: 0, amount: 0 }, is_draft: false };
  retState.pendingReturns = data.pending_returns || [];
  setPanelHtml(["screen", "screen_mobile"], renderPendingReturns());
  setPanelHtml(["ordscreen", "ordscreen_mobile"], renderSaleItems());
  maybePromptVoid(data.single_item_void || {});
}

function showRetError(message) {
  var html = '<div class="alert alert-danger" style="margin-bottom:0;">' + retEscapeHtml(message || 'Something went wrong.') + '</div>';
  setPanelHtml(["screen", "screen_mobile"], html);
  setPanelHtml(["ordscreen", "ordscreen_mobile"], html);
}

function loadRetPage() {
  setPanelHtml(["screen", "screen_mobile"], '<div class="ret-loading">Loading selected returns...</div>');
  setPanelHtml(["ordscreen", "ordscreen_mobile"], '<div class="ret-loading">Loading sale items...</div>');
  $.getJSON("API/ret_api.php", { action: "bootstrap", sale_id: sale_id })
    .done(function(response) {
      if (!response.ok) {
        showRetError(response.message);
        return;
      }
      renderRetState(response.data || {});
    })
    .fail(function(xhr) {
      showRetError((xhr.responseJSON && xhr.responseJSON.message) || 'Could not load the return page.');
    });
}

function load_item_list() {
  loadRetPage();
}

function load_order_list() {
  loadRetPage();
}

function ret_del_this(id) {
  $.post("API/ret_api.php", { action: "delete_return", id: id })
    .done(function(response) {
      if (response.ok) {
        renderRetState(response.data || {});
      } else {
        alert(response.message || 'Could not remove return item.');
      }
    })
    .fail(function(xhr) {
      alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not remove return item.');
    });
}

function openFinishReturn() {
  $('#finish_return_modal').modal('show');
}

function finishReturnRequest() {
  $.post("API/ret_api.php", { action: "finish_return", sale_id: sale_id })
    .done(function(response) {
      if (!response.ok) {
        alert(response.message || 'Could not finish return.');
        return;
      }
      $('#finish_return_modal').modal('hide');
      window.location.href = (response.data && response.data.redirect) ? response.data.redirect : '../dash/dash.php';
    })
    .fail(function(xhr) {
      alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not finish return.');
    });
}

function maybePromptVoid(voidState) {
  if (retState.voidPromptShown || !voidState || !voidState.should_prompt) {
    return;
  }
  retState.voidPromptShown = true;
  var modalHtml = '';
  modalHtml += '<div class="modal-content ret-void-shell">';
  modalHtml += '<div class="modal-header ret-void-head"><h4 class="modal-title"><b>Void Sale Instead</b></h4></div>';
  modalHtml += '<div class="modal-body ret-void-body">';
  modalHtml += '<p class="ret-void-copy">' + retEscapeHtml(voidState.message || 'This sale has only one item. Void the full sale instead of raising a return.') + '</p>';
  modalHtml += '<p class="ret-void-copy" style="margin-bottom:0;">Go back to sales and void this bill from there so stock and sale flow stay clean.</p>';
  modalHtml += '</div>';
  modalHtml += '<div class="modal-footer ret-void-actions">';
  modalHtml += '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>';
  modalHtml += '<a class="btn btn-success" href="' + retEscapeHtml(voidState.void_url || '../view/my_sales.php') + '">Go To Sales</a>';
  modalHtml += '</div></div>';
  $('#load').html(modalHtml);
  $('#modal').modal('show');
}

function openReturnModal(itemId, counter) {
  $.getJSON("API/ret_api.php", {
    action: "return_modal",
    sale_id: sale_id,
    item_id: itemId,
    counter: counter
  }).done(function(response) {
    if (!response.ok) {
      alert(response.message || 'Could not load return item.');
      return;
    }
    renderReturnModal((response.data || {}).item || {});
  }).fail(function(xhr) {
    alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not load return item.');
  });
}

function renderReturnModal(item) {
  var html = '';
  html += '<div class="modal-content ret-modal-shell">';
  html += '<div class="modal-header ret-modal-head"><h3>Return ' + retEscapeHtml(item.item_name) + ' To ' + retEscapeHtml(item.counter_name) + '</h3></div>';
  html += '<div class="modal-body ret-modal-body">';
  html += '<div class="ret-qty-grid">';
  html += '<div class="ret-qty-card"><label>Ordered</label><input type="text" class="form-control" value="' + retEscapeHtml(item.ordered_qty) + '" disabled></div>';
  html += '<div class="ret-qty-card"><label>Returned</label><input type="number" min="0" step="0.01" id="ret_modal_qty" class="form-control" autofocus></div>';
  html += '</div>';
  html += '<input type="hidden" id="ret_modal_item_id" value="' + Number(item.item_id) + '">';
  html += '<input type="hidden" id="ret_modal_counter" value="' + Number(item.counter) + '">';
  html += '<input type="hidden" id="ret_modal_oqty" value="' + retEscapeHtml(item.ordered_qty) + '">';
  html += '</div>';
  html += '<div class="modal-footer ret-modal-actions">';
  html += '<button type="button" id="close" class="btn ret-modal-btn ret-modal-close" data-dismiss="modal">Close</button>';
  html += '<button type="button" id="btn-ret" class="btn ret-modal-btn ret-modal-submit">Return</button>';
  html += '</div></div>';
  $('#dynamic-content').html(html);
  $('#edit_item').modal('show');
}

function hasPendingReturns() {
  return (retState.pendingReturns || []).length > 0;
}
