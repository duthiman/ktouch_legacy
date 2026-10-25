<?php
session_start();
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../funcs.php';
ensure_user_sales_limit_system();

$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}

$result = mysqli_query($connection, "SELECT u.user_id, u.username, COALESCE(u.max_sale,0) AS max_sale, COALESCE(SUM(s.amount),0) AS amt FROM kp_users u LEFT JOIN kp_sales s ON u.user_id=s.user_id AND s.shift='$shift' AND s.pay_status=2 GROUP BY u.user_id ORDER BY amt DESC") or die('could not fetch users');
$waiters = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ((float)$row['amt'] <= 0) {
            continue;
        }
        $sid = (int)$row['user_id'];
        $waiters[] = [
            'user_id' => $sid,
            'name' => $row['username'],
            'amount' => (float)$row['amt'],
            'max_sale' => (float)$row['max_sale']
        ];
    }
}
?>
<link rel="stylesheet" type="text/css" href="clrbill/clear_bills.css">

    <div class="cash-clearb-layout">
      <div class="cash-clearb-selector">
        <div class="cash-clearb-card-head">
          <h4>Select User</h4>
          <p>Choose using big buttons or the select box.</p>
        </div>
        <div class="cash-clearb-selector-body">
          <div class="cash-clearb-toggle">
            <button type="button" class="is-active" data-view="buttons" onclick="cashSetClearBMode('buttons')">Big Buttons</button>
            <button type="button" data-view="select" onclick="cashSetClearBMode('select')">Select Box</button>
          </div>

          <div class="cash-clearb-scrollnav">
            <span>Swipe or use arrows to view more users.</span>
            <div class="cash-clearb-scrollbtns">
              <button type="button" class="cash-clearb-scrollbtn cash-clearb-scrollbtn-left is-hidden" data-rail="cash-clearb-buttons" onclick="cashScrollUserRail('cash-clearb-buttons', -260)">&lsaquo;</button>
              <button type="button" class="cash-clearb-scrollbtn cash-clearb-scrollbtn-right is-hidden" data-rail="cash-clearb-buttons" onclick="cashScrollUserRail('cash-clearb-buttons', 260)">&rsaquo;</button>
            </div>
          </div>

          <div class="cash-clearb-buttons is-active" id="cash-clearb-buttons">
            <button type="button" class="cash-clearb-chip is-active" data-clearb-user="0" onclick="cashPickClearBUser(0, 'All Users')">
              <small>View</small>
              <span>All Users</span>
              <b>All current unpaid bills</b>
            </button>
            <?php foreach ($waiters as $waiter) { ?>
              <div class="cash-clearb-chip" data-clearb-user="<?php echo $waiter['user_id']; ?>" data-max-sale="<?php echo number_format($waiter['max_sale'], 2, '.', ''); ?>" onclick="cashPickClearBUser(<?php echo $waiter['user_id']; ?>, '<?php echo addslashes($waiter['name']); ?>')">
                <span class="cash-clearb-user-name"><?php echo htmlspecialchars($waiter['name']); ?></span>
                <b class="cash-clearb-user-sales"><?php echo number_format($waiter['amount'], 2); ?></b>
                <div class="cash-clearb-user-limit">Max: <?php echo number_format($waiter['max_sale'], 2); ?></div>
              </div>
            <?php } ?>
            <?php if (count($waiters) < 1) { ?>
              <div class="cash-clearb-placeholder">No current unpaid bills were found for this shift.</div>
            <?php } ?>
          </div>

          <div class="cash-clearb-select" id="cash-clearb-select-wrap">
            <select class="form-control form-control-xs pickers text-black" id="cash_clearb_user_id" name="cash_clearb_user_id">
              <option value="0">ALL USERS</option>
              <?php foreach ($waiters as $waiter) { ?>
                <option value="<?php echo $waiter['user_id']; ?>"><?php echo htmlspecialchars($waiter['name']); ?> (Sales: <?php echo number_format($waiter['amount'],2); ?> / Max: <?php echo number_format($waiter['max_sale'],2); ?>)</option>
              <?php } ?>
            </select>
          </div>

        </div>
      </div>

      <div class="cash-clearb-main">
        <div class="cash-clearb-main-body">
          

          <div class="cash-clearb-row">
            <div class="cash-clearb-work">
              <div class="cash-clearb-card-head">
                <h4>Work Area</h4>
                <p>The orders list loads here for payment selection.</p>
              </div>
              <div class="cash-clearb-work-body" id="stasales">
                <div class="cash-clearb-placeholder">Pick a user on the left to load the current unpaid bills here.</div>
              </div>
            </div>

            <div class="cash-clearb-selected">
              <div class="cash-panel">
                <div class="cash-panel-head"><h4>Selected Bills</h4></div>
                <div class="cash-clearb-summarybar m-20">
                  <div class="cash-clearb-summarystats">
                    <div class="cash-clearb-summaryitem">
                      <strong>Selected Bills</strong>
                      <span id="cash-clearb-selected-count">0</span>
                    </div>
                    <div class="cash-clearb-summaryitem">
                      <strong>Selected Amount</strong>
                      <span id="cash-clearb-selected-amount">0.00</span>
                    </div>
                  </div> 
                </div>
                <div class="cash-panel-body cash-scroll-box" id="sale-items">
                  <div class="alert alert-warning text-center" style="margin-bottom:0;">
                    <strong>No bills selected.</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
   
<script type="text/javascript">
  $('.pickers').select2({});

  function cashScrollUserRail(id, amount) {
    var rail = document.getElementById(id);
    if (!rail) {
      return;
    }
    rail.scrollBy({ left: amount, behavior: 'smooth' });
    setTimeout(function(){ cashSyncUserRailButtons(id); }, 220);
  }

  function cashSyncUserRailButtons(id) {
    var rail = document.getElementById(id);
    if (!rail) {
      return;
    }
    var leftBtn = document.querySelector('.cash-clearb-scrollbtn-left[data-rail="' + id + '"]');
    var rightBtn = document.querySelector('.cash-clearb-scrollbtn-right[data-rail="' + id + '"]');
    var maxLeft = rail.scrollWidth - rail.clientWidth;
    var canScroll = maxLeft > 8;
    var atLeft = rail.scrollLeft <= 4;
    var atRight = rail.scrollLeft >= (maxLeft - 4);

    if (leftBtn) {
      leftBtn.classList.toggle('is-hidden', !canScroll || atLeft);
    }
    if (rightBtn) {
      rightBtn.classList.toggle('is-hidden', !canScroll || atRight);
    }
  }

  function cashSetClearBMode(mode) {
    $('.cash-clearb-toggle button').removeClass('is-active');
    $('.cash-clearb-toggle button[data-view="' + mode + '"]').addClass('is-active');
    if (mode === 'select') {
      $('#cash-clearb-buttons').removeClass('is-active');
      $('#cash-clearb-select-wrap').addClass('is-active');
    } else {
      $('#cash-clearb-select-wrap').removeClass('is-active');
      $('#cash-clearb-buttons').addClass('is-active');
    }
  }

  function cashPickClearBUser(id, name) {
    id = parseInt(id, 10) || 0;
    $('#cash_clearb_user_id').val(String(id)).trigger('change.select2');
    $('[data-clearb-user]').removeClass('is-active');
    $('[data-clearb-user="' + id + '"]').addClass('is-active');
    if (id > 0) {
      $('#cash-clearb-loadbtn').text("LOAD " + name + "'S BILLS");
    } else {
      $('#cash-clearb-loadbtn').text('LOAD ALL CURRENT BILLS');
    }
    cashLoadCurrentBills(id);
  }

  function cashLoadCurrentBillsForSelected() {
    var id = parseInt($('#cash_clearb_user_id').val(), 10) || 0;
    cashLoadCurrentBills(id);
  }

  function cashLoadCurrentBills(id) {
    var st = id > 0 ? 1 : 2;
    $('#cash-clearb-selected-count').text('0');
    $('#cash-clearb-selected-amount').text('0.00');
    $('#sale-items')
      .attr('data-selected-invoices', '')
      .html('<div class="alert alert-warning text-center" style="margin-bottom:0;"><strong>No bills selected.</strong></div>');
    $.ajax({
      url: 'clrbill/staff_sales.php',
      type: 'GET',
      data: { id: id, st: st },
      dataType: 'html'
    })
    .done(function(data){
      $('#stasales').html(data);
    })
    .fail(function(){
      $('#stasales').html('<div class="alert alert-danger">Could not load bills.</div>');
    });
  }

  function cashUpdateCurrentBillSummary() {
    var count = $('input.bulk:checked').length;
    var amount = 0;
    $('input.bulk:checked').each(function(){
      var row = $(this).closest('tr');
      var cellText = $.trim(row.find('td:last').text()).replace(/,/g, '');
      var rowAmount = parseFloat(cellText);
      if (!isNaN(rowAmount)) {
        amount += rowAmount;
      }
    });
    $('#cash-clearb-selected-count').text(count);
    $('#cash-clearb-selected-amount').text(amount.toFixed(2));
  }

  $('#cash_clearb_user_id').change(function(){
    var id = parseInt($(this).val(), 10) || 0;
    var selected = $('#cash_clearb_user_id option:selected').text();
    cashPickClearBUser(id, selected);
  });

  $(document).on('change', 'input.bulk', function(){
    cashUpdateCurrentBillSummary();
  });

  var clearBRail = document.getElementById('cash-clearb-buttons');
  if (clearBRail) {
    clearBRail.addEventListener('scroll', function(){ cashSyncUserRailButtons('cash-clearb-buttons'); });
    window.addEventListener('resize', function(){ cashSyncUserRailButtons('cash-clearb-buttons'); });
    setTimeout(function(){ cashSyncUserRailButtons('cash-clearb-buttons'); }, 120);
  }

  cashLoadCurrentBills(0);
</script>
