(function($) {
  'use strict';

  window.runMenuSearch = function() {
    var $button = $('#apply-menu-filters');
    var originalText = $button.html();
    var filters = {
      action: 'search',
      cat_id: $('#cat_id').val() || 0,
      sub_id: $('#sub_id').val() || 0,
      dpt: $('#dpt').val() || 0,
      stock: $('#stock').val() || 0,
      counter_id: $('#counter_id').val() || 0,
      counter_assignment: window.menuCounterAssignmentMode || 'assigned',
      search: $('#menu-search').val() || ''
    };

    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Searching');
    $.ajax({ url: menuApiUrl, type: 'GET', dataType: 'json', data: filters })
      .done(function(response) {
        var data = response && response.data ? response.data : {};
        $('.menu-active-count').html('<i class="fa fa-archive"></i> ' + (data.total || 0) + ' matching items');
        reloadMenuTable();
      })
      .fail(function(xhr) {
        var message = 'Could not search menu items.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        if (window.Swal && Swal.fire) { Swal.fire('Search failed', message, 'error'); } else { window.alert(message); }
      })
      .always(function() {
        $button.prop('disabled', false).html(originalText);
      });
  };

  $(function() {
    App.init();
    loadMenuBootstrap().done(function() {
      hydrateMenuQuickForm();
      hydrateCounterFilter();
      window.menuCounterAssignmentMode = 'assigned';
      updateCounterAssignmentTabs();
      if ($.fn.select2) {
        $('.pickers').select2({ width: '100%' });
      }
    }).always(function() {
      showRecords();
    });

    $('.menu-add-product').on('click', function(e) {
      e.preventDefault();
      $('#prods-add').trigger('click');
    });

    $(document).on('click', '.menu-drop-toggle', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $drop = $(this).closest('.menu-drop');
      $('.menu-drop.open').not($drop).removeClass('open');
      $drop.toggleClass('open');
    });
    $(document).on('click', function() { $('.menu-drop.open').removeClass('open'); });
    $(document).on('click', '.menu-quick-add-product', function(e) {
      e.preventDefault();
      $('.menu-drop').removeClass('open');
      $('#prods-add').trigger('click');
    });
    $(document).on('click', '.menu-quick-add-category', function(e) {
      e.preventDefault();
      $('.menu-drop').removeClass('open');
      $('#cat').trigger('click');
    });
    $(document).on('click', '.menu-quick-add-subcategory', function(e) {
      e.preventDefault();
      $('.menu-drop').removeClass('open');
      $('#sub_cat').trigger('click');
    });
    $(document).on('click', '.menu-quick-add-settings', function(e) {
      e.preventDefault();
      $('.menu-drop').removeClass('open');
      $('#set').trigger('click');
    });
  });
}(jQuery));
