<?php
session_start();
include('../../../inc/connection.php');
require_once('../../../inc/gen_funcs.php');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
  $shift = DATE('Y-m-d');
} else {
  $shift = get_column('kp_shift', 'shift', "1");
}
?>
<style type="text/css">
  .cash-bill-shell {
    border: 1px solid #e7dbc9;
    border-radius: 18px;
    background: #fffdf9;
    overflow: hidden;
  }
  .cash-bill-head {
    padding: 16px 18px;
    border-bottom: 1px solid #eee2d5;
    background: linear-gradient(135deg, #fff6e8 0%, #fffdfa 100%);
  }
  .cash-bill-head h3 {
    margin: 0 0 4px;
    color: #734416;
    font-weight: 900;
  }
  .cash-bill-head p {
    margin: 0;
    color: #826f5b;
  }
  .cash-bill-body {
    padding: 16px 18px 18px;
  }
  .cash-bill-tools {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 12px;
    align-items: end;
    margin-bottom: 14px;
  }
  .cash-bill-toggle {
    display: inline-flex;
    gap: 8px;
    padding: 4px;
    border-radius: 999px;
    background: #f8efe3;
    border: 1px solid #ecdcc6;
    margin-bottom: 12px;
  }
  .cash-bill-toggle button {
    border: 0;
    background: transparent;
    color: #7f5830;
    padding: 9px 14px;
    border-radius: 999px;
    font-weight: 800;
  }
  .cash-bill-toggle button.is-active {
    background: #f0a53a;
    color: #fff;
  }
  .cash-bill-select {
    display: none;
  }
  .cash-bill-select.is-active {
    display: block;
  }
  .cash-bill-buttons {
    display: none;
    flex-wrap: wrap;
    gap: 10px;
  }
  .cash-bill-buttons.is-active {
    display: flex;
  }
  .cash-bill-chip {
    min-width: 160px;
    border: 1px solid #ead5b8;
    background: #fff;
    color: #55391d;
    border-radius: 14px;
    padding: 11px 14px;
    text-align: left;
    font-weight: 800;
    box-shadow: 0 6px 18px rgba(117, 79, 145, 0.05);
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .cash-bill-chip:hover {
    box-shadow: 0 10px 24px rgba(117, 79, 145, 0.12);
    transform: translateY(-2px);
  }
  .cash-bill-chip small,
  .cash-bill-chip span {
    display: block;
  }
  .cash-bill-chip small {
    color: #9a7d5d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-bill-chip span {
    margin-top: 4px;
    font-size: 15px;
    color: #2e2114;
  }
  .cash-bill-chip span.bill-amount {
    margin-top: 8px;
    font-size: 13px;
    color: #734416;
    font-weight: 700;
  }
  .cash-bill-chip.is-active {
    background: #fff2db;
    border-color: #f0a53a;
  }
  .cash-bill-load {
    border-radius: 12px;
    font-weight: 800;
    padding: 11px 18px;
  }
  .cash-bill-results {
    min-height: 240px;
  }
  .cash-bill-loading {
    border: 1px dashed #e2d2bb;
    border-radius: 16px;
    background: #fff;
    padding: 28px 20px;
    color: #86735f;
    text-align: center;
  }
  .cash-bill-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
  }
  .cash-bill-summary-card {
    border: 1px solid #ead5b8;
    border-radius: 12px;
    background: #fff;
    padding: 12px 14px;
  }
  .cash-bill-summary-card small {
    display: block;
    color: #9a7d5d;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-bill-summary-card strong {
    display: block;
    margin-top: 4px;
    color: #5b3d26;
    font-size: 18px;
  }
  .cash-bill-placeholder {
    border: 1px dashed #e2d2bb;
    border-radius: 16px;
    background: #fff;
    padding: 20px;
    color: #86735f;
    text-align: center;
  }
  .cash-bill-detail {
    border: 1px solid #ead5b8;
    border-radius: 14px;
    background: linear-gradient(135deg, #fff8ef 0%, #fffdf9 100%);
    padding: 16px 18px;
    margin-bottom: 14px;
  }
  .cash-bill-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee2d5;
  }
  .cash-bill-detail-row:last-child {
    border-bottom: none;
  }
  .cash-bill-detail-label {
    color: #9a7d5d;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .cash-bill-detail-value {
    color: #2e2114;
    font-size: 14px;
    font-weight: 800;
  }
  .cash-bill-detail-value.amount {
    color: #734416;
    font-size: 16px;
  }
  .cash-bill-items {
    margin-top: 16px;
    border: 1px solid #ead5b8;
    border-radius: 10px;
    overflow: hidden;
  }
  .cash-bill-items-header {
    background: #f0a53a;
    color: #fff;
    padding: 12px 14px;
    font-weight: 800;
    font-size: 12px;
  }
  .cash-bill-item-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid #eed9c5;
    font-size: 13px;
    color: #55391d;
  }
  .cash-bill-item-row:last-child {
    border-bottom: none;
  }
  .cash-bill-action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 16px;
  }
  .cash-bill-action-btn {
    border: 0;
    border-radius: 10px;
    padding: 12px 14px;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .cash-bill-action-btn.pay {
    background: #2e7d3e;
    color: #fff;
  }
  .cash-bill-action-btn.pay:hover {
    background: #245a2f;
    box-shadow: 0 6px 16px rgba(46, 125, 62, 0.2);
  }
  .cash-bill-action-btn.cancel {
    background: #f8efe3;
    color: #7f5830;
    border: 1px solid #ecdcc6;
  }
  .cash-bill-action-btn.cancel:hover {
    background: #efe3d3;
  }
  .cash-bill-user-pill {
    width: 100%;
    border: 1px solid #ead5b8;
    background: #fff;
    color: #55391d;
    border-radius: 14px;
    padding: 12px 14px;
    text-align: left;
    font-weight: 800;
    box-shadow: 0 6px 18px rgba(117, 79, 145, 0.05);
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .cash-bill-user-pill:hover {
    box-shadow: 0 10px 24px rgba(117, 79, 145, 0.12);
    transform: translateY(-2px);
  }
  .cash-bill-user-pill.is-active {
    background: #fff2db;
    border-color: #f0a53a;
  }
  .cash-bill-user-pill small,
  .cash-bill-user-pill span {
    display: block;
  }
  .cash-bill-user-pill small {
    color: #9a7d5d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-bill-user-pill .bill-amount {
    margin-top: 8px;
    font-size: 13px;
    color: #734416;
    font-weight: 700;
  }
  @media (max-width: 767px) {
    .cash-bill-tools {
      grid-template-columns: 1fr;
    }
    .cash-bill-chip {
      width: 100%;
      min-width: 0;
    }
    .cash-bill-load {
      width: 100%;
    }
    .cash-bill-action-buttons {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="cash-bill-shell">
  <div class="cash-bill-head">
    <h3>Bill Payment</h3>
    <p>Load the unpaid users list from the API, then review each user's unpaid bills.</p>
  </div>
  <div class="cash-bill-body">
    <div class="cash-bill-toggle">
      <button type="button" class="is-active" data-view="buttons" onclick="cashSetBillPickerMode('buttons')">Big Buttons</button>
      <button type="button" data-view="select" onclick="cashSetBillPickerMode('select')">Select Box</button>
    </div>

    <div class="cash-bill-summary" id="cash-bill-summary">
      <div class="cash-bill-summary-card">
        <small>Shift</small>
        <strong id="cash-bill-shift"><?php echo htmlspecialchars($shift); ?></strong>
      </div>
      <div class="cash-bill-summary-card">
        <small>Users</small>
        <strong id="cash-bill-users-count">0</strong>
      </div>
      <div class="cash-bill-summary-card">
        <small>Unpaid Bills</small>
        <strong id="cash-bill-sales-count">0</strong>
      </div>
      <div class="cash-bill-summary-card">
        <small>Total Unpaid</small>
        <strong id="cash-bill-total-unpaid">KES 0.00</strong>
      </div>
    </div>

    <div class="cash-bill-tools">
      <div>
        <div class="cash-bill-buttons is-active" id="cash-bill-buttons">
          <div class="cash-bill-loading">Loading unpaid users from the API...</div>
        </div>

        <div class="cash-bill-select" id="cash-bill-select-wrap">
          <select class="form-control form-control-xs pickers text-black" id="cash_bill_user_id" name="cash_bill_user_id">
            <option value="0">Loading users...</option>
          </select>
        </div>
      </div>
    </div>

    <input type="hidden" id="cash-selected-user-id" value="0" />
    <div class="cash-bill-results" id="bill-results">
      <div class="cash-bill-loading">
        Bills will load here after the API responds.
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var cashBillsState = {
    users: [],
    unpaidSales: [],
    summary: { shift: '<?php echo addslashes($shift); ?>', users_count: 0, sales_count: 0, total_unpaid: 0 },
    selectedUserId: 0
  };

  function cashFormatMoney(value) {
    var num = Number(value || 0);
    return 'KES ' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function cashEscape(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function cashInitSelect2() {
    if ($.fn.select2) {
      $('.pickers').select2({ width: '100%' });
    }
  }

  function cashSetSummary(summary) {
    summary = summary || {};
    $('#cash-bill-shift').text(summary.shift || '<?php echo addslashes($shift); ?>');
    $('#cash-bill-users-count').text(summary.users_count || 0);
    $('#cash-bill-sales-count').text(summary.sales_count || 0);
    $('#cash-bill-total-unpaid').text(cashFormatMoney(summary.total_unpaid || 0));
  }

  function cashBuildUserButtons(users) {
    if (!users || !users.length) {
      return '<div class="cash-bill-placeholder">No unpaid users found for this shift.</div>';
    }

    return users.map(function(user) {
      return [
        '<button type="button" class="cash-bill-user-pill" data-bill-user="',
        cashEscape(user.user_id),
        '" onclick="cashPickUser(',
        cashEscape(user.user_id),
        ')">',
        '<small>User</small>',
        '<span>',
        cashEscape(user.username),
        '</span>',
        '<span class="bill-amount">',
        cashFormatMoney(user.unpaid_total),
        ' • ',
        cashEscape(user.unpaid_count),
        ' bills</span>',
        '</button>'
      ].join('');
    }).join('');
  }

  function cashBuildSelectOptions(users) {
    var options = ['<option value="0">-----SELECT USER-----</option>'];
    (users || []).forEach(function(user) {
      options.push('<option value="' + cashEscape(user.user_id) + '">' + cashEscape(user.username) + ' - ' + cashFormatMoney(user.unpaid_total) + '</option>');
    });
    return options.join('');
  }

  function cashRenderUserDetails(userId) {
    userId = parseInt(userId, 10) || 0;
    cashBillsState.selectedUserId = userId;
    $('#cash-selected-user-id').val(userId);

    $('[data-bill-user]').removeClass('is-active');
    $('[data-bill-user="' + userId + '"]').addClass('is-active');
    $('#cash_bill_user_id').val(String(userId)).trigger('change.select2');

    if (userId < 1) {
      $('#bill-results').html('<div class="cash-bill-placeholder">Select a user first.</div>');
      return;
    }

    $('#bill-results').html('<div class="cash-bill-loading">Loading unpaid bills for selected user...</div>');

    $.ajax({
      url: 'bill/api/bills_api.php',
      type: 'GET',
      dataType: 'json',
      data: {
        action: 'get_unpaid_user',
        target_user_id: userId,
        shift: cashBillsState.summary.shift || '<?php echo addslashes($shift); ?>'
      }
    })
    .done(function(response) {
      if (!response || !response.ok) {
        $('#bill-results').html('<div class="cash-bill-placeholder">No unpaid bills found for the selected user.</div>');
        return;
      }

      var data = response.data || {};
      var user = data.user || null;
      var sales = data.sales || [];

      if (!user) {
        $('#bill-results').html('<div class="cash-bill-placeholder">No unpaid bills found for the selected user.</div>');
        return;
      }

      var html = '';
      html += '<div class="cash-bill-detail">';
      html += '<div class="cash-bill-detail-row"><span class="cash-bill-detail-label">User</span><span class="cash-bill-detail-value">' + cashEscape(user.username) + '</span></div>';
      html += '<div class="cash-bill-detail-row"><span class="cash-bill-detail-label">Unpaid Count</span><span class="cash-bill-detail-value">' + cashEscape(user.unpaid_count) + '</span></div>';
      html += '<div class="cash-bill-detail-row"><span class="cash-bill-detail-label">Unpaid Total</span><span class="cash-bill-detail-value amount">' + cashFormatMoney(user.unpaid_total) + '</span></div>';
      html += '</div>';

      html += '<div class="cash-bill-items">';
      html += '<div class="cash-bill-items-header">UNPAID BILLS</div>';
      if (!sales.length) {
        html += '<div class="cash-bill-item-row" style="grid-template-columns: 1fr;"><div>No unpaid sales found for this user.</div></div>';
      } else {
        sales.forEach(function(sale) {
          html += '<div class="cash-bill-item-row">';
          html += '<div>#' + cashEscape(sale.sale_id) + ' - ' + cashEscape(sale.ord_date || sale.shift || '') + '</div>';
          html += '<div>' + cashEscape(sale.pay_status) + '</div>';
          html += '<div><strong>' + cashFormatMoney(sale.amount) + '</strong></div>';
          html += '</div>';
        });
      }
      html += '</div>';

      html += '<div class="cash-bill-action-buttons">';
      html += '<button type="button" class="cash-bill-action-btn pay" onclick="cashProcessPayment(' + userId + ')">OPEN PAYMENT</button>';
      html += '<button type="button" class="cash-bill-action-btn cancel" onclick="cashClearBillSelection()">CLEAR</button>';
      html += '</div>';

      $('#bill-results').html(html);
    })
    .fail(function() {
      $('#bill-results').html('<div class="cash-bill-placeholder">Could not load unpaid bills for the selected user.</div>');
    });
  }

  function cashSetBillPickerMode(mode) {
    $('.cash-bill-toggle button').removeClass('is-active');
    $('.cash-bill-toggle button[data-view="' + mode + '"]').addClass('is-active');
    if (mode === 'select') {
      $('#cash-bill-buttons').removeClass('is-active');
      $('#cash-bill-select-wrap').addClass('is-active');
    } else {
      $('#cash-bill-select-wrap').removeClass('is-active');
      $('#cash-bill-buttons').addClass('is-active');
    }
  }

  function cashPickUser(userId) {
    userId = parseInt(userId, 10) || 0;
    cashRenderUserDetails(userId);
  }

  function cashProcessPayment(userId) {
    alert('Payment processing for User #' + userId);
  }

  function cashClearBillSelection() {
    $('#cash-selected-user-id').val(0);
    $('#cash_bill_user_id').val('0').trigger('change.select2');
    $('[data-bill-user]').removeClass('is-active');
    $('#bill-results').html('<div class="cash-bill-placeholder">Select a user using the buttons or select box above.</div>');
  }

  function cashLoadUnpaidUsers() {
    $.ajax({
      url: 'bill/api/bills_api.php',
      type: 'GET',
      dataType: 'json',
      data: {
        action: 'get_unpaid_users'
      }
    })
    .done(function(response) {
      if (!response || !response.ok) {
        $('#cash-bill-buttons').html('<div class="cash-bill-placeholder">Could not load unpaid users.</div>');
        $('#cash-bill-select-wrap').html('<div class="cash-bill-placeholder">Could not load unpaid users.</div>');
        return;
      }

      var data = response.data || {};
      cashBillsState.users = data.users || [];
      cashBillsState.unpaidSales = data.unpaid_sales || [];
      cashBillsState.summary = data.summary || cashBillsState.summary;

      cashSetSummary(cashBillsState.summary);

      $('#cash-bill-buttons').html(cashBuildUserButtons(cashBillsState.users));
      $('#cash_bill_user_id').html(cashBuildSelectOptions(cashBillsState.users));

      cashInitSelect2();

      if (cashBillsState.users.length) {
        cashPickUser(cashBillsState.users[0].user_id);
      } else {
        $('#bill-results').html('<div class="cash-bill-placeholder">No unpaid users found for this shift.</div>');
      }
    })
    .fail(function() {
      $('#cash-bill-buttons').html('<div class="cash-bill-placeholder">Could not load unpaid users from the API.</div>');
      $('#bill-results').html('<div class="cash-bill-placeholder">Could not load unpaid users from the API.</div>');
    });
  }

  $("#cash_bill_user_id").change(function(){
    var id = parseInt($("#cash_bill_user_id").val(), 10) || 0;
    if (id > 0) {
      cashPickUser(id);
    }
  });

  $(function() {
    cashInitSelect2();
    cashLoadUnpaidUsers();
  });
</script>
