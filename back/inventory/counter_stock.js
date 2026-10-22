var inventoryApiUrl = 'API/inventory_api.php';
var counterStockState = {
  counters: [],
  rows: [],
  filteredRows: [],
  categories: [],
  subCategories: [],
  departments: [],
  stockTypes: [],
  selectedCounterId: '',
  requestNo: 0,
  table: null
};

function escapeInventoryHtml(value) {
  return $('<div>').text(value == null ? '' : String(value)).html();
}

function formatCounterQty(value) {
  var numeric = parseFloat(value || 0);
  if (!isFinite(numeric)) {
    numeric = 0;
  }
  if (Math.abs(numeric - Math.round(numeric)) < 0.00001) {
    return String(Math.round(numeric));
  }
  return numeric.toFixed(2);
}

function formatCurrency(value) {
  var numeric = parseFloat(value || 0);
  if (!isFinite(numeric)) {
    numeric = 0;
  }
  return numeric.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function stockTypeBadge(type) {
  var numeric = parseInt(type, 10);
  if (numeric === 1) {
    return '<span class="label label-success">Inventory</span>';
  }
  if (numeric === 3) {
    return '<span class="label label-primary">Service</span>';
  }
  return '<span class="label label-warning">Non Inventory</span>';
}

function populateSelect(selector, items, allLabel, valueKey, labelKey) {
  var html = '<option value="">' + allLabel + '</option>';
  (items || []).forEach(function(item) {
    html += '<option value="' + escapeInventoryHtml(item[valueKey]) + '">' + escapeInventoryHtml(item[labelKey]) + '</option>';
  });
  $(selector).html(html);
}

function populateSubCategoryOptions(selectedCategory) {
  var html = '<option value="">All Sub Categories</option>';
  (counterStockState.subCategories || []).forEach(function(item) {
    if (selectedCategory && String(item.cat_id) !== String(selectedCategory)) {
      return;
    }
    html += '<option value="' + escapeInventoryHtml(item.id) + '">' + escapeInventoryHtml(item.name) + '</option>';
  });
  $('#filter-subcategory').html(html);
}

function buildHeaderMap() {
  var headers = [
    { key: 'item_id', label: 'Item ID' },
    { key: 'name', label: 'Item Name' },
    { key: 'cat_name', label: 'Category' },
    { key: 'sub_name', label: 'Sub Category' },
    { key: 'dpt_label', label: 'Department' },
    { key: 'stock_type_label', label: 'Stock Type' },
    { key: 'total_qty', label: 'Total Qty' },
    { key: 'total_value', label: 'Stock Value' }
  ];

  var selectedCounter = getSelectedCounter();
  if (selectedCounter) {
    headers.push({ key: 'counter_' + selectedCounter.id, label: selectedCounter.name });
    headers.push({ key: 'reorder_' + selectedCounter.id, label: 'Reorder Level' });
  }

  return headers;
}

function getRowValueByHeader(row, header) {
  if (header.key === 'total_qty') {
    return formatCounterQty(row.total_qty);
  }
  if (header.key === 'total_value') {
    return formatCurrency(row.total_value);
  }
  if (header.key.indexOf('counter_') === 0) {
    var counterId = parseInt(header.key.replace('counter_', ''), 10);
    var match = (row.counter_qty || []).find(function(counterRow) {
      return parseInt(counterRow.counter_id, 10) === counterId;
    });
    return formatCounterQty(match ? match.qty : 0);
  }
  if (header.key.indexOf('reorder_') === 0) {
    var reorderCounterId = parseInt(header.key.replace('reorder_', ''), 10);
    var reorderMatch = (row.counter_qty || []).find(function(counterRow) {
      return parseInt(counterRow.counter_id, 10) === reorderCounterId;
    });
    return formatCounterQty(reorderMatch ? reorderMatch.reorder : 0);
  }
  return row[header.key] == null ? '' : row[header.key];
}

function getSelectedCounter() {
  var selectedId = counterStockState.selectedCounterId || $('#filter-counter').val();
  return (counterStockState.counters || []).find(function(counter) {
    return String(counter.id) === String(selectedId);
  }) || null;
}

function getSelectedCounterRow(row) {
  var counter = getSelectedCounter();
  if (!counter) return null;
  // Always resolve the displayed quantity from the selected counter's own
  // payload entry. Cached selected_counter_* values can belong to the
  // previous request when the counter dropdown is changed quickly.
  if (row.counter_stock && Object.prototype.hasOwnProperty.call(row.counter_stock, String(counter.id))) {
    return row.counter_stock[String(counter.id)];
  }
  if (parseInt(row.selected_counter_id, 10) === parseInt(counter.id, 10)) {
    return {
      counter_id: counter.id,
      qty: parseFloat(row.selected_counter_qty || 0),
      value: 0,
      reorder: parseFloat(row.selected_counter_reorder || 0)
    };
  }
  return { counter_id: counter.id, qty: 0, value: 0, reorder: 0 };
}

function renderExportColumnChooser() {
  var html = '';
  buildHeaderMap().forEach(function(header, index) {
    html += '<label><input type="checkbox" data-key="' + escapeInventoryHtml(header.key) + '" data-label="' + escapeInventoryHtml(header.label) + '"' + (index < 8 ? ' checked' : '') + '> <span>' + escapeInventoryHtml(header.label) + '</span></label>';
  });
  $('#counter-stock-export-columns').html(html);
}

function getSelectedExportHeaders() {
  var selected = [];
  $('#counter-stock-export-columns input:checked').each(function() {
    selected.push({
      key: $(this).data('key'),
      label: $(this).data('label')
    });
  });
  return selected;
}

function buildRowsForOutput(headers) {
  return (counterStockState.filteredRows || []).map(function(row) {
    var outputRow = {};
    headers.forEach(function(header) {
      outputRow[header.label] = getRowValueByHeader(row, header);
    });
    return outputRow;
  });
}

function downloadBlob(filename, blob) {
  var url = URL.createObjectURL(blob);
  var link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  setTimeout(function() {
    URL.revokeObjectURL(url);
  }, 1000);
}

function exportCurrentRowsToExcel(headers) {
  var rows = buildRowsForOutput(headers);
  var html = '<table><tr>' + headers.map(function(header) {
    return '<th>' + escapeInventoryHtml(header.label) + '</th>';
  }).join('') + '</tr>';

  rows.forEach(function(row) {
    html += '<tr>';
    headers.forEach(function(header) {
      html += '<td>' + escapeInventoryHtml(row[header.label]) + '</td>';
    });
    html += '</tr>';
  });
  html += '</table>';

  downloadBlob('counter_stock.xls', new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' }));
}

function exportCurrentRowsToPdf(headers) {
  if (typeof pdfMake === 'undefined' || !pdfMake.vfs || !Object.keys(pdfMake.vfs).length) {
    printCurrentRows(headers, 'a4');
    return;
  }
  var body = [headers.map(function(header) { return { text: header.label, style: 'tableHeader' }; })];

  buildRowsForOutput(headers).forEach(function(row) {
    body.push(headers.map(function(header) {
      return String(row[header.label] == null ? '' : row[header.label]);
    }));
  });

  pdfMake.createPdf({
    pageOrientation: 'landscape',
    content: [
      { text: 'Counter Stock Report', style: 'header' },
      { text: 'Generated on ' + new Date().toLocaleString(), margin: [0, 0, 0, 10] },
      { table: { headerRows: 1, body: body }, layout: 'lightHorizontalLines' }
    ],
    styles: {
      header: { fontSize: 16, bold: true },
      tableHeader: { bold: true, fillColor: '#e9eff9' }
    },
    defaultStyle: {
      fontSize: 9
    }
  }).download('counter_stock.pdf');
}

function printCurrentRows(headers, mode) {
  var rows = buildRowsForOutput(headers);
  var isThermal = mode === 'thermal';
  var style = isThermal
    ? 'body{font-family:Arial,sans-serif;font-size:12px;margin:0;padding:8px;}table{width:100%;border-collapse:collapse;}th,td{padding:4px 3px;border-bottom:1px dashed #999;text-align:left;}h2{font-size:16px;margin:0 0 8px;}@page{size:80mm auto;margin:4mm;}'
    : 'body{font-family:Arial,sans-serif;font-size:12px;margin:18px;}table{width:100%;border-collapse:collapse;}th,td{padding:6px 8px;border:1px solid #ccc;text-align:left;}h2{margin:0 0 8px;}@page{size:A4 landscape;margin:10mm;}';
  var html = '<html><head><title>Counter Stock Print</title><style>' + style + '</style></head><body>';
  html += '<h2>Counter Stock Report</h2>';
  html += '<div>Generated on ' + escapeInventoryHtml(new Date().toLocaleString()) + '</div>';
  html += '<table><thead><tr>';
  headers.forEach(function(header) {
    html += '<th>' + escapeInventoryHtml(header.label) + '</th>';
  });
  html += '</tr></thead><tbody>';
  rows.forEach(function(row) {
    html += '<tr>';
    headers.forEach(function(header) {
      html += '<td>' + escapeInventoryHtml(row[header.label]) + '</td>';
    });
    html += '</tr>';
  });
  html += '</tbody></table></body></html>';

  var win = window.open('', '_blank');
  if (!win) {
    return;
  }
  win.document.open();
  win.document.write(html);
  win.document.close();
  win.focus();
  setTimeout(function() {
    win.print();
  }, 250);
}

function buildCounterStockHead() {
  var counter = getSelectedCounter();
  if (!counter) return '<tr><th>Item ID</th><th>Item Name</th></tr>';
  var html = '<tr><th>Item ID</th><th>Item Name</th><th>Category</th><th>Sub Category</th><th>Department</th><th>Stock Type</th><th>' + escapeInventoryHtml(counter.name) + ' Stock</th><th>Reorder Level</th><th>Status</th></tr>';
  return html;
}

function buildCounterStockBody(rows) {
  if (!rows || !rows.length) {
    return '<tr><td colspan="9" class="text-center">No counter stock items found.</td></tr>';
  }

  var html = '';
  rows.forEach(function(row) {
    html += '<tr>';
    html += '<td>' + escapeInventoryHtml(row.item_id) + '</td>';
    html += '<td>' + escapeInventoryHtml(row.name) + '</td>';
    html += '<td>' + escapeInventoryHtml(row.cat_name) + '</td>';
    html += '<td>' + escapeInventoryHtml(row.sub_name || '-') + '</td>';
    html += '<td>' + escapeInventoryHtml(row.dpt_label) + '</td>';
    html += '<td>' + stockTypeBadge(row.stock_type) + '</td>';
    var counterRow = getSelectedCounterRow(row);
    var qty = parseFloat(counterRow.qty || 0);
    var reorder = parseFloat(counterRow.reorder || 0);
    var status = qty <= 0 ? '<span class="label label-danger">Out of stock</span>' : (qty <= reorder ? '<span class="label label-warning">Below reorder</span>' : '<span class="label label-success">Healthy</span>');
    html += '<td><strong>' + escapeInventoryHtml(formatCounterQty(qty)) + '</strong></td>';
    html += '<td><input type="number" min="0" step="1" class="counter-reorder-edit" data-item-id="' + escapeInventoryHtml(row.item_id) + '" data-counter-id="' + escapeInventoryHtml(counterRow.counter_id) + '" data-old="' + escapeInventoryHtml(reorder) + '" value="' + escapeInventoryHtml(reorder) + '"></td>';
    html += '<td>' + status + '</td>';
    html += '</tr>';
  });
  return html;
}

function renderCards() {
  var rows = counterStockState.filteredRows || [];
  var totalQty = rows.reduce(function(sum, row) { return sum + parseFloat(row.total_qty || 0); }, 0);
  var totalValue = rows.reduce(function(sum, row) { return sum + parseFloat(row.total_value || 0); }, 0);
  var html = '';

  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Visible Items</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(rows.length) + '</span><span class="counter-stock-card-details">Items in the current filter view</span></div></div>';
  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Total Counter Qty</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(formatCounterQty(totalQty)) + '</span><span class="counter-stock-card-details">Combined quantity across all visible counters</span></div></div>';
  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Total Stock Value</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(formatCurrency(totalValue)) + '</span><span class="counter-stock-card-details">Using current stored cost price</span></div></div>';

  var topCounter = { name: 'None', value: 0, qty: 0 };
  (counterStockState.counters || []).forEach(function(counter) {
    var counterQty = 0;
    var counterValue = 0;
    rows.forEach(function(row) {
      (row.counter_qty || []).forEach(function(counterRow) {
        if (parseInt(counterRow.counter_id, 10) !== parseInt(counter.id, 10)) {
          return;
        }
        counterQty += parseFloat(counterRow.qty || 0);
        counterValue += parseFloat(counterRow.value || 0);
      });
    });
    if (counterValue > topCounter.value) {
      topCounter = { name: counter.name, value: counterValue, qty: counterQty };
    }
  });

  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Highest Value Counter</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(topCounter.name) + '</span><span class="counter-stock-card-details">' + escapeInventoryHtml(formatCurrency(topCounter.value)) + ' value | ' + escapeInventoryHtml(formatCounterQty(topCounter.qty)) + ' qty</span></div></div>';

  (counterStockState.counters || []).forEach(function(counter) {
    var itemCount = 0;
    var qty = 0;
    var value = 0;

    rows.forEach(function(row) {
      (row.counter_qty || []).forEach(function(counterRow) {
        if (parseInt(counterRow.counter_id, 10) !== parseInt(counter.id, 10)) {
          return;
        }
        qty += parseFloat(counterRow.qty || 0);
        value += parseFloat(counterRow.value || 0);
        if (parseFloat(counterRow.qty || 0) !== 0) {
          itemCount++;
        }
      });
    });

    html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">' + escapeInventoryHtml(counter.name) + '</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(formatCounterQty(qty)) + '</span><span class="counter-stock-card-details">' + escapeInventoryHtml(itemCount) + ' items | Value ' + escapeInventoryHtml(formatCurrency(value)) + '</span></div></div>';
  });

  $('#counter-stock-cards').html(html);
}

function renderCards() {
  var rows = counterStockState.filteredRows || [];
  var counter = getSelectedCounter();
  var totalQty = rows.reduce(function(sum, row) {
    var selected = getSelectedCounterRow(row);
    return sum + parseFloat(selected ? selected.qty || 0 : 0);
  }, 0);
  var totalValue = rows.reduce(function(sum, row) {
    var selected = getSelectedCounterRow(row);
    return sum + parseFloat(selected ? selected.value || 0 : 0);
  }, 0);
  var name = counter ? counter.name : 'No Counter Selected';
  var html = '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Selected Counter</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(name) + '</span><span class="counter-stock-card-details">Only this counter is displayed</span></div></div>';
  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Visible Items</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(rows.length) + '</span><span class="counter-stock-card-details">Items in the current filter view</span></div></div>';
  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Counter Quantity</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(formatCounterQty(totalQty)) + '</span><span class="counter-stock-card-details">Current selected counter stock</span></div></div>';
  html += '<div class="col-md-2 col-sm-6"><div class="counter-stock-card"><h4 class="counter-stock-card-title">Stock Value</h4><span class="counter-stock-card-number">' + escapeInventoryHtml(formatCurrency(totalValue)) + '</span><span class="counter-stock-card-details">Selected counter value</span></div></div>';
  $('#counter-stock-cards').html(html);
}

function applyFilters() {
  counterStockState.selectedCounterId = $('#filter-counter').val();
  var category = $('#filter-category').val();
  var subCategory = $('#filter-subcategory').val();
  var stockType = $('#filter-stock-type').val();
  var department = $('#filter-department').val();

  counterStockState.filteredRows = (counterStockState.rows || []).filter(function(row) {
    return !!counterStockState.selectedCounterId && !!getSelectedCounterRow(row);
  });

  renderSimpleCounterReport();

  renderCards();
}

function renderSimpleCounterReport() {
  var container = $('#counter-stock-report');
  if (!container.length) return;
  var counter = getSelectedCounter();
  if (!counter) {
    container.html('<div class="text-center">Select a counter.</div>');
    return;
  }
  var rows = counterStockState.filteredRows || [];
  if (!rows.length) {
    container.html('<div class="text-center">No stock found for ' + escapeInventoryHtml(counter.name) + '.</div>');
    return;
  }
  var html = '<table class="table table-bordered table-hover counter-stock-simple-table"><thead><tr>'
    + '<th>Item ID</th><th>Item</th><th>Category</th><th>Sub Category</th><th>Department</th>'
    + '<th>Stock Type</th><th>' + escapeInventoryHtml(counter.name) + ' Stock</th><th>Reorder Level</th><th>Status</th>'
    + '</tr></thead><tbody>';
  rows.forEach(function(row) {
    var selected = getSelectedCounterRow(row) || { qty: 0, reorder: 0, counter_id: counter.id };
    var qty = parseFloat(selected.qty || 0);
    var reorder = parseFloat(selected.reorder || 0);
    var status = qty <= 0 ? '<span class="label label-danger">Out of stock</span>'
      : (qty <= reorder ? '<span class="label label-warning">Below reorder</span>' : '<span class="label label-success">Healthy</span>');
    html += '<tr><td>' + escapeInventoryHtml(row.item_id) + '</td>'
      + '<td>' + escapeInventoryHtml(row.name) + '</td>'
      + '<td>' + escapeInventoryHtml(row.cat_name) + '</td>'
      + '<td>' + escapeInventoryHtml(row.sub_name || '-') + '</td>'
      + '<td>' + escapeInventoryHtml(row.dpt_label) + '</td>'
      + '<td>' + stockTypeBadge(row.stock_type) + '</td>'
      + '<td><strong>' + escapeInventoryHtml(formatCounterQty(qty)) + '</strong></td>'
      + '<td>' + escapeInventoryHtml(formatCounterQty(reorder)) + '</td>'
      + '<td>' + status + '</td></tr>';
  });
  html += '</tbody></table>';
  container.html(html);
}

function loadCounterStockTable(counterId) {
  var requestNo = ++counterStockState.requestNo;
  var previousCounterId = counterStockState.selectedCounterId || $('#filter-counter').val();
  var requestedCounterId = counterId || previousCounterId || '';
  $.ajax({
    url: inventoryApiUrl,
    type: 'GET',
    cache: false,
    dataType: 'json',
    data: {
      action: 'counter_stock_bootstrap',
      counter_id: requestedCounterId,
      category_id: $('#filter-category').val() || '',
      subcategory_id: $('#filter-subcategory').val() || '',
      stock_type: $('#filter-stock-type').val() || '',
      department_id: $('#filter-department').val() || ''
    }
  })
  .done(function(res) {
    var data = (res && res.data) ? res.data : {};
    counterStockState.counters = (data.counters || []).map(function(counter) {
      return {
        id: counter.id != null ? counter.id : counter.counter_id,
        name: counter.name != null ? counter.name : counter.counter_name,
        qty_column: counter.qty_column || ''
      };
    });
    counterStockState.rows = data.rows || [];
    counterStockState.filteredRows = data.rows || [];
    counterStockState.categories = data.categories || [];
    counterStockState.subCategories = data.sub_categories || [];
    counterStockState.departments = data.departments || [];
    counterStockState.stockTypes = data.stock_types || [];

    populateSelect('#filter-counter', counterStockState.counters, 'Select Counter', 'id', 'name');
    if (counterStockState.counters.length) {
      var counterStillExists = counterStockState.counters.some(function(counter) {
        return String(counter.id) === String(previousCounterId);
      });
      counterStockState.selectedCounterId = counterStillExists ? String(previousCounterId) : String(counterStockState.counters[0].id);
      $('#filter-counter').val(counterStockState.selectedCounterId);
    }
    if (!requestedCounterId && counterStockState.selectedCounterId) {
      loadCounterStockTable(counterStockState.selectedCounterId);
      return;
    }
    populateSelect('#filter-category', counterStockState.categories, 'All Categories', 'id', 'name');
    populateSubCategoryOptions('');
    populateSelect('#filter-stock-type', counterStockState.stockTypes, 'All Stock Types', 'id', 'name');
    populateSelect('#filter-department', counterStockState.departments, 'All Departments', 'id', 'name');
    renderExportColumnChooser();
    applyFilters();
  })
  .fail(function() {
    $('#counter-stock-head').html('<tr><th>Counter Stock</th></tr>');
    $('#counter-stock-body').html('<tr><td class="text-center">Could not load counter stock right now.</td></tr>');
  });
}

$(document).on('change', '#filter-category', function() {
  populateSubCategoryOptions($(this).val());
  $('#filter-subcategory').val('');
  loadCounterStockTable(counterStockState.selectedCounterId);
});

$(document).on('change', '#filter-counter', function() {
  counterStockState.selectedCounterId = $(this).val();
  loadCounterStockTable(counterStockState.selectedCounterId);
});

$(document).on('change', '#filter-subcategory, #filter-stock-type, #filter-department', function() {
  loadCounterStockTable(counterStockState.selectedCounterId);
});

$(document).on('click', '#apply-counter-stock-filters', function() {
  loadCounterStockTable(counterStockState.selectedCounterId);
});

$(document).on('click', '#clear-counter-stock-filters', function() {
  $('#filter-counter').val(counterStockState.counters.length ? String(counterStockState.counters[0].id) : '');
  counterStockState.selectedCounterId = $('#filter-counter').val();
  $('#filter-category').val('');
  populateSubCategoryOptions('');
  $('#filter-subcategory').val('');
  $('#filter-stock-type').val('');
  $('#filter-department').val('');
  loadCounterStockTable(counterStockState.selectedCounterId);
});

var pendingReorderInput = null;

$(document).on('blur', '.counter-reorder-edit', function() {
  var input = $(this);
  var oldValue = parseFloat(input.attr('data-old')) || 0;
  var newValue = parseFloat(input.val());
  if (!isFinite(newValue) || newValue < 0) {
    input.val(oldValue);
    return;
  }
  if (newValue === oldValue) {
    input.val(formatCounterQty(oldValue));
    return;
  }
  pendingReorderInput = input;
  var row = input.closest('tr');
  $('#counter-reorder-item-id').val(input.data('item-id'));
  $('#counter-reorder-counter-id').val(input.data('counter-id'));
  $('#counter-reorder-old').val(oldValue);
  $('#counter-reorder-new').val(newValue);
  $('#counter-reorder-item-label').text($.trim(row.find('td').eq(1).text()) + ' — ' + (getSelectedCounter() ? getSelectedCounter().name : 'Counter'));
  $('#counter-reorder-change-message').html('Change reorder level from <strong>' + escapeInventoryHtml(formatCounterQty(oldValue)) + '</strong> to <strong>' + escapeInventoryHtml(formatCounterQty(newValue)) + '</strong>?');
  $('#counter-reorder-modal').modal('show');
});

$(document).on('click', '#save-counter-reorder', function() {
  var button = $(this);
  var qty = parseFloat($('#counter-reorder-new').val());
  if (!isFinite(qty) || qty < 0) {
    alert('Enter a valid reorder level.');
    return;
  }
  button.prop('disabled', true).text('Saving...');
  $.ajax({
    url: inventoryApiUrl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'adjust_counter_reorder_qty',
      item_id: $('#counter-reorder-item-id').val(),
      counter_id: $('#counter-reorder-counter-id').val(),
      qty: qty,
      reason: 'Counter stock reorder level'
    }
  }).done(function(res) {
    if (requestNo !== counterStockState.requestNo) return;
    if (res && res.ok) {
      if (pendingReorderInput) {
        pendingReorderInput.attr('data-old', qty).val(formatCounterQty(qty));
      }
      $('#counter-reorder-modal').modal('hide');
      loadCounterStockTable();
      return;
    }
    alert(res && res.message ? res.message : 'Could not save reorder level.');
  }).fail(function(xhr) {
    var res = xhr.responseJSON;
    alert(res && res.message ? res.message : 'Could not save reorder level.');
  }).always(function() {
    button.prop('disabled', false).text('Confirm Change');
  });
});

$('#counter-reorder-modal').on('hidden.bs.modal', function() {
  if (pendingReorderInput) {
    pendingReorderInput.val(pendingReorderInput.attr('data-old'));
    pendingReorderInput = null;
  }
});

$(document).on('click', '#counter-stock-export-excel, #counter-stock-export-pdf, #counter-stock-print-a4, #counter-stock-print-thermal', function() {
  $('#counter-stock-export-modal').modal('show');
});

$(document).on('click', '#counter-stock-run-excel', function() {
  exportCurrentRowsToExcel(getSelectedExportHeaders());
  $('#counter-stock-export-modal').modal('hide');
});

$(document).on('click', '#counter-stock-run-pdf', function() {
  exportCurrentRowsToPdf(getSelectedExportHeaders());
  $('#counter-stock-export-modal').modal('hide');
});

$(document).on('click', '#counter-stock-run-a4', function() {
  printCurrentRows(getSelectedExportHeaders(), 'a4');
  $('#counter-stock-export-modal').modal('hide');
});

$(document).on('click', '#counter-stock-run-thermal', function() {
  printCurrentRows(getSelectedExportHeaders(), 'thermal');
  $('#counter-stock-export-modal').modal('hide');
});

$(document).ready(function() {
  loadCounterStockTable();
});
