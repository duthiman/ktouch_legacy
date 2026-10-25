<?php
session_start();
include_once('../../inc/connection.php');
include_once('funcs.php');

$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");
$counter = user_counter($user_id);

$pending_returns = array();
$pending_returns_q = mysqli_query($connection, "SELECT sale_id,amount,user_id,stock_counter FROM kp_ret_items WHERE shift='$shift' AND stock_counter='$counter' AND ret_status=3 ORDER BY sale_id DESC, id DESC") or die(mysqli_error($connection));
while ($row = mysqli_fetch_assoc($pending_returns_q)) {
  $pending_returns[] = $row;
}

$unreceived_transfers = array();
$unreceived_transfers_q = mysqli_query($connection, "SELECT req_id,req_user,issue_user FROM kp_counter_req WHERE counter='$counter' AND issue_status=1 AND rec_status=2 ORDER BY req_id DESC") or die(mysqli_error($connection));
while ($row = mysqli_fetch_assoc($unreceived_transfers_q)) {
  $req_id = (int)$row['req_id'];
  $row['items'] = (int)column_count('kp_counter_req_items','id',"req_id='$req_id'");
  $issued_qty = (float)column_total('kp_counter_req_items','issue_qty',"req_id='$req_id'");
  $requested_qty = (float)column_total('kp_counter_req_items','req_qty',"req_id='$req_id'");
  $row['qty'] = $issued_qty > 0 ? $issued_qty : $requested_qty;
  $unreceived_transfers[] = $row;
}

$can_close = empty($pending_returns) && empty($unreceived_transfers);
$close_pin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

if (!function_exists('close_modal_waiter_name')) {
  function close_modal_waiter_name($id) {
    if (empty($id)) {
      return 'Unknown';
    }
    return get_user($id);
  }
}
?> 
<style>
  .close-check-shell .modal-content {
    border-radius: 5px;
    overflow: hidden;
  }
  .close-check-shell .modal-header {
    background: linear-gradient(135deg, #7d1418 0%, #a61c21 100%);
    color: #fff;
    border-bottom: 0;
    padding: 16px 18px;
  }
  .close-check-shell .modal-title {
    font-weight: 800;
    font-size: 18px;
    letter-spacing: .3px;
  }
  .close-check-shell .modal-body {
    background: #fffaf5;
    padding: 16px;
    max-height: 72vh;
    overflow-y: auto;
  }
  .close-check-shell .modal-footer {
    background: #fff7ef;
    border-top: 0;
    padding: 14px 16px 16px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    flex-wrap: wrap;
  }
  .close-check-shell .close {
    color: #fff;
    opacity: 1;
    text-shadow: none;
  }
  .close-check-intro {
    background: #fff;
    border: 1px solid #f0d2cb;
    border-left: 5px solid #a61c21;
    border-radius: 5px;
    padding: 14px 16px;
    margin-bottom: 14px;
    color: #5a2d20;
  }
  .close-check-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
  }
  .close-check-card {
    background: #fff;
    border: 1px solid #ecdccf;
    border-radius: 5px;
    padding: 14px;
    box-shadow: 0 6px 18px rgba(84, 39, 26, .06);
  }
  .close-check-card strong,
  .close-check-card span {
    display: block;
  }
  .close-check-card strong {
    color: #7a4131;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 6px;
  }
  .close-check-card span {
    color: #24130d;
    font-size: 20px;
    font-weight: 800;
  }
  .close-check-panel {
    background: #fff;
    border: 1px solid #ead8cb;
    border-radius: 5px;
    padding: 14px;
    margin-bottom: 14px;
  }
  .close-check-panel h5 {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: 800;
    color: #6f261e;
  }
  .close-check-note {
    margin: 0 0 10px;
    color: #77584b;
    font-size: 13px;
  }
  .close-check-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .close-check-item {
    border: 1px solid #efe2d7;
    border-radius: 5px;
    padding: 12px;
    background: #fffaf6;
  }
  .close-check-item b,
  .close-check-item span {
    display: block;
  }
  .close-check-item b {
    color: #2e1a14;
    font-size: 14px;
    margin-bottom: 3px;
  }
  .close-check-item span {
    color: #7c5d51;
    font-size: 13px;
  }
  .close-check-ok {
    background: #f2fff5;
    border: 1px solid #cfe7d4;
    border-radius: 5px;
    padding: 18px;
    text-align: center;
    color: #1f6a3c;
    font-weight: 700;
  }
  .close-check-btn {
    min-width: 140px;
    border-radius: 5px;
    font-weight: 800;
    padding: 12px 18px;
  }
  .close-check-btn.btn-default {
    border-color: #d8c8bc;
    background: #fff;
    color: #6a5548;
  }
  .close-check-btn.btn-danger {
    background: #a61c21;
    border-color: #a61c21;
  }
  .close-check-btn.btn-muted {
    background: #9b9b9b;
    color: #fff;
    border-color: #9b9b9b;
    cursor: not-allowed;
  }
  .close-counter-progress {
    display: none;
    width: 100%;
    margin: 0 0 12px;
    padding: 12px 14px;
    border: 1px solid #ead8cb;
    border-radius: 5px;
    background: #fff;
    color: #6f261e;
    font-weight: 700;
  }
  .close-counter-progress-bar {
    height: 6px;
    margin-top: 8px;
    overflow: hidden;
    border-radius: 5px;
    background: #f1ddd2;
  }
  .close-counter-progress-bar span {
    display: block;
    width: 35%;
    height: 100%;
    border-radius: 5px;
    background: #a61c21;
    animation: closeCounterProgress 1.2s ease-in-out infinite;
  }
  .close-pin-overlay {
    display:none;
    position:fixed;
    z-index:1060;
    inset:0;
    align-items:center;
    justify-content:center;
    padding:18px;
    background:rgba(20,28,38,.68);
  }
  .close-pin-card {
    width:min(360px,100%);
    padding:24px;
    border-radius:10px;
    background:#fff;
    box-shadow:0 18px 55px rgba(0,0,0,.28);
    text-align:center;
  }
  .close-pin-code {
    margin:10px 0 16px;
    padding:10px;
    border-radius:8px;
    background:#fff4e8;
    color:#9b321c;
    font-size:30px;
    font-weight:800;
    letter-spacing:8px;
  }
  .close-pin-input { text-align:center; font-size:22px; letter-spacing:5px; }
  .close-pin-pad { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:14px; }
  .close-pin-pad button { min-height:50px; border:1px solid #d8e1ea; border-radius:7px; background:#f7faff; color:#243b53; font-size:21px; font-weight:700; }
  .close-pin-action { min-height:50px; border:0; border-radius:7px; color:#fff; font-size:16px; font-weight:800; }
  #close-pin-cancel { background:#dc3545; }
  #close-pin-submit { background:#f39c12; border-color:#f39c12; }
  @keyframes closeCounterProgress {
    0% { transform: translateX(-110%); }
    100% { transform: translateX(310%); }
  }
  @media (max-width: 767px) {
    .close-check-shell .modal-dialog {
      width: calc(100vw - 18px);
      margin: 10px auto;
    }
    .close-check-grid {
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    .close-check-shell .modal-footer {
      justify-content: stretch;
    }
    .close-check-shell .modal-footer .close-check-btn {
      flex: 1 1 calc(50% - 5px);
      min-width: 0;
    }
  }
</style>
<div class="close-check-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Close Counter Check</h4>
    </div>
    <div class="modal-body"> 
      <div id="tt"></div>
      <div class="close-check-intro">
        The counter can close as long as it has no unapproved returns.
      </div>

      <div class="close-check-grid">
        <div class="close-check-card">
          <strong>Counter</strong>
          <span><?php echo get_counter($counter); ?></span>
        </div>
        <div class="close-check-card">
          <strong>Shift</strong>
          <span><?php echo $shift; ?></span>
        </div>
        <div class="close-check-card">
          <strong>Pending Stock</strong>
          <span><?php echo count($unreceived_transfers); ?></span>
        </div>
      </div>

      <div class="close-check-panel">
        <h5>Pending Returns</h5>
        <p class="close-check-note">These returns are still waiting to be confirmed or rejected before counter close.</p>
        <?php if (!empty($pending_returns)) { ?>
        <div class="close-check-list">
          <?php foreach ($pending_returns as $row) { ?>
          <div class="close-check-item">
            <b>Bill <?php echo (int)$row['sale_id']; ?> | Return Value <?php echo number_format((float)$row['amount'], 2); ?></b>
            <span>Waiter: <?php echo close_modal_waiter_name($row['user_id']); ?></span>
            <span>Status: Pending return confirmation</span>
          </div>
          <?php } ?>
        </div>
        <?php } else { ?>
        <div class="close-check-ok">There are no pending returns on this counter.</div>
        <?php } ?>
      </div>

      <div class="close-check-panel" style="margin-bottom:0;">
        <h5>Unreceived Stock</h5>
        <p class="close-check-note">These stock requests must be received before counter closing.</p>
        <?php if (!empty($unreceived_transfers)) { ?>
        <div class="close-check-list">
          <?php foreach ($unreceived_transfers as $row) { ?>
          <div class="close-check-item">
            <b>Transfer Req <?php echo (int)$row['req_id']; ?> | Qty <?php echo number_format((float)$row['qty'], 2); ?></b>
            <span>Requested by: <?php echo close_modal_waiter_name($row['req_user']); ?></span>
            <span>Issued by: <?php echo close_modal_waiter_name($row['issue_user']); ?></span>
            <span>Items: <?php echo (int)$row['items']; ?></span>
            <span>Status: Waiting to be received into stock</span>
          </div>
          <?php } ?>
        </div>
        <?php } else { ?>
        <div class="close-check-ok">No stock transfer is waiting to be received for this counter.</div>
        <?php } ?>
      </div>
    </div>
    <div class="modal-footer">
      <div class="close-counter-progress" id="close-counter-progress" role="status" aria-live="polite">
        <span id="close-counter-progress-text">Closing counter and saving the summary...</span>
        <div class="close-counter-progress-bar"><span></span></div>
      </div>
      <?php if (!empty($pending_returns)) { ?>
      <a href="../ret/appr/returns.php" class="btn btn-warning close-check-btn">Handle Returns</a>
      <?php } ?>
      <?php if (!empty($unreceived_transfers)) { ?>
      <a href="../rec/rec_pending.php" class="btn btn-warning close-check-btn">Receive Requests</a>
      <?php } ?>
      <button type="button" class="btn btn-default close-check-btn" data-dismiss="modal">Back</button>
      <?php if ($can_close) { ?>
      <button type="button" class="btn btn-danger close-check-btn" id="confirm-counter-close">Confirm Close</button>
      <?php } ?>
    </div>
  </div>
</div>
<div class="close-pin-overlay" id="close-pin-overlay" role="dialog" aria-modal="true" aria-labelledby="close-pin-title">
  <div class="close-pin-card">
    <h4 id="close-pin-title" style="margin-top:0;color:#243b53;font-weight:800;">Confirm Counter Close</h4>
    <p style="color:#718096;margin-bottom:6px;">Enter this PIN using the numpad exactly as shown.</p>
    <div class="close-pin-code" id="close-pin-code"><?php echo $close_pin; ?></div>
    <input type="text" inputmode="numeric" maxlength="4" class="form-control close-pin-input" id="close-pin-input" autocomplete="off" readonly>
    <div class="close-pin-pad" id="close-pin-pad">
      <?php for ($pin_digit = 1; $pin_digit <= 9; $pin_digit++) { ?><button type="button" data-pin-digit="<?php echo $pin_digit; ?>"><?php echo $pin_digit; ?></button><?php } ?>
      <button type="button" data-pin-action="clear">Clear</button>
      <button type="button" data-pin-digit="0">0</button>
      <button type="button" data-pin-action="backspace">⌫</button>
    </div>
    <div style="display:flex;gap:8px;margin-top:16px;">
      <button type="button" class="close-pin-action" id="close-pin-cancel" style="flex:1;">Close</button>
      <button type="button" class="close-pin-action" id="close-pin-submit" style="flex:1;">Verify &amp; Close</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  (function ($) {
    var closePin = <?php echo json_encode($close_pin); ?>;
    var $pinOverlay = $('#close-pin-overlay');
    var $pinInput = $('#close-pin-input');
    var $closeButton = $('#confirm-counter-close');
    $('#close-pin-pad').on('click', 'button', function () {
      var action = $(this).data('pin-action');
      var digit = $(this).data('pin-digit');
      var value = String($pinInput.val() || '');
      if (action === 'clear') value = '';
      else if (action === 'backspace') value = value.slice(0, -1);
      else if (digit !== undefined && value.length < 4) value += String(digit);
      $pinInput.val(value);
    });
    $('#close-pin-cancel').on('click', function () { $pinOverlay.css('display','none'); $pinInput.val(''); });
    $('#close-pin-submit').on('click', function () {
      if (String($pinInput.val()) !== closePin) {
        if (window.Swal) Swal.fire({icon:'error', title:'Incorrect PIN', text:'Enter the displayed PIN exactly as shown.'});
        else alert('Incorrect PIN.');
        $pinInput.val('');
        return;
      }
      $pinOverlay.css('display','none');
      $pinInput.val('');
      $closeButton.data('pin-verified', true).trigger('click');
    });
    $(document).off('click.closeCounterAjax', '#confirm-counter-close').on('click.closeCounterAjax', '#confirm-counter-close', function (e) {
      e.preventDefault();
      var $btn = $(this);
      if ($btn.prop('disabled')) {
        return;
      }
      if (!$btn.data('pin-verified')) {
        $pinOverlay.css('display','flex');
        return;
      }
      $btn.removeData('pin-verified');
      var $modal = $btn.closest('.modal-content');
      var $progress = $modal.find('#close-counter-progress');
      var $progressText = $modal.find('#close-counter-progress-text');
      $btn.prop('disabled', true).text('Closing...');
      $modal.find('[data-dismiss="modal"]').prop('disabled', true);
      $progress.css('display', 'block');
      $progressText.text('Closing counter and saving the summary...');
      window.setTimeout(function () {
        $progressText.text('Finalizing stock and counter records...');
      }, 900);
      $.ajax({
        url: '../counter/close_counter.php',
        type: 'POST',
        dataType: 'json',
        data: { ajax: 1 }
      }).done(function (res) {
        var message = (res && res.message) ? res.message : 'Counter is closed.';
        if (res && res.redirect) {
          window.location.href = res.redirect;
        } else {
          window.location.href = '../log_out.php';
        }
      }).fail(function (xhr) {
        var message = 'Could not close the counter right now. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        } else if (xhr.responseText && xhr.responseText.trim()) {
          message = xhr.responseText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        }
        alert(message);
        $btn.prop('disabled', false).text('Confirm Close');
        $modal.find('[data-dismiss="modal"]').prop('disabled', false);
        $progress.hide();
      });
    });
  })(jQuery);
</script>
