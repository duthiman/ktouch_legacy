<?php
include("funcs.php");
$filter_po = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$where = $filter_po > 0 ? "WHERE g.po_id='$filter_po'" : "";
$title = $filter_po > 0 ? "PO #$filter_po — GOODS RECEIVED NOTES" : "ALL GOODS RECEIVED NOTES";
?>
<style>
.grn-header { background:linear-gradient(135deg,#008080,#0d9488); color:#fff; padding:.75rem 1rem; border-radius:8px 8px 0 0; }
.grn-header h5 { font-size:.88rem; font-weight:700; letter-spacing:.03em; margin:0; }
.grn-badge-complete { background:#d1fae5; color:#065f46; font-size:.62rem; padding:2px 8px; border-radius:4px; font-weight:700; }
.grn-badge-ongoing  { background:#fef3c7; color:#92400e; font-size:.62rem; padding:2px 8px; border-radius:4px; font-weight:700; }
.grn-badge-pending  { background:#fee2e2; color:#991b1b; font-size:.62rem; padding:2px 8px; border-radius:4px; font-weight:700; }
.grn-tbl thead th { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:#f8f9fa; padding:.4rem .5rem; }
.grn-tbl tbody td { font-size:.78rem; vertical-align:middle; padding:.35rem .5rem; }
.grn-tbl tbody tr:hover { background:#f5f7ff !important; }
</style>

<div class="modal-content" style="border:none;box-shadow:0 8px 40px rgba(0,0,0,.18);">
  <div class="grn-header d-flex justify-content-between align-items-center">
    <h5><i class="fa fa-clipboard-list me-2"></i><?= $title ?></h5>
    <button type="button" class="btn-close" style="filter:invert(1) brightness(2);opacity:.8;" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body p-2" style="max-height:70vh;overflow-y:auto;">
    <?php
    $query = "SELECT g.*, s.sup_name, u.username AS raised_by
              FROM kp_grn g
              LEFT JOIN kp_suppliers s ON s.sup_id = g.sup_id
              LEFT JOIN kp_users u ON u.user_id = g.user_id
              $where
              ORDER BY g.grn_id DESC LIMIT 100";
    $result = mysqli_query($connection, $query);
    $no = 0;
    if ($result && mysqli_num_rows($result) > 0): ?>
    <table class="table table-sm table-hover mb-0 grn-tbl">
      <thead>
        <tr>
          <th>#</th><th>GRN No.</th><th>PO #</th><th>Supplier</th>
          <th>Raised By</th><th>Date</th><th>Amount</th><th>Status</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):
          $no++;
          $status = (int)$row['status'];
          if ($status == 1) { $badge = '<span class="grn-badge-complete">Complete</span>'; }
          elseif ($status == 3) { $badge = '<span class="grn-badge-ongoing">Ongoing</span>'; }
          elseif ($status == 2) { $badge = '<span class="grn-badge-pending">Pending</span>'; }
          else { $badge = '<span class="grn-badge-pending">Error</span>'; }
          $amt = number_format(floatval($row['amount'] ?? 0), 2);
        ?>
        <tr>
          <td><?= $no ?></td>
          <td><strong class="text-info">#<?= (int)$row['grn_id'] ?></strong></td>
          <td class="text-muted">#<?= (int)$row['po_id'] ?></td>
          <td><?= htmlspecialchars($row['sup_name'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['raised_by'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['day'] ?? '–') ?></td>
          <td class="fw-medium"><?= $amt ?></td>
          <td><?= $badge ?></td>
          <td>
            <button class="btn btn-xs btn-outline-cyan grn-view-btn" data-id="<?= (int)$row['grn_id'] ?>">
              <i class="fa fa-eye"></i>
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
      <i class="fa fa-clipboard fa-3x mb-2" style="opacity:.2;"></i>
      <div class="small">No GRN records found.</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
$(document).off('click.grnView').on('click.grnView', '.grn-view-btn', function (e) {
  e.preventDefault();
  var id = $(this).data('id');
  // Use modal2 so we don't destroy the GRN list
  $('#load2').html('<div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div></div>');
  $('#modal2').modal('show');
  $.ajax({ url: 'kug/dash/vwgrn.php', type: 'GET', data: { id: id }, dataType: 'html' })
    .done(function (data) { $('#load2').html(data); })
    .fail(function () { $('#load2').html('<div class="modal-content"><div class="modal-body text-danger p-3">Failed to load GRN details.</div></div>'); });
});
</script>