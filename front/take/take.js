    var takeApiUrl = 'API/take_api.php';
    var takeState = {
      remainingCount: 0,
      varianceCount: 0,
      countedCount: 0
    };

    function takeApiRequest(options) {
      return $.ajax({
        url: takeApiUrl,
        type: options.type || 'GET',
        dataType: 'json',
        data: options.data || {}
      });
    }
    function applyTakeContext(context) {
      context = context || {};
      var counterName = $.trim(context.counter_name || 'Counter');
      var shiftLabel = $.trim(context.shift || 'Closed');
      $('#take_counter_name_desktop, #take_counter_name_mobile').text(counterName);
      $('#take_shift_mobile').text(shiftLabel);
    }
    function updateTakeProgress() {
      $('#take_remaining_count_desktop, #take_remaining_count_mobile').text(takeState.remainingCount || 0);
      $('#take_variance_count_desktop, #take_variance_count_mobile').text(takeState.varianceCount || 0);
    }
    function applyTakeMetrics(metrics) {
      metrics = metrics || {};
      if (typeof metrics.remaining_count !== 'undefined') {
        takeState.remainingCount = parseInt(metrics.remaining_count, 10) || 0;
      }
      if (typeof metrics.variance_count !== 'undefined') {
        takeState.varianceCount = parseInt(metrics.variance_count, 10) || 0;
      }
      if (typeof metrics.counted_count !== 'undefined') {
        takeState.countedCount = parseInt(metrics.counted_count, 10) || 0;
      }
      updateTakeProgress();
    }
    function applyTakeItemsHtml(data, metrics) {
      $("#item_id").html(data || '');
      $("#item_id_mobile").html(data || '');
      if (metrics) {
        applyTakeMetrics(metrics);
      } else {
        takeState.remainingCount = $('#item_id_mobile option').length || $('#item_id option').length || 0;
        updateTakeProgress();
      }
      syncTakeForms(data || '');
      updateSelectedItemLabel('#item_id', '#selected_item_label_desktop');
      updateSelectedItemLabel('#item_id_mobile', '#selected_item_label_mobile');
      updateTakeEntryMode('#item_id', '#telNumber', '#take_variance_desktop', '#qty_value', '#take_entry_label_desktop', '#take_entry_help_desktop');
      updateTakeEntryMode('#item_id_mobile', '#telNumber_mobile', '#take_variance_mobile', '#qty_value_mobile', '#take_entry_label_mobile', '#take_entry_help_mobile');
    }
    function bootstrapTakePage() {
      takeApiRequest({
        type: 'GET',
        data: { action: 'boot' }
      })
      .done(function(res){
        var data = res && res.data ? res.data : {};
        applyTakeContext(data.context || {});
        applyTakeItemsHtml(data.items_html || '', data.metrics || {});
        applyTakeListHtml(data.list_html || '', data.metrics || {});
      })
      .fail(function(){
        $('#results').html('<div class="alert alert-danger"><strong>Warning!</strong> Could not load stock take. Try again.</div>');
        $('#results_mobile').html('<div class="alert alert-danger"><strong>Warning!</strong> Could not load stock take. Try again.</div>');
      });
    }

    function openTakeConfirmModal() {
      takeApiRequest({
        type: 'GET',
        data: { action: 'confirm_modal' }
      })
      .done(function(res){
        $('#take_confirm_load').html((res && res.data && res.data.html) ? res.data.html : '');
        $('#take_confirm_modal').addClass('open');
      })
      .fail(function(){
        $('#take_confirm_load').html('<div class="take-empty-card">Could not load stock take review. Please try again.</div>');
        $('#take_confirm_modal').addClass('open');
      });
    }
    function closeTakeConfirmModal() {
      $('#take_confirm_modal').removeClass('open');
    }
    function openTakeVarianceModal() {
      takeApiRequest({
        type: 'GET',
        data: { action: 'variance_modal' }
      })
      .done(function(res){
        $('#take_variance_load').html((res && res.data && res.data.html) ? res.data.html : '');
        $('#take_variance_modal').addClass('open');
      })
      .fail(function(){
        $('#take_variance_load').html('<div class="take-variance-modal-card"><div class="take-variance-modal-head"><strong>Variance Items</strong><button type="button" class="take-variance-close" onclick="closeTakeVarianceModal()">&times;</button></div><div class="take-variance-modal-body"><div class="take-variance-empty">Could not load variance items. Please try again.</div></div></div>');
        $('#take_variance_modal').addClass('open');
      });
    }
    function closeTakeVarianceModal() {
      $('#take_variance_modal').removeClass('open');
    }
    function openTakeWeightModal(config) {
      var itemName = $.trim($(config.selectSelector).find('option:selected').text()) || 'Selected Item';
      $('#take_weight_item_name').text(itemName);
      $('#take_weight_results').html('');
      $('#take_weight_input').val($(config.inputSelector).val());
      $('#take_weight_modal').data('take-config', config).addClass('open');
      window.setTimeout(function () {
        $('#take_weight_input').trigger('focus').trigger('select');
      }, 60);
    }
    function closeTakeWeightModal() {
      $('#take_weight_modal').removeData('take-config').removeClass('open');
      $('#take_weight_results').html('');
    }
    function showTakeToast(kind, title, message, timeoutMs) {
      var stack = document.getElementById('take_toast_stack');
      if (!stack) {
        return;
      }
      var toast = document.createElement('div');
      toast.className = 'take-toast ' + (kind || 'success');
      toast.innerHTML = '<strong>' + (title || 'Saved') + '</strong><span>' + (message || '') + '</span>';
      stack.appendChild(toast);
      window.setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translate3d(18px, -6px, 0)';
        toast.style.transition = 'opacity .18s ease, transform .18s ease';
        window.setTimeout(function() {
          if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
          }
        }, 220);
      }, timeoutMs || 1800);
    }
    function extractAlertText(html) {
      var temp = document.createElement('div');
      temp.innerHTML = html || '';
      return (temp.textContent || temp.innerText || '').replace(/\s+/g, ' ').trim();
    }
    function del_item(id){
      takeApiRequest({
        type: 'GET',
        data: { action: 'delete_item', id: id }
      })
      .done(function(data){
        var payload = data && data.data ? data.data : {};
        applyTakeItemsHtml(payload.items_html || '', {
          remaining_count: payload.remaining_count,
          has_items: payload.has_items
        });
        applyTakeListHtml(payload.list_html || '', {
          variance_count: payload.variance_count
        });
      })
      .fail(function(){
        $('#results').html('<div class="alert alert-danger"><strong>Warning!</strong> Could not remove item. Try again.</div>');
      });
    }
    function fetchitems(){
      takeApiRequest({
        type: 'GET',
        data: { action: 'fetch_items' }
      }).done(function(res) {
          var payload = res && res.data ? res.data : {};
          applyTakeItemsHtml(payload.html || '', {
            remaining_count: payload.remaining_count,
            has_items: payload.has_items
          });
      });
    }
    function updateSelectedItemLabel(selectSelector, labelSelector) {
      var text = $.trim($(selectSelector).find('option:selected').text());
      if (!text) {
        text = 'No remaining items';
      }
      $(labelSelector).find('span').text(text);
    }
    function formatTakeNumber(value) {
      var num = parseFloat(value);
      if (isNaN(num)) {
        return '0';
      }
      return num % 1 === 0 ? String(num) : num.toFixed(2).replace(/\.?0+$/, '');
    }
    function getTakeMeta(selectSelector) {
      var option = $(selectSelector).find('option:selected');
      return {
        opening: parseFloat(option.data('opening')) || 0,
        stockTakeType: parseInt(option.data('stock-take-type'), 10) || 2,
        emptyWeight: parseFloat(option.data('empty-weight')) || 0,
        fullWeight: parseFloat(option.data('full-weight')) || 0,
        bottleMls: parseFloat(option.data('bottle-mls')) || 0,
        totNum: parseFloat(option.data('tot-num')) || 0
      };
    }
    function calcWeightTakeQty(meta, entryValue) {
      var entry = parseFloat(entryValue);
      if (isNaN(entry)) {
        return 0;
      }
      if (meta.fullWeight <= meta.emptyWeight || meta.bottleMls <= 0 || meta.totNum <= 0) {
        return NaN;
      }
      var liquidWeight = entry - meta.emptyWeight;
      if (liquidWeight < 0) {
        liquidWeight = 0;
      }
      var fullLiquidWeight = meta.fullWeight - meta.emptyWeight;
      var currentMls = (liquidWeight / fullLiquidWeight) * meta.bottleMls;
      if (currentMls < 0) {
        currentMls = 0;
      }
      if (currentMls > meta.bottleMls) {
        currentMls = meta.bottleMls;
      }
      var mlPerTot = meta.bottleMls / meta.totNum;
      if (mlPerTot <= 0) {
        return NaN;
      }
      return Math.round((currentMls / mlPerTot) * 100) / 100;
    }
    function getTakeCountedQty(selectSelector, inputSelector) {
      var meta = getTakeMeta(selectSelector);
      var entry = $(inputSelector).val();
      if (meta.stockTakeType === 1) {
        return calcWeightTakeQty(meta, entry);
      }
      var entered = parseFloat(entry);
      if (isNaN(entered)) {
        return 0;
      }
      return entered;
    }
    function updateTakeEntryMode(selectSelector, inputSelector, wrapSelector, hiddenSelector, labelSelector, helpSelector) {
      var meta = getTakeMeta(selectSelector);
      var label = 'Counted Units';
      var help = 'Enter the counted units for this item.';
      var formScope = $(inputSelector).closest('form');
      if (meta.stockTakeType === 1) {
        label = 'Take Type: Weight';
        help = 'This item is counted by weight. Tap add to list to enter the current bottle weight in a popup.';
        formScope.find('.numpad-wrap').hide();
      } else {
        formScope.find('.numpad-wrap').show();
      }
      $(labelSelector).text(label);
      $(helpSelector).text(help);
      updateTakeVarianceCard(selectSelector, inputSelector, wrapSelector, hiddenSelector);
    }
    function updateTakeVarianceCard(selectSelector, inputSelector, wrapSelector, hiddenSelector) {
      var meta = getTakeMeta(selectSelector);
      var opening = meta.opening;
      var entered = getTakeCountedQty(selectSelector, inputSelector);
      if (isNaN(entered)) {
        entered = 0;
      }
      $(hiddenSelector).val(formatTakeNumber(entered));
      var variance = entered - opening;
      var varianceCard = $(wrapSelector).find('.take-variance-card').last();
      $(wrapSelector).find('.take-variance-card').removeClass('is-ok is-warning is-danger');
      $(wrapSelector).find('.take-variance-expected').text(formatTakeNumber(opening));
      $(wrapSelector).find('.take-variance-entered').text(formatTakeNumber(entered));
      $(wrapSelector).find('.take-variance-value').text((variance > 0 ? '+' : '') + formatTakeNumber(variance));
      if (variance === 0) {
        varianceCard.addClass('is-ok');
      } else if (variance > 0) {
        varianceCard.addClass('is-warning');
      } else {
        varianceCard.addClass('is-danger');
      }
    }
    function resetTakeEntryState() {
      $('#telNumber, #telNumber_mobile').val('');
      $('#qty_value, #qty_value_mobile').val('');
      updateTakeEntryMode('#item_id', '#telNumber', '#take_variance_desktop', '#qty_value', '#take_entry_label_desktop', '#take_entry_help_desktop');
      updateTakeEntryMode('#item_id_mobile', '#telNumber_mobile', '#take_variance_mobile', '#qty_value_mobile', '#take_entry_label_mobile', '#take_entry_help_mobile');
    }
    function syncTakeForms(data) {
      var hasItems = $.trim(data).length > 0;
      if (hasItems) {
        $('#form').show();
        $('#confirm_take_desktop').hide();
        $('#form_mobile').show();
        $('#take_mobile_empty').hide();
        $('#form .numpad-wrap').show();
        $('#form_mobile .numpad-wrap').show();
        resetTakeEntryState();
        $('#telNumber_mobile').focus();
      } else {
        $('#form').hide();
        $('#confirm_take_desktop').show();
        $('#form_mobile').hide();
        $('#take_mobile_empty').show();
      }
    }
    function applyTakeListHtml(data, metrics) {
      $('#list').html(data);
      $('#list_mobile').html(data);
      if (metrics) {
        applyTakeMetrics(metrics);
      }
    }
    function bindTakePad(scopeSelector, inputSelector) {
      $(scopeSelector).find('.num').off('click.takepad').on('click.takepad', function () {
        var num = $(this);
        var text = $.trim(num.find('.txt').clone().children().remove().end().text());
        var telNumber = $(inputSelector);
        var current = telNumber.val();
        if (text === '.') {
          if (!current || current.indexOf('.') !== -1) {
            return;
          }
        }
        if (current.indexOf('.') !== -1 && current.split('.')[1].length >= 2 && text !== '.') {
          return;
        }
        telNumber.val(current + text);
        telNumber.trigger('change');
      });
      $(scopeSelector).find('#clear').off('click.takepad').on('click.takepad', function () {
        $(inputSelector).val('').trigger('change');
      });
    }
    function submitTake(formSelector, inputSelector, resultsSelector, selectSelector, wrapSelector, hiddenSelector) {
      if (getTakeMeta(selectSelector).stockTakeType === 1 && !$('#take_weight_modal').data('submitting')) {
        openTakeWeightModal({
          formSelector: formSelector,
          inputSelector: inputSelector,
          resultsSelector: resultsSelector,
          selectSelector: selectSelector,
          wrapSelector: wrapSelector,
          hiddenSelector: hiddenSelector
        });
        return;
      }
      updateTakeVarianceCard(selectSelector, inputSelector, wrapSelector, hiddenSelector);
      var str = $(formSelector).serialize();
      str += '&action=add_item';
      takeApiRequest({
        type: 'POST',
        data: str
      })
      .done(function(res){
        if (!res || !res.ok) {
          $(resultsSelector).html('');
          var message = (res && res.message) ? res.message : 'Error occured.';
          var toastKind = /error|could not|unable/i.test(message) ? 'danger' : 'warning';
          showTakeToast(toastKind, 'Check', message, 2200);
          return;
        }
        $(resultsSelector).html('');
        showTakeToast('success', 'Taken', ((res.data && res.data.item_name) ? res.data.item_name : 'Item') + ' - ' + ((res.data && res.data.counted_label) ? res.data.counted_label : '0') + ' recorded.', 1600);
        resetTakeEntryState();
        $(inputSelector).focus();
        applyTakeItemsHtml((res.data && res.data.items_html) ? res.data.items_html : '', {
          remaining_count: res.data && typeof res.data.remaining_count !== 'undefined' ? res.data.remaining_count : 0,
          has_items: res.data && typeof res.data.has_items !== 'undefined' ? res.data.has_items : false
        });
        applyTakeListHtml((res.data && res.data.list_html) ? res.data.list_html : '', {
          variance_count: res.data && typeof res.data.variance_count !== 'undefined' ? res.data.variance_count : 0
        });
      })
      .fail(function(){
        $(resultsSelector).html('');
        showTakeToast('danger', 'Error', 'Could not save the stock take right now.', 2200);
      });
    }
    $(document).ready(function () {
      bootstrapTakePage();
      bindTakePad(document, '#telNumber');
      bindTakePad('#form_mobile', '#telNumber_mobile');
      updateSelectedItemLabel('#item_id', '#selected_item_label_desktop');
      updateSelectedItemLabel('#item_id_mobile', '#selected_item_label_mobile');
      updateTakeEntryMode('#item_id', '#telNumber', '#take_variance_desktop', '#qty_value', '#take_entry_label_desktop', '#take_entry_help_desktop');
      updateTakeEntryMode('#item_id_mobile', '#telNumber_mobile', '#take_variance_mobile', '#qty_value_mobile', '#take_entry_label_mobile', '#take_entry_help_mobile');
      $('#item_id').on('change', function(){
        updateSelectedItemLabel('#item_id', '#selected_item_label_desktop');
        updateTakeEntryMode('#item_id', '#telNumber', '#take_variance_desktop', '#qty_value', '#take_entry_label_desktop', '#take_entry_help_desktop');
      });
      $('#item_id_mobile').on('change', function(){
        updateSelectedItemLabel('#item_id_mobile', '#selected_item_label_mobile');
        updateTakeEntryMode('#item_id_mobile', '#telNumber_mobile', '#take_variance_mobile', '#qty_value_mobile', '#take_entry_label_mobile', '#take_entry_help_mobile');
      });
      $('#telNumber').on('input change', function(){
        updateTakeVarianceCard('#item_id', '#telNumber', '#take_variance_desktop', '#qty_value');
      });
      $('#telNumber_mobile').on('input change', function(){
        updateTakeVarianceCard('#item_id_mobile', '#telNumber_mobile', '#take_variance_mobile', '#qty_value_mobile');
      });
      $('#take').on('click', function(e){
        e.preventDefault();
        submitTake('#form', '#telNumber', '#results', '#item_id', '#take_variance_desktop', '#qty_value');
      });
      $('#take_mobile').on('click', function(e){
        e.preventDefault();
        submitTake('#form_mobile', '#telNumber_mobile', '#results_mobile', '#item_id_mobile', '#take_variance_mobile', '#qty_value_mobile');
      });
      $('#take_weight_cancel').on('click', function(){
        closeTakeWeightModal();
      });
      $('#take_weight_confirm').on('click', function(){
        var config = $('#take_weight_modal').data('take-config');
        var weight = $.trim($('#take_weight_input').val());
        if (!config) {
          closeTakeWeightModal();
          return;
        }
        if (weight === '' || isNaN(parseFloat(weight))) {
          $('#take_weight_results').html('<div class="alert alert-warning"><strong>Check:</strong> Enter the current bottle weight.</div>');
          $('#take_weight_input').trigger('focus');
          return;
        }
        $(config.inputSelector).val(weight).trigger('change');
        $('#take_weight_modal').data('submitting', true);
        closeTakeWeightModal();
        submitTake(config.formSelector, config.inputSelector, config.resultsSelector, config.selectSelector, config.wrapSelector, config.hiddenSelector);
        $('#take_weight_modal').removeData('submitting');
      });
      $('#telNumber, #telNumber_mobile').on('click', function(){
        var inputSelector = '#' + this.id;
        var isMobile = this.id.indexOf('_mobile') !== -1;
        var selectSelector = isMobile ? '#item_id_mobile' : '#item_id';
        var resultsSelector = isMobile ? '#results_mobile' : '#results';
        var formSelector = isMobile ? '#form_mobile' : '#form';
        var wrapSelector = isMobile ? '#take_variance_mobile' : '#take_variance_desktop';
        var hiddenSelector = isMobile ? '#qty_value_mobile' : '#qty_value';
        if (getTakeMeta(selectSelector).stockTakeType === 1) {
          openTakeWeightModal({
            formSelector: formSelector,
            inputSelector: inputSelector,
            resultsSelector: resultsSelector,
            selectSelector: selectSelector,
            wrapSelector: wrapSelector,
            hiddenSelector: hiddenSelector
          });
        }
      });
      $(document).on('click', '.open-take-confirm', function(e){
        e.preventDefault();
        openTakeConfirmModal();
      });
      $(document).on('click', '.open-variance-items', function(e){
        e.preventDefault();
        openTakeVarianceModal();
      });
      $(document).on('click', '.take-live-remove', function(e){
        e.preventDefault();
        del_item($(this).data('take-delete-id'));
      });
      $(document).on('click', '#take_confirm_close_btn, #take_confirm_cancel_btn', function(e){
        e.preventDefault();
        closeTakeConfirmModal();
      });
      $(document).on('click', '#take_variance_close_btn', function(e){
        e.preventDefault();
        closeTakeVarianceModal();
      });
      $(document).on('click', '#take_confirm_now', function(e){
        e.preventDefault();
        var button = $(this);
        var feedback = $('#take_confirm_feedback');
        if (button.prop('disabled')) {
          return;
        }
        feedback.removeClass('is-warning is-error is-success').text('');
        button.prop('disabled', true).text('Confirming...');
        takeApiRequest({
          type: 'POST',
          data: { action: 'confirm_take' }
        }).done(function(res){
          var payload = res && res.data ? res.data : {};
          var status = payload.status || (res && res.ok ? 'success' : 'error');
          feedback.removeClass('is-warning is-error is-success').addClass(status === 'warning' ? 'is-warning' : (status === 'success' ? 'is-success' : 'is-error')).text((res && res.message) ? res.message : 'Confirmation failed.');
          if (res && res.ok) {
            setTimeout(function(){
              if (payload.redirect) {
                window.location.href = payload.redirect;
              } else {
                closeTakeConfirmModal();
              }
            }, 500);
            return;
          }
          button.prop('disabled', false).text('Confirm Now');
        }).fail(function(){
          feedback.removeClass('is-warning is-success').addClass('is-error').text('Could not complete confirmation. Please try again.');
          button.prop('disabled', false).text('Confirm Now');
        });
      });
      $('#take_confirm_modal').on('click', function(e){
        if (e.target === this) {
          e.preventDefault();
        }
      });
      $('#take_variance_modal').on('click', function(e){
        if (e.target === this) {
          e.preventDefault();
        }
      });
      $('#take_weight_modal').on('click', function(e){
        if (e.target === this) {
          closeTakeWeightModal();
        }
      });
    });
