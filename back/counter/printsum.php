<?php
session_start();
include_once('../../inc/connection.php');
include_once('funcs.php');
include_once('../../front/chaba/funcs.php');

$id = (int)($_REQUEST['id'] ?? 0);
$shift = trim((string)($_REQUEST['shift'] ?? ''));
$summary_id = (int)($_REQUEST['summary_id'] ?? 0);
$action = trim((string)($_REQUEST['action'] ?? ''));
$user_id = (int)($_SESSION['user_id'] ?? 0);

if ($id < 1 && $action !== 'queue_shift') {
    exit;
}

if ($shift === '') {
    $shift = (string)get_column('kp_shift', 'shift', "1");
}

if ($action === 'queue') {
    header('Content-Type: application/json');
    if ($user_id < 1) {
        echo json_encode(['ok' => false, 'message' => 'User session missing.']);
        exit;
    }
    ensure_chaba_print_system($connection);
    $routeQuery = 'summary_id=' . urlencode((string)$summary_id)
        . '&cntr_id=' . urlencode((string)$id)
        . '&shift=' . urlencode($shift);
    $queue_id = kp_print_enqueue($connection, [
        'sale_id' => 0,
        'ref_id' => $summary_id,
        'print_type' => 'counter_sum',
        'label' => 'COUNTER SUMMARY',
        'cntr_id' => $id,
        'route_path' => 'render_counter_sum.php',
        'route_query' => $routeQuery,
        'requested_by' => $user_id,
    ]);
    if ($queue_id > 0) {
        echo json_encode(['ok' => true, 'message' => 'Counter summary queued to this counter printer.']);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Could not queue counter summary.']);
    }
    exit;
}

if ($action === 'queue_shift') {
    header('Content-Type: application/json');
    if ($user_id < 1) {
        echo json_encode(['ok' => false, 'message' => 'User session missing.']);
        exit;
    }
    ensure_chaba_print_system($connection);
    $payload = json_encode([
        'payload_version' => 1,
        'document_type' => 'SSP',
        'title' => 'SHIFT SUMMARY PRINT',
        'header' => ['shift_date' => $shift, 'print_time' => date('Y-m-d H:i:s')],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $queue_id = kp_print_enqueue($connection, [
        'sale_id' => 0,
        'ref_id' => 0,
        'print_type' => 'ssp',
        'label' => 'SHIFT SUMMARY',
        'cntr_id' => 0,
        'route_path' => 'ssp.php',
        'route_query' => 'shift=' . urlencode($shift),
        'requested_by' => $user_id,
        'payload_json' => $payload,
    ]);
    echo json_encode($queue_id > 0
        ? ['ok' => true, 'message' => 'Shift summary queued to the global print queue.']
        : ['ok' => false, 'message' => 'Could not queue the shift summary.']);
    exit;
}

$renderUrl = '../../front/chaba/render_counter_sum.php'
    . '?summary_id=' . urlencode((string)$summary_id)
    . '&cntr_id=' . urlencode((string)$id)
    . '&shift=' . urlencode($shift);

?>
<div class="modal-content" style="border-radius:5px; overflow:hidden;">
  <div class="modal-header" style="background:#0b6b7f;color:#fff;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
    <h4 class="modal-title">Counter Summary Print</h4>
  </div>
  <div class="modal-body" style="background:#fff;">
    <div class="alert alert-info" style="margin-bottom:10px;">
      Review the summary below, then click print to queue it directly to this counter's printer.
    </div>
    <div style="height:500px; overflow-y:auto; border:1px solid #d9e4ea; border-radius:5px; background:#fff;">
      <iframe
        id="counter-summary-frame"
        src="<?php echo htmlspecialchars($renderUrl); ?>"
        style="width:100%;height:100%;min-height:480px;border:none;background:#fff;"
      ></iframe>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-outline-success btn-sm" id="queue-counter-summary-btn">Print Summary</button>
  </div>
</div>
<script type="text/javascript">
  (function ($) {
    $(document).off('click.queueCounterSummary', '#queue-counter-summary-btn').on('click.queueCounterSummary', '#queue-counter-summary-btn', function (e) {
      e.preventDefault();
      var $btn = $(this);
      if ($btn.prop('disabled')) {
        return;
      }
      $btn.prop('disabled', true).text('Queueing...');
      $.ajax({
        url: 'printsum.php',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'queue',
          id: <?php echo (int)$id; ?>,
          shift: <?php echo json_encode($shift); ?>,
          summary_id: <?php echo (int)$summary_id; ?>
        }
      }).done(function (res) {
        alert((res && res.message) ? res.message : 'Counter summary queued.');
        if (res && res.ok) {
          $('.modal').modal('hide');
        } else {
          $btn.prop('disabled', false).text('Print Summary');
        }
      }).fail(function () {
        alert('Could not queue the counter summary right now.');
        $btn.prop('disabled', false).text('Print Summary');
      });
    });
  })(jQuery);
</script>
