(function () {
  'use strict';
  var api = 'API/take_api.php', selected = 0;

  function esc(value) { return $('<div>').text(value == null ? '' : value).html(); }
  function call(data) { return $.post(api, data, null, 'json'); }
  function notify(message) { window.alert(message); }
  function loadScopes() {
    var categoryId = $('#scopeType').val() === 'subcategory' ? ($('#scopeValue').data('category-id') || 0) : 0;
    call({ action: 'options', category_id: categoryId }).done(function (r) {
      if (!r.ok) return notify(r.message);
      var type = $('#scopeType').val(), list = type === 'category' ? r.data.categories : (type === 'subcategory' ? r.data.subcategories : r.data.departments);
      $('#scopeValue').html('<option value="0">Select ' + type + '…</option>' + (list || []).map(function (x) { return '<option value="' + x.id + '" data-category-id="' + (x.cat_id || '') + '">' + esc(x.name) + '</option>'; }).join(''));
    });
  }

  function load() {
    call({ action: 'list' }).done(function (r) {
      if (!r.ok) return notify(r.message);
      var rows = r.data.takes || [];
      $('#takeRows').html(rows.length ? rows.map(function (t) {
        var action = t.status === 'SUBMITTED' ? '<a class="btn btn-light" href="review.php?take_id=' + t.take_id + '">Review</a>' :
          (t.status === 'APPROVED_FOR_POSTING' ? '<button class="btn btn-primary post-stock" data-id="' + t.take_id + '">Post Stock</button>' :
          (t.status === 'COMPLETED' ? '<button class="btn btn-light view" data-id="' + t.take_id + '">View</button>' : '<button class="btn btn-light view" data-id="' + t.take_id + '">Open</button>'));
        return '<tr><td><b>' + esc(t.take_code) + '</b></td><td>' + esc(t.scope_type) +
          '</td><td><span class="status">' + esc(t.status === 'APPROVED_FOR_POSTING' ? 'PENDING POSTING' : t.status) + '</span></td><td>' + esc(t.created_at) +
          '</td><td>' + action + '</td></tr>';
      }).join('') : '<tr><td colspan="5">No stock takes yet.</td></tr>');
      $('#statDraft').text(rows.filter(function (t) { return ['DRAFT', 'APPROVED', 'ASSIGNED'].indexOf(t.status) >= 0; }).length);
      $('#statActive').text(rows.filter(function (t) { return ['IN_PROGRESS', 'RETAKE_REQUIRED'].indexOf(t.status) >= 0; }).length);
      $('#statReview').text(rows.filter(function (t) { return ['SUBMITTED', 'APPROVED_FOR_POSTING'].indexOf(t.status) >= 0; }).length);
      $('#statDone').text(rows.filter(function (t) { return t.status === 'COMPLETED'; }).length);
    });
  }

  function nextLine(lines) {
    return lines.filter(function (l) { return parseInt(l.retake_required, 10) === 1; })[0] || lines.filter(function (l) { return l.counted_qty === null || l.counted_qty === ''; })[0] || null;
  }

  function renderTake(t) {
    var lines = t.lines || [], current = nextLine(lines), counted = lines.filter(function (l) { return l.counted_qty !== null && l.counted_qty !== ''; }).length;
    var buttons = '';
    if (t.status === 'DRAFT') buttons += '<button class="btn btn-success do" data-action="approve">Approve</button>';
    if (['APPROVED', 'ASSIGNED'].indexOf(t.status) >= 0) buttons += '<button class="btn btn-primary do" data-action="start">Start counting</button>';
    if (['IN_PROGRESS', 'RETAKE_REQUIRED'].indexOf(t.status) >= 0) buttons += '<button class="btn btn-success do" data-action="submit">Submit for review</button>';
    if (t.status === 'SUBMITTED') buttons += '<button class="btn btn-success do" data-action="review">Approve review</button>';
    if (t.status === 'APPROVED_FOR_POSTING') buttons += '<button class="btn btn-primary do" data-action="post">Post Stock</button>';
    if (t.status === 'COMPLETED') buttons += '<a class="btn btn-light save-pdf" target="_blank" href="../../../front/chaba/render_take2.php?take_id=' + t.take_id + '&report=sheet&format=a4">Save PDF</a>';
    var progress = '<div class="take-progress"><b>' + counted + '</b> of <b>' + lines.length + '</b> counted' + (current ? ' · next item is ready' : ' · counting complete') + '</div>';
    var guided = current ? '<div class="guided-card"><div class="guided-label">NEXT ITEM TO COUNT</div><h3>' + esc(current.item_name) + '</h3><div class="guided-metrics"><span>Expected<strong>' + current.expected_qty + '</strong></span><span>Counted<strong id="guidedVarianceValue">—</strong></span><span>Variance<strong id="guidedVariance">—</strong></span></div><div class="guided-entry"><input id="guidedQty" type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]+)?" placeholder="Enter counted quantity"><button class="btn btn-primary save-next" data-line="' + current.line_id + '">Save &amp; Next</button></div><small>Enter <b>00</b> for zero stock. Save this count and the next uncounted item will load automatically.</small></div>' : '<div class="guided-card guided-complete"><h3>All items have been counted</h3><p>Review the variances below, then submit this take for approval.</p></div>';
    var list = lines.map(function (l, i) {
      var done = l.counted_qty !== null && l.counted_qty !== '';
      return '<tr class="' + (current && current.line_id == l.line_id ? 'current-line' : '') + '"><td>' + (i + 1) + '</td><td>' + esc(l.item_name) + '</td><td>' + l.expected_qty + '</td><td>' + (done ? l.counted_qty : '—') + '</td><td>' + (done ? l.variance_qty : '<span class="line-status">Remaining</span>') + '</td><td><button class="btn btn-light jump-line" data-line="' + l.line_id + '">' + (done ? 'Edit' : 'Count') + '</button> <button class="btn btn-danger retake-line" data-line="' + l.line_id + '">Retake</button></td></tr>';
    }).join('');
    $('#takeDetail').removeClass('empty').html('<div class="detail-top"><div><h3>' + esc(t.take_code) + '</h3><small>' + esc(t.notes || '') + '</small></div><span class="status">' + esc(t.status) + '</span></div><div class="action-row">' + buttons + '<button class="btn btn-light print" data-report="sheet" data-format="a4">Sheet A4</button><button class="btn btn-light print" data-report="sheet" data-format="80mm">Sheet 80mm</button><button class="btn btn-light print" data-report="variance" data-format="a4">Variance A4</button><button class="btn btn-light print" data-report="variance" data-format="80mm">Variance 80mm</button></div>' + progress + guided + '<div class="table-wrap"><table><thead><tr><th>#</th><th>Item</th><th>Expected</th><th>Counted</th><th>Variance</th><th>Actions</th></tr></thead><tbody>' + list + '</tbody></table></div>');
    if (current) $('#guidedQty').trigger('focus');
  }

  function view(id) {
    selected = id;
    call({ action: 'view', take_id: id }).done(function (r) { if (!r.ok) return notify(r.message); if (r.data.take) renderTake(r.data.take); });
  }

  $(document).on('click', '.view', function () { view($(this).data('id')); })
    .on('click', '#refreshBtn', load)
    .on('click', '.save-next', function () {
      var button = $(this), qty = String($('#guidedQty').val() || '').trim();
      if (qty === '' || qty === '0') return notify('Enter 00 to record zero stock.');
      if (!/^[0-9]+(?:\.[0-9]+)?$/.test(qty)) return notify('Enter a valid quantity.');
      call({ action: 'count', take_id: selected, line_id: button.data('line'), qty: qty }).done(function (r) { if (!r.ok) return notify(r.message); view(selected); load(); });
    })
    .on('click', '.jump-line', function () {
      var lineId = $(this).data('line');
      var qty = prompt('Enter counted quantity for this item:');
      qty = qty === null ? null : String(qty).trim();
      if (qty !== null && qty !== '' && qty !== '0' && /^[0-9]+(?:\.[0-9]+)?$/.test(qty)) call({ action: 'count', take_id: selected, line_id: lineId, qty: qty }).done(function (r) { if (!r.ok) return notify(r.message); view(selected); });
      else if (qty !== null) notify('Enter 00 to record zero stock, or enter a valid quantity.');
    })
    .on('click', '.retake-line', function () { call({ action: 'retake', take_id: selected, line_id: $(this).data('line'), reason: 'Operator requested a courtesy retake during stock take.' }).done(function (r) { if (!r.ok) return notify(r.message); view(selected); }); })
    .on('click', '.post-stock', function () { selected = $(this).data('id'); $('#postModal').addClass('open'); })
    .on('click', '[data-close-post]', function () { $('#postModal').removeClass('open'); })
    .on('click', '#confirmPost', function () { var button = $(this); if (!selected) return; button.prop('disabled', true).text('Posting…'); call({ action: 'post', take_id: selected }).done(function (r) { if (!r.ok) { button.prop('disabled', false).text('Yes, post stock'); return notify(r.message); } $('#postModal').removeClass('open'); button.prop('disabled', false).text('Yes, post stock'); load(); view(selected); }); })
    .on('click', '.do', function () { var action = $(this).data('action'); if (action === 'post') { $('#postModal').addClass('open'); return; } var notes = prompt('Notes (optional):', ''); call({ action: action, take_id: selected, notes: notes || '' }).done(function (r) { if (!r.ok) return notify(r.message); view(selected); load(); }); })
    .on('click', '.print', function () { call({ action: 'print', take_id: selected, report: $(this).data('report'), format: $(this).data('format') }).done(function (r) { notify(r.ok ? 'Print job sent to Chaba queue #' + r.data.queue_id : r.message); }); })
    .on('click', '[data-open="initialize"]', function () { $('#initializeModal').addClass('open'); loadScopes(); })
    .on('click', '[data-close]', function () { $('#initializeModal').removeClass('open'); })
    .on('change', '#scopeType', function () { $('#scopeIdWrap').toggleClass('hidden', this.value === 'all'); $('#scopeValue').data('category-id', 0); loadScopes(); })
    .on('change', '#scopeValue', function () { if ($('#scopeType').val() === 'category') { $('#scopeValue').data('category-id', $(this).val()); } })
    .on('submit', '#initializeForm', function (e) { e.preventDefault(); var data = $(this).serializeArray(); data.push({ name: 'action', value: 'initialize' }); call($.param(data)).done(function (r) { if (!r.ok) return notify(r.message); $('#initializeModal').removeClass('open'); $('#initializeForm')[0].reset(); load(); view(r.data.take.take_id); }); });
  $(load);
}());
