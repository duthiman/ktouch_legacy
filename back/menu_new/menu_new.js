$(document).ready(function () {
  function showSaveResult(message, type) {
    var css = type === 'danger' ? 'alert-danger' : 'alert-success';
    $('#mn-save-results').html(
      '<div class="alert ' + css + '">' + message + '</div>'
    );
  }

  function refreshDrafts() {
    $('#mn-drafts-list').load('list_items.php');
  }

  function saveDraft(payload) {
    $.ajax({
      url: 'save_item.php',
      type: 'POST',
      dataType: 'json',
      data: payload
    })
    .done(function (res) {
      if (res && res.ok) {
        showSaveResult(res.message + ' Draft #' + res.draft_id + ' created.', 'success');
        refreshDrafts();
      } else {
        showSaveResult((res && res.message) ? res.message : 'Failed to save draft.', 'danger');
      }
    })
    .fail(function () {
      showSaveResult('Failed to save draft due to a request error.', 'danger');
    });
  }

  $(document).on('click', '.mn-navbtn', function () {
    var target = $(this).data('target');
    $('.mn-navbtn').removeClass('active');
    $(this).addClass('active');
    $('.mn-panel').removeClass('active');
    $('#' + target).addClass('active');
    if (target === 'drafts') {
      refreshDrafts();
    }
  });

  function updateBuilder() {
    var itemType = $('#mn-item-type').val();
    var controlMethod = $('#mn-control-method').val();
    var pricingMode = $('#mn-pricing-mode').val();
    var itemName = $.trim($('#mn-item-name').val()) || 'This item';

    $('.mn-builder-group').hide();
    $('#mn-fields-common').show();

    if (itemType === 'raw_material') {
      $('#mn-fields-raw').show();
    }
    if (itemType === 'sellable_item') {
      $('#mn-fields-sellable').show();
    }
    if (itemType === 'batch_item') {
      $('#mn-fields-sellable, #mn-fields-batch').show();
    }
    if (itemType === 'bottle_item') {
      $('#mn-fields-sellable, #mn-fields-bottle').show();
    }
    if (itemType === 'service_item') {
      $('#mn-fields-sellable').show();
    }

    if (controlMethod === 'recipe') {
      $('#mn-fields-recipe').show();
    }
    if (controlMethod === 'batch') {
      $('#mn-fields-batch').show();
    }
    if (controlMethod === 'bottle') {
      $('#mn-fields-bottle').show();
    }
    if (controlMethod === 'conversion') {
      $('#mn-fields-conversion').show();
    }
    if (controlMethod === 'simple_issue') {
      $('#mn-fields-simple').show();
    }

    $('#mn-sell-price-wrap').toggle(pricingMode === 'fixed' || pricingMode === 'attribute');

    var summary = '<strong>' + itemName + '</strong> will be handled as <strong>' +
      $('#mn-item-type option:selected').text() + '</strong> using <strong>' +
      $('#mn-control-method option:selected').text() + '</strong> with <strong>' +
      $('#mn-pricing-mode option:selected').text() + '</strong>.';

    $('#mn-rule-summary').html(summary);
  }

  $(document).on('change', '#mn-item-type, #mn-control-method, #mn-pricing-mode', updateBuilder);
  $(document).on('input', '#mn-item-name', updateBuilder);

  updateBuilder();

  function updateSellableForm() {
    var name = $.trim($('#sf-name').val()) || 'New sellable item';
    var control = $('#sf-control-method').val();
    var controlLabel = $('#sf-control-method option:selected').text();
    var pricing = $('#sf-pricing-mode').val();
    var pricingLabel = $('#sf-pricing-mode option:selected').text();
    var departmentLabel = $('#sf-department option:selected').text();
    var posEnabled = $('#sf-pos').is(':checked');
    var active = $('#sf-active').is(':checked');
    var taxable = $('#sf-taxable').is(':checked');
    var price = $('#sf-price').val() || '0';

    $('#sf-batch-card').toggle(control === 'batch');
    $('#sf-bottle-card').toggle(control === 'bottle');
    $('#sf-recipe-card').toggle(control === 'recipe');

    $('#sf-min-wrap, #sf-max-wrap').toggle(pricing === 'permission');
    $('#sf-price-row').toggle(pricing !== 'variable');

    $('#sf-open-price').prop('checked', pricing === 'variable' || pricing === 'permission');
    $('#sf-require-perm').prop('checked', pricing === 'permission');

    $('#sf-metric-control').text('Control: ' + controlLabel);
    $('#sf-metric-price').text('Price: ' + pricingLabel);

    var summary = '<strong>' + name + '</strong> is configured for the <strong>' + departmentLabel +
      '</strong> department using <strong>' + controlLabel + '</strong>. ' +
      (posEnabled ? 'It will appear on POS.' : 'It will stay hidden from POS.') + ' ' +
      (active ? 'It is marked active.' : 'It is currently inactive.') + ' ' +
      (taxable ? 'Tax applies.' : 'Tax is disabled.');
    $('#sf-summary-banner').html(summary);

    var effectText = 'This item will ';
    if (!posEnabled) {
      effectText += 'stay out of the POS until enabled. ';
    } else {
      effectText += 'be sellable on the POS. ';
    }

    if (control === 'direct_sell') {
      effectText += 'Sales will follow a direct sell flow';
    } else if (control === 'recipe') {
      effectText += 'Sales should deduct linked recipe ingredients';
    } else if (control === 'batch') {
      var batchYield = $('#sf-batch-yield').val() || '0';
      var batchUnit = $.trim($('#sf-batch-unit').val()) || 'portions';
      effectText += 'production will create approximately ' + batchYield + ' ' + batchUnit + ' per batch';
    } else if (control === 'bottle') {
      var bottleMl = $('#sf-bottle-ml').val() || '0';
      var shotMl = $('#sf-shot-ml').val() || '1';
      var estShots = Math.floor(parseFloat(bottleMl || 0) / Math.max(parseFloat(shotMl || 1), 1));
      effectText += 'bottle stock will be interpreted as approximately ' + estShots + ' shots from a ' + bottleMl + 'ml bottle';
    }

    effectText += '. ';

    if (pricing === 'fixed') {
      effectText += 'Selling price is fixed at ' + price + '.';
    } else if (pricing === 'variable') {
      effectText += 'Cashier can enter the price during sale.';
    } else if (pricing === 'permission') {
      effectText += 'Price changes should require approval and respect min/max limits.';
    } else if (pricing === 'attribute') {
      effectText += 'Base price is ' + price + ' and attributes may add price adjustments.';
    }

    $('#sf-effect-text').text(effectText);
  }

  $(document).on(
    'change input',
    '#sf-name, #sf-code, #sf-category, #sf-subcategory, #sf-department, #sf-control-method, #sf-pricing-mode, #sf-price, #sf-min-price, #sf-max-price, #sf-pos, #sf-taxable, #sf-active, #sf-attr-accomp, #sf-attr-temp, #sf-attr-chilli, #sf-attr-addon, #sf-open-price, #sf-require-perm, #sf-batch-source, #sf-batch-yield, #sf-batch-unit, #sf-bottle-ml, #sf-shot-ml, #sf-empty-weight, #sf-full-weight',
    updateSellableForm
  );

  updateSellableForm();

  function updateRawForm() {
    var name = $.trim($('#rf-name').val()) || 'New raw material';
    var control = $('#rf-control-method').val();
    var controlLabel = $('#rf-control-method option:selected').text();
    var purchaseUnit = $('#rf-purchase-unit option:selected').text();
    var stockUnit = $('#rf-stock-unit option:selected').text();
    var conversionBasis = $('#rf-conversion-basis option:selected').text();
    var conversionQty = $('#rf-conversion-qty').val() || '1';
    var conversionUnit = $.trim($('#rf-conversion-unit').val()) || stockUnit;
    var reorder = $('#rf-reorder').val() || '0';
    var purchasable = $('#rf-purchasable').is(':checked');
    var stockable = $('#rf-stockable').is(':checked');
    var directIssue = $('#rf-direct-issue').is(':checked');
    var lowControl = $('#rf-low-control').is(':checked');

    $('#rf-conversion-card').toggle(control === 'conversion');
    $('#rf-source-card').toggle(control === 'recipe' || control === 'batch_source');

    $('#rf-metric-control').text('Control: ' + controlLabel);
    $('#rf-metric-unit').text('Unit: ' + stockUnit);

    var summary = '<strong>' + name + '</strong> is configured as a raw material using <strong>' +
      controlLabel + '</strong>. Purchase unit is <strong>' + purchaseUnit +
      '</strong> and stock unit is <strong>' + stockUnit + '</strong>. Reorder level is <strong>' +
      reorder + '</strong>.';
    $('#rf-summary-banner').html(summary);

    var effectText = '';
    if (purchasable) {
      effectText += 'This item is expected to be bought from suppliers. ';
    } else {
      effectText += 'This item is not currently marked as a supplier purchase item. ';
    }

    if (stockable) {
      effectText += 'It will be tracked in stock. ';
    } else {
      effectText += 'It will not update stock balances. ';
    }

    if (control === 'simple_issue') {
      effectText += 'Operations will issue it directly to kitchen or bar without detailed sell-side deduction.';
    } else if (control === 'conversion') {
      effectText += 'Each ' + purchaseUnit + ' converts to approximately ' + conversionQty + ' ' + conversionUnit + ' using ' + conversionBasis.toLowerCase() + ' logic.';
    } else if (control === 'recipe') {
      effectText += 'This item is intended to be consumed mainly through recipe deduction.';
    } else if (control === 'batch_source') {
      effectText += 'This item is intended to feed batch production such as ugali or chapati output.';
    }

    if (directIssue) {
      effectText += ' Direct issue is allowed.';
    } else {
      effectText += ' Direct issue is not allowed.';
    }

    if (lowControl) {
      effectText += ' Low-control issue mode is enabled for operational flexibility.';
    }

    $('#rf-effect-text').text(effectText);
  }

  $(document).on(
    'change input',
    '#rf-name, #rf-code, #rf-category, #rf-store-area, #rf-control-method, #rf-purchase-unit, #rf-stock-unit, #rf-purchase-price, #rf-reorder, #rf-conversion-basis, #rf-conversion-qty, #rf-conversion-unit, #rf-purchasable, #rf-stockable, #rf-direct-issue, #rf-low-control',
    updateRawForm
  );

  updateRawForm();

  function updateBatchForm() {
    var name = $.trim($('#bf-name').val()) || 'New batch item';
    var department = $('#bf-department option:selected').text();
    var sourceItem = $.trim($('#bf-source-item').val()) || 'source stock';
    var sourceQty = $('#bf-source-qty').val() || '1';
    var sourceUnit = $.trim($('#bf-source-unit').val()) || 'unit';
    var yieldQty = $('#bf-yield').val() || '0';
    var productionUnit = $.trim($('#bf-production-unit').val()) || 'portions';
    var saleUnit = $.trim($('#bf-sale-unit').val()) || productionUnit;
    var mode = $('#bf-mode option:selected').text();
    var pos = $('#bf-pos').is(':checked');
    var stockOutput = $('#bf-stock-output').is(':checked');
    var sourceDeduct = $('#bf-source-deduct').is(':checked');

    $('#bf-metric-yield').text('Yield: ' + yieldQty);
    $('#bf-metric-unit').text('Unit: ' + productionUnit);

    var summary = '<strong>' + name + '</strong> is a batch-produced item for <strong>' + department +
      '</strong>. One batch uses <strong>' + sourceQty + ' ' + sourceUnit + '</strong> of <strong>' +
      sourceItem + '</strong> and yields about <strong>' + yieldQty + ' ' + productionUnit + '</strong>.';
    $('#bf-summary-banner').html(summary);

    var effectText = 'This item runs in ' + mode + ' mode. ';
    effectText += 'Production converts ' + sourceItem + ' into ' + yieldQty + ' ' + saleUnit + '. ';
    effectText += stockOutput
      ? 'Produced stock should be added to sellable inventory. '
      : 'Produced output is not currently set to increase sellable inventory. ';
    effectText += sourceDeduct
      ? 'Source stock should be deducted during production. '
      : 'Source stock is not currently set to deduct automatically. ';
    effectText += pos
      ? 'The produced item will appear on POS.'
      : 'The produced item will stay hidden from POS.';

    $('#bf-effect-text').text(effectText);
  }

  $(document).on(
    'change input',
    '#bf-name, #bf-department, #bf-category, #bf-price, #bf-sale-unit, #bf-source-item, #bf-source-qty, #bf-source-unit, #bf-yield, #bf-production-unit, #bf-mode, #bf-pos, #bf-stock-output, #bf-source-deduct, #bf-notes',
    updateBatchForm
  );

  updateBatchForm();

  function updateBottleForm() {
    var name = $.trim($('#tf-name').val()) || 'New bottle / tot item';
    var bottleName = $.trim($('#tf-bottle-name').val()) || 'linked bottle';
    var bottleMl = parseFloat($('#tf-bottle-ml').val() || 0);
    var shotMl = Math.max(parseFloat($('#tf-shot-ml').val() || 1), 1);
    var emptyWeight = parseFloat($('#tf-empty-weight').val() || 0);
    var fullWeight = parseFloat($('#tf-full-weight').val() || 0);
    var currentWeight = parseFloat($('#tf-current-weight').val() || 0);
    var pricingMode = $('#tf-pricing-mode option:selected').text();
    var pos = $('#tf-pos').is(':checked');
    var trackWeight = $('#tf-track-weight').is(':checked');
    var trackMl = $('#tf-track-ml').is(':checked');

    var estimatedShots = Math.floor(bottleMl / shotMl);
    var remainingMl = 0;
    if (trackWeight && fullWeight > emptyWeight && currentWeight > emptyWeight) {
      var ratio = (currentWeight - emptyWeight) / (fullWeight - emptyWeight);
      ratio = Math.max(0, Math.min(1, ratio));
      remainingMl = Math.round(ratio * bottleMl);
    }

    $('#tf-metric-shots').text('Shots: ' + estimatedShots);
    $('#tf-metric-remaining').text('Remain: ' + remainingMl + ' ml');

    var summary = '<strong>' + name + '</strong> sells from <strong>' + bottleName + '</strong>. ' +
      'Bottle size is <strong>' + bottleMl + 'ml</strong> and shot size is <strong>' + shotMl +
      'ml</strong>, giving roughly <strong>' + estimatedShots + '</strong> shots per bottle.';
    $('#tf-summary-banner').html(summary);

    var effectText = '';
    effectText += trackMl
      ? 'Ml is treated as the official stock basis. '
      : 'Ml tracking is disabled, which is not recommended. ';
    effectText += trackWeight
      ? 'Bottle weight is used as a support check for current balance. '
      : 'Bottle weight checks are disabled. ';
    effectText += remainingMl > 0
      ? 'Based on the current measured weight, about ' + remainingMl + 'ml may still be in the bottle. '
      : 'Current measured weight is not yet enough to estimate remaining ml. ';
    effectText += 'Pricing mode is ' + pricingMode + '. ';
    effectText += pos
      ? 'This item will be available on POS.'
      : 'This item will stay hidden from POS.';

    $('#tf-effect-text').text(effectText);
  }

  $(document).on(
    'change input',
    '#tf-name, #tf-department, #tf-bottle-name, #tf-price, #tf-shot-name, #tf-bottle-ml, #tf-shot-ml, #tf-empty-weight, #tf-full-weight, #tf-current-weight, #tf-pricing-mode, #tf-pos, #tf-track-weight, #tf-track-ml, #tf-allow-open-bottle',
    updateBottleForm
  );

  updateBottleForm();

  $(document).on('click', '#save-sellable-draft', function () {
    saveDraft({
      form_type: 'sellable',
      name: $('#sf-name').val(),
      code: $('#sf-code').val(),
      category: $('#sf-category').val(),
      subcategory: $('#sf-subcategory').val(),
      department: $('#sf-department').val(),
      control_method: $('#sf-control-method').val(),
      pricing_mode: $('#sf-pricing-mode').val(),
      price: $('#sf-price').val(),
      min_price: $('#sf-min-price').val(),
      max_price: $('#sf-max-price').val(),
      pos: $('#sf-pos').is(':checked') ? 1 : 0,
      taxable: $('#sf-taxable').is(':checked') ? 1 : 0,
      active: $('#sf-active').is(':checked') ? 1 : 0,
      attr_accomp: $('#sf-attr-accomp').is(':checked') ? 1 : 0,
      attr_temp: $('#sf-attr-temp').is(':checked') ? 1 : 0,
      attr_chilli: $('#sf-attr-chilli').is(':checked') ? 1 : 0,
      attr_addon: $('#sf-attr-addon').is(':checked') ? 1 : 0,
      open_price: $('#sf-open-price').is(':checked') ? 1 : 0,
      require_perm: $('#sf-require-perm').is(':checked') ? 1 : 0,
      batch_source: $('#sf-batch-source').val(),
      batch_yield: $('#sf-batch-yield').val(),
      batch_unit: $('#sf-batch-unit').val(),
      bottle_ml: $('#sf-bottle-ml').val(),
      shot_ml: $('#sf-shot-ml').val(),
      empty_weight: $('#sf-empty-weight').val(),
      full_weight: $('#sf-full-weight').val()
    });
  });

  $(document).on('click', '#save-raw-draft', function () {
    saveDraft({
      form_type: 'raw',
      name: $('#rf-name').val(),
      code: $('#rf-code').val(),
      category: $('#rf-category').val(),
      store_area: $('#rf-store-area').val(),
      control_method: $('#rf-control-method').val(),
      purchase_unit: $('#rf-purchase-unit').val(),
      stock_unit: $('#rf-stock-unit').val(),
      purchase_price: $('#rf-purchase-price').val(),
      reorder: $('#rf-reorder').val(),
      conversion_basis: $('#rf-conversion-basis').val(),
      conversion_qty: $('#rf-conversion-qty').val(),
      conversion_unit: $('#rf-conversion-unit').val(),
      purchasable: $('#rf-purchasable').is(':checked') ? 1 : 0,
      stockable: $('#rf-stockable').is(':checked') ? 1 : 0,
      direct_issue: $('#rf-direct-issue').is(':checked') ? 1 : 0,
      low_control: $('#rf-low-control').is(':checked') ? 1 : 0
    });
  });

  $(document).on('click', '#save-batch-draft', function () {
    saveDraft({
      form_type: 'batch',
      name: $('#bf-name').val(),
      department: $('#bf-department').val(),
      category: $('#bf-category').val(),
      price: $('#bf-price').val(),
      sale_unit: $('#bf-sale-unit').val(),
      source_item: $('#bf-source-item').val(),
      source_qty: $('#bf-source-qty').val(),
      source_unit: $('#bf-source-unit').val(),
      yield: $('#bf-yield').val(),
      production_unit: $('#bf-production-unit').val(),
      mode: $('#bf-mode').val(),
      pos: $('#bf-pos').is(':checked') ? 1 : 0,
      stock_output: $('#bf-stock-output').is(':checked') ? 1 : 0,
      source_deduct: $('#bf-source-deduct').is(':checked') ? 1 : 0,
      notes: $('#bf-notes').val()
    });
  });

  $(document).on('click', '#save-bottle-draft', function () {
    saveDraft({
      form_type: 'bottle',
      name: $('#tf-name').val(),
      department: $('#tf-department').val(),
      bottle_name: $('#tf-bottle-name').val(),
      price: $('#tf-price').val(),
      shot_name: $('#tf-shot-name').val(),
      bottle_ml: $('#tf-bottle-ml').val(),
      shot_ml: $('#tf-shot-ml').val(),
      empty_weight: $('#tf-empty-weight').val(),
      full_weight: $('#tf-full-weight').val(),
      current_weight: $('#tf-current-weight').val(),
      pricing_mode: $('#tf-pricing-mode').val(),
      pos: $('#tf-pos').is(':checked') ? 1 : 0,
      track_weight: $('#tf-track-weight').is(':checked') ? 1 : 0,
      track_ml: $('#tf-track-ml').is(':checked') ? 1 : 0,
      allow_open_bottle: $('#tf-allow-open-bottle').is(':checked') ? 1 : 0
    });
  });

  refreshDrafts();
});
