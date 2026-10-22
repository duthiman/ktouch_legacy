function importFileInput() {
  return document.getElementById('file');
}

function readImportPreview(file, onSuccess, onError) {
  var reader = new FileReader();
  reader.onload = function(event) {
    try {
      var text = event.target.result || '';
      var lines = text.split(/\r?\n/).filter(function(line) {
        return $.trim(line) !== '';
      });
      var rows = [];
      for (var i = 1; i < lines.length; i++) {
        var parts = lines[i].split(',');
        if (!parts[1] || $.trim(parts[1]) === '') {
          continue;
        }
        rows.push({
          stock_code: $.trim(parts[0] || ''),
          name: $.trim(parts[1] || '').toUpperCase(),
          base_price: $.trim(parts[2] || ''),
          sprice: $.trim(parts[3] || ''),
          wprice: $.trim(parts[4] || ''),
          bprice: $.trim(parts[5] || ''),
          reorder: $.trim(parts[6] || ''),
          qty: $.trim(parts[7] || '')
        });
      }
      onSuccess(rows);
    } catch (error) {
      onError(error);
    }
  };
  reader.onerror = onError;
  reader.readAsText(file);
}

function renderImportPreview(rows) {
  if (!rows || rows.length < 1) {
    $('#results').html('<div class="alert alert-warning">No valid item rows were found in the file.</div>');
    return;
  }
  var html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
  html += '<thead><tr><th>Stock Code</th><th>Name</th><th>Buying Price</th><th>Base Price</th><th>Shop Price</th><th>Online Price</th><th>Reorder Level</th><th>Stock Level</th></tr></thead><tbody>';
  rows.forEach(function(row) {
    html += '<tr>';
    html += '<td>' + $('<div>').text(row.stock_code).html() + '</td>';
    html += '<td>' + $('<div>').text(row.name).html() + '</td>';
    html += '<td>' + $('<div>').text(row.base_price).html() + '</td>';
    html += '<td>' + $('<div>').text(row.sprice).html() + '</td>';
    html += '<td>' + $('<div>').text(row.wprice).html() + '</td>';
    html += '<td>' + $('<div>').text(row.bprice).html() + '</td>';
    html += '<td>' + $('<div>').text(row.reorder).html() + '</td>';
    html += '<td>' + $('<div>').text(row.qty).html() + '</td>';
    html += '</tr>';
  });
  html += '</tbody></table></div>';
  $('#results').html(html);
}

function validateImportFile() {
  var input = importFileInput();
  if (!input || !input.files || input.files.length < 1) {
    $('#upload-msg').html('<div class="alert alert-danger">Choose a CSV file first.</div>');
    return null;
  }
  $('#upload-msg').html('');
  return input.files[0];
}

$(document).on('click', '#preview-import', function() {
  var file = validateImportFile();
  if (!file) {
    return;
  }
  readImportPreview(file, function(rows) {
    renderImportPreview(rows);
  }, function() {
    $('#results').html('<div class="alert alert-danger">Could not preview the selected CSV file.</div>');
  });
});

$(document).on('click', '#submit-import', function() {
  var file = validateImportFile();
  if (!file) {
    return;
  }
  var formData = new FormData();
  formData.append('action', 'import_items');
  formData.append('file', file);
  $('#upload-msg').html('<div class="alert alert-info">Uploading items...</div>');
  $.ajax({
    url: menuApiUrl,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json'
  })
  .done(function(res) {
    if (!res || !res.ok) {
      $('#upload-msg').html('<div class="alert alert-danger">' + ((res && res.message) ? res.message : 'Import failed.') + '</div>');
      return;
    }
    $('#upload-msg').html('<div class="alert alert-success">' + (res.message || 'Data imported successfully.') + '</div>');
    renderImportPreview((res.data && res.data.rows) ? res.data.rows : []);
    if (res.data && res.data.errors && res.data.errors.length > 0) {
      var errorHtml = '<div class="alert alert-warning"><strong>Some rows failed:</strong><ul>';
      res.data.errors.forEach(function(error) {
        errorHtml += '<li>' + $('<div>').text((error.name || 'Item') + ': ' + (error.error || 'Unknown error')).html() + '</li>';
      });
      errorHtml += '</ul></div>';
      $('#results').prepend(errorHtml);
    }
  })
  .fail(function() {
    $('#upload-msg').html('<div class="alert alert-danger">Import failed. Please try again.</div>');
  });
});
