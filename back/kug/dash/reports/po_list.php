<?php
include("funcs.php");
$user_id = $_SESSION['mwene'];
?>
<style>
.rpt-back { cursor:pointer; }
.rpt-back:hover { color:#008080 !important; }
.rpt-tbl thead th { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:#f8f9fa; padding:.4rem .5rem; }
.rpt-tbl tbody td { font-size:.78rem; vertical-align:middle; padding:.35rem .5rem; }
.rpt-tbl tbody tr:hover { background:#f5f7ff !important; }
.po-click { cursor:pointer; text-decoration:underline; }
.po-click:hover { color:#008080; }
</style>

<div class="widget">
  <div class="widget-header p-2 d-flex justify-content-between align-items-center">
    <h4 class="my-0 text-cyan" style="font-size:.84rem;">
      <span class="rpt-back text-muted me-2" onclick="kugLoad()"><i class="fa fa-arrow-left"></i></span>
      <i class="fa fa-file-invoice me-1"></i> PURCHASE ORDER LIST
    </h4>
    <div class="d-flex gap-2 align-items-center">
      <input type="text" id="rpt-po-search" class="form-control form-control-sm" placeholder="Search…" style="width:180px;font-size:.74rem;">
    </div>
  </div>
  <div class="p-2" style="overflow-x:auto;max-height:70vh;overflow-y:auto;">
    <?php
    $query = "SELECT p.*, s.sup_name, u.username AS raised_by,
                     (SELECT COUNT(*) FROM kp_po_items WHERE po_id=p.po_id) AS item_count,
                     (SELECT COALESCE(SUM(total),0) FROM kp_po_items WHERE po_id=p.po_id) AS amount
              FROM kp_po p
              LEFT JOIN kp_suppliers s ON s.sup_id = p.sup_id
              LEFT JOIN kp_users u ON u.user_id = p.user_id
              ORDER BY p.po_id DESC LIMIT 200";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0): ?>
    <table class="table table-sm table-hover mb-0 rpt-tbl" id="rpt-po-table">
      <thead>
        <tr>
          <th>PO #</th><th>Supplier</th><th>Raised By</th><th>Date</th>
          <th>Items</th><th>Amount</th><th>Type</th><th>Approval</th><th>Receiving</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):
          $appr = (int)($row['appr_status'] ?? 2);
          $rec  = (int)($row['rec_status'] ?? 2);
          $appr_badge = $appr == 1 ? '<span class="badge bg-success" style="font-size:.6rem;">Approved</span>'
                                   : '<span class="badge bg-warning text-dark" style="font-size:.6rem;">Pending</span>';
          $rec_badge  = $rec == 1 ? '<span class="badge bg-success" style="font-size:.6rem;">Received</span>'
                      : ($rec == 3 ? '<span class="badge bg-info" style="font-size:.6rem;">Partial</span>'
                                   : '<span class="badge bg-danger" style="font-size:.6rem;">Pending</span>');
          $potype = (int)($row['potype'] ?? 2);
          $type_badge = $potype == 1 ? '<span class="badge bg-info" style="font-size:.6rem;">Intl</span>'
                                     : '<span class="badge bg-secondary" style="font-size:.6rem;">Local</span>';
          $amt = number_format(floatval($row['amount'] ?? 0), 2);
        ?>
        <tr>
          <td><strong class="text-primary po-click" data-po="<?= (int)$row['po_id'] ?>">#<?= (int)$row['po_id'] ?></strong></td>
          <td><?= htmlspecialchars($row['sup_name'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['raised_by'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['po_date'] ?? $row['day'] ?? '–') ?></td>
          <td class="text-center"><span class="badge bg-secondary"><?= (int)($row['item_count'] ?? 0) ?></span></td>
          <td class="fw-medium"><?= $amt ?></td>
          <td><?= $type_badge ?></td>
          <td><?= $appr_badge ?></td>
          <td><?= $rec_badge ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
      <i class="fa fa-file-invoice fa-3x mb-2" style="opacity:.2;"></i>
      <div class="small">No purchase orders found.</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Search filter
$('#rpt-po-search').on('input', function () {
  var term = $(this).val().toLowerCase();
  $('#rpt-po-table tbody tr').each(function () {
    $(this).toggle($(this).text().toLowerCase().indexOf(term) >= 0);
  });
});

// Clickable PO → show detail in modal
$(document).off('click.poClick').on('click.poClick', '.po-click', function () {
  var po = $(this).data('po');
  $('#load').html('<div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div></div>');
  $('#modal').modal('show');
  $.ajax({ url: 'kug/dash/reports/po_detail.php', type: 'GET', data: { id: po }, dataType: 'html' })
    .done(function (d) { $('#load').addClass('modal-lg').html(d); })
    .fail(function () { $('#load').html('<div class="modal-content"><div class="modal-body text-danger p-3">Failed to load PO details.</div></div>'); });
});
</script>
