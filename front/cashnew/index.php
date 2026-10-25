<?php  
include("../clude/head.php");   
include_once('../ret/API/ret_schema.php');
$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");
if ($shift==0) {
  $shift =DATE('Y-m-d');
}
$cashier_name = get_column('kp_users','username',"user_id='$user_id'");
if (function_exists('kp_return_ensure_schema')) {
  kp_return_ensure_schema($connection);
}
?>
<link href="cash.css" rel="stylesheet" />
   <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
  <link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" /> 
  <link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" /> 
<link href="../../assets/plugins/DataTables/extensions/Select/css/select.bootstrap.min.css" rel="stylesheet" />
<!-- ================== BEGIN DATATABLE================== --> 
<link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<link href="../assets/css/virtual_keyboard.css" rel="stylesheet" />
<!-- ================== END DATATABLE ================== -->  
<body class="fixed-sn deep-orange-skin cashier-compact">
  <!-- <header>
    <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
      <ul class="nav navbar-nav navbar-left"> 
      <li  class="nav-item">
          <a  href="../dash/dash.php" style=" font-family: 'comic sans ms'; font-size: 15PX; font-weight: bold; color: orange !important; padding-top:15%; border-radius: 10%; height: 50px; width:100px; background-color: #FFF; border: 2px solid orange;" class="btn btn-sm btn-success">SALES</a>
        </li>  
      </ul>
      <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
        <li  class="nav-item">
          <a  href="../log_out.php" style=" font-family: 'comic sans ms'; font-size: 15PX; font-weight: bold; color: orange !important; padding-top:15%; border-radius: 10%; height: 50px; width:100px; background-color: #FFF; border: 2px solid orange;" class="btn btn-sm btn-success">LOG OUT</a>
        </li>  
      </ul>
    </nav> 
  </header> -->
  <div id="content" class="content m-l-10 cash-desktop-shell">
    <div class="cash-page-head">
      <div>
        <h2>Cashier Clearing Desk</h2>
        <p>Clear bills, review collections, print shift summaries, and close the shift from one simple workspace.</p>
      </div>
      <div class="cash-page-badge">Shift: <span id="cash-shift-badge"><?php echo $shift; ?></span></div>
    </div>
    <div class="row cash-main-layout">
      <div class="col-md-3">
        <div class="cash-sidebar-stack">
          <div class="cash-cashier-card">
             <strong>Active Cashier</strong>
             <span><?php echo htmlspecialchars($cashier_name, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="cash-panel">
            <div class="cash-panel-head"><h4>Today</h4></div>
            <div class="cash-panel-body">
              <div class="cash-mission-grid">
                <button type="button" class="cash-mission-card cash-mission-primary" id="cash-card-clear-today">
                  <strong>Clear Today's Bills</strong>
                  <span id="cash-today-uncleared-bills">0</span>
                  <small>Unpaid bills waiting for cashier action.</small>
                </button>
                <button type="button" class="cash-mission-card cash-mission-secondary" id="cash-card-returns" disabled>
                  <strong>Approve Returns</strong>
                  <span id="cash-pending-returns-count">0</span>
                  <small id="cash-pending-returns-label">Counter handles returns</small>
                </button>
                <button type="button" class="cash-mission-card cash-mission-dark" id="cash-card-reprint">
                  <strong>Reprint Waiter Bills</strong>
                  <span id="cash-reprintable-bills">0</span>
                  <small>Open today's bill list and reprint from there.</small>
                </button>
                <button type="button" class="cash-mission-card cash-mission-soft" id="cash-card-clear-waiters">
                  <strong>Clear Waiters</strong>
                  <span id="cash-waiters-to-clear">0</span>
                  <small>Waiters still carrying uncleared bills this shift.</small>
                </button>
              </div>
            </div>
          </div>
          <div class="cash-panel">
            <div class="cash-panel-head"><h4>Money</h4></div>
            <div class="cash-panel-body">
              <div class="cash-focus-grid">
                <button type="button" class="cash-focus-card" id="cash-card-cashbox">
                  <strong>Manage Cashbox</strong>
                  <span id="cash-cashbox-total-today">0.00</span>
                  <small><span id="cash-cashbox-entries">0</span> payment entries today</small>
                </button>
                <button type="button" class="cash-focus-card" id="cash-card-cashbox-summary">
                  <strong>Cashbox Summary</strong>
                  <span>Report</span>
                  <small>Daily paid totals, payment modes, and receipt rows.</small>
                </button>
                <button type="button" class="cash-focus-card" id="cash-card-expenses">
                  <strong>Enter Expenses</strong>
                  <span id="cash-expense-total-side">0.00</span>
                  <small><span id="cash-expense-count-side">0</span> expenses captured this shift</small>
                </button>
                <button type="button" class="cash-focus-card" id="cash-card-credit">
                  <strong>Add Customer Credit</strong>
                  <span id="cash-credit-customer-count-side">0</span>
                  <small>Customers with credit sales today</small>
                </button>
                <button type="button" class="cash-focus-card" id="cash-card-summary">
                  <strong>Cashier Summary</strong>
                  <span>Report</span>
                  <small>Sales, counters, departments, payments, credit, revenue, expenses, and purchases.</small>
                </button>
              </div>
            </div>
          </div>
          <div class="cash-panel">
            <div class="cash-panel-head"><h4>Coming Next</h4></div>
            <div class="cash-panel-body">
              <div class="cash-plan-grid">
                <div class="cash-plan-card">
                  <strong>Stock Desk</strong>
                  <small>Buy stock, raise requests, and transfer approvals will live here.</small>
                </div>
                <div class="cash-plan-card">
                  <strong>Cashbox Reports</strong>
                  <small>Shift reporting, summaries, and deeper cashbox controls will follow next.</small>
                </div>
              </div>
            </div>
          </div>
          <div class="cash-panel">
            <div class="cash-panel-head"><h4>Legacy Shortcuts</h4></div>
            <div class="cash-panel-body cash-legacy-shortcuts">
              <div class="cash-action-stack">
                <div class="cash-actions-grid">
                  <button id="clearb" class="cash-action-btn">Clear Current Bills</button>
                  <button id="clearp" class="cash-action-btn">Clear Previous Bills</button>
                  <button id="clearsta" class="cash-action-btn">Clear Staff</button>
                  <button id="mergebills" class="cash-action-btn">Merge Bills</button>
                  <button id="cashbox" class="cash-action-btn">Cash Box</button>
                  <button id="cashbox-summary" class="cash-action-btn">Cashbox Summary</button>
                  <button id="kpesa" class="cash-action-btn has-badge">KPesa<span class="cash-action-badge" id="cash-kpesa-badge" style="display:none;">0</span></button>
                  <?php if (function_exists('get_print_mode') ? (int)get_print_mode() == 1 : true) { ?>
                    <button id="printsum" class="cash-action-btn">Print Shift Summary</button>
                  <?php } else { ?>
                    <a href="../chaba/receipt_counter.php?type=shift_sum&id=1&amp;return_to=..%2Fcash%2Findex.php" class="cash-action-btn" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">Print Shift Summary</a>
                  <?php } ?>
                  <button id="handover" class="cash-action-btn">Shift Handover</button>
                  <button id="customer-credit" class="cash-action-btn">Credit Customers</button>
                  <button id="expenses" class="cash-action-btn">Add Expense</button>
                  <button id="cashier-summary" class="cash-action-btn">Cashier Summary</button>
                </div>
 
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-9">
        <div class="cash-hero-grid">
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-accent cash-hero-button" id="cash-hero-expenses">
            <strong>Today's Expenses</strong>
            <span id="cash-expense-total">0.00</span>
            <div class="cash-hero-merge">
              <div>
                <small>Entries</small>
                <b id="cash-expense-count-top">0</b>
              </div>
              <div>
                <small>Net After Exp</small>
                <b id="cash-net-after-expense-top">0.00</b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-button" id="cash-hero-sales">
            <strong>Total Sales</strong>
            <span id="cash-total-sales">0.00</span>
            <div class="cash-hero-merge">
              <div>
                <small>Sales Paid</small>
                <b id="cash-sales-paid">0.00</b>
              </div>
              <div>
                <small>Sales Unpaid</small>
                <b id="cash-sales-unpaid">0.00</b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-button" id="cash-hero-paid-summary">
            <strong>Paid Summary</strong>
            <div class="cash-hero-merge">
              <div>
                <small>Previous Paid</small>
                <b id="cash-previous-paid">0.00</b>
              </div>
              <div>
                <small>Total Paid</small>
                <b id="cash-total-paid">0.00</b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-credit cash-hero-button" id="cash-hero-credit">
            <strong>Credit Paid Summary</strong>
            <span id="cash-credit-total-top">0.00</span>
            <div class="cash-hero-merge">
              <div>
                <small>Paid Today</small>
                <b id="cash-credit-paid-today-top">0.00</b>
              </div>
              <div>
                <small>Paid Previous</small>
                <b id="cash-credit-paid-previous-top">0.00</b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-button" id="cash-hero-credit-customers"><strong>Credit Customers</strong><span id="cash-credit-customers">0</span></button>
        </div> 
        <div class="cash-section-rail">
          <div class="cash-section-pill cash-section-pill-today">
            <strong>Today Desk</strong>
            <span>Clear bills, approve returns, reprint waiter bills, and clear waiters.</span>
          </div>
      <div class="cash-section-pill cash-section-pill-money">
            <strong>Money Desk</strong>
            <span>Cashbox, expenses, and customer credit stay together in one cashier workspace.</span>
          </div>
          <div class="cash-section-pill cash-section-pill-next">
            <strong>Next Phases</strong>
            <span>Stock approvals, transfers, and cashbox reports can plug into this same shell next.</span>
          </div>
        </div>
        <div id="cash-main-area">
          <div class="cash-work-row">
            <div class="cash-work-pane cash-work-pane-main">
              <div class="cash-panel">
                <div class="cash-panel-head"><h4>Work Area</h4></div>
                <div class="cash-panel-body cash-scroll-box" id="cleardiv">
                  <div class="cash-desk-placeholder">
                    Pick an action from the left to load Cash Box, KPesa, Clear Staff, Credit, or Expenses here.
                  </div>
                </div>
              </div>
            </div>
            <div class="cash-work-pane cash-work-pane-side">
              <div class="cash-panel">
                <div class="cash-panel-head"><h4>Selected Bills</h4></div>
                <div class="cash-panel-body cash-scroll-box" id="sale-items"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

        <!--/Main layout-->
      <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true" >
        <div class="modal-dialog" id="load"></div>
      </div>
      <div id="modal2" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true" >
        <div class="modal-dialog" id="load2"></div>
      </div>
        </div> 
      </div>
    </div>
  </div> 
  <!-- ================== BEGIN BASE JS ================== -->
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>  
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>  
  <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
    <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script> 
      <script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script> 
  <script src="../assets/js/virtual_keyboard.js"></script>
  </body>
</html>
<script type="text/javascript">  
  var cashApiUrl = 'API/cash_api.php';
  var cashPageState = {
    returnCashierMode: 0,
    shift: $('#cash-shift-badge').text() || ''
  };

  function isCashModalActive() {
    return $('#modal').hasClass('show') || $('#modal2').hasClass('show');
  }

  function loadCashLive() {
    $.ajax({
      url: cashApiUrl,
      type: 'GET',
      dataType: 'json',
      data: { action: 'live' }
    })
    .done(function(data){
      var payload = data && data.data ? data.data : data;
      cashPageState.returnCashierMode = parseInt(payload.return_cashier_mode || 0, 10);
      cashPageState.shift = payload.shift || cashPageState.shift;
      $('#cash-shift-value').text(payload.shift);
      $('#cash-shift-badge').text(payload.shift);
      $('#cash-total-sales').text(payload.total_sales);
      $('#cash-sales-unpaid').text(payload.sales_unpaid);
      $('#cash-sales-paid').text(payload.sales_paid);
      $('#cash-previous-paid').text(payload.previous_paid);
      $('#cash-total-paid').text(payload.total_paid);
      $('#cash-credit-total').text(payload.credit_today);
      $('#cash-credit-total-top').text(payload.credit_today);
      $('#cash-credit-paid-today').text(payload.credit_paid_today);
      $('#cash-credit-paid-today-top').text(payload.credit_paid_today);
      $('#cash-credit-paid-previous').text(payload.credit_paid_previous);
      $('#cash-credit-paid-previous-top').text(payload.credit_paid_previous);
      $('#cash-credit-customers').text(payload.credit_customer_count);
      $('#cash-credit-customer-count-side').text(payload.credit_customer_count);
      $('#cash-expense-total-top').text(payload.expense_total);
      $('#cash-expense-total-side').text(payload.expense_total);
      $('#cash-expense-count').text(payload.expense_count);
      $('#cash-expense-count-top').text(payload.expense_count);
      $('#cash-expense-count-side').text(payload.expense_count);
      $('#cash-today-uncleared-bills').text(payload.today_uncleared_bills || 0);
      $('#cash-pending-returns-count').text(payload.pending_returns_count || 0);
      $('#cash-pending-returns-label').text(payload.pending_returns_label || 'Counter handles returns');
      $('#cash-reprintable-bills').text(payload.reprintable_bills || 0);
      $('#cash-waiters-to-clear').text(payload.waiters_to_clear || 0);
      $('#cash-cashbox-total-today').text(payload.cashbox_total_today || 0);
      $('#cash-cashbox-entries').text(payload.cashbox_entries || 0);
      $('#cash-card-returns').prop('disabled', !cashPageState.returnCashierMode);
      if (parseInt(payload.kpesa_unapproved_count || 0, 10) > 0) {
        $('#cash-kpesa-badge').text(payload.kpesa_unapproved_count).show();
      } else {
        $('#cash-kpesa-badge').hide().text('0');
      }
      var totalPaid = parseFloat(payload.total_paid || 0);
      var expenseTotal = parseFloat(payload.expense_total || 0);
      $('#cash-net-after-expense-top').text((totalPaid - expenseTotal).toFixed(2));
    });
  }
  function ensureCashWorkShell() {
    if ($('#cleardiv').length) {
      return $('#cleardiv');
    }
    var selectedHtml = $('#sale-items').length ? $('#sale-items').html() : '';
    var selectedInvoices = $('#sale-items').length ? ($('#sale-items').attr('data-selected-invoices') || '') : '';
    $('#cash-main-area').html(
      '<div class="cash-work-row">' +
        '<div class="cash-work-pane cash-work-pane-main">' +
          '<div class="cash-panel">' +
            '<div class="cash-panel-head"><h4>Work Area</h4></div>' +
            '<div class="cash-panel-body cash-scroll-box" id="cleardiv">' +
              '<div class="cash-desk-placeholder">Pick an action from the left to load Cash Box, KPesa, Clear Staff, Credit, or Expenses here.</div>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="cash-work-pane cash-work-pane-side">' +
          '<div class="cash-panel">' +
            '<div class="cash-panel-head"><h4>Selected Bills</h4></div>' +
            '<div class="cash-panel-body cash-scroll-box" id="sale-items" data-selected-invoices="' + $('<div>').text(selectedInvoices).html() + '">' + selectedHtml + '</div>' +
          '</div>' +
        '</div>' +
      '</div>'
    );
    return $('#cleardiv');
  }
  function loadDeskPanel(url, data, emptyMessage) {
    var $target = ensureCashWorkShell();
    var panelMap = {
      'cash_mode_clear_bill': 'clear_bill_mode',
      'cash_mode_clear_waiter': 'clear_waiter_mode',
      'cashbox_panel.php': 'cashbox',
      'kpesa_panel.php': 'kpesa',
      'merge/index.php': 'merge',
      'clrbill/clearb.php': 'clearb',
      'clrbill/clearp.php': 'clearp',
      'clrstaff/clearsta.php': 'clearsta'
    };
    if (panelMap[url]) {
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        dataType: 'html',
        data: { action: 'panel', panel: panelMap[url] }
      })
      .done(function(html){
        $target.html(html);
      })
      .fail(function(){
        $target.html('<div class="alert alert-danger">' + (emptyMessage || 'Could not load this panel.') + '</div>');
      });
      return;
    }
    $.ajax({
      url: url,
      type: 'GET',
      data: data || {},
      dataType: 'html'
    })
    .done(function(data){
      $target.html(data);
    })
    .fail(function(){
      $target.html('<div class="alert alert-danger">' + (emptyMessage || 'Could not load this panel.') + '</div>');
    });
  }
  function filter_uncleared(id){
    $.ajax({
      url: cashApiUrl,
      type: 'GET',
      data: { action: 'clrbill_staff_sales', id: id },
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data).css('visibility', 'visible');
    })
    .fail(function(){
      $('#load').html('<div class="alert alert-danger">Could not load uncleared bills.</div>').css('visibility', 'visible');
    });
  } 
  function bulk_pay(){ 
    var invoices = [];
    $("input.bulk:checked").each(function() { 
      invoices.push($(this).val()); 
    });
    invoices = invoices
      .map(function(v){ return $.trim(String(v)); })
      .filter(function(v){ return v !== ''; })
      .sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); });
    var invoiceString = invoices.join(',');
    $.ajax({
      url: cashApiUrl,
      type: 'POST',
      data: { action: 'modal', modal: 'bulk_form', invoices: invoiceString },
      dataType: 'html'
    })
    .done(function(data){  
      $('#sale-items').html(''); // blank before load.
      $('#sale-items')
        .attr('data-selected-invoices', invoiceString)
        .html(data);
    })
    .fail(function(){
      $('#sale-items').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
    }); 
  }  
  function pay(){ 
    var invoiceString = $('#sale-items').attr('data-selected-invoices') || '';
    var invoices = invoiceString ? invoiceString.split(',').filter(function(v){ return $.trim(v) !== ''; }) : [];
    if (!invoices.length) {
      $("input.bulk:checked").each(function() {
        invoices.push($(this).val());
      });
      invoices = invoices
        .map(function(v){ return $.trim(String(v)); })
        .filter(function(v){ return v !== ''; })
        .sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); });
      invoiceString = invoices.join(',');
    }
    amount=$('#amo').text();
    orduser=$('#cash_btn').data('orduser') || 0;
    type=$('#user').data('id2');
    console.log(orduser);
    console.log(type);
    $('#modal').modal('show');
    $.ajax({
      url: cashApiUrl,
      type: 'POST',
      data:  { action: 'modal', modal: 'pay_modal', invoices: invoiceString, amount: amount, orduser: orduser, type: type },
      dataType: 'html'
    }) 
    .done(function(data){  
      //console.log(data);
      $('#load').html('');  
      $('#load').html(data);
    })
    .fail(function(){
      $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
    }); 
  }
  function sendToCredit() {
    var invoiceString = $('#sale-items').attr('data-selected-invoices') || '';
    var invoices = invoiceString ? invoiceString.split(',').filter(function(v){ return $.trim(v) !== ''; }) : [];
    if (!invoices.length) {
      $("input.bulk:checked").each(function() {
        invoices.push($(this).val());
      });
      invoices = invoices
        .map(function(v){ return $.trim(String(v)); })
        .filter(function(v){ return v !== ''; })
        .sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); });
      invoiceString = invoices.join(',');
    }
    var amount = $('#amo').text();
    if (!invoices.length) {
      alert('Select at least one bill to send to credit.');
      return;
    }
    $('#modal').modal('show');
    $.ajax({
      url: cashApiUrl,
      type: 'POST',
      data: { action: 'modal', modal: 'credit_assign_modal', invoices: invoiceString, amount: amount },
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data);
    })
    .fail(function(){
      $('#load').html('<div class="alert alert-danger">Could not load customer credit form.</div>');
    });
  }

  $( document ).ready(function() { 
    loadCashLive();
    loadDeskPanel('cashbox_panel.php', {}, 'Could not load cash box.');
    try {
      if (new URLSearchParams(window.location.search).get('desk') === 'clearb') {
        setTimeout(function(){ $('#clearb').trigger('click'); }, 250);
      }
    } catch (e) {}
    setInterval(function(){
      if (isCashModalActive()) {
        return;
      }
      loadCashLive();
    }, 15000);
    $(document).on('click','#cash-card-clear-today', function(e){
      e.preventDefault();
      $('#clearb').trigger('click');
    });
    $(document).on('click','#cash-card-returns', function(e){
      e.preventDefault();
      if (cashPageState.returnCashierMode) {
        window.location.href = '../ret/appr/returns.php';
        return;
      }
      alert('Returns are currently approved at counter level, not from cashier desk.');
    });
    $(document).on('click','#cash-card-reprint', function(e){
      e.preventDefault();
      $('#clearb').trigger('click');
    });
    $(document).on('click','#cash-card-clear-waiters', function(e){
      e.preventDefault();
      $('#clearsta').trigger('click');
    });
    $(document).on('click','#cash-card-cashbox', function(e){
      e.preventDefault();
      $('#cashbox').trigger('click');
    });
    $(document).on('click','#cash-card-cashbox-summary', function(e){
      e.preventDefault();
      $('#cashbox-summary').trigger('click');
    });
    $(document).on('click','#cash-card-expenses', function(e){
      e.preventDefault();
      $('#expenses').trigger('click');
    });
    $(document).on('click','#cash-card-credit', function(e){
      e.preventDefault();
      $('#customer-credit').trigger('click');
    });
    $(document).on('click','#cash-card-summary', function(e){
      e.preventDefault();
      $('#cashier-summary').trigger('click');
    });
      $(document).on('click','#split', function(e){
        e.preventDefault();
        var uid = $(this).data('id'); // get id of clicked row 
        $('#load').html(''); // leave this diff blank
        $('#modal-loader').show(); // load ajax loader button cliked
        $.ajax({
          url: cashApiUrl,
          type: 'POST',
          data: { action: 'modal', modal: 'split_modal', id: uid },
          dataType: 'html'
        })
        .done(function(data){
          $('#load').html(''); // blank before load.
          $('#load').html(data);
        }).fail(function(){
          $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
          $('#modal-loader').hide();
        });
      }); 
    $(document).on('click','#clearp', function(e){  
      e.preventDefault();
      loadDeskPanel('clrbill/clearp.php', {}, 'Could not load previous bills.');
    });
    $(document).on('click','#clearsta', function(e){  
      e.preventDefault();
      loadDeskPanel('clrstaff/clearsta.php', {}, 'Could not load waiter clearance desk.');
    }); 
    $(document).on('click','#mergebills', function(e){
      e.preventDefault();
      loadDeskPanel('merge/index.php', {}, 'Could not load merge bills desk.');
    });
    $(document).on('click','#cashbox', function(e){
      e.preventDefault();
      loadDeskPanel('cashbox_panel.php', {}, 'Could not load cash box.');
    });
    $(document).on('click','#cashbox-summary', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'cashbox_summary_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load cashbox summary.</div></div></div></div>');
      });
    });
    $(document).on('click','#kpesa', function(e){
      e.preventDefault();
      loadDeskPanel('kpesa_panel.php', {}, 'Could not load KPesa desk.');
    });
    $(document).on('click','#credit-today', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'credit_snapshot_modal', mode: 'today' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load today credit.</div></div></div></div>');
      });
    });
    $(document).on('click','#credit-paid-today', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'credit_snapshot_modal', mode: 'paid_today' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load paid credit for today.</div></div></div></div>');
      });
    });
    $(document).on('click','#credit-paid-previous', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'credit_snapshot_modal', mode: 'paid_previous' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load previous paid credit.</div></div></div></div>');
      });
    });
    $(document).on('click','#view-expenses, #view-expense-count', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'expenses_panel_modal', shift: cashPageState.shift },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load expenses.</div></div></div></div>');
      });
    });
    $(document).on('click','#cash-hero-expenses', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'expenses_panel_modal', shift: cashPageState.shift },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load expenses.</div></div></div></div>');
      });
    });
    $(document).on('click','#cash-hero-credit', function(e){
      e.preventDefault();
      $('#credit-today').trigger('click');
    });
    $(document).on('click','#cash-hero-credit-customers', function(e){
      e.preventDefault();
      $('#customer-credit').trigger('click');
    });
    $(document).on('click','#cash-hero-paid-summary', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'paid_summary_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load paid summary.</div></div></div></div>');
      });
    });
    $(document).on('click','#cash-hero-sales', function(e){
      e.preventDefault();
      $('#clearb').trigger('click');
    });
    $(document).on('click','#clearb', function(e){  
      e.preventDefault();
      loadDeskPanel('clrbill/clearb.php', {}, 'Could not load current bills.');
    });
    $(document).on('click','#colle', function(e){ 
      id=$(this).data('id'); 
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST', 
        data: { action: 'modal', modal: 'collect_modal', id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(''); 
        $('#load').html(data);
      })
      .fail(function() {
        $('#modal-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
         // $('#modal-loader').hide();
      });
    }); 
    $(document).on('click','#errview', function(e){ 
      id=$(this).data('id'); 
      $('#modal').modal('show'); 
      $.ajax({
        url: cashApiUrl,
        type: 'POST', 
        data: { action: 'modal', modal: 'error_view', id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(''); 
        $('#load').html(data);
      })
      .fail(function() {
        $('#modal-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
         // $('#modal-loader').hide();
      });
    });
    $(document).on('click','#approve', function(e){
        e.preventDefault();
        var uid = $(this).data('id'); // get id of clicked row 
        $('#load').html(''); // leave this diff blank
        $('#modal-loader').show(); // load ajax loader button cliked
        $.ajax({
          url: cashApiUrl,
          type: 'POST',
          data: { action: 'modal', modal: 'returns_approve', id: uid },
          dataType: 'html'
        })
        .done(function(data){
          $('#load').html(''); // blank before load.
          $('#load').html(data);
        }).fail(function(){
          $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
          $('#modal-loader').hide();
        });
      }); 
      
    $(document).on('click','#printsum', function(e){ 
      id=$(this).data('id'); 
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST', 
        data: { action: 'modal', modal: 'shift_sum', id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(''); 
        $('#load').html(data);
      })
      .fail(function() {
        $('#modal-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
         // $('#modal-loader').hide();
      });
    }); 
    $(document).on('click','#handover', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST',
        data: { action: 'modal', modal: 'handover_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<div class="alert alert-danger">Could not open shift handover.</div>');
      });
    });
    $(document).on('click','#expenses', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST',
        data: { action: 'modal', modal: 'expense_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    });
    $(document).on('click','#cashier-summary', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'GET',
        data: { action: 'modal', modal: 'cashier_summary_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load cashier summary.</div></div></div></div>');
      });
    });
    $(document).on('click','.cash-expense-delete', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST',
        data: { action: 'modal', modal: 'expense_delete_modal', id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="alert alert-danger">Could not open expense delete approval.</div>');
      });
    });
    $(document).on('click','#customer-credit', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST',
        data: { action: 'modal', modal: 'customer_credit_modal' },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="alert alert-danger">Could not open customer credit desk.</div>');
      });
    });
    $(document).on('click','.cash-credit-pay', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      $('#modal').modal('show');
      $.ajax({
        url: cashApiUrl,
        type: 'POST',
        data: { action: 'modal', modal: 'customer_credit_modal', cust_id: id },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="alert alert-danger">Could not open customer credit payment.</div>');
      });
    });
  });
</script>
