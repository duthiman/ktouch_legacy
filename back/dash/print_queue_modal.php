<?php
session_start();
include('../../inc/connection.php');

if (!isset($_SESSION['user_id'])) {
    echo '<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Unauthorized.</div></div></div>';
    exit;
}

$status = [
    'running' => false,
    'heartbeat_age_seconds' => null,
    'pending' => 0,
    'processing' => 0,
    'failed' => 0,
    'not_printed_total' => 0,
];
$jobs = [];

$heartbeatFile = dirname(__DIR__, 2) . '/tools/print_worker.heartbeat';
if (is_file($heartbeatFile)) {
    $raw = @file_get_contents($heartbeatFile);
    $ts = (int) trim((string) $raw);
    if ($ts > 0) {
        $status['heartbeat_age_seconds'] = time() - $ts;
        $status['running'] = $status['heartbeat_age_seconds'] >= 0 && $status['heartbeat_age_seconds'] <= 120;
    }
}

$chk = mysqli_query($connection, "SHOW TABLES LIKE 'kp_print_queue'");
if ($chk && mysqli_num_rows($chk) > 0) {
    $r = mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status = 0");
    if ($r && ($row = mysqli_fetch_assoc($r))) {
        $status['pending'] = (int) $row['n'];
    }
    $r = mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status = 3");
    if ($r && ($row = mysqli_fetch_assoc($r))) {
        $status['processing'] = (int) $row['n'];
    }
    $r = mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status = 2");
    if ($r && ($row = mysqli_fetch_assoc($r))) {
        $status['failed'] = (int) $row['n'];
    }
    $status['not_printed_total'] = $status['pending'] + $status['processing'];

    $r = mysqli_query(
        $connection,
        "SELECT q.queue_id, q.sale_id, q.print_type, q.status, q.created_at,
                COALESCE(c.name, '') AS counter_name,
                COALESCE(NULLIF(TRIM(c.printer_name), ''), '') AS printer_name
         FROM kp_print_queue q
         LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
         ORDER BY q.queue_id DESC
         LIMIT 30"
    );
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $row['printer_display'] = trim((string) $row['printer_name']) !== '' ? $row['printer_name'] : ($row['counter_name'] !== '' ? $row['counter_name'] : '—');
            $st = (int) $row['status'];
            if ($st === 1) {
                $row['status_label'] = 'Printed';
                $row['status_class'] = 'success';
            } elseif ($st === 3) {
                $row['status_label'] = 'Printing';
                $row['status_class'] = 'warning';
            } elseif ($st === 2) {
                $row['status_label'] = 'Failed';
                $row['status_class'] = 'danger';
            } else {
                $row['status_label'] = 'Pending';
                $row['status_class'] = 'info';
            }
            $jobs[] = $row;
        }
    }
}

$heartbeatText = 'No heartbeat';
if ($status['heartbeat_age_seconds'] !== null && $status['heartbeat_age_seconds'] >= 0) {
    $heartbeatText = (int) $status['heartbeat_age_seconds'] . 's ago';
}
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><i class="fa fa-print"></i> Print Queue Status</h4>
  </div>
  <div class="modal-body">
    <div class="row" style="margin-bottom:12px;">
      <div class="col-sm-3 col-xs-6">
        <div class="alert alert-info" style="margin-bottom:10px;">
          <strong>Pending</strong><br><?php echo (int) $status['pending']; ?>
        </div>
      </div>
      <div class="col-sm-3 col-xs-6">
        <div class="alert alert-warning" style="margin-bottom:10px;">
          <strong>Printing</strong><br><?php echo (int) $status['processing']; ?>
        </div>
      </div>
      <div class="col-sm-3 col-xs-6">
        <div class="alert alert-danger" style="margin-bottom:10px;">
          <strong>Failed</strong><br><?php echo (int) $status['failed']; ?>
        </div>
      </div>
      <div class="col-sm-3 col-xs-6">
        <div class="alert <?php echo $status['running'] ? 'alert-success' : 'alert-warning'; ?>" style="margin-bottom:10px;">
          <strong><?php echo $status['running'] ? 'Worker Running' : 'Worker Offline'; ?></strong><br><?php echo htmlspecialchars($heartbeatText); ?>
        </div>
      </div>
    </div>

    <div style="margin-bottom:10px;display:flex;gap:8px;flex-wrap:wrap;">
      <a href="../../gen/startup_print.bat" download class="btn btn-sm btn-primary">Download Setup</a>
      <a href="../../front/chaba/index.php" target="_blank" rel="noopener" class="btn btn-sm btn-warning">Open Printing Page</a>
      <a href="../settings/settings.php?tab=printer-set" class="btn btn-sm btn-success">Open Printer Setup</a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-condensed">
        <thead>
          <tr>
            <th>Queue</th>
            <th>Sale</th>
            <th>Type</th>
            <th>Counter/Printer</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$jobs) { ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No print jobs found.</td>
            </tr>
          <?php } else { foreach ($jobs as $job) { ?>
            <tr>
              <td><?php echo (int) $job['queue_id']; ?></td>
              <td><?php echo (int) $job['sale_id']; ?></td>
              <td><?php echo htmlspecialchars(strtoupper((string) $job['print_type'])); ?></td>
              <td><?php echo htmlspecialchars((string) $job['printer_display']); ?></td>
              <td><span class="label label-<?php echo $job['status_class']; ?>"><?php echo htmlspecialchars($job['status_label']); ?></span></td>
              <td><?php echo htmlspecialchars((string) $job['created_at']); ?></td>
            </tr>
          <?php } } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
