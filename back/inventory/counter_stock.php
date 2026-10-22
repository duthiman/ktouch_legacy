<?php
require("../clude/head.php");
?>
<link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
<link href="counter_stock.css?v=<?php echo filemtime(__DIR__ . '/counter_stock.css'); ?>" rel="stylesheet" />

<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>

  <div id="content" class="content counter-stock-page">
    <section class="counter-stock-titlebar">
      <div class="counter-stock-title-icon"><i class="fa fa-bar-chart"></i></div>
      <div class="counter-stock-title-copy">
        <h2>Counter Stock Dashboard</h2>
        <p>Monitor and manage your inventory across all counters in real-time.</p>
      </div>
      <button type="button" class="btn counter-stock-refresh" onclick="window.location.reload();">
        <i class="fa fa-refresh"></i> Refresh Data
      </button>
    </section>

    <section class="counter-stock-toolbar">
      <div class="counter-filter-field counter-filter-counter-field">
        <label for="filter-counter">Counter</label>
        <select id="filter-counter" class="form-control"><option value="">Select Counter</option></select>
      </div>
      <div class="counter-filter-field">
        <label for="filter-category">Category</label>
        <select id="filter-category" class="form-control"><option value="">All Categories</option></select>
      </div>
      <div class="counter-filter-field">
        <label for="filter-subcategory">Sub Category</label>
        <select id="filter-subcategory" class="form-control"><option value="">All Sub Categories</option></select>
      </div>
      <div class="counter-filter-field">
        <label for="filter-stock-type">Stock Type</label>
        <select id="filter-stock-type" class="form-control"><option value="">All Stock Types</option></select>
      </div>
      <div class="counter-filter-field">
        <label for="filter-department">Department</label>
        <select id="filter-department" class="form-control"><option value="">All Departments</option></select>
      </div>
      <button type="button" class="btn counter-filter-apply" id="apply-counter-stock-filters">
        <i class="fa fa-filter"></i> Apply Filters
      </button>
      <button type="button" class="btn counter-filter-clear" id="clear-counter-stock-filters">
        <i class="fa fa-refresh"></i> Clear
      </button>
    </section>

    <section class="counter-stock-cards">
      <div class="row" id="counter-stock-cards">
        <div class="col-md-2 col-sm-6">
          <div class="counter-stock-card">
            <h4 class="counter-stock-card-title">Loading Counters</h4>
            <span class="counter-stock-card-number">...</span>
            <span class="counter-stock-card-details">Preparing dashboard cards</span>
          </div>
        </div>
      </div>
    </section>

    <section class="counter-stock-table-shell">
      <div class="counter-stock-table-toolbar">
        <h3>Counter Stock Details</h3>
        <div class="counter-stock-actions">
          <div id="counter-stock-search-home" class="counter-stock-search-home"></div>
          <button type="button" class="btn btn-success" id="counter-stock-export-excel"><i class="fa fa-file-excel-o"></i> Export Excel</button>
          <button type="button" class="btn btn-danger" id="counter-stock-export-pdf"><i class="fa fa-file-pdf-o"></i> Export PDF</button>
          <button type="button" class="btn btn-primary" id="counter-stock-print-a4"><i class="fa fa-print"></i> Print A4</button>
          <button type="button" class="btn btn-warning" id="counter-stock-print-thermal"><i class="fa fa-print"></i> Print Thermal</button>
        </div>
      </div>

      <div class="counter-stock-table-wrap">
        <div id="counter-stock-report" class="counter-stock-report">
          <div class="text-center">Loading counter stock...</div>
        </div>
      </div>
    </section>
  </div>

  <div id="stock-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" id="stock-load"></div>
  </div>

  <div id="counter-stock-export-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          <h4 class="modal-title">Choose Headers</h4>
        </div>
        <div class="modal-body">
          <p>Select the columns you want to include for export or print.</p>
          <div class="export-columns-grid" id="counter-stock-export-columns"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" id="counter-stock-run-excel">Export Excel</button>
          <button type="button" class="btn btn-danger" id="counter-stock-run-pdf">Export PDF</button>
          <button type="button" class="btn btn-primary" id="counter-stock-run-a4">Print A4</button>
          <button type="button" class="btn btn-warning" id="counter-stock-run-thermal">Print Thermal</button>
        </div>
      </div>
    </div>
  </div>

  <div id="counter-reorder-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          <h4 class="modal-title">Confirm Reorder Level Change</h4>
        </div>
        <div class="modal-body">
          <p id="counter-reorder-item-label" class="text-muted"></p>
          <input type="hidden" id="counter-reorder-item-id">
          <input type="hidden" id="counter-reorder-counter-id">
          <input type="hidden" id="counter-reorder-old">
          <input type="hidden" id="counter-reorder-new">
          <div id="counter-reorder-change-message" class="counter-reorder-change-message"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="save-counter-reorder">Confirm Change</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require("../clude/foot.php"); ?>
<script>
$(document).ready(function () { App.init(); });
<?php include("inve.js"); ?>
</script>
<script src="counter_stock.js?v=<?php echo filemtime(__DIR__ . '/counter_stock.js'); ?>"></script>
<script>
(function ($) {
  function normalizeCounterName(value) {
    return $.trim($(value).text()).toUpperCase().replace(/\s+/g, ' ');
  }

  function paintCounterColumns() {
    var table = document.getElementById('data-table-combine');
    if (!table || !table.tHead || !table.tHead.rows.length) return;
    $(table).find('th,td').removeClass('counter-col-bar counter-col-kitchen counter-col-b');
    $(table.tHead.rows[0].cells).each(function (index) {
      var name = normalizeCounterName(this);
      var className = name === 'BAR' ? 'counter-col-bar' :
                      name === 'KITCHEN' ? 'counter-col-kitchen' :
                      name === 'COUNTER B' ? 'counter-col-b' : '';
      if (className) $(table).find('tr').each(function () { $(this.cells[index]).addClass(className); });
    });
  }

  function decorateCards() {
    $('#counter-stock-cards .counter-stock-card').each(function () {
      var name = normalizeCounterName($(this).find('.counter-stock-card-title'));
      $(this).removeClass('card-bar card-kitchen card-counter-b');
      if (name === 'BAR') $(this).addClass('card-bar');
      if (name === 'KITCHEN') $(this).addClass('card-kitchen');
      if (name === 'COUNTER B') $(this).addClass('card-counter-b');
    });
  }

  function moveSearch() {
    var $filter = $('#data-table-combine_filter');
    if ($filter.length && !$('#counter-stock-search-home').children().length) {
      $('#counter-stock-search-home').append($filter);
      $filter.find('label').contents().filter(function(){ return this.nodeType === 3; }).remove();
      $filter.find('input').attr('placeholder', 'Search stock...');
    }
  }

  var observer = new MutationObserver(function () {
    decorateCards(); paintCounterColumns(); moveSearch();
  });
  $(function () {
    observer.observe(document.getElementById('counter-stock-cards'), {childList:true, subtree:true});
  var report = document.getElementById('counter-stock-report');
  if (report) observer.observe(report, {childList:true, subtree:true});
    $(document).on('draw.dt', '#data-table-combine', function(){ paintCounterColumns(); moveSearch(); });
    setTimeout(function(){ decorateCards(); paintCounterColumns(); moveSearch(); }, 250);
  });
})(jQuery);
</script>
</body>
</html>
