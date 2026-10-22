<?php
session_start();
include('../../../inc/connection.php');
require_once('../../../inc/gen_funcs.php');

$db = isset($connection) ? $connection : (isset($conn) ? $conn : null);
$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}

$result = mysqli_query($db, "SELECT user_id, SUM(amount) AS amt FROM kp_sales WHERE shift!='$shift' AND pay_status=2 GROUP BY user_id ORDER BY amt DESC") or die('could not fetch users');
$waiters = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sid = (int) $row['user_id'];
        $waiters[] = [
            'user_id' => $sid,
            'name' => get_column('kp_users', 'username', "user_id='$sid'"),
            'amount' => (float) $row['amt']
        ];
    }
}
?>
<link rel="stylesheet" type="text/css" href="clrbill/clear_bills.css">
<div class="cash-clearb-shell">
  <div class="cash-clearb-head">
    <h3>Clear Previous Bills</h3>
    <p>Select a waiter on the left, then review and tick unpaid bills from older shifts in the work area.</p>
  </div>
  <div class="cash-clearb-body">
    <div class="cash-clearb-layout">
      <div class="cash-clearb-selector">
        <div class="cash-clearb-card-head">
          <h4>Select User</h4>
          <p>Choose using big buttons or the select box.</p>
        </div>
        <div class="cash-clearb-selector-body">
          <div class="cash-clearb-toggle">
            <button type="button" class="is-active" data-view="buttons" onclick="cashSetClearPMode('buttons')">Big Buttons</button>
            <button type="button" data-view="select" onclick="cashSetClearPMode('select')">Select Box</button>
          </div>

          <div class="cash-clearb-scrollnav">
            <span>Swipe or use arrows to view more users.</span>
            <div class="cash-clearb-scrollbtns">
              <button type="button" class="cash-clearb-scrollbtn cash-clearb-scrollbtn-left is-hidden" data-rail="cash-clearp-buttons" onclick="cashScrollUserRail('cash-clearp-buttons', -260)">&lsaquo;</button>
              <button type="button" class="cash-clearb-scrollbtn cash-clearb-scrollbtn-right is-hidden" data-rail="cash-clearp-buttons" onclick="cashScrollUserRail('cash-clearp-buttons', 260)">&rsaquo;</button>
            </div>
          </div>

          <div class="cash-clearb-buttons is-active" id="cash-clearp-buttons">
            <button type="button" class="cash-clearb-chip is-active" data-clearp-user="0" onclick="cashPickClearPUser(0, 'All Users')">
              <small>View</small>
              <span>All Users</span>
              <b>All previous unpaid bills</b>
            </button>
            <?php foreach ($waiters as $waiter) { ?>
              <button type="button" class="cash-clearb-chip" data-clearp-user="<?php echo $waiter['user_id']; ?>" onclick="cashPickClearPUser(<?php echo $waiter['user_id']; ?>, '<?php echo addslashes($waiter['name']); ?>')">
                <small>Waiter</small>
                <span><?php echo htmlspecialchars($waiter['name']); ?></span>
                <b><?php echo number_format($waiter['amount'], 2); ?></b>
              </button>
            <?php } ?>
            <?php if (count($waiters) < 1) { ?>
              <div class="cash-clearb-placeholder">No previous unpaid bills were found outside this shift.</div>
            <?php } ?>
          </div>

          <div class="cash-clearb-select" id="cash-clearp-select-wrap">
            <select class="form-control form-control-xs pickers text-black" id="cash_clearp_user_id" name="cash_clearp_user_id">
              <option value="0">ALL USERS</option>
              <?php foreach ($waiters as $waiter) { ?>
                <option value="<?php echo $waiter['user_id']; ?>"><?php echo htmlspecialchars($waiter['name']); ?></option>
              <?php } ?>
            </select>
          </div>

          <button class="btn btn-outline-danger btn-md cash-clearb-load" type="button" id="cash-clearp-loadbtn" onclick="cashLoadPreviousBillsForSelected()">Load Bills</button>
        </div>
      </div>

      <div class="cash-clearb-main">
        <div class="cash-clearb-card-head">
          <h4>Previous Bills Desk</h4>
          <p>Work area stays on the left and selected bills stay on the right in one row.</p>
        </div>
        <div class="cash-clearb-main-body">
          <div class="cash-clearb-row">
            <div class="cash-clearb-work">
              <div class="cash-clearb-card-head">
                <h4>Work Area</h4>
                <p>The orders list loads here for payment selection.</p>
              </div>
              <div class="cash-clearb-work-body" id="stasales">
                <div class="cash-clearb-placeholder">Pick a user on the left to load the previous unpaid bills here.</div>
              </div>
            </div>

            <div class="cash-clearb-selected">
              <div class="cash-panel">
                <div class="cash-panel-head"><h4>Selected Bills</h4></div>
                <div class="cash-clearb-summarybar m-20">
                  <div class="cash-clearb-summarystats">
                    <div class="cash-clearb-summaryitem">
                      <strong>Selected Bills</strong>
                      <span id="cash-clearp-selected-count">0</span>
                    </div>
                    <div class="cash-clearb-summaryitem">
                      <strong>Selected Amount</strong>
                      <span id="cash-clearp-selected-amount">0.00</span>
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

  function cashSetClearPMode(mode) {
    $('.cash-clearb-toggle button').removeClass('is-active');
    $('.cash-clearb-toggle button[data-view="' + mode + '"]').addClass('is-active');
    if (mode === 'select') {
      $('#cash-clearp-buttons').removeClass('is-active');
      $('#cash-clearp-select-wrap').addClass('is-active');
    } else {
      $('#cash-clearp-select-wrap').removeClass('is-active');
      $('#cash-clearp-buttons').addClass('is-active');
    }
  }

  function cashPickClearPUser(id, name) {
    id = parseInt(id, 10) || 0;
    $('#cash_clearp_user_id').val(String(id)).trigger('change.select2');
    $('[data-clearp-user]').removeClass('is-active');
    $('[data-clearp-user="' + id + '"]').addClass('is-active');
    if (id > 0) {
      $('#cash-clearp-loadbtn').text("LOAD " + name + "'S BILLS");
    } else {
      $('#cash-clearp-loadbtn').text('LOAD ALL PREVIOUS BILLS');
    }
    cashLoadPreviousBills(id);
  }

  function cashLoadPreviousBillsForSelected() {
    var id = parseInt($('#cash_clearp_user_id').val(), 10) || 0;
    cashLoadPreviousBills(id);
  }

  function cashLoadPreviousBills(id) {
    var st = id > 0 ? 1 : 2;
    $('#cash-clearp-selected-count').text('0');
    $('#cash-clearp-selected-amount').text('0.00');
    $('#sale-items')
      .attr('data-selected-invoices', '')
      .html('<div class="alert alert-warning text-center" style="margin-bottom:0;"><strong>No bills selected.</strong></div>');
    $.ajax({
      url: 'API/cash_api.php',
      type: 'GET',
      data: { action: 'clrbill_staff_prev', id: id, st: st },
      dataType: 'html'
    })
    .done(function(data){
      $('#stasales').html(data);
    })
    .fail(function(){
      $('#stasales').html('<div class="alert alert-danger">Could not load bills.</div>');
    });
  }

  function cashUpdatePreviousBillSummary() {
    var count = $('input.bulk:checked').length;
    var amount = 0;
    $('input.bulk:checked').each(function(){
      var value = parseFloat($(this).attr('data-amount') || 0);
      if (!isNaN(value)) {
        amount += value;
      }
    });
    $('#cash-clearp-selected-count').text(count);
    $('#cash-clearp-selected-amount').text(amount.toFixed(2));
  }

  $('#cash_clearp_user_id').change(function(){
    var id = parseInt($(this).val(), 10) || 0;
    var selected = $('#cash_clearp_user_id option:selected').text();
    cashPickClearPUser(id, selected);
  });

  $(document).on('change', 'input.bulk', function(){
    cashUpdatePreviousBillSummary();
  });

  var clearPRail = document.getElementById('cash-clearp-buttons');
  if (clearPRail) {
    clearPRail.addEventListener('scroll', function(){ cashSyncUserRailButtons('cash-clearp-buttons'); });
    window.addEventListener('resize', function(){ cashSyncUserRailButtons('cash-clearp-buttons'); });
    setTimeout(function(){ cashSyncUserRailButtons('cash-clearp-buttons'); }, 120);
  }

  cashLoadPreviousBills(0);
</script>
