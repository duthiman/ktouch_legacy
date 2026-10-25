<?php  
include("../clude/head.php");   
include_once('../ret/API/ret_schema.php');
$user_id = $_SESSION['user_id'];
$job = get_column('kp_users','job',"user_id='$user_id'");//$_SESSION['job'];
$djob = get_column('kp_users','drole',"user_id='$user_id'");
$shift = get_column('kp_shift','shift',"1");
if ($shift==0) {
  $shift =DATE('Y-m-d');
} 
$today = date('Y-m-d');
if (function_exists('kp_return_ensure_schema')) {
  kp_return_ensure_schema($connection);
}

if (!function_exists('cash_distinct_count')) {
  function cash_distinct_count($connection, $table, $column, $where = '1') {
    $result = mysqli_query($connection, "SELECT COUNT(DISTINCT $column) AS total FROM $table WHERE $where") or die(mysqli_error($connection));
    $row = $result ? mysqli_fetch_assoc($result) : array();
    return (int)($row['total'] ?? 0);
  }
}
mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_shop_expenses (
  exp_id INT NOT NULL AUTO_INCREMENT,
  shift_date DATE NOT NULL,
  title VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  note TEXT NULL,
  user_id INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (exp_id)
)") or die(mysqli_error($connection));

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payments (
  credit_pay_id INT NOT NULL AUTO_INCREMENT,
  cust_id INT NOT NULL DEFAULT 0,
  user_id INT NOT NULL DEFAULT 0,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  pay_mode_id INT NOT NULL DEFAULT 0,
  reference_no VARCHAR(120) NOT NULL DEFAULT '',
  note TEXT NULL,
  shift_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (credit_pay_id)
)") or die(mysqli_error($connection));

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_items (
  credit_item_id INT NOT NULL AUTO_INCREMENT,
  credit_pay_id INT NOT NULL DEFAULT 0,
  cust_id INT NOT NULL DEFAULT 0,
  sale_id INT NOT NULL DEFAULT 0,
  ord_date DATE NOT NULL,
  pay_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (credit_item_id)
)") or die(mysqli_error($connection));

$expense_total = column_total('kp_shop_expenses','amount',"shift_date='$shift'");
$expense_count = column_count('kp_shop_expenses','exp_id',"shift_date='$shift'");
$credit_total = column_total('kp_cust_hist','amount',"trans='SALE' AND trans_date='$today'");
$credit_paid_today = column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date='$shift'");
$credit_paid_previous = column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date!='$shift'");
$credit_customer_count = mysqli_num_rows(mysqli_query($connection, "SELECT DISTINCT cust_id FROM kp_cust_hist WHERE trans='SALE' AND trans_date='$today'"));
$expense_preview = mysqli_query($connection, "SELECT title, amount, note, created_at FROM kp_shop_expenses WHERE shift_date='$shift' ORDER BY exp_id DESC LIMIT 4");
$kpesa_unapproved_count = (int)column_count('kp_split_message','id',"claim_status=1 AND assign_status=2");
$kpesa_unsplit_count = (int)column_count('mpesa_message','id',"split_status=0");
$print_mode = function_exists('get_print_mode') ? (int)get_print_mode() : 1;
$shift_sum_href = '../chaba/receipt_counter.php?type=shift_sum&id=1&return_to=' . urlencode('../cash/index.php');
$today_uncleared_bills = (int)column_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2");
$previous_uncleared_bills = (int)column_count('kp_sales','sale_id',"shift!='$shift' AND pay_status=2");
$waiters_to_clear = cash_distinct_count($connection, 'kp_sales', 'user_id', "shift='$shift' AND pay_status=2");
$reprintable_bills = (int)column_count('kp_sales','sale_id',"shift='$shift'");
$cashbox_entries = (int)column_count('kp_sale_payment','pay_id',"pay_date='$shift'");
$cashbox_total_today = (float)column_total('kp_sale_payment','paid_total',"pay_date='$shift'");
$return_cashier_mode = function_exists('kp_return_requires_cashier_approval') ? kp_return_requires_cashier_approval($connection) : false;
$pending_returns_count = $return_cashier_mode
  ? (int)column_count('kp_ret_items','id',"cashier_status=2 AND ret_status!=1")
  : 0;
$pending_returns_label = $return_cashier_mode ? 'Awaiting cashier approval' : 'Counter handles returns';
$cash_waiter_totals = mysqli_query($connection, "SELECT s.user_id, u.username, COALESCE(SUM(s.amount),0) AS total, COUNT(s.sale_id) AS bills FROM kp_sales s LEFT JOIN kp_users u ON u.user_id=s.user_id WHERE s.shift='$shift' GROUP BY s.user_id, u.username ORDER BY total DESC");
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
  <header>
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
  </header>
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
             <span><?php echo get_column('kp_users','username',"user_id='$user_id'"); ?></span>
          </div>
          <button type="button" class="cash-refresh-dashboard" id="cash-refresh-dashboard"><i class="fa fa-refresh"></i> Refresh Dashboard</button>
          <div class="cash-panel">
            <div class="cash-panel-head"><h4>Today</h4></div>
            <div class="cash-panel-body">
              <div class="cash-mission-grid">
                <button type="button" class="cash-mission-card cash-mission-primary" id="cash-card-clear-today">
                  <strong>Clear Today's Bills</strong>
                  <span><?php echo $today_uncleared_bills; ?></span>
                  <small>Unpaid bills waiting for cashier action.</small>
                </button>
                <button type="button" class="cash-mission-card cash-mission-secondary" id="cash-card-returns"<?php echo !$return_cashier_mode ? ' disabled' : ''; ?>>
                  <strong>Approve Returns</strong>
                  <span><?php echo $pending_returns_count; ?></span>
                  <small><?php echo htmlspecialchars($pending_returns_label); ?></small>
                </button>
                <button type="button" class="cash-mission-card cash-mission-dark" id="cash-card-reprint">
                  <strong>Reprint Waiter Bills</strong>
                  <span><?php echo $reprintable_bills; ?></span>
                  <small>Open today's bill list and reprint from there.</small>
                </button>
                <button type="button" class="cash-mission-card cash-mission-soft" id="cash-card-clear-waiters">
                  <strong>Clear Waiters</strong>
                  <span><?php echo $waiters_to_clear; ?></span>
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
                  <span><?php echo number_format($cashbox_total_today, 2); ?></span>
                  <small><?php echo $cashbox_entries; ?> payment entries today</small>
                </button>
                <button type="button" class="cash-focus-card" id="cash-card-expenses">
                  <strong>Enter Expenses</strong>
                  <span><?php echo number_format($expense_total, 2); ?></span>
                  <small><?php echo $expense_count; ?> expenses captured this shift</small>
                </button>
                <button type="button" class="cash-focus-card" id="cash-card-credit">
                  <strong>Add Customer Credit</strong>
                  <span><?php echo $credit_customer_count; ?></span>
                  <small>Customers with credit sales today</small>
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
                  <button id="kpesa" class="cash-action-btn has-badge">KPesa<?php if ($kpesa_unapproved_count > 0) { ?><span class="cash-action-badge" id="cash-kpesa-badge"><?php echo $kpesa_unapproved_count; ?></span><?php } ?></button>
                  <?php if ($print_mode == 1) { ?>
                    <button id="printsum" class="cash-action-btn">Print Shift Summary</button>
                  <?php } else { ?>
                    <a href="<?php echo htmlspecialchars($shift_sum_href); ?>" class="cash-action-btn" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">Print Shift Summary</a>
                  <?php } ?>
                  <button id="handover" class="cash-action-btn">Shift Handover</button>
                  <button id="customer-credit" class="cash-action-btn">Credit Customers</button>
                  <button id="expenses" class="cash-action-btn">Add Expense</button>
                </div>
 
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="cash-main-area" class="col-md-9">
        <div class=" cash-hero-grid">
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-accent cash-hero-button" id="cash-hero-expenses">
            <strong>Today's Expenses</strong>
            <span id="cash-expense-total"><?php echo $expense_total; ?></span>
            <div class="cash-hero-merge">
              <div>
                <small>Entries</small>
                <b id="cash-expense-count-top"><?php echo $expense_count; ?></b>
              </div>
              <div>
                <small>Net After Exp</small>
                <b id="cash-net-after-expense-top"><?php echo column_total('kp_sale_payment','paid_total',"pay_date='$shift'") - $expense_total; ?></b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-button" id="cash-hero-sales">
            <strong>Total Sales</strong>
            <span id="cash-total-sales"><?php echo column_total('kp_sales','amount',"shift='$shift'"); ?></span>
            <div class="cash-hero-merge">
              <div>
                <small>Sales Paid</small>
                <b id="cash-sales-paid"><?php echo column_total('kp_sales','amount',"shift='$shift' AND pay_status=1"); ?></b>
              </div>
              <div>
                <small>Sales Unpaid</small>
                <b id="cash-sales-unpaid"><?php echo column_total('kp_sales','amount',"shift='$shift' AND pay_status=2"); ?></b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-button" id="cash-hero-paid-summary">
            <strong>Paid Summary</strong>
            <div class="cash-hero-merge">
              <div>
                <small>Previous Paid</small>
                <b id="cash-previous-paid"><?php echo column_total('kp_sales_paid','paid_total',"ord_date!=pay_date AND pay_date='$shift'"); ?></b>
              </div>
              <div>
                <small>Total Paid</small>
                <b id="cash-total-paid"><?php echo column_total('kp_sale_payment','paid_total',"pay_date='$shift'"); ?></b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-wide cash-hero-credit cash-hero-button" id="cash-hero-credit">
            <strong>Credit Paid Summary</strong>
            <span id="cash-credit-total-top"><?php echo $credit_total; ?></span>
            <div class="cash-hero-merge">
              <div>
                <small>Paid Today</small>
                <b id="cash-credit-paid-today-top"><?php echo $credit_paid_today; ?></b>
              </div>
              <div>
                <small>Paid Previous</small>
                <b id="cash-credit-paid-previous-top"><?php echo $credit_paid_previous; ?></b>
              </div>
            </div>
          </button>
          <button type="button" class="cash-hero-card cash-hero-button" id="cash-hero-credit-customers"><strong>Credit Customers</strong><span id="cash-credit-customers"><?php echo $credit_customer_count; ?></span></button>
        </div>  
        <div>
          <div class="cash-work-row">
            <div class="cash-work-pane cash-work-pane-main">
              <div class="cash-panel">
                <div class="cash-panel-head"><h4>Work Area</h4></div>
                <div class="cash-panel-body cash-scroll-box" id="cleardiv">
                  <div class="cash-dashboard-stack">
                    <div class="cash-dashboard-total"><small>TOTAL COLLECTED</small><strong><?php echo number_format($cashbox_total_today, 2); ?></strong><span><?php echo $cashbox_entries; ?> payment entries this shift</span></div>
                    <div class="cash-dashboard-card"><h4>Waiter Totals</h4><?php if ($cash_waiter_totals && mysqli_num_rows($cash_waiter_totals)) { while ($waiter_total=mysqli_fetch_assoc($cash_waiter_totals)) { ?><div class="cash-dashboard-waiter"><span><?php echo htmlspecialchars($waiter_total['username'] ?: 'Unknown user'); ?><small><?php echo (int)$waiter_total['bills']; ?> bills</small></span><b><?php echo number_format((float)$waiter_total['total'], 2); ?></b></div><?php } } else { ?><div class="cash-desk-placeholder">No waiter collections recorded for this shift.</div><?php } ?></div>
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
  function isCashModalActive() {
    return $('#modal').hasClass('show') || $('#modal2').hasClass('show');
  }

  $(document).on('click', '#cash-refresh-dashboard', function(){
    var button=$(this); button.prop('disabled',true).html('<i class="fa fa-refresh fa-spin"></i> Refreshing...');
    window.location.reload();
  });

  function loadCashLive() {
    $.ajax({
      url: 'cash_live.php',
      type: 'GET',
      dataType: 'json'
    })
    .done(function(data){
      $('#cash-shift-value').text(data.shift);
      $('#cash-shift-badge').text(data.shift);
      $('#cash-total-sales').text(data.total_sales);
      $('#cash-sales-unpaid').text(data.sales_unpaid);
      $('#cash-sales-paid').text(data.sales_paid);
      $('#cash-previous-paid').text(data.previous_paid);
      $('#cash-total-paid').text(data.total_paid);
      $('#cash-credit-total').text(data.credit_today);
      $('#cash-credit-total-top').text(data.credit_today);
      $('#cash-credit-paid-today').text(data.credit_paid_today);
      $('#cash-credit-paid-today-top').text(data.credit_paid_today);
      $('#cash-credit-paid-previous').text(data.credit_paid_previous);
      $('#cash-credit-paid-previous-top').text(data.credit_paid_previous);
      $('#cash-credit-customers').text(data.credit_customer_count);
      $('#cash-expense-total-top').text(data.expense_total);
      $('#cash-expense-count').text(data.expense_count);
      $('#cash-expense-count-top').text(data.expense_count);
      var totalPaid = parseFloat(data.total_paid || 0);
      var expenseTotal = parseFloat(data.expense_total || 0);
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
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET","staff_sales.php?id="+id,false);
    xmlhttp.send(null);
    document.getElementById("load").innerHTML = xmlhttp.responseText;
    document.getElementById("load").style.visibility = "visible";
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
      url: 'clrbill/bulk_form.php',
      type: 'POST',
      data: { invoices: invoiceString },
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
      url: 'pay/pay_modal.php',
      type: 'POST',
      data:  { invoices: invoiceString, amount: amount, orduser: orduser, type: type },
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
      url: 'CUSTCRED/credit_assign_modal.php',
      type: 'POST',
      data: { invoices: invoiceString, amount: amount },
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
      <?php if ($return_cashier_mode) { ?>
      window.location.href = '../ret/returns.php';
      <?php } else { ?>
      alert('Returns are currently approved at counter level, not from cashier desk.');
      <?php } ?>
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
    $(document).on('click','#cash-card-expenses', function(e){
      e.preventDefault();
      $('#expenses').trigger('click');
    });
    $(document).on('click','#cash-card-credit', function(e){
      e.preventDefault();
      $('#customer-credit').trigger('click');
    });
      $(document).on('click','#split', function(e){
        e.preventDefault();
        var uid = $(this).data('id'); // get id of clicked row 
        $('#load').html(''); // leave this diff blank
        $('#modal-loader').show(); // load ajax loader button cliked
        $.ajax({
          url: '../split/split_modal.php',
          type: 'POST',
          data: 'id='+uid,
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
      $.ajax({
        url: 'clrbill/clearp.php',
        type: 'POST', 
        data: {},
        dataType: 'html'
      })
      .done(function(data){
        $('#cash-main-area').html(''); 
        $('#cash-main-area').html(data);
      })
      .fail(function() {
        $('#cash-main-area').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
         // $('#modal-loader').hide();
      });
    });
    $(document).on('click','#clearsta', function(e){  
      e.preventDefault();
      $.ajax({
        url: 'clrstaff/clearsta.php',
        type: 'POST', 
        data: {},
        dataType: 'html'
      })
      .done(function(data){
        $('#cash-main-area').html(''); 
        $('#cash-main-area').html(data);
      })
      .fail(function() {
        $('#cash-main-area').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
         // $('#modal-loader').hide();
      });
    }); 
    $(document).on('click','#mergebills', function(e){
      e.preventDefault();
      loadDeskPanel('merge/index.php', {}, 'Could not load merge bills desk.');
    });
    $(document).on('click','#cashbox', function(e){
      e.preventDefault();
      loadDeskPanel('cashbox_panel.php', {}, 'Could not load cash box.');
    });
    $(document).on('click','#kpesa', function(e){
      e.preventDefault();
      loadDeskPanel('kpesa_panel.php', {}, 'Could not load KPesa desk.');
    });
    $(document).on('click','#credit-today', function(e){
      e.preventDefault();
      $('#modal').modal('show');
      $.ajax({
        url: 'CUSTCRED/credit_snapshot_modal.php',
        type: 'GET',
        data: { mode: 'today' },
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
        url: 'CUSTCRED/credit_snapshot_modal.php',
        type: 'GET',
        data: { mode: 'paid_today' },
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
        url: 'CUSTCRED/credit_snapshot_modal.php',
        type: 'GET',
        data: { mode: 'paid_previous' },
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
        url: 'exp/expenses_panel_modal.php',
        type: 'GET',
        data: { shift: '<?php echo $shift; ?>' },
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
        url: 'exp/expenses_panel_modal.php',
        type: 'GET',
        data: { shift: '<?php echo $shift; ?>' },
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
        url: 'pay/paid_summary_modal.php',
        type: 'GET',
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
      $.ajax({
        url: 'clrbill/clearb.php',
        type: 'POST', 
        data: {},
        dataType: 'html'
      })
      .done(function(data){
        $('#cash-main-area').html(''); 
        $('#cash-main-area').html(data);
      })
      .fail(function() {
        $('#cash-main-area').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
         // $('#modal-loader').hide();
      });
    }); 
    $(document).on('click','#colle', function(e){ 
      id=$(this).data('id'); 
      $('#modal').modal('show');
      $.ajax({
        url: 'colle.php',
        type: 'POST', 
        data: {id:id},
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
    $(document).on('click','#close', function(e){ 
      id=$(this).data('id'); 
      $('#modal').modal('show');
      $.ajax({
        url: 'close_modal.php',
        type: 'POST', 
        data: {id:id},
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
        url: 'errview.php',
        type: 'POST', 
        data: {id:id},
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
          url: '../claims/approve_modal.php',
          type: 'POST',
          data: 'id='+uid,
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
        url: '../chaba/shift_sum.php',
        type: 'POST', 
        data: {id:id},
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
        url: 'shifthand/handover_modal.php',
        type: 'POST',
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
        url: 'exp/expense_modal.php',
        type: 'POST',
        data: {},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function() {
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    });
    $(document).on('click','.cash-expense-delete', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      $('#modal').modal('show');
      $.ajax({
        url: 'exp/expense_delete_modal.php',
        type: 'POST',
        data: {id:id},
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
        url: 'CUSTCRED/customer_credit_modal.php',
        type: 'POST',
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
        url: 'CUSTCRED/customer_credit_modal.php',
        type: 'POST',
        data: { cust_id: id },
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
