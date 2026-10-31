<?php
$item_id = (int)($item_id ?? 0);
$assignments = is_array($counter_assignments ?? null) ? $counter_assignments : [];
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><i class="fa fa-building"></i> Assign Item to Counters</h4>
  </div>
  <div class="modal-body">
    <p><strong><?php echo htmlspecialchars($item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></p>
    <p class="text-muted">Select the counters where this item should be available. Set opening quantity and reorder level for each selected counter.</p>
    <?php if (!$assignments): ?>
      <div class="alert alert-warning">No active counters are configured.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-condensed">
          <thead><tr><th>Assign</th><th>Counter</th><th>Quantity</th><th>Reorder level</th></tr></thead>
          <tbody>
          <?php foreach ($assignments as $counter): ?>
            <tr>
              <td><input type="checkbox" class="counter-assignment-check" value="<?php echo (int)$counter['id']; ?>" <?php echo !empty($counter['is_active']) ? 'checked' : ''; ?>></td>
              <td><?php echo htmlspecialchars($counter['name'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><input type="number" min="0" step="0.001" class="form-control counter-assignment-qty" data-counter="<?php echo (int)$counter['id']; ?>" value="<?php echo htmlspecialchars((string)$counter['qty'], ENT_QUOTES, 'UTF-8'); ?>"></td>
              <td><input type="number" min="0" step="0.001" class="form-control counter-assignment-reorder" data-counter="<?php echo (int)$counter['id']; ?>" value="<?php echo htmlspecialchars((string)$counter['reorder_level'], ENT_QUOTES, 'UTF-8'); ?>"></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <div id="counter-assignment-result"></div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    <?php if ($assignments): ?><button type="button" class="btn btn-primary" id="save-counter-assignments" data-item-id="<?php echo $item_id; ?>">Save Assignments</button><?php endif; ?>
  </div>
</div>
