var menuBootstrapCache = null;
var menuBootstrapPromise = null;
window.menuCounterAssignmentMode = 'assigned';

function loadMenuBootstrap(forceReload) {
  if (!forceReload && menuBootstrapCache) {
    return $.Deferred().resolve(menuBootstrapCache).promise();
  }
  if (!forceReload && menuBootstrapPromise) {
    return menuBootstrapPromise;
  }
  menuBootstrapPromise = $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'bootstrap' }
  }).then(function(res) {
    var data = (res && res.data) ? res.data : {};
    menuBootstrapCache = {
      categories: data.categories || [],
      subCategories: data.sub_categories || [],
      taxes: data.taxes || [],
      stockItems: data.stock_items || [],
      counters: data.counters || []
      ,uoms: data.uoms || []
    };
    return menuBootstrapCache;
  }).always(function() {
    menuBootstrapPromise = null;
  });
  return menuBootstrapPromise;
}

function menuCategoryName(catId) {
  var id = parseInt(catId, 10);
  var found = (menuBootstrapCache && menuBootstrapCache.categories || []).find(function(category) {
    return parseInt(category.id, 10) === id;
  });
  return found ? found.name : '';
}

function menuSubCategoryName(subId) {
  var id = parseInt(subId, 10);
  var found = (menuBootstrapCache && menuBootstrapCache.subCategories || []).find(function(subCategory) {
    return parseInt(subCategory.id, 10) === id;
  });
  return found ? found.name : '';
}

function menuTaxLabelByObject(tax) {
  return $.trim((tax.name || '') + ' ' + (tax.percent || 0) + '%');
}

function menuTaxLabel(taxId) {
  var selected = parseInt(taxId || 0, 10);
  var found = null;
  (menuBootstrapCache && menuBootstrapCache.taxes || []).forEach(function(tax) {
    if (parseInt(tax.id, 10) === selected) {
      found = tax;
    }
  });
  return found ? menuTaxLabelByObject(found) : 'SET VAT';
}

function menuTaxOptions(selectedId, placeholder) {
  var selected = parseInt(selectedId, 10);
  var options = '<option value="0">' + (placeholder || '---SELECT TAX----') + '</option>';
  (menuBootstrapCache && menuBootstrapCache.taxes || []).forEach(function(tax) {
    var taxId = parseInt(tax.id, 10);
    options += '<option value="' + taxId + '"' + (taxId === selected ? ' selected' : '') + '>' +
      $('<div>').text(menuTaxLabelByObject(tax)).html() + '</option>';
  });
  return options;
}

function menuBooleanHtml(value, itemId, columnName) {
  var numeric = parseInt(value, 10);
  if (columnName === 'stock') {
    var stockLabels = {
      1: '<span>INVENTORY</span>',
      2: '<span>NON INVENTORY</span>',
      3: '<span>SERVICE</span>'
    };
    var stockNext = numeric === 1 ? 2 : 1;
    return '<a href="javascript:;" class="btn btn-outline-warning btn-xs menu-action-btn menu-inline-toggle" data-id="' + itemId + '" data-column="' + columnName + '" data-next="' + stockNext + '">' +
      (stockLabels[numeric] || stockLabels[2]) + '</a>';
  }
  if (columnName === 'dpt') {
    var deptLabels = {
      1: '<span>BAR</span>',
      2: '<span>KITCHEN</span>'
    };
    numeric = numeric === 1 ? 1 : 2;
    return '<a href="javascript:;" class="btn btn-outline-warning btn-xs menu-action-btn menu-inline-toggle" data-id="' + itemId + '" data-column="' + columnName + '" data-next="' + (numeric === 1 ? 2 : 1) + '">' +
      (deptLabels[numeric] || '<span class="label label-default">UNKNOWN</span>') + '</a>';
  }
  numeric = numeric === 1 ? 1 : 2;
  return '<a href="javascript:;" class="btn btn-outline-success btn-xs menu-action-btn menu-inline-toggle" data-id="' + itemId + '" data-column="' + columnName + '" data-next="' + (numeric === 1 ? 2 : 1) + '">' +
    (numeric === 1 ? '<span>YES</span>' : '<span>NO</span>') + '</a>';
}

function renderMenuTaxCell(taxId, itemId) {
  return '<a href="javascript:;" class="menu-tax-launch menu-action-link" data-id="' + itemId + '" data-tax="' + (taxId || 0) + '">' +
    '<span class="label label-info menu-pill menu-pill-tax">' + $('<div>').text(menuTaxLabel(taxId)).html() + '</span>' +
    '</a>';
}

function renderMenuCategoryCell(catId, itemId) {
  var label = menuCategoryName(catId) || 'SELECT CATEGORY';
  return '<a href="javascript:;" class="menu-category-launch menu-action-link" data-id="' + itemId + '" data-cat="' + (catId || 0) + '">' +
    '<span class="label label-primary menu-pill menu-pill-category">' + $('<div>').text(label).html() + '</span>' +
    '</a>';
}

function renderMenuSubCategoryCell(subId, itemId, catId) {
  var label = menuSubCategoryName(subId) || 'SELECT SUB CATEGORY';
  return '<a href="javascript:;" class="menu-subcategory-launch menu-action-link" data-id="' + itemId + '" data-sub="' + (subId || 0) + '" data-cat="' + (catId || 0) + '">' +
    '<span class="label label-primary menu-pill menu-pill-subcategory">' + $('<div>').text(label).html() + '</span>' +
    '</a>';
}

function renderMenuRecipeCell(recipe, itemId) {
  var isSet = parseInt(recipe, 10) === 1;
  return '<a href="javascript:;" class="menu-recipe-launch menu-action-link" data-id="' + itemId + '">' +
    '<span class="label label-' + (isSet ? 'success' : 'danger') + ' menu-pill menu-pill-recipe">' + (isSet ? 'RECIPE SET' : 'SET RECIPE') + '</span>' +
    '</a>';
}

function renderMenuPortionCell(portion, itemId) {
  var isSet = parseInt(portion, 10) === 1;
  return '<a href="javascript:;" class="menu-portion-launch menu-action-link" data-id="' + itemId + '">' +
    '<span class="label label-' + (isSet ? 'success' : 'warning') + ' menu-pill menu-pill-portion">' + (isSet ? 'PORTIONED' : 'SET PORTION') + '</span>' +
    '</a>';
}

function renderMenuTotsCell(tots, itemId) {
  var isSet = parseInt(tots, 10) === 1;
  return '<a href="javascript:;" class="menu-tots-launch menu-action-link" data-id="' + itemId + '">' +
    '<span class="label label-' + (isSet ? 'success' : 'warning') + ' menu-pill menu-pill-tots">' + (isSet ? 'TOTS SET' : 'SET TOTS') + '</span>' +
    '</a>';
}

function renderStockTakeTypeCell(stockTakeType, itemId) {
  var label = parseInt(stockTakeType, 10) === 1 ? 'WEIGHT' : 'UNITS';
  return '<a href="javascript:;" class="menu-stocktake-launch menu-action-link" data-id="' + itemId + '">' +
    '<span class="label label-' + (parseInt(stockTakeType, 10) === 1 ? 'warning' : 'success') + ' menu-pill menu-pill-stocktake">' + label + '</span>' +
    '</a>';
}

function renderMenuUomCell(uomId, itemId) {
  var selected = (menuBootstrapCache && menuBootstrapCache.uoms || []).find(function (uom) {
    return parseInt(uom.id, 10) === parseInt(uomId, 10);
  });
  var label = selected ? selected.name : 'SET UOM';
  return '<a href="javascript:;" class="menu-units-launch menu-action-link" data-id="' + itemId + '"><span class="label label-info menu-pill menu-pill-tax">' + $('<div>').text(label).html() + '</span></a>';
}

function renderMenuActionsCell(itemId) {
  return '<div class="dropdown menu-row-actions">' +
    '<button type="button" class="btn btn-default btn-xs w-100 menu-more-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
      '<i class="fa fa-ellipsis-v"></i> Actions <i class="fa fa-angle-down"></i></button>' +
    '<ul class="dropdown-menu dropdown-menu-right">' +
      '<li><a href="javascript:;" class="menu-view-stocks" data-id="' + itemId + '"><i class="fa fa-cubes"></i> View Stocks</a></li>' +
      '<li><a href="javascript:;" class="menu-counter-assign" data-id="' + itemId + '"><i class="fa fa-building"></i> Assign Counters</a></li>' +
      '<li><a href="javascript:;" class="menu-butchery-toggle" data-id="' + itemId + '"><i class="fa fa-cutlery"></i> Butchery: toggle</a></li>' +
      '<li class="divider"></li>' +
      '<li><a href="javascript:;" class="menu-delete-item text-danger" data-id="' + itemId + '"><i class="fa fa-trash"></i> Delete Item</a></li>' +
    '</ul>' +
  '</div>';
}

function fillSelectOptions(selector, items, placeholder, valueKey, labelBuilder, selectedId) {
  var selected = parseInt(selectedId || 0, 10);
  var html = '<option value="0">' + placeholder + '</option>';
  (items || []).forEach(function(item) {
    var itemId = parseInt(item[valueKey], 10);
    html += '<option value="' + itemId + '"' + (itemId === selected ? ' selected' : '') + '>' +
      $('<div>').text(labelBuilder(item)).html() +
      '</option>';
  });
  $(selector).html(html).trigger('change.select2');
}

function fillSubCategoryOptions(selector, catId, selectedId) {
  var selectedCat = parseInt(catId, 10);
  var selectedSub = parseInt(selectedId || 0, 10);
  var html = '<option value="0">---SELECT A SUB CATEGORY----</option>';
  (menuBootstrapCache && menuBootstrapCache.subCategories || []).forEach(function(subCategory) {
    var subCatId = parseInt(subCategory.cat_id, 10);
    var subId = parseInt(subCategory.id, 10);
    if (selectedCat > 0 && subCatId !== selectedCat) {
      return;
    }
    html += '<option value="' + subId + '"' + (subId === selectedSub ? ' selected' : '') + '>' +
      $('<div>').text(subCategory.name).html() +
      '</option>';
  });
  $(selector).html(html).trigger('change.select2');
}

function hydrateMenuQuickForm() {
  fillSelectOptions('#cat_id', menuBootstrapCache.categories, '---SELECT DEPARTMENT----', 'id', function(category) {
    return category.name;
  });
  fillSelectOptions('#tax_id', menuBootstrapCache.taxes, '---SELECT TAX----', 'id', function(tax) {
    return menuTaxLabelByObject(tax);
  });
  fillSelectOptions('#uom', menuBootstrapCache.uoms, '---SELECT UNIT OF MEASURE----', 'id', function(uom) {
    return uom.name;
  });
  fillSubCategoryOptions('#sub_id', $('#cat_id').val(), 0);
}

function hydrateCounterFilter() {
  var html = '<option value="0">--All Counters--</option>';
  (menuBootstrapCache && menuBootstrapCache.counters || []).forEach(function(counter) {
    html += '<option value="' + parseInt(counter.id, 10) + '">' + $('<div>').text(counter.name).html() + '</option>';
  });
  $('#counter_id').html(html).trigger('change.select2');
}

function updateCounterAssignmentTabs() {
  var hasCounter = parseInt($('#counter_id').val(), 10) > 0;
  $('.counter-assignment-tabs-wrap').toggle(hasCounter);
  $('.counter-assignment-tab').each(function() {
    var active = $(this).data('assignment-mode') === (window.menuCounterAssignmentMode || 'assigned');
    $(this).toggleClass('active btn-primary', active).toggleClass('btn-default', !active);
  });
}

function refreshBooleanCell(id, columnName, value) {
  var dt = getMenuTable();
  var cellIndexMap = { dpt: 5, menu: 9, deci: 10, stock: 11, recipe: 12, portion: 13, tots: 14 };
  if (!dt || cellIndexMap[columnName] === undefined) {
    return;
  }
  var rowNode = dt.row('.row' + id).node();
  if (!rowNode) {
    return;
  }
  $(rowNode).find('td:eq(' + cellIndexMap[columnName] + ')').html(menuBooleanHtml(value, id, columnName));
}

function refreshTaxCell(id, taxId) {
  var dt = getMenuTable();
  if (!dt) {
    return;
  }
  var rowNode = dt.row('.row' + id).node();
  if (!rowNode) {
    return;
  }
  $(rowNode).find('td:eq(16)').html(renderMenuTaxCell(taxId, id));
}

function showRecords() {
  $('.act_btn').hide();
  $('#data-table-combine').DataTable({
    "processing": true,
    "serverSide": true,
    "bDestroy": true,
    "dom": 'rt<"bottom"ip><"clear">',
    "ajax": {
      "url": menuApiUrl,
      "type": "GET",
      "data": function(d) {
        d.action = 'datatable';
        d.cat_id = $('#cat_id').val() || 0;
        d.sub_id = $('#sub_id').val() || 0;
        d.dpt = $('#dpt').val() || 0;
        d.stock = $('#stock').val() || 0;
        d.counter_id = $('#counter_id').val() || 0;
        d.counter_assignment = window.menuCounterAssignmentMode || 'assigned';
      }
    },
    "columns": [
      { data: null, defaultContent: '<input type="checkbox" name="items[]" onclick="item_select()" class="bulk" />' },
      { "class": "item_id", "data": "id" },
      { "class": "name", "data": "name" },
      { "class": "cat", "data": "cat_id" },
      { "class": "sub", "data": "sub_id" },
      { "class": "dpt", "data": "dpt" },
      { "class": "bprice", "data": "bprice" },
      { "class": "price", "data": "price" },
      { "class": "hhr", "data": "hhr" },
      { "class": "menu", "data": "menu" },
      { "class": "deci", "data": "deci" },
      { "class": "stock", "data": "stock" },
      { "class": "recipe", "data": "recipe" },
      { "class": "portion", "data": "portion" },
      { "class": "tots", "data": "tots" },
      { "class": "stock_take_type", "data": "stock_take_type" },
      { "class": "uom", "data": "uom_id" },
      { "class": "tax", "data": "tax" },
      { "class": "actions", "data": null, "defaultContent": '<button type="button" class="menu-more-btn">•••</button>', "searchable": false, "orderable": false }
    ],
    columnDefs: [
      { "targets": [0], "searchable": false, "orderable": false, "visible": true }
    ],
    createdRow: function(row) {
      var itemId = $(row).find('td:eq(1)').html();
      $(row).find('td:eq(0) input.bulk').val(itemId);
      var catId = $(row).find('td:eq(3)').html();
      var subId = $(row).find('td:eq(4)').html();
      var dpt = $(row).find('td:eq(5)').html();
      var menu = $(row).find('td:eq(9)').html();
      var deci = $(row).find('td:eq(10)').html();
      var stock = $(row).find('td:eq(11)').html();
      var recipe = $(row).find('td:eq(12)').html();
      var portion = $(row).find('td:eq(13)').html();
      var tots = $(row).find('td:eq(14)').html();
      var stockTakeType = $.trim($(row).find('td:eq(15)').html());
      var uomId = $(row).find('td:eq(16)').html();
      var taxId = $(row).find('td:eq(17)').html();

      $(row).find('td:eq(3)').html(renderMenuCategoryCell(catId, itemId));
      $(row).find('td:eq(4)').html(renderMenuSubCategoryCell(subId, itemId, catId));
      $(row).find('td:eq(5)').html(menuBooleanHtml(dpt, itemId, 'dpt'));
      $(row).find('td:eq(9)').html(menuBooleanHtml(menu, itemId, 'menu'));
      $(row).find('td:eq(10)').html(menuBooleanHtml(deci, itemId, 'deci'));
      $(row).find('td:eq(11)').html(menuBooleanHtml(stock, itemId, 'stock'));
      $(row).find('td:eq(12)').html(renderMenuRecipeCell(recipe, itemId));
      $(row).find('td:eq(13)').html(renderMenuPortionCell(portion, itemId));
      $(row).find('td:eq(14)').html(renderMenuTotsCell(tots, itemId));
      $(row).find('td:eq(16)').html(renderMenuTaxCell(taxId, itemId));

      $(row).find('td:eq(15)').html(renderStockTakeTypeCell(stockTakeType, itemId));
      $(row).find('td:eq(16)').html(renderMenuUomCell(uomId, itemId));
      $(row).find('td:eq(17)').html(renderMenuTaxCell(taxId, itemId));
      $(row).find('td:eq(18)').html(renderMenuActionsCell(itemId));

      $(row).find('td:eq(2)').attr('contenteditable', "true").attr('data-id1', itemId);
      $(row).find('td:eq(6)').attr('contenteditable', "true").attr('data-id1', itemId);
      $(row).find('td:eq(7)').attr('contenteditable', "true").attr('data-id1', itemId);
      $(row).find('td:eq(8)').attr('contenteditable', "true").attr('data-id1', itemId);
    },
      "order": [[3, 'asc']]
  });

  $('#menu-search').off('input.menuSearch').on('input.menuSearch', function() {
    var table = getMenuTable();
    if (table) {
      table.search(this.value).draw();
    }
  });
  $('#menu-page-length').off('change.menuLength').on('change.menuLength', function() {
    var table = getMenuTable();
    if (table) {
      table.page.len(parseInt(this.value, 10) || 10).draw();
    }
  });
}

function getMenuTable() {
  return $.fn.DataTable.isDataTable('#data-table-combine') ? $('#data-table-combine').DataTable() : null;
}

function buildExportColumnPicker() {
  var headers = [];
  $('#data-table-combine thead th').each(function(index) {
    if (index === 0) {
      return;
    }
    headers.push({
      index: index,
      label: $.trim($(this).text())
    });
  });
  var html = '';
  headers.forEach(function(header) {
    html += '<div class="col-md-4 col-sm-6"><label class="checkbox-inline" style="margin-bottom:8px;">';
    html += '<input type="checkbox" class="export-column" value="' + header.index + '" checked> ' + header.label;
    html += '</label></div>';
  });
  $('#export-columns').html(html);
}

function menuCellText(cell) {
  return $.trim($(cell).text()).replace(/\s+/g, ' ');
}

function menuDisplayValue(columnIndex, row) {
  switch (columnIndex) {
    case 1:
      return row.id || '';
    case 2:
      return row.name || '';
    case 3:
      return menuCategoryName(row.cat_id);
    case 4:
      return menuSubCategoryName(row.sub_id);
    case 5:
      return parseInt(row.dpt, 10) === 1 ? 'YES' : 'NO';
    case 6:
      return row.bprice || '';
    case 7:
      return row.price || '';
    case 8:
      return row.hhr || '';
    case 9:
      return parseInt(row.menu, 10) === 1 ? 'YES' : 'NO';
    case 10:
      return parseInt(row.deci, 10) === 1 ? 'YES' : 'NO';
    case 11:
      if (parseInt(row.stock, 10) === 1) {
        return 'INVENTORY';
      }
      if (parseInt(row.stock, 10) === 3) {
        return 'SERVICE';
      }
      return 'NON INVENTORY';
    case 12:
      return parseInt(row.recipe, 10) === 1 ? 'YES' : 'NO';
    case 13:
      return parseInt(row.recipe, 10) === 1 ? '' : (parseInt(row.portion, 10) === 1 ? 'YES' : 'NO');
    case 14:
      return parseInt(row.recipe, 10) === 1 ? '' : (parseInt(row.tots, 10) === 1 ? 'YES' : 'NO');
    case 15:
      return parseInt(row.stock_take_type, 10) === 1 ? 'WEIGHT' : 'UNITS';
    case 16:
      return menuTaxOptions ? menuTaxLabelByObject(((menuBootstrapCache && menuBootstrapCache.taxes || []).find(function(tax) {
        return parseInt(tax.id, 10) === parseInt(row.tax, 10);
      })) || { name: '', percent: '' }) : '';
    default:
      return '';
  }
}

function buildExportTableHtml(selectedColumns, rows) {
  var tableHtml = '<table><thead><tr>';
  selectedColumns.forEach(function(column) {
    tableHtml += '<th>' + $('<div>').text(column.label).html() + '</th>';
  });
  tableHtml += '</tr></thead><tbody>';
  rows.forEach(function(row) {
    tableHtml += '<tr>';
    row.forEach(function(value) {
      tableHtml += '<td>' + $('<div>').text(value).html() + '</td>';
    });
    tableHtml += '</tr>';
  });
  tableHtml += '</tbody></table>';
  return tableHtml;
}

function downloadExportTable(selectedColumns, rows) {
  var tableHtml = buildExportTableHtml(selectedColumns, rows);
  var blob = new Blob(['\ufeff' + tableHtml], { type: 'application/vnd.ms-excel' });
  var fileName = 'menu-export-' + new Date().toISOString().slice(0, 10) + '.xls';
  var link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(link.href);
  $('#export-results').html('<div class="alert alert-success"><strong>Done:</strong> Export started for ' + rows.length + ' row(s).</div>');
  setTimeout(function() {
    $('#export-modal').modal('hide');
  }, 1200);
}

function exportMenuRows() {
  var dt = getMenuTable();
  var selectedColumns = [];
  $('#export-columns input.export-column:checked').each(function() {
    selectedColumns.push({
      index: parseInt($(this).val(), 10),
      label: $.trim($(this).parent().text())
    });
  });
  if (!dt || selectedColumns.length < 1) {
    $('#export-results').html('<div class="alert alert-danger"><strong>Warning!</strong> Select at least one header to export.</div>');
    return;
  }
  var params = $.extend(true, {}, dt.ajax.params ? dt.ajax.params() : {});
  params.action = 'export_rows';
  params.start = 0;
  params.length = -1;
  $('#export-results').html('<div class="alert alert-info"><strong>Exporting:</strong> Preparing full export, please wait...</div>');
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: params
  })
  .done(function(res) {
    var sourceRows = (res && res.data) ? res.data : [];
    if (!sourceRows || sourceRows.length < 1) {
      $('#export-results').html('<div class="alert alert-warning"><strong>Check:</strong> No rows are available in the current filtered view.</div>');
      return;
    }
    var rows = sourceRows.map(function(sourceRow) {
      var row = [];
      selectedColumns.forEach(function(column) {
        row.push(menuDisplayValue(column.index, sourceRow));
      });
      return row;
    });
    downloadExportTable(selectedColumns, rows);
  })
  .fail(function() {
    $('#export-results').html('<div class="alert alert-danger"><strong>Warning!</strong> Could not export the full list.</div>');
  });
}

$.fn.get_cat = function() {
  hydrateMenuQuickForm();
};

function menuApiModal(view, data, targetSelector) {
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: $.extend({ action: 'modal', view: view }, data || {})
  })
  .done(function(res) {
    $(targetSelector).html((res && res.data && res.data.html) ? res.data.html : '');
    if ($('#modal').length) {
      $('#modal').modal('show');
      $('#modal-loader').hide();
    }
  })
  .fail(function() {
    $(targetSelector).html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}

function openMenuCategoryPicker(itemId, currentId, isSubCategory, itemName, itemCategoryId) {
  loadMenuBootstrap().done(function() {
    var title = isSubCategory ? 'Change Sub Category' : 'Change Category';
    var action = isSubCategory ? 'save_sub_category' : 'save_category';
    var field = isSubCategory ? 'sub_id' : 'cat_id';
    var options = '';
    var selected = parseInt(currentId || 0, 10);

    if (isSubCategory) {
      var currentSub = (menuBootstrapCache.subCategories || []).find(function(row) {
        return parseInt(row.id, 10) === selected;
      });
      var categoryId = parseInt(itemCategoryId || 0, 10) || (currentSub ? parseInt(currentSub.cat_id, 10) : 0);
      options = '<option value="0">---SELECT A SUB CATEGORY----</option>';
      (menuBootstrapCache.subCategories || []).forEach(function(row) {
        if (categoryId > 0 && parseInt(row.cat_id, 10) !== categoryId) return;
        var id = parseInt(row.id, 10);
        options += '<option value="' + id + '"' + (id === selected ? ' selected' : '') + '>' +
          $('<div>').text(row.name).html() + '</option>';
      });
    } else {
      options = '<option value="0">---SELECT A CATEGORY----</option>';
      (menuBootstrapCache.categories || []).forEach(function(row) {
        var id = parseInt(row.id, 10);
        options += '<option value="' + id + '"' + (id === selected ? ' selected' : '') + '>' +
          $('<div>').text(row.name).html() + '</option>';
      });
    }

    $('#load').html(
      '<div class="modal-content">' +
        '<div class="modal-header menu-picker-header"><button type="button" class="close" data-dismiss="modal">&times;</button>' +
          '<h4 class="modal-title"><i class="fa fa-pencil"></i> ' + title + '</h4></div>' +
        '<div class="modal-body"><div class="menu-picker-context"><span class="menu-picker-context-label">ITEM</span><strong>' +
          $('<div>').text(itemName || ('Item #' + itemId)).html() + '</strong><small>Item ID: ' + itemId + '</small></div>' +
          '<form id="menu-category-picker-form">' +
          '<div class="form-group"><label for="menu-category-picker">' + title + '</label>' +
            '<select id="menu-category-picker" class="form-control">' + options + '</select></div>' +
          '<div id="menu-category-picker-result"></div>' +
        '</form></div>' +
        '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' +
          '<button type="button" class="btn btn-primary" id="menu-category-picker-save">Save</button></div>' +
      '</div>'
    );
    $('#modal').modal('show');

    $('#menu-category-picker-save').off('click.menuPicker').on('click.menuPicker', function() {
      var value = parseInt($('#menu-category-picker').val(), 10) || 0;
      if (value < 1) {
        $('#menu-category-picker-result').html('<div class="alert alert-danger">Please select a value.</div>');
        return;
      }
      var $button = $(this);
      $button.prop('disabled', true).text('Saving...');
      var payload = { action: action, items: itemId };
      payload[field] = value;
      $.ajax({
        url: menuApiUrl,
        type: 'POST',
        dataType: 'json',
        data: payload
      }).done(function(res) {
        if (res && res.ok) {
          $('#modal').modal('hide');
          var table = getMenuTable();
          if (table) table.ajax.reload(null, false);
          return;
        }
        $('#menu-category-picker-result').html('<div class="alert alert-danger">' +
          $('<div>').text((res && res.message) || 'Unable to save the change.').html() + '</div>');
      }).fail(function() {
        $('#menu-category-picker-result').html('<div class="alert alert-danger">Unable to save the change.</div>');
      }).always(function() {
        $button.prop('disabled', false).text('Save');
      });
    });
  });
}

$(document).on('click', '.menu-category-launch', function(e) {
  e.preventDefault();
  var itemId = $(this).data('id');
  var row = getMenuTable() ? getMenuTable().row('.row' + itemId).data() : null;
  openMenuCategoryPicker(itemId, $(this).data('cat'), false, row && row.name, $(this).data('cat'));
});

$(document).on('click', '.menu-subcategory-launch', function(e) {
  e.preventDefault();
  var itemId = $(this).data('id');
  var row = getMenuTable() ? getMenuTable().row('.row' + itemId).data() : null;
  openMenuCategoryPicker(itemId, $(this).data('sub'), true, row && row.name, $(this).data('cat'));
});

$(document).on('click', '.menu-delete-item', function(e) {
  e.preventDefault();
  var itemId = $(this).data('id');
  confirmMenuChange('Delete item?', 'This item will be removed from the active menu.', function() {
    $.ajax({
      url: menuApiUrl,
      type: 'POST',
      dataType: 'json',
      data: { action: 'discontinue_items', items: itemId }
    }).done(function(res) {
      if (res && res.ok) {
        var table = getMenuTable();
        if (table) table.ajax.reload(null, false);
        return;
      }
      alert((res && res.message) || 'Unable to delete item.');
    }).fail(function() {
      alert('Unable to delete item.');
    });
  });
});

$(document).on('click', '.menu-view-stocks', function(e) {
  e.preventDefault();
  var itemId = $(this).data('id');
  $('#load').html('<div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading stock details...</div></div>');
  $('#modal').modal('show');
  $.ajax({
    url: '../../gen/get_stock_info.php',
    type: 'GET',
    data: { id: itemId },
    dataType: 'html'
  }).done(function(data) {
    $('#load').html(data);
  }).fail(function() {
    $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Unable to load stock details.</div></div></div>');
  });
});

$(document).on('click', '.menu-counter-assign', function(e) {
  e.preventDefault();
  var itemId = parseInt($(this).data('id'), 10) || 0;
  $('#load').html('<div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading counter assignments...</div></div>');
  $('#modal').modal('show');
  $.getJSON(menuApiUrl, { action: 'modal', view: 'counter_assign', id: itemId })
    .done(function(res) {
      $('#load').html((res && res.data && res.data.html) ? res.data.html : '<div class="modal-content"><div class="modal-body">Unable to load assignments.</div></div>');
    })
    .fail(function() {
      $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Unable to load counter assignments.</div></div></div>');
    });
});

$(document).on('shown.bs.dropdown', '.menu-row-actions', function() {
  var container = $(this);
  var menu = container.find('.dropdown-menu');
  var button = container.find('.menu-more-btn')[0];
  if (!menu.length || !button) return;
  menu.removeClass('menu-dropdown-below');
  var buttonRect = button.getBoundingClientRect();
  var menuRect = menu[0].getBoundingClientRect();
  var header = $(this).closest('table').find('thead')[0];
  var headerBottom = header ? header.getBoundingClientRect().bottom : 0;
  if (menuRect.top < Math.max(headerBottom, 0)) {
    menu.addClass('menu-dropdown-below');
  }
});

$(document).on('click', '#save-counter-assignments', function() {
  var button = $(this);
  var assignments = {};
  $('.counter-assignment-check:checked').each(function() {
    var counterId = $(this).val();
    assignments[counterId] = {
      qty: $('.counter-assignment-qty[data-counter="' + counterId + '"]').val() || 0,
      reorder_level: $('.counter-assignment-reorder[data-counter="' + counterId + '"]').val() || 0
    };
  });
  button.prop('disabled', true).text('Saving...');
  $.ajax({
    url: menuApiUrl,
    type: 'POST',
    dataType: 'json',
    data: { action: 'save_counter_assignments', item_id: button.data('item-id'), assignments: assignments }
  }).done(function(res) {
    if (res && res.ok) {
      $('#modal').modal('hide');
      var table = getMenuTable();
      if (table) table.ajax.reload(null, false);
    } else {
      $('#counter-assignment-result').html('<div class="alert alert-danger">' + ((res && res.message) || 'Could not save assignments.') + '</div>');
    }
  }).fail(function(xhr) {
    var message = 'Could not save assignments.';
    if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
    $('#counter-assignment-result').html('<div class="alert alert-danger">' + message + '</div>');
  }).always(function() {
    button.prop('disabled', false).text('Save Assignments');
  });
});

$(document).on('click', '.menu-butchery-toggle', function(e) {
  e.preventDefault();
  var itemId = $(this).data('id');
  var $link = $(this);

  if (typeof Swal === 'undefined') {
    var enabledFallback = window.confirm('Add this item to the butchery list?');
    $.post(menuApiUrl, { action: 'set_butchery', item_id: itemId, enabled: enabledFallback ? 1 : 0 }, function(res) {
      if (res && res.ok && getMenuTable()) getMenuTable().ajax.reload(null, false);
    }, 'json');
    return;
  }

  Swal.fire({
    title: '<span class="menu-butchery-modal-title"><i class="fa fa-cutlery"></i> Butchery listing</span>',
    html: '<div class="menu-butchery-modal-copy">Choose how this product should appear in the butchery screen.</div>' +
      '<div class="menu-butchery-modal-note"><i class="fa fa-info-circle"></i> Only products enabled here will be available for butchery sales.</div>',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: '<i class="fa fa-check"></i> Add to butchery',
    denyButtonText: '<i class="fa fa-minus-circle"></i> Remove',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    focusConfirm: false,
    customClass: {
      popup: 'menu-butchery-modal',
      confirmButton: 'menu-butchery-enable',
      denyButton: 'menu-butchery-disable',
      cancelButton: 'menu-butchery-cancel'
    },
    buttonsStyling: false,
    preConfirm: function() { return 1; },
    preDeny: function() { return 0; }
  }).then(function(result) {
    if (!result.isConfirmed && !result.isDenied) return;
    var enabled = result.isConfirmed ? 1 : 0;
    Swal.fire({
      title: enabled ? 'Adding to butchery…' : 'Removing from butchery…',
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: function() { Swal.showLoading(); }
    });
    $.post(menuApiUrl, { action: 'set_butchery', item_id: itemId, enabled: enabled }, function(res) {
      if (res && res.ok) {
        Swal.fire({
          icon: 'success',
          title: enabled ? 'Added to butchery' : 'Removed from butchery',
          text: (res.message || 'Butchery setting saved.'),
          timer: 1500,
          showConfirmButton: false
        });
        if (getMenuTable()) getMenuTable().ajax.reload(null, false);
      } else {
        Swal.fire({ icon: 'error', title: 'Could not save setting', text: (res && res.message) || 'Please try again.' });
      }
    }, 'json').fail(function() {
      Swal.fire({ icon: 'error', title: 'Could not save setting', text: 'Please check the connection and try again.' });
    });
  });
});

function edit_data(id, text, column_name, field) {
  $.ajax({
    url: menuApiUrl,
    method: "POST",
    data: { action: 'edit_item', id: id, text: text, column_name: column_name, field: field },
    dataType: "json",
    success: function(res) {
      var hulla = new hullabaloo();
      hulla.send((res && res.message) ? res.message : 'Updated', "success");
      var boolCols = ['menu', 'deci', 'stock', 'recipe', 'portion', 'tots', 'dpt'];
      if (boolCols.indexOf(column_name) !== -1) {
        refreshBooleanCell(id, column_name, text);
      }
      if (column_name === 'tax_id') {
        refreshTaxCell(id, text);
      }
      if (column_name === 'recipe') {
        refreshBooleanCell(id, 'recipe', text);
      }
      if (column_name === 'portion') {
        refreshBooleanCell(id, 'portion', text);
      }
      if (column_name === 'tots') {
        refreshBooleanCell(id, 'tots', text);
      }
      if (column_name === 'dpt') {
        refreshBooleanCell(id, 'dpt', text);
      }
    }
  });
}

function confirmMenuChange(title, text, onConfirm) {
  if (window.Swal && typeof window.Swal.fire === 'function') {
    window.Swal.fire({
      title: title,
      text: text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d9534f',
      confirmButtonText: 'Yes, change it',
      cancelButtonText: 'Cancel'
    }).then(function(result) {
      if (result && result.isConfirmed) {
        onConfirm();
      }
    });
    return;
  }
  if (window.swal) {
    swal({
      title: title,
      text: text,
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d9534f',
      confirmButtonText: 'Yes, change it',
      cancelButtonText: 'Cancel'
    }, function(isConfirm) {
      if (isConfirm) {
        onConfirm();
      }
    });
    return;
  }
  if (window.confirm(text)) {
    onConfirm();
  }
}

$(document).on('click', '.menu-inline-toggle', function(e) {
  e.preventDefault();
  var column = $(this).data('column');
  var id = $(this).data('id');
  var next = $(this).data('next');
  var confirmCols = ['dpt', 'b_price', 'price', 'menu', 'stock'];
  if (confirmCols.indexOf(column) !== -1) {
    confirmMenuChange('Confirm change?', 'Are you sure you want to change this field?', function() {
      edit_data(id, next, column, column);
    });
    return;
  }
  edit_data(id, next, column, column);
});

$(document).on('click', '.menu-recipe-launch', function(e) {
  e.preventDefault();
  menuApiModal('recipe_items', { id: $(this).data('id') }, '#load');
});

$(document).on('click', '.menu-portion-launch', function(e) {
  e.preventDefault();
  menuApiModal('portion_manage', { id: $(this).data('id') }, '#load');
});

$(document).on('click', '.menu-tots-launch', function(e) {
  e.preventDefault();
  menuApiModal('tots_manage', { id: $(this).data('id') }, '#load');
});

$(document).on('click', '.menu-tax-launch', function(e) {
  e.preventDefault();
  menuApiModal('prods_tax', { id: $(this).data('id') }, '#load');
});

$(document).on('click', '.menu-stocktake-launch', function(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  var itemId = parseInt($(this).data('id'), 10) || 0;
  if (itemId < 1) return false;
  menuApiModal('tots_manage', { id: itemId }, '#load');
  return false;
});

$(document).on('click', '.menu-units-launch', function(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  menuApiModal('units_manage', { id: parseInt($(this).data('id'), 10) || 0 }, '#load');
  return false;
});

function confirmMenuInlineEdit(cell, columnName, label) {
  var $cell = $(cell), id = $cell.data('id1'), oldValue = $cell.data('edit-original');
  var newValue = $.trim($cell.text());
  if (oldValue === undefined) oldValue = newValue;
  oldValue = String(oldValue);
  if (newValue === oldValue) return;

  var safeOld = $('<div>').text(oldValue).html();
  var safeNew = $('<div>').text(newValue).html();
  var confirmRequest = window.Swal ? Swal.fire({
    title: 'Confirm change',
    html: '<p>Are you sure you want to change the ' + label.toLowerCase() + '?</p>' +
      '<div style="display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px;">' +
      '<strong style="padding:8px 12px;background:#fff1f2;color:#c74651;border-radius:6px;">' + safeOld + '</strong>' +
      '<span>→</span>' +
      '<strong style="padding:8px 12px;background:#effcf4;color:#16844b;border-radius:6px;">' + safeNew + '</strong>' +
      '</div>',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, change it',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }) : Promise.resolve({ isConfirmed: window.confirm('Change ' + label + ' from "' + oldValue + '" to "' + newValue + '"?') });

  confirmRequest.then(function (result) {
    if (result.isConfirmed) {
      $cell.removeData('edit-original');
      edit_data(id, newValue, columnName, label);
    } else {
      $cell.text(oldValue);
      $cell.removeData('edit-original');
    }
  });
}

$(document).on('focus', '.name, .price, .bprice', function() {
  $(this).data('edit-original', $.trim($(this).text()));
});

$(document).on('blur', '.name', function() {
  confirmMenuInlineEdit(this, 'name', 'Name');
});

$(document).on('blur', '.price', function() {
  confirmMenuInlineEdit(this, 'price', 'Selling Price');
});

$(document).on('blur', '.hhr', function() {
  edit_data($(this).data("id1"), $(this).text(), "hhr", "Happy Hour");
});

$(document).on('blur', '.bprice', function() {
  confirmMenuInlineEdit(this, 'b_price', 'Buying Price');
});

$(function() {
  $('.checkAll').click(function() {
    if (this.checked) {
      $(".bulk").prop("checked", true);
      $('.act_btn').show();
    } else {
      $(".bulk").prop("checked", false);
      $('.act_btn').hide();
    }
  });
});

function item_select() {
  var checked = (function() {
    var valor = [];
    $('input:checked').each(function() {
      if (this.checked) {
        var row = $(this).closest("tr")[0];
        valor.push(row.cells[1].innerHTML);
      }
    });
    return valor;
  })();
  $('.act_btn').toggle(checked.length > 0);
}

$(document).on('click', '#prods-add', function(e) {
  e.preventDefault();
  $('#load').html('');
  $('#modal-loader').show();
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'modal', view: 'prod_new2' }
  })
  .done(function(res) {
    $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
    $.fn.get_cat();
  })
  .fail(function() {
    $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
});

function selectedMenuItems() {
  var items = [];
  $('input[name="items[]"]:checked').each(function() {
    if (this.checked) {
      var row = $(this).closest("tr")[0];
      items.push(row.cells[1].innerHTML);
    }
  });
  return items.join(',');
}

$(document).on('click', '#cat, #sub_cat, #del, #set, #tax', function(e) {
  e.preventDefault();
  var viewMap = {
    cat: 'prod_cat',
    sub_cat: 'prod_sub',
    del: 'prods_del',
    set: 'prod_set',
    tax: 'prods_tax'
  };
  var view = viewMap[this.id];
  $('#load').html('');
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'modal', view: view, items: selectedMenuItems() }
  })
  .done(function(res) {
    $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
  })
  .fail(function() {
    $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
});

function loadMenuCreateModal(url) {
  $('#load2').html('<div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading…</div></div>');
  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'html'
  }).done(function(data) {
    $('#load2').html(data);
    $('#modal2').modal('show');
  }).fail(function() {
    $('#load2').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Something went wrong. Please try again.</div></div></div>');
    $('#modal2').modal('show');
  });
}

$(document).on('click', '#new-cat', function(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  loadMenuCreateModal('../cat/cat_new.php');
});

$(document).on('click', '#new-sub', function(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  loadMenuCreateModal('../subcat/sub_new.php');
});

$(document).on('click.menuCreate', '#new_cat', function(e) {
  e.preventDefault();
  var $button = $(this);
  $button.prop('disabled', true).text('Saving...');
  $.ajax({
    url: '../cat/cat_new_act.php',
    type: 'POST',
    data: {
      name: $('#cat-new-name').val(),
      dpt: $('#cat-new-dpt').val()
    },
    dataType: 'html'
  }).done(function(data) {
    $('#cat-new-results').html(data);
    if ($.fn.get_cat) {
      loadMenuBootstrap(true).done(function() {
        $.fn.get_cat();
      });
    }
    if (data.indexOf('alert-success') !== -1) {
      setTimeout(function() { $('#modal2').modal('hide'); }, 700);
    }
  }).fail(function() {
    $('#cat-new-results').html('<div class="alert alert-danger">Unable to add the category. Please try again.</div>');
  }).always(function() {
    $button.prop('disabled', false).text('Save');
  });
});

$(document).on('click.menuCreate', '#new_sub', function(e) {
  e.preventDefault();
  var $button = $(this);
  $button.prop('disabled', true).text('Saving...');
  $.ajax({
    url: '../subcat/sub_new_act.php',
    type: 'POST',
    data: {
      name: $('#sub-new-name').val(),
      cat_id: $('#sub-new-category').val(),
      dpt: $('#sub-new-dpt').val()
    },
    dataType: 'html'
  }).done(function(data) {
    $('#sub-new-results').html(data);
    loadMenuBootstrap(true).done(function() {
      hydrateMenuQuickForm();
    });
    if (data.indexOf('alert-success') !== -1) {
      setTimeout(function() { $('#modal2').modal('hide'); }, 700);
    }
  }).fail(function() {
    $('#sub-new-results').html('<div class="alert alert-danger">Unable to add the subcategory. Please try again.</div>');
  }).always(function() {
    $button.prop('disabled', false).text('Save');
  });
});

$(document).on('click', '#addRecipe', function() {
  var uid = $(this).data('id');
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'modal', view: 'recipe_confirm', id: uid }
  })
  .done(function(res) {
    $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
  })
  .fail(function() {
    $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
});

$(document).on('click', '#recipe', function() {
  var uid = $(this).data('id');
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'modal', view: 'recipe_items', id: uid }
  })
  .done(function(res) {
    $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
  })
  .fail(function() {
    $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
});

$(document).on('click', '#stock-info', function(e) {
  e.preventDefault();
  var uid = $(this).data('id');
  $('#load').html('');
  $.ajax({
    url: '../../gen/get_stock_info.php',
    typr: 'GET',
    data: { id: uid },
    dataType: 'html'
  })
  .done(function(data) {
    $('#load').html(data);
  })
  .fail(function() {
    $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
});

$(document).on('click', '#manage-portion, #manage-tots', function() {
  var uid = $(this).data('id');
  var view = this.id === 'manage-portion' ? 'portion_manage' : 'tots_manage';
  $.ajax({
    url: menuApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'modal', view: view, id: uid }
  })
  .done(function(res) {
    $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
  })
  .fail(function() {
    $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
    $('#modal-loader').hide();
  });
});

$(document).on('click', '#export-menu', function() {
  $('#export-results').html('');
  buildExportColumnPicker();
});

$(document).on('click', '#run-export', function(e) {
  e.preventDefault();
  exportMenuRows();
});
$(document).on('change', '#cat_id', function() {
  fillSubCategoryOptions('#sub_id', $(this).val(), 0);
  reloadMenuTable();
});

$(document).on('change', '#sub_id, #dpt, #stock, #counter_id', function() {
  if ($(this).is('#counter_id')) {
    updateCounterAssignmentTabs();
    $('#assign-selected-counter').toggle($('#data-table-combine .bulk:checked').length > 0 && parseInt($('#counter_id').val(), 10) > 0);
  }
  reloadMenuTable();
});

$(document).on('click', '.counter-assignment-tab', function() {
  window.menuCounterAssignmentMode = $(this).data('assignment-mode') || 'assigned';
  updateCounterAssignmentTabs();
  reloadMenuTable();
});

$(document).on('click', '#reset-menu-filters', function() {
  $('#cat_id, #sub_id, #dpt, #stock, #counter_id').val('0').trigger('change.select2');
  window.menuCounterAssignmentMode = 'assigned';
  updateCounterAssignmentTabs();
  fillSubCategoryOptions('#sub_id', 0, 0);
  reloadMenuTable();
});

$(document).on('click', '#apply-menu-filters', function() {
  if (typeof runMenuSearch === 'function') {
    runMenuSearch();
  } else {
    reloadMenuTable();
  }
});

$(document).on('change', '#data-table-combine .bulk, #data-table-combine .checkAll', function() {
  var hasSelected = $('#data-table-combine .bulk:checked').length > 0;
  var hasCounter = parseInt($('#counter_id').val(), 10) > 0;
  $('#assign-selected-counter').toggle(hasSelected && hasCounter);
});

$(document).on('click', '#assign-selected-counter', function() {
  var counterId = parseInt($('#counter_id').val(), 10) || 0;
  var items = $('#data-table-combine .bulk:checked').map(function() { return $(this).val(); }).get();
  if (!counterId || !items.length) {
    if (window.Swal) Swal.fire('Select items', 'Select a counter and at least one item.', 'warning');
    return;
  }
  var counterName = $('#counter_id option:selected').text();
  var confirmAssign = function() {
    $.ajax({
      url: menuApiUrl,
      type: 'POST',
      dataType: 'json',
      data: { action: 'bulk_assign_counter', items: items, counter_id: counterId }
    }).done(function(res) {
      if (window.Swal) Swal.fire(res.ok ? 'Assigned' : 'Assignment failed', res.message || '', res.ok ? 'success' : 'error');
      if (res && res.ok) {
        $('.checkAll, .bulk').prop('checked', false);
        $('#assign-selected-counter').hide();
        reloadMenuTable();
      }
    }).fail(function(xhr) {
      var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not assign the selected items.';
      if (window.Swal) Swal.fire('Assignment failed', message, 'error');
    });
  };
  if (window.Swal) Swal.fire({ title: 'Assign selected items?', text: items.length + ' item(s) will be assigned to ' + counterName + '.', icon: 'question', showCancelButton: true, confirmButtonText: 'Assign' }).then(function(result) { if (result.isConfirmed) confirmAssign(); });
  else if (window.confirm('Assign ' + items.length + ' item(s) to ' + counterName + '?')) confirmAssign();
});

function reloadMenuTable() {
  var table = getMenuTable();
  if (table) {
    table.ajax.reload(null, true);
  }
}
