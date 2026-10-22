<?php
$item_id = (int)($item_id ?? 0);
$item_name = (string)($item_name ?? '');
$row = is_array($item_row ?? null) ? $item_row : [];
$current = (int)($row['uom_id'] ?? 0);
$items = is_array($uoms ?? null) ? $uoms : [];
?>
<div class="modal-content" style="width:420px;">
  <div class="modal-header menu-picker-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Units</h4></div>
  <div class="modal-body"><p><strong><?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></strong></p><div id="units-results"></div>
    <form id="units-form">
      <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
      <label for="units-item">Units Item</label>
      <select class="form-control" id="units-item" name="uom_id" required><option value="0">Select unit of measure</option>
        <?php foreach ($items as $unit): ?><option value="<?php echo (int)$unit['id']; ?>"<?php echo $current === (int)$unit['id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($unit['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
      </select>
      <small class="text-muted">Choose the product used as the unit for this item.</small>
    </form>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-success" id="save-units">Save Units</button></div>
</div>
<script>
(function ($) {
  $('#save-units').off('click.units').on('click.units', function () {
    var $button = $(this), item = $('#units-item').val();
    if (!item || item === '0') { $('#units-results').html('<div class="alert alert-danger">Select a units item first.</div>'); return; }
    $button.prop('disabled', true);
    $.post(window.menuApiUrl, $('#units-form').serialize() + '&action=save_units', null, 'json')
      .done(function (res) { if (res && res.ok) { $('#modal').modal('hide'); var table = getMenuTable(); if (table) table.ajax.reload(null, false); } else { $('#units-results').html('<div class="alert alert-danger">' + ((res && res.message) || 'Unable to save units.') + '</div>'); } })
      .fail(function () { $('#units-results').html('<div class="alert alert-danger">Unable to save units.</div>'); })
      .always(function () { $button.prop('disabled', false); });
  });
})(jQuery);
</script>
