<?php
session_start();
include('../../inc/connection.php'); 
include_once('../../inc/gen_funcs.php');

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id < 1) {
  ?>
  <script>
    window.location = "../log_out.php";
  </script>
  <?php
  exit;
}

$req_id = (int)($_SESSION['req'] ?? 0);
$counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
$print_mode = (int)get_print_mode();
$requisitions = mysqli_query($connection, "SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' ORDER BY id DESC") or die(mysqli_error($connection));
$item_count = mysqli_num_rows($requisitions);
$counters = null;

if ($print_mode !== 1) {
  $counters = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, cntr_id ASC") or die(mysqli_error($connection));
}
?>
<style type="text/css">
  .req-confirm-shell .modal-content {
    display: flex;
    flex-direction: column;
    border-radius: 16px;
    overflow: hidden;
    max-height: 92vh;
  }
  .req-confirm-shell .modal-header {
    padding: 14px 16px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
    border-bottom: 0;
  }
  .req-confirm-shell .close {
    color: #fff;
    opacity: 1;
  }
  .req-confirm-shell .modal-title {
    font-weight: 900;
    text-transform: uppercase;
  }
  .req-confirm-body {
    background: linear-gradient(180deg, #f6f0d7 0%, #efe3bf 100%);
    padding: 14px;
    overflow-y: auto;
  }
  .req-confirm-warning {
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff7dc;
    border: 1px solid rgba(126, 58, 32, 0.12);
    color: #7e3a20;
    font-weight: 800;
    margin-bottom: 12px;
  }
  .req-confirm-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08);
    margin-bottom: 12px;
  }
  .req-confirm-summary strong,
  .req-confirm-summary span {
    display: block;
  }
  .req-confirm-summary strong {
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #7e3a20;
  }
  .req-confirm-summary span {
    font-size: 24px;
    font-weight: 900;
    color: #b94828;
    line-height: 1;
    margin-top: 4px;
  }
  .req-confirm-list {
    max-height: 42vh;
    overflow-y: auto;
    padding-right: 4px;
  }
  .req-confirm-empty,
  .req-confirm-item {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08);
    margin-bottom: 10px;
    padding: 12px 14px;
  }
  .req-confirm-item-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
  }
  .req-confirm-item-no {
    min-width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #f9e7cd;
    color: #7e3a20;
    font-size: 13px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .req-confirm-item-name {
    flex: 1 1 auto;
    min-width: 0;
    font-size: 14px;
    font-weight: 900;
    color: #382819;
    line-height: 1.3;
  }
  .req-confirm-item-qty {
    min-width: 82px;
    text-align: right;
  }
  .req-confirm-item-qty strong {
    display: block;
    font-size: 10px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8a6a3a;
  }
  .req-confirm-item-qty span {
    display: block;
    margin-top: 4px;
    font-size: 20px;
    font-weight: 900;
    color: #4f7e1e;
    line-height: 1;
  }
  .req-confirm-pick-copy {
    margin: 14px 0 10px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff;
    color: #7e3a20;
    box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08);
    font-weight: 800;
  }
  .req-print-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
    margin-top: 10px;
  }
  .req-print-btn {
    width: 100%;
    min-height: 88px;
    border: 2px solid #d8c3a2;
    border-radius: 16px;
    background: #fff;
    color: #6b4218;
    text-align: center;
    padding: 12px 10px;
    box-shadow: 0 10px 18px rgba(79, 58, 24, 0.08);
    font-weight: 900;
  }
  .req-print-btn.active {
    border-color: #2f7a31;
    background: linear-gradient(135deg, #eef8e9 0%, #fff2e6 100%);
    color: #235f25;
  }
  .req-print-btn strong {
    display: block;
    font-size: 26px;
    line-height: 1;
  }
  .req-print-btn span {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    text-transform: uppercase;
  }
  .req-confirm-shell .modal-footer {
    flex: 0 0 auto;
    padding: 12px 14px 14px;
    border-top: 0;
    background: #fffdf8;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .req-confirm-btn {
    min-height: 48px;
    border: 0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .req-confirm-close {
    background: #ead6d6;
    color: #7e2014;
  }
  .req-confirm-submit {
    background: linear-gradient(135deg, #4f7e1e 0%, #7fbb33 100%);
    color: #fff;
  }
  @media (max-width: 767px) {
    .req-confirm-shell .modal-dialog {
      margin: 10px;
    }
    .req-confirm-shell .modal-content {
      max-height: calc(100vh - 20px);
    }
    .req-confirm-body {
      padding: 10px;
    }
    .req-confirm-summary {
      padding: 10px 12px;
    }
    .req-confirm-summary span {
      font-size: 20px;
    }
    .req-confirm-list {
      max-height: 28vh;
    }
    .req-print-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .req-print-btn {
      min-height: 74px;
      padding: 10px 8px;
    }
    .req-print-btn strong {
      font-size: 22px;
    }
    .req-print-btn span {
      font-size: 11px;
    }
    .req-confirm-shell .modal-footer {
      position: sticky;
      bottom: 0;
      z-index: 2;
    }
  }
</style>
<div class="req-confirm-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title" style="font-weight: bold;"><?php echo get_counter($counter); ?> STOCK REQUEST CONFIRMATION</h4>
    </div>
    <div class="modal-body req-confirm-body">
      <div id="req-print-feedback"></div>
      <div class="req-confirm-warning">
        Make sure you confirm the requested items below. The request cannot go through without items.
      </div>
      <div class="req-confirm-summary">
        <div>
          <strong>Counter</strong>
          <span><?php echo get_counter($counter); ?></span>
        </div>
        <div style="text-align:right;">
          <strong>Items Raised</strong>
          <span><?php echo $item_count; ?></span>
        </div>
      </div>
      <div class="req-confirm-list">
        <?php if ($item_count < 1) { ?>
          <div class="req-confirm-empty">
            No items have been added to this requisition yet.
          </div>
        <?php } else { ?>
          <?php $no = 0; while ($row = mysqli_fetch_assoc($requisitions)) { ?>
            <?php $item_id = (int)$row['item_id']; $no++; ?>
            <div class="req-confirm-item">
              <div class="req-confirm-item-top">
                <div class="req-confirm-item-no"><?php echo $no; ?></div>
                <div class="req-confirm-item-name"><?php echo get_item($item_id); ?></div>
                <div class="req-confirm-item-qty">
                  <strong>Qty</strong>
                  <span><?php echo $row['req_qty']; ?></span>
                </div>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
      <?php if ($item_count > 0 && $print_mode !== 1) { ?>
        <div class="req-confirm-pick-copy">
          Choose where to collect the printed requisition.
        </div>
        <div class="req-print-grid">
          <?php while ($printer_counter = mysqli_fetch_assoc($counters)) { ?>
            <?php $active = ((int)$printer_counter['cntr_id'] === $counter) ? ' active' : ''; ?>
            <button
              type="button"
              class="req-print-btn<?php echo $active; ?>"
              data-req-print-counter="<?php echo (int)$printer_counter['cntr_id']; ?>">
              <strong><?php echo (int)$printer_counter['cntr_id']; ?></strong>
              <span><?php echo htmlspecialchars($printer_counter['name']); ?><?php echo !empty($printer_counter['dpt']) ? ' - DPT ' . (int)$printer_counter['dpt'] : ''; ?></span>
            </button>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
    <?php if ($item_count > 0) { ?>
      <div class="modal-footer">
        <button type="button" class="req-confirm-btn req-confirm-close" data-dismiss="modal">Close</button>
        <button
          type="button"
          class="req-confirm-btn req-confirm-submit"
          id="gwitia-submit-request"
          data-print-mode="<?php echo $print_mode; ?>">
          Request
        </button>
      </div>
    <?php } else { ?>
      <div class="modal-footer" style="grid-template-columns:1fr;">
        <button type="button" class="req-confirm-btn req-confirm-close" data-dismiss="modal">Close</button>
      </div>
    <?php } ?>
  </div>
</div>

<script type="text/javascript">
  (function($) {
    var selectedReqCounter = <?php echo $print_mode === 1 ? 0 : (int)$counter; ?>;

    $(document).off('click.gwitiaCounter').on('click.gwitiaCounter', '[data-req-print-counter]', function() {
      selectedReqCounter = parseInt($(this).data('reqPrintCounter'), 10) || 0;
      $('[data-req-print-counter]').removeClass('active');
      $(this).addClass('active');
      $('#req-print-feedback').html('');
    });

    $(document).off('click.gwitiaSubmit').on('click.gwitiaSubmit', '#gwitia-submit-request', function() {
      var printMode = parseInt($(this).data('printMode'), 10) || 0;
      if (printMode !== 1 && !selectedReqCounter) {
        $('#req-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">Please select a printer counter.</div>');
        return;
      }

      $('#gwitia-submit-request').prop('disabled', true);
      $('#req-print-feedback').html('<div class="alert alert-info" style="margin-bottom:12px;font-weight:700;">Sending requisition...</div>');

      $.ajax({
        url: 'API/gwit_api.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'submit_request', cntr_id: selectedReqCounter }
      })
      .done(function(res) {
        if (res && res.ok) {
          var redirect = res.data && res.data.redirect ? res.data.redirect : '../dash/dash.php';
          if (redirect) {
            window.location.replace(redirect);
            return;
          }
          $('#req-print-feedback').html('<div class="alert alert-success" style="margin-bottom:12px;font-weight:700;">Requisition sent successfully.</div>');
          $('#modal').modal('hide');
          window.location = '../dash/dash.php';
          return;
        }
        $('#req-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">' + ((res && res.message) ? res.message : 'Could not submit requisition.') + '</div>');
        $('#gwitia-submit-request').prop('disabled', false);
      })
      .fail(function() {
        $('#req-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">Could not submit requisition.</div>');
        $('#gwitia-submit-request').prop('disabled', false);
      });
    });
  })(jQuery);
</script>
