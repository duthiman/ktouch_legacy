<?php
session_start();
include('../../../inc/connection.php');
require_once('../../../inc/gen_funcs.php');
$user_id = $_SESSION['user_id']; 

$shift = get_column('kp_shift','shift',"1");
if ($shift==0) {
  $shift =DATE('Y-m-d');
}else{
  $shift = get_column('kp_shift','shift',"1");
}
?>
<?php
$sql1 = "SELECT user_id,SUM(amount) AS amt FROM kp_sales WHERE shift='$shift' GROUP BY user_id ORDER BY amt DESC";
$result = mysqli_query($connection, $sql1) or die('could not fetch sub categories');
$waiters = [];
$cleared_map = [];
$clearance_q = mysqli_query($connection, "SELECT staff_user_id, cleared_by, created_at FROM kp_staff_clearances WHERE shift_date='$shift' ORDER BY clearance_id DESC");
if ($clearance_q) {
    while ($clear_row = mysqli_fetch_assoc($clearance_q)) {
        $sid = (int)$clear_row['staff_user_id'];
        if (!isset($cleared_map[$sid])) {
            $cleared_map[$sid] = $clear_row;
        }
    }
}
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sid = (int)$row['user_id'];
        $cleared_info = $cleared_map[$sid] ?? null;
        $waiters[] = [
            'user_id' => $sid,
            'name' => get_column('kp_users', 'username', "user_id='$sid'"),
            'amount' => (float)$row['amt'],
            'is_cleared' => $cleared_info ? 1 : 0,
            'cleared_by' => $cleared_info && !empty($cleared_info['cleared_by']) ? get_user((int)$cleared_info['cleared_by']) : '',
            'cleared_at' => $cleared_info['created_at'] ?? ''
        ];
    }
}
?>
<style type="text/css">
  .cash-clear-staff-shell {
    border: 1px solid #e7dbc9;
    border-radius: 18px;
    background: #fffdf9;
    overflow: hidden;
  }
  .cash-clear-staff-head {
    padding: 16px 18px;
    border-bottom: 1px solid #eee2d5;
    background: linear-gradient(135deg, #fff6e8 0%, #fffdfa 100%);
  }
  .cash-clear-staff-head h3 {
    margin: 0 0 4px;
    color: #734416;
    font-weight: 900;
  }
  .cash-clear-staff-head p {
    margin: 0;
    color: #826f5b;
  }
  .cash-clear-staff-body {
    padding: 16px 18px 18px;
  }
  .cash-clear-staff-tools {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 12px;
    align-items: end;
    margin-bottom: 14px;
  }
  .cash-clear-toggle {
    display: inline-flex;
    gap: 8px;
    padding: 4px;
    border-radius: 999px;
    background: #f8efe3;
    border: 1px solid #ecdcc6;
    margin-bottom: 12px;
  }
  .cash-clear-toggle button {
    border: 0;
    background: transparent;
    color: #7f5830;
    padding: 9px 14px;
    border-radius: 999px;
    font-weight: 800;
  }
  .cash-clear-toggle button.is-active {
    background: #f0a53a;
    color: #fff;
  }
  .cash-clear-staff-select {
    display: none;
  }
  .cash-clear-staff-select.is-active {
    display: block;
  }
  .cash-clear-staff-buttons {
    display: none;
    flex-wrap: wrap;
    gap: 10px;
  }
  .cash-clear-staff-buttons.is-active {
    display: flex;
  }
  .cash-clear-staff-chip {
    min-width: 160px;
    border: 1px solid #ead5b8;
    background: #fff;
    color: #55391d;
    border-radius: 14px;
    padding: 11px 14px;
    text-align: left;
    font-weight: 800;
    box-shadow: 0 6px 18px rgba(117, 79, 145, 0.05);
  }
  .cash-clear-staff-chip small,
  .cash-clear-staff-chip span {
    display: block;
  }
  .cash-clear-staff-chip small {
    color: #9a7d5d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-clear-staff-chip span {
    margin-top: 4px;
    font-size: 15px;
    color: #2e2114;
  }
  .cash-clear-staff-chip.is-active {
    background: #fff2db;
    border-color: #f0a53a;
  }
  .cash-clear-staff-chip.is-cleared {
    background: #f2fbf3;
    border-color: #8dc79a;
    box-shadow: 0 10px 22px rgba(65, 132, 78, 0.08);
  }
  .cash-clear-staff-chip.is-cleared small {
    color: #4f8a58;
  }
  .cash-clear-staff-chip .cash-clear-badge {
    display: inline-block;
    margin-top: 6px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .04em;
    text-transform: uppercase;
    background: #f7ead3;
    color: #915f24;
  }
  .cash-clear-staff-chip.is-cleared .cash-clear-badge {
    background: #dff3e2;
    color: #2e7d3e;
  }
  .cash-clear-load {
    border-radius: 12px;
    font-weight: 800;
    padding: 11px 18px;
  }
  .cash-clear-staff-results {
    min-height: 240px;
  }
  .cash-clear-placeholder {
    border: 1px dashed #e2d2bb;
    border-radius: 16px;
    background: #fff;
    padding: 20px;
    color: #86735f;
  }
  @media (max-width: 767px) {
    .cash-clear-staff-tools {
      grid-template-columns: 1fr;
    }
    .cash-clear-staff-chip {
      width: 100%;
      min-width: 0;
    }
    .cash-clear-load {
      width: 100%;
    }
  }
</style>
<div class="cash-clear-staff-shell">
  <div class="cash-clear-staff-head">
    <h3>Clear Staff</h3>
    <p>Pick a waiter, review the shift summary, then clear bills without losing the selected bills panel.</p>
  </div>
  <div class="cash-clear-staff-body">
    <div class="cash-clear-toggle">
      <button type="button" class="is-active" data-view="buttons" onclick="cashSetStaffPickerMode('buttons')">Big Buttons</button>
      <button type="button" data-view="select" onclick="cashSetStaffPickerMode('select')">Select Box</button>
    </div>

    <div class="cash-clear-staff-tools">
      <div>
        <div class="cash-clear-staff-buttons is-active" id="cash-staff-buttons">
          <?php if (count($waiters) < 1) { ?>
            <div class="cash-clear-placeholder">No waiters with shift sales were found.</div>
          <?php } else { ?>
            <?php foreach ($waiters as $waiter) { ?>
              <button type="button"
                      class="cash-clear-staff-chip <?php echo $waiter['is_cleared'] ? 'is-cleared' : ''; ?>"
                      data-staff-button="<?php echo $waiter['user_id']; ?>"
                      onclick="cashPickStaff(<?php echo $waiter['user_id']; ?>, '<?php echo addslashes($waiter['name']); ?>')">
                <small>Waiter</small>
                <span><?php echo htmlspecialchars($waiter['name']); ?></span>
                <span class="cash-clear-badge"><?php echo $waiter['is_cleared'] ? 'Cleared' : 'Pending'; ?></span>
              </button>
            <?php } ?>
          <?php } ?>
        </div>

        <div class="cash-clear-staff-select" id="cash-staff-select-wrap">
          <select class="form-control form-control-xs pickers text-black" id="cash_staff_user_id" name="cash_staff_user_id">
            <?php if (count($waiters) < 1) { ?>
              <option value="0">NO WAITERS FOUND</option>
            <?php } else { ?>
              <option value="0">-----SELECT WAITER-----</option>
              <?php foreach ($waiters as $waiter) { ?>
                <option value="<?php echo $waiter['user_id']; ?>"><?php echo htmlspecialchars($waiter['name'] . ($waiter['is_cleared'] ? ' (CLEARED)' : '')); ?></option>
              <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>

    </div>

    <input type="hidden" id="cash-selected-staff-id" value="0" />
    <div class="cash-clear-staff-results" id="stasales">
      <div class="cash-clear-placeholder">
        Select a waiter using the buttons or the select box above. The shift summary and previous uncleared bills will load here.
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">  
  $('.pickers').select2({}); 

  function cashSetStaffPickerMode(mode) {
    $('.cash-clear-toggle button').removeClass('is-active');
    $('.cash-clear-toggle button[data-view="' + mode + '"]').addClass('is-active');
    if (mode === 'select') {
      $('#cash-staff-buttons').removeClass('is-active');
      $('#cash-staff-select-wrap').addClass('is-active');
    } else {
      $('#cash-staff-select-wrap').removeClass('is-active');
      $('#cash-staff-buttons').addClass('is-active');
    }
  }

  function cashPickStaff(id, name) {
    id = parseInt(id, 10) || 0;
    $('#cash-selected-staff-id').val(id);
    $('#cash_staff_user_id').val(String(id)).trigger('change.select2');
    $('[data-staff-button]').removeClass('is-active');
    $('[data-staff-button="' + id + '"]').addClass('is-active');
    if (id > 0) {
      cashLoadStaffSummary(id, 'previous');
    }
  }

  function cashLoadStaffSummary(id, scope) {
    id = parseInt(id, 10) || 0;
    scope = scope || 'today';
    if (id < 1) {
      $('#stasales').html('<div class="alert alert-warning">Select a waiter first.</div>');
      return;
    }
    $('#cash-selected-staff-id').val(id);
    $.ajax({
      url: 'API/cash_api.php',
      type: 'GET',
      data: { action: 'clrstaff_summary', id: id, scope: scope },
      dataType: 'html'
    })
    .done(function(data){
      $('#stasales').html(data);
    })
    .fail(function(){
      $('#stasales').html('<div class="alert alert-danger">Could not load the waiter summary.</div>');
    });
  }

  $("#cash_staff_user_id").change(function(){
    var selected = $("#cash_staff_user_id option:selected").text();
    var id = parseInt($("#cash_staff_user_id").val(), 10) || 0;
    cashPickStaff(id, selected);
  });
</script>
