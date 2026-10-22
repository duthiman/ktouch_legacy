var recipeApiUrl = 'API/recipe_api.php';
var recipeItemId = 0;

function recipeAlert(type, message) {
  $('#results').html('<div class="alert alert-' + type + '"><p>' + $('<div>').text(message).html() + '</p></div>');
}

function renderRecipeOptions(items) {
  var html = '<option value="0">--Select Ingridient/R </option>';
  (items || []).forEach(function(item) {
    html += '<option value="' + item.id + '">' + $('<div>').text(item.name).html() + '</option>';
  });
  $('#raw_id').html(html).trigger('change.select2');
}

function renderRecipeItems(items) {
  var html = '<table class="table table-bordered" id="TheSo"><thead><tr><th>Raw Material</th><th>Quantity</th><th>Action</th></tr></thead><tbody>';
  if (!items || items.length < 1) {
    html += '<tr><td colspan="3">MAKE SURE YOU ADD ITEMS TO THIS LIST</td></tr>';
    html += '<tr><td colspan="3"><a href="../menu/" class="btn btn-outline-danger btn-lg pull-right">UPDATE/CLEAR RECIPE SETTINGS</a></td></tr>';
  } else {
    items.forEach(function(item) {
      html += '<tr>';
      html += '<td>' + $('<div>').text(item.raw_name || '').html() + '</td>';
      html += '<td>' + $('<div>').text(item.qty || '').html() + '</td>';
      html += '<td><a type="button" class="text-red btn btn-outline-danger btn-sm" onclick="delete_recipe_item(' + item.id + ',' + recipeItemId + ');"><i class="fa fa-lg fa-trash"></i> Remove Item</a></td>';
      html += '</tr>';
    });
    html += '<tfoot><tr><td colspan="3"><a href="../menu/" class="btn btn-outline-success btn-lg pull-right">Finish Adding Recipe</a></td></tr></tfoot>';
  }
  html += '</tbody></table>';
  $('#items_list').html(html).css('visibility', 'visible');
}

function load_recipe_bootstrap(itemId) {
  recipeItemId = parseInt(itemId, 10) || 0;
  $.ajax({
    url: recipeApiUrl,
    type: 'GET',
    dataType: 'json',
    data: { action: 'bootstrap', item_id: recipeItemId }
  })
  .done(function(res) {
    if (!res || !res.ok) {
      recipeAlert('danger', (res && res.message) ? res.message : 'Could not load recipe page.');
      return;
    }
    var data = res.data || {};
    var item = data.item || {};
    $('#recipe-item-name').text(item.name || '');
    renderRecipeOptions(data.stock_items || []);
    renderRecipeItems(data.recipe_items || []);
  })
  .fail(function() {
    recipeAlert('danger', 'Could not load recipe page.');
  });
}

function submit_recipe_item(itemId) {
  $.ajax({
    url: recipeApiUrl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'add_recipe_item',
      item_id: itemId,
      raw_id: $('#raw_id').val(),
      qty: $('#qty').val()
    }
  })
  .done(function(res) {
    if (!res || !res.ok) {
      recipeAlert('danger', (res && res.message) ? res.message : 'Could not add recipe item.');
      return;
    }
    $('#results').html('');
    renderRecipeItems((res.data && res.data.recipe_items) ? res.data.recipe_items : []);
    $('#qty').val('0');
  })
  .fail(function() {
    recipeAlert('danger', 'Could not add recipe item.');
  });
}

function delete_recipe_item(id, itemId) {
  $.ajax({
    url: recipeApiUrl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'delete_recipe_item',
      id: id,
      item_id: itemId
    }
  })
  .done(function(res) {
    if (!res || !res.ok) {
      recipeAlert('danger', (res && res.message) ? res.message : 'Could not remove recipe item.');
      return;
    }
    $('#results').html('');
    renderRecipeItems((res.data && res.data.recipe_items) ? res.data.recipe_items : []);
  })
  .fail(function() {
    recipeAlert('danger', 'Could not remove recipe item.');
  });
}
