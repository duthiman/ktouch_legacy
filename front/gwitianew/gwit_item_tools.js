(function ($) {
  'use strict';

  function filterItems(value) {
    var query = String(value || '').toLowerCase().trim();
    $('#gwit-item-search-desktop, #gwit-item-search-mobile').val(value || '');
    $('.gwit-item-btn').each(function () {
      var name = $(this).text().toLowerCase();
      $(this).toggle(!query || name.indexOf(query) !== -1);
    });
  }

  $(function () {
    $(document).on('input', '#gwit-item-search-desktop, #gwit-item-search-mobile', function () {
      filterItems($(this).val());
    });
    $(document).on('click', '.gwit-refresh', function () {
      $('#gwit-item-search-desktop, #gwit-item-search-mobile').val('');
      if (typeof window.loadBootstrap === 'function') {
        window.loadBootstrap();
      } else {
        window.location.reload();
      }
    });
  });
}(jQuery));
