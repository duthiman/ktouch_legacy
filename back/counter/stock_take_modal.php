<?php
session_start();
include('../../inc/connection.php');
include_once('funcs.php');

$counter = (int)($_REQUEST['cntr_id'] ?? 0);
$shift = trim((string)($_REQUEST['shift'] ?? ''));
$view = strtolower(trim((string)($_REQUEST['view'] ?? 'variance')));
$summary_id = (int)($_REQUEST['summary_id'] ?? 0);

if ($counter < 1) {
    ?>
    <div class="modal-content" style="border-radius:5px;">
      <div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Invalid counter selected.</div></div>
    </div>
    <?php
    exit;
}

if ($shift === '') {
    $shift = (string)get_column('kp_shift', 'shift', "1");
}

$summary = kp_counter_stock_take_snapshot_summary($connection, $counter, $shift, $summary_id);
$summary_id = (int)($summary['summary_id'] ?? $summary_id);
$rows = kp_counter_stock_take_snapshot_rows($connection, $counter, $shift, $summary_id, $view === 'ok' ? 'ok' : ($view === 'variance' ? 'variance' : 'all'));
$title = $view === 'ok' ? 'OK Items' : ($view === 'variance' ? 'Variance Items' : 'Stock Take Items');
$returnTo = '../../back/counter/index.php';
$variancePrintUrl = '../../front/chaba/receipt_counter.php?type=variance&id=' . urlencode((string)$counter) . '&cntr_id=' . urlencode((string)$counter) . '&shift=' . urlencode($shift) . '&summary_id=' . urlencode((string)$summary_id) . '&return_to=' . urlencode($returnTo);
?>
<div class="modal-content" style="border-radius:5px; overflow:hidden;">
  <div class="modal-header" style="background:#0b6b7f;color:#fff;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
    <h4 class="modal-title"><?php echo htmlspecialchars(get_counter($counter) . ' - ' . $title); ?></h4>
  </div>
  <div class="modal-body" style="background:#fff;">
    <div class="row" style="margin-bottom:10px;">
      <div class="col-sm-4"><strong>Shift:</strong> <?php echo htmlspecialchars($shift); ?></div>
      <div class="col-sm-4"><strong>OK Items:</strong> <?php echo (int)$summary['ok_count']; ?></div>
      <div class="col-sm-4"><strong>Variance Items:</strong> <?php echo (int)$summary['variance_count']; ?></div>
    </div>
    <div class="table-responsive" style="max-height:60vh; overflow-y:auto;">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Item</th>
            <th>Opening</th>
            <th>Counted</th>
            <th>Closing</th>
            <th>Variance</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($rows) < 1) { ?>
            <tr><td colspan="6" class="text-center">No items found for this selection.</td></tr>
          <?php } else { ?>
            <?php $no = 0; foreach ($rows as $row) { $no++; ?>
            <tr>
              <td><?php echo $no; ?></td>
              <td><?php echo htmlspecialchars($row['item']); ?></td>
              <td class="text-right"><?php echo number_format((float)$row['opening'], 2); ?></td>
              <td class="text-right"><?php echo number_format((float)$row['counted'], 2); ?></td>
              <td class="text-right"><?php echo number_format((float)$row['closing'], 2); ?></td>
              <td class="text-right <?php echo $row['is_ok'] ? 'text-success' : 'text-danger'; ?>"><?php echo number_format((float)$row['variance'], 2); ?></td>
            </tr>
            <?php } ?>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
    <?php if ($view === 'variance') { ?>
      <a href="<?php echo htmlspecialchars($variancePrintUrl); ?>" class="btn btn-outline-primary">Queue Print Variance</a>
    <?php } ?>
    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
  </div>
</div>
